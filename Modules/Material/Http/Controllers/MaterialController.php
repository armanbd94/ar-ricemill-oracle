<?php

namespace Modules\Material\Http\Controllers;

use Keygen\Keygen;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Material\Entities\Material;
use App\Http\Controllers\BaseController;
use Modules\Material\Entities\SiteMaterial;
use Modules\Material\Http\Requests\MaterialFormRequest;

class MaterialController extends BaseController
{
    public function __construct(Material $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('material-access')){
            $this->setPageData('Manage Material','Manage Material','fas fa-toolbox',[['name' => 'Manage Material']]);
            $data = [
                'units'      => Unit::where('status',1)->get(),
                'taxes'      => Tax::activeTaxes(),
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
                    $row[] = $value->material_name;
                    $row[] = $value->material_code;
                    $row[] = $value->category->name;
                    $row[] = MATERIAL_TYPE[$value->type];
                    $row[] = $value->cost ? number_format($value->cost,2,'.',',') : 0;
                    $row[] = $value->unit->unit_name;
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
                    $collection = collect($request->validated())->except('alert_qty','tax_id');
                    $alert_qty  = $request->alert_qty ? $request->alert_qty : 0;
                    $tax_id     = ($request->tax_id != 0) ? $request->tax_id : null;
                    $collection = $collection->merge(compact('alert_qty','tax_id'));
                    $collection = $this->track_data($collection,$request->update_id);
                    $result     = $this->model->updateOrCreate(['id'=>$request->update_id],$collection->all());
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
                $material = $this->model->with('unit')->findOrFail($request->id);
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
                $output = $this->unauthorized();
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
                SiteMaterial::where('material_id',$request->id)->delete();
                $material  = $this->model->find($request->id)->delete();
                $output   = $this->delete_message($material);
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
                SiteMaterial::whereIn('material_id',$request->ids)->delete();
                $material  = $this->model->destroy($request->ids);
                $output   = $this->bulk_delete_message($material);
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

    public function material_stock_data(Request $request)
    {
        $material_stock = DB::table('site_material')->where([
            'site_id'     => $request->site_id,
            'location_id' => $request->location_id,
            'material_id' => $request->material_id,
        ])->first();

        return $material_stock ? $material_stock->qty : 0;
    }

    public function material_list(Request $request)
    {
        $materials = DB::table('site_material as sm')
        ->select('m.id','m.material_name','c.name as category_name','u.unit_name','u.unit_code','sm.qty')
        ->leftJoin('materials as m','sm.material_id','=','m.id')
        ->leftJoin('categories as c','m.category_id','=','c.id')
        ->leftJoin('units as u','m.unit_id','=','u.id')
        ->where([
            'sm.site_id'     => $request->site_id,
            'sm.location_id' => $request->location_id,
            'm.type'         => 1
        ])->get();

        $output = '<option value="">Select Please</option>';
        if(!$materials->isEmpty())
        {
            foreach ($materials as $value) {
                $output .= '<option value="'.$value->id.'" data-stockqty="'.$value->qty.'" data-category="'.$value->category_name.'" data-unitname="'.$value->unit_name.'" data-unitcode="'.$value->unit_code.'">'.$value->material_name.'</option>';
            }
        }
        return $output;
    }
    public function bag_list(Request $request)
    {
        $materials = DB::table('site_material as sm')
        ->select('m.id','m.material_name','m.cost','c.name as category_name','u.unit_name','u.unit_code','sm.qty')
        ->leftJoin('materials as m','sm.material_id','=','m.id')
        ->leftJoin('categories as c','m.category_id','=','c.id')
        ->leftJoin('units as u','m.unit_id','=','u.id')
        ->where([
            'sm.site_id'     => $request->site_id,
            'sm.location_id' => $request->location_id,
            'm.type'         => 2
        ])->get();

        $output = '<option value="">Select Please</option>';
        if(!$materials->isEmpty())
        {
            foreach ($materials as $value) {
                $output .= '<option value="'.$value->id.'" data-cost="'.($value->cost ? $value->cost : 0 ).'" data-stockqty="'.$value->qty.'" data-category="'.$value->category_name.'" data-unitname="'.$value->unit_name.'" data-unitcode="'.$value->unit_code.'">'.$value->material_name.'</option>';
            }
        }
        return $output;
    }


}
