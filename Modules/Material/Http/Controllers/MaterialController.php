<?php

namespace Modules\Material\Http\Controllers;

use Keygen\Keygen;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Material\Entities\Material;
use Modules\Setting\Entities\Warehouse;
use App\Http\Controllers\BaseController;
use App\Traits\UploadAble;
use Modules\Material\Entities\WarehouseMaterial;
use Modules\Material\Http\Requests\MaterialFormRequest;

class MaterialController extends BaseController
{
    use UploadAble;
    public function __construct(Material $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('material-access')){
            $this->setPageData('Material','Material','fas fa-toolbox',[['name' => 'Material']]);
            $data = [
                'units'      => Unit::where('status',1)->get(),
                'taxes'      => Tax::activeTaxes(),
                'warehouses' => Warehouse::activeWarehouses(),
                'categories' => Category::allMaterialCategories(),
            ];
            return view('material::index',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('material-access')){

                if (!empty($request->material_name)) {
                    $this->model->setMaterialName($request->material_name);
                }
                if (!empty($request->material_code)) {
                    $this->model->setMaterialCode($request->material_code);
                }
                if (!empty($request->status)) {
                    $this->model->setStatus($request->status);
                }
                if (!empty($request->category_id)) {
                    $this->model->setCategoryID($request->category_id);
                }

                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('material-edit')){
                        $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if(permission('material-view')){
                        $action .= ' <a class="dropdown-item view_data" data-id="' . $value->id . '" data-name="' . $value->material_name . '">'.self::ACTION_BUTTON['View'].'</a>';
                    }
                    if(permission('material-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->material_name . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }

                    $row = [];
                    if(permission('material-bulk-delete')){
                        $row[] = row_checkbox($value->id);//custom helper function to show the table each row checkbox
                    }
                    $row[] = $no;
                    $row[] = $this->table_image(MATERIAL_IMAGE_PATH,$value->material_image,$value->material_name);
                    $row[] = $value->material_name;
                    $row[] = $value->material_code;
                    $row[] = $value->category->name;
                    $row[] = MATERIAL_TYPE[$value->type];
                    $row[] = $value->cost ? number_format($value->cost,2,'.',',') : 0;
                    $row[] = $value->unit->unit_name;
                    $row[] = $value->purchase_unit->unit_name;
                    $row[] = $value->qty ? $value->qty : "<span class='label label-rounded label-danger'>0</span>";
                    $row[] = $value->alert_qty ? $value->alert_qty : "<span class='label label-rounded label-danger'>0</span>";
                    $row[] = permission('material-edit') ? change_status($value->id,$value->status, $value->material_name) : STATUS_LABEL[$value->status];
                    $row[] = action_button($action);//custom helper function for action button
                    $data[] = $row;
                }
                return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
                $this->model->count_filtered(), $data);
            }
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function store_or_update_data(MaterialFormRequest $request)
    {
        if($request->ajax()){
            if(permission('material-add') || permission('material-edit')){
                DB::beginTransaction();
                try {
                    $collection = collect($request->validated())->except('alert_qty','tax_id','material_image');
                    if(empty($request->update_id)){
                        $cost       = $request->opening_cost ? $request->opening_cost : 0;
                        $collection = $collection->merge(compact('cost'));
                    }
                    $alert_qty  = $request->alert_qty ? $request->alert_qty : 0;
                    $tax_id     = ($request->tax_id != 0) ? $request->tax_id : null;
                    $has_opening_stock     = $request->has_opening_stock ? $request->has_opening_stock : 2;
                    $opening_cost     = $request->opening_cost ? $request->opening_cost : 0;
                    $material_image = $request->old_material_image;
                    if($request->hasFile('material_image')){
                        $material_image  = $this->upload_file($request->file('material_image'),MATERIAL_IMAGE_PATH);
                        if(!empty($request->old_material_image)){
                            $this->delete_file($request->old_material_image, MATERIAL_IMAGE_PATH);
                        }  
                    }
                    $collection = $collection->merge(compact('material_image','has_opening_stock','opening_cost','alert_qty','tax_id'));
                    $collection = $this->track_data($collection,$request->update_id);
                    if($request->update_id)
                    {
                        $material_old_data = $this->model->find($request->update_id);
                        if($has_opening_stock == 1)
                        {
                            if(!empty($material_old_data->opening_stock_qty))
                            {
                                $old_warehouse_material = WarehouseMaterial::where([
                                    ['warehouse_id',$material_old_data->opening_warehouse_id],
                                    ['material_id',$material_old_data->id],
                                    ['qty','<>',0]
                                    ])->first();
                                if($old_warehouse_material){
                                    $old_warehouse_material->qty -= $material_old_data->opening_stock_qty;
                                    $old_warehouse_material->update();
                                }

                                $material_old_data->qty -= $material_old_data->opening_stock_qty; 
                                $material_old_data->update();

                                $material_new_data = $this->model->find($request->update_id);
                                $material_new_data->qty += $request->opening_stock_qty;
                                $material_new_data->update();

                                $new_warehouse_material = WarehouseMaterial::where(['warehouse_id' =>$request->opening_warehouse_id,'material_id' => $request->update_id])->first();
                                if($new_warehouse_material)
                                {
                                    $new_warehouse_material->qty += $request->opening_stock_qty;
                                    $new_warehouse_material->update();
                                }else{
                                    WarehouseMaterial::create([
                                        'warehouse_id' => $request->opening_warehouse_id,
                                        'material_id' => $request->update_id,
                                        'qty' => $request->opening_stock_qty,
                                    ]);
                                }
                            }else{
                                $material_old_data->qty += $request->opening_stock_qty; 
                                $material_old_data->update();

                                $new_warehouse_material = WarehouseMaterial::where(['warehouse_id' =>$request->opening_warehouse_id,'material_id' => $request->update_id])->first();
                                if($new_warehouse_material)
                                {
                                    $new_warehouse_material->qty += $request->opening_stock_qty;
                                    $new_warehouse_material->update();
                                }else{
                                    WarehouseMaterial::create([
                                        'warehouse_id' => $request->opening_warehouse_id,
                                        'material_id' => $request->update_id,
                                        'qty' => $request->opening_stock_qty,
                                    ]);
                                }
                            }
                        }else{
                            if(!empty($material_old_data->opening_stock_qty))
                            {
                                $old_warehouse_material = WarehouseMaterial::where([
                                    ['warehouse_id',$material_old_data->opening_warehouse_id],
                                    ['material_id', $material_old_data->id],
                                    ['qty','<>',0]
                                    ])->first();
                                if($old_warehouse_material){
                                    $old_warehouse_material->qty -= $material_old_data->opening_stock_qty;
                                    $old_warehouse_material->update();
                                }
                                $material_old_data->qty -= $material_old_data->opening_stock_qty; 
                                $material_old_data->update();
                            }
                        }
                        $result     = $this->model->updateOrCreate(['id'=>$request->update_id],$collection->all());
                    }else{
                        
                        $result     = $this->model->updateOrCreate(['id'=>$request->update_id],$collection->all());
                        if($has_opening_stock == 1)
                        {
                            $material = $this->model->find($result->id);
                            $material->qty = $request->opening_stock_qty;
                            $material->update();
                            WarehouseMaterial::create([
                                'warehouse_id' => $request->opening_warehouse_id,
                                'material_id' => $result->id,
                                'qty' => $request->opening_stock_qty,
                            ]);
                        }
                    }
                    $output     = $this->store_message($result, $request->update_id);
                    DB::commit();
                }catch (\Throwable $th) {
                   DB::rollback();
                   $output = ['status' => 'error','message' => $th->getMessage()];
                }
            }else{
                $output     = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function show(Request $request)
    {
        if($request->ajax()){
            if(permission('material-view')){
                $material = $this->model->with('unit','purchase_unit')->findOrFail($request->id);
                return view('material::view-modal-data',compact('material'))->render();
            }
        }
    }

    public function edit(Request $request)
    {
        if($request->ajax()){
            if(permission('material-edit')){
                $data   = $this->model->findOrFail($request->id);
                $output = $this->data_message($data); //if data found then it will return data otherwise return error message
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function delete(Request $request)
    {
        if($request->ajax()){
            if(permission('material-delete')){
                WarehouseMaterial::where('material_id',$request->id)->delete();
                $material  = $this->model->find($request->id);
                $old_image = $material ? $material->material_image : '';
                $result    = $material->delete();
                if($result && $old_image != ''){
                    $this->delete_file($old_image, MATERIAL_IMAGE_PATH);
                }
                $output   = $this->delete_message($result);
            }else{
                $output   = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function bulk_delete(Request $request)
    {
        if($request->ajax()){
            if(permission('material-bulk-delete')){
                WarehouseMaterial::whereIn('material_id',$request->ids)->delete();
                foreach ($request->ids as $id) {
                    $material  = $this->model->find($request->id);
                    $old_image = $material ? $material->material_image : '';
                    $result    = $material->delete();
                    if($result && $old_image != ''){
                        $this->delete_file($old_image, MATERIAL_IMAGE_PATH);
                    }
                }
                $output   = $this->bulk_delete_message($result);
            }else{
                $output   = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function change_status(Request $request)
    {
        if($request->ajax()){
            if(permission('material-edit')){
                $result   = $this->model->find($request->id)->update(['status' => $request->status]);
                $output   = $result ? ['status' => 'success','message' => 'Status Has Been Changed Successfully']
                : ['status' => 'error','message' => 'Failed To Change Status'];
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    //Generate Material Code
    public function generateMaterialCode()
    {
        $code = Keygen::numeric(8)->generate();
        //Check Material Code ALready Exist or Not
        if(DB::table('materials')->where('material_code',$code)->exists())
        {
            $this->generateMaterialCode();
        }else{
            return response()->json($code);
        }
    }

    //Purchase Form Material Auto Complete Search Data
    public function autocomplete_search_material(Request $request)
    {
        if(!empty($request->search)){
            $data = $this->model->where('material_name', 'like','%'.$request->search.'%')
                                ->orWhere('material_code', 'like','%'.$request->search.'%')
                                ->get();
            $output = array();
            if(!$data->isEmpty())
            {
                foreach($data as $row)
                {
                    $temp_array             = array();
                    $temp_array['code']     = $row->material_code;
                    $temp_array['value']    = $row->material_code.' - '.$row->material_name;
                    $temp_array['label']    = $row->material_code.' - '.$row->material_name;
                    $output[]               = $temp_array;
                }
            } else{
                $output['value']            = '';
                $output['label']            = 'No Record Found';
            }
            return $output; 
        }
    }

    //Purchase Form On Select Material Fetch All Data of It
    public function search_material(Request $request)
    {

        $material_data = $this->model->where('material_code', $request['data'])->first();
        if($material_data)
        {
            $material['id']         = $material_data->id;
            $material['name']       = $material_data->material_name;
            $material['code']       = $material_data->material_code;
            $material['cost']       = $material_data->cost;
            $material['tax_rate']   = $material_data->tax->rate ? $material_data->tax->rate : 0;
            $material['tax_name']   = $material_data->tax->name;
            $material['tax_method'] = $material_data->tax_method;

            $units = Unit::where('base_unit',$material_data->unit_id)->orWhere('id',$material_data->unit_id)->get();
            $unit_name            = [];
            $unit_operator        = [];
            $unit_operation_value = [];
            if($units)
            {
                foreach ($units as $unit) {
                    if($material_data->purchase_unit_id == $unit->id)
                    {
                        array_unshift($unit_name,$unit->unit_name);
                        array_unshift($unit_operator,$unit->operator);
                        array_unshift($unit_operation_value,$unit->operation_value);
                    }else{
                        $unit_name           [] = $unit->unit_name;
                        $unit_operator       [] = $unit->operator;
                        $unit_operation_value[] = $unit->operation_value;
                    }
                }
            }
            $material['unit_name'] = implode(',',$unit_name).',';
            $material['unit_operator'] = implode(',',$unit_operator).',';
            $material['unit_operation_value'] = implode(',',$unit_operation_value).',';
            return $material;
        }
    }

    //Purchase Form On Select Material Fetch All Data of It
    public function search_material_by_id(Request $request)
    {

        $material_data = $this->model->where('id', $request->data)->first();
        if($material_data)
        {
            $material['id']         = $material_data->id;
            $material['name']       = $material_data->material_name;
            $material['code']       = $material_data->material_code;
            $material['cost']       = $material_data->cost;
            $material['tax_rate']   = $material_data->tax->rate ? $material_data->tax->rate : 0;
            $material['tax_name']   = $material_data->tax->name;
            $material['tax_method'] = $material_data->tax_method;

            $units = Unit::where('base_unit',$material_data->unit_id)->orWhere('id',$material_data->unit_id)->get();
            $unit_name            = [];
            $unit_operator        = [];
            $unit_operation_value = [];
            if($units)
            {
                foreach ($units as $unit) {
                    if($material_data->purchase_unit_id == $unit->id)
                    {
                        array_unshift($unit_name,$unit->unit_name);
                        array_unshift($unit_operator,$unit->operator);
                        array_unshift($unit_operation_value,$unit->operation_value);
                    }else{
                        $unit_name           [] = $unit->unit_name;
                        $unit_operator       [] = $unit->operator;
                        $unit_operation_value[] = $unit->operation_value;
                    }
                }
            }
            $material['unit_name'] = implode(',',$unit_name).',';
            $material['unit_operator'] = implode(',',$unit_operator).',';
            $material['unit_operation_value'] = implode(',',$unit_operation_value).',';
            return $material;
        }
    }

    //Product Wise Material List Show In Production Form
    public function warehouse_wise_materials(Request $request)
    {
        if($request->ajax())
        {
            $materials = WarehouseMaterial::with('material')->where([['warehouse_id',$request->warehouse_id],['qty','>',0]])->get();
            $output = '';
            if(!$materials->isEmpty())
            {
                $output .= '<option value="">Select Please</option>';
                foreach ($materials as $key => $value) {
                    $output .= '<option value="'.$value->material_id.'" data-unitid="'.$value->material->unit_id.'" 
                    data-unitname="'.$value->material->unit->unit_name.'"  data-qty="'.$value->qty.'">'.$value->material->material_name.'</option>';
                }
            }
            return $output;
        }
    }


}
