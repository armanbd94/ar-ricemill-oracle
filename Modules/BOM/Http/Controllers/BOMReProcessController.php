<?php

namespace Modules\BOM\Http\Controllers;

use Exception;
use App\Models\ItemClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Setting\Entities\Site;
use Modules\Setting\Entities\Batch;
use Modules\BOM\Entities\BomProcess;
use Modules\Product\Entities\Product;
use Modules\Material\Entities\Material;
use App\Http\Controllers\BaseController;
use Modules\Product\Entities\SiteProduct;
use Modules\Material\Entities\SiteMaterial;
use Modules\BOM\Http\Requests\BOMProcessFormRequest;

class BOMReProcessController extends BaseController
{
    public function __construct(BomProcess $model)
    {
        $this->model = $model;
    }
    
    public function index()
    {
        if(permission('bom-re-process-access')){
            $this->setPageData('Manage BOM Re Process','Manage BOM Re Process','fas fa-box',[['name' => 'Manage BOM Re Process']]);
            $batches = Batch::whereBetween('batch_start_date',[date('Y-01-01'),date('Y-12-31')])->get();
            return view('bom::bom-re-process.index',compact('batches'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('bom-re-process-access')){

                if (!empty($request->process_type)) {
                    $this->model->setProcessType($request->process_type);
                }
                if (!empty($request->batch_id)) {
                    $this->model->setBatchID($request->batch_id);
                }
                if (!empty($request->from_date)) {
                    $this->model->setFromDate($request->from_date);
                }
                if (!empty($request->to_date)) {
                    $this->model->setToDate($request->to_date);
                }


                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('bom-re-process-edit')){
                        $action .= ' <a class="dropdown-item" href="'.route("bom.re.process.edit",$value->id).'">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if(permission('bom-re-process-view')){
                        $action .= ' <a class="dropdown-item view_data" href="'.route("bom.re.process.view",$value->id).'">'.self::ACTION_BUTTON['View'].'</a>';
                    }
                    if(permission('bom-re-process-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->id . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }
                    
                    $row = [];
                    if(permission('bom-re-process-bulk-delete')){
                        $row[] = row_checkbox($value->id);//custom helper function to show the table each row checkbox
                    }
                    $row[] = $no;
                    $row[] = $value->batch_no;
                    $row[] = $value->product_name;
                    $row[] = $value->storage_site;
                    $row[] = $value->storage_location;
                    $row[] = $value->total_rice_qty;
                    $row[] = $value->total_bag_qty;
                    $row[] = date(config('settings.date_format'),strtotime($value->process_date));
                    $row[] = $value->created_by;
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


    public function create()
    {
        if(permission('bom-re-process-add')){
            $this->setPageData('BOM Re Process Form','BOM Re Process Form','fas fa-box',[['name' => 'BOM Re Process Form']]);
            $data = [
                'batches'    => Batch::whereBetween('batch_start_date',[date('Y-01-01'),date('Y-12-31')])->get(),
                'sites'      => Site::allSites(),
                'products'   => Product::where('status',1)->whereIn('category_id',[4,5])->get(),
                'classes'    => ItemClass::allItemClass()
            ];
            return view('bom::bom-re-process.create',$data);
        }else{
            return $this->access_blocked();
        }
    } 

    public function store(BOMProcessFormRequest $request)
    {
        if($request->ajax()){
            if(permission('bom-re-process-add')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $from_product                     = Product::findOrFail($request->from_product_id);
                    $bag                              = Material::findOrFail($request->bag_id);
                    $to_product                       = Product::findOrFail($request->to_product_id);
                    $to_product_qty                   = SiteProduct::where('product_id',$request->to_product_id)->sum('qty');
                    $to_product_current_stock_value   = ($to_product->cost ? $to_product->cost : 0) * ($to_product_qty ?? 0);
                    $to_product_converted_stock_value = (($from_product->cost ? $from_product->cost : 0) * $request->total_rice_qty) + (($bag->cost ? $bag->cost : 0) * $request->total_bag_qty);
                    $to_product_new_cost              = ($to_product_current_stock_value + $to_product_converted_stock_value) / (($to_product_qty ?? 0) + $request->total_rice_qty);
                    $bomProcessData                   = $this->model->create(BomProcess::bom_process_data($request,2,$from_product,$to_product,$bag,$to_product_new_cost));

                    if($bomProcessData){
                        $to_product->cost = $to_product_new_cost;
                        $to_product->update();
                        $process_item = $this->addition_subtraction_process_item_qty($request);
                        if($process_item == true)
                        {
                            $output = ['status'=>'success','message'=>'Data has been saved successfully','process_id'=>$bomProcessData->id];
                        }else{
                            $output = ['status' => 'error','message' => $process_item];
                        }
                        
                    }else{
                        $output = ['status'=>'error','message'=>'Failed to save data','purchase_id'=>''];
                    }
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollback();
                    $output = ['status' => 'error','message' => $e->getMessage()];
                }
            }else{
                $output     = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function show(int $id)
    {
        if(permission('bom-re-process-view')){
            $this->setPageData('BOM Re Process Details','BOM Re Process Details','fas fa-file',[['name'=>'BOM','link' => 'javascript::void();'],['name' => 'BOM Re Process Details']]);
            $data = $this->model->with('batch','from_site','from_location','to_site','to_location','bag_site','bag_location','bag','from_product','to_product','bag_class','product_class')->find($id);
            return view('bom::bom-re-process.details',compact('data'));
        }else{
            return $this->access_blocked();
        }
    }

    public function edit(int $id)
    {
        if(permission('bom-re-process-edit')){
            $this->setPageData('BOM Re Process Edit Form','BOM Re Process Edit Form','fas fa-edit',[['name' => 'BOM Re Process Edit Form']]);
            $bom_process = $this->model->find($id);
            $data = [
                'data'       => $bom_process,
                'batches'    => Batch::whereBetween('batch_start_date',[date('Y-01-01'),date('Y-12-31')])->get(),
                'sites'      => Site::allSites(),
                'products'   => Product::where('status',1)->whereIn('category_id',[4,5])->get(),
                'from_product_qty' => DB::table('site_product')->where([
                    'site_id'     => $bom_process->from_site_id,
                    'location_id' => $bom_process->from_location_id,
                    'product_id' => $bom_process->from_product_id,
                ])->value('qty'),
                'classes'   => ItemClass::allItemClass(),
                'bags' => DB::table('site_material as sm')
                ->select('m.id','m.material_name','m.cost','c.name as category_name','u.unit_name','u.unit_code','sm.qty')
                ->leftJoin('materials as m','sm.material_id','=','m.id')
                ->leftJoin('categories as c','m.category_id','=','c.id')
                ->leftJoin('units as u','m.unit_id','=','u.id')
                ->where([
                    'sm.site_id'     => $bom_process->bag_site_id,
                    'sm.location_id' => $bom_process->bag_location_id,
                    'm.type'         => 2
                ])->get()
            ];
            return view('bom::bom-re-process.edit',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function update(BOMProcessFormRequest $request)
    {
        if($request->ajax()){
            if(permission('bom-re-process-edit')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $bomProcessData            = $this->model->find($request->process_id);
                    $packet_rice_product       = Product::findOrFail($bomProcessData->to_product_id);
                    $packet_rice_product->cost = $bomProcessData->to_product_old_cost;
                    $packet_rice_product->update();
                    
                    if($bomProcessData)
                    {
                        $this->remove_process_item_qty($bomProcessData);
                    }

                    $from_product                     = Product::findOrFail($request->from_product_id);
                    $bag                              = Material::findOrFail($request->bag_id);
                    $to_product                       = Product::findOrFail($request->to_product_id);
                    $to_product_qty                   = SiteProduct::where('product_id',$request->to_product_id)->sum('qty');
                    $to_product_current_stock_value   = ($to_product->cost ? $to_product->cost : 0) * ($to_product_qty ?? 0);
                    $to_product_converted_stock_value = (($from_product->cost ? $from_product->cost : 0) * $request->total_rice_qty) + (($bag->cost ? $bag->cost : 0) * $request->total_bag_qty);
                    $to_product_new_cost              = ($to_product_current_stock_value + $to_product_converted_stock_value) / (($to_product_qty ?? 0) + $request->total_rice_qty);
                    $result                           = $bomProcessData->update(BomProcess::bom_process_data($request,2,$from_product,$to_product,$bag,$to_product_new_cost));
                    if($result)
                    {
                        $to_product->cost = $to_product_new_cost;
                        $to_product->update();
                        $process_item = $this->addition_subtraction_process_item_qty($request);
                        if($process_item == true)
                        {
                            $output  = $this->store_message($result, $request->process_id);
                        }else{
                            $output = ['status' => 'error','message' => $process_item];
                        }
                    }else{
                        $output = ['status' => 'error','message' => 'Failed to update data!'];
                    }
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollback();
                    $output = ['status' => 'error','message' => $e->getMessage()];
                }
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
            if(permission('bom-re-process-delete')){
                DB::beginTransaction();
                try {
                    $bomProcessData = $this->model->find($request->id);
                    $packet_rice_product       = Product::findOrFail($bomProcessData->to_product_id);
                    $packet_rice_product->cost = $bomProcessData->to_product_old_cost;
                    $packet_rice_product->update();
                    $process_item_qty_remove = $this->remove_process_item_qty($bomProcessData);
                    if($process_item_qty_remove == true)
                    {
                        $result = $bomProcessData->delete();
                        if($result)
                        {
                            $output = ['status' => 'success','message' => 'Data has been deleted successfully'];
                            
                        }else{
                            $output = ['status' => 'error','message' => 'Failed to delete data'];
                        }
                    }else{
                        $output = ['status'=>'error','message'=> $process_item_qty_remove];
                    }
                    
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                    $output = ['status'=>'error','message'=>$e->getMessage()];
                }
                return response()->json($output);
            }else{
                $output = $this->access_blocked();
            }
            return response()->json($output);
        }else{
            return response()->json($this->access_blocked());
        }
    }

    public function bulk_delete(Request $request)
    {
        if($request->ajax()){
            if(permission('bom-re-process-bulk-delete')){
                DB::beginTransaction();
                try {
                    foreach ($request->ids as $id) {
                        $bomProcessData = $this->model->find($id);
                        $packet_rice_product       = Product::findOrFail($bomProcessData->to_product_id);
                        $packet_rice_product->cost = $bomProcessData->to_product_old_cost;
                        $packet_rice_product->update();
                        $this->remove_process_item_qty($bomProcessData);
                    }
                    $result = $this->model->destroy($request->ids);
                    if($result)
                    {
                        $output = ['status' => 'success','message' => 'Data has been deleted successfully'];
                    }else{
                        $output = ['status' => 'error','message' => 'Failed to delete data'];
                    }
                DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                    $output = ['status'=>'error','message'=>$e->getMessage()];
                }
            }else{
                $output = $this->access_blocked();
            }
            return response()->json($output);
        }else{
            return response()->json($this->access_blocked());
        }
    }

    protected function addition_subtraction_process_item_qty($data)
    {
        try {
            $bag = Material::find($data->bag_id);
            if($bag)
            {
                $bag->qty -= $data->bag_required_qty;
                $bag->update();
            }
            $from_site_bag = SiteMaterial::where([
                ['site_id',$data->bag_site_id],
                ['location_id',$data->bag_location_id],
                ['material_id',$data->bag_id],
            ])->first();
            
            if($from_site_bag)
            {
                $from_site_bag->qty -= $data->bag_required_qty;
                $from_site_bag->update();
            }
            //Subtract Product From Silo
            $from_site_product = SiteProduct::where([
                ['site_id',$data->from_site_id],
                ['location_id',$data->from_location_id],
                ['product_id',$data->from_product_id],
            ])->first();
            
            if($from_site_product)
            {
                $from_site_product->qty -= $data->total_rice_qty;
                $from_site_product->update();
            }
            //Add Packet Rice Into Stock
            $to_site_product = SiteProduct::where([
                ['site_id',$data->to_site_id],
                ['location_id',$data->to_location_id],
                ['product_id',$data->to_product_id],
            ])->first();
            
            if($to_site_product)
            {
                $to_site_product->qty += $data->total_rice_qty;
                $to_site_product->update();
            }else{
                SiteProduct::create([
                    'site_id'     => $data->to_site_id,
                    'location_id' => $data->to_location_id,
                    'product_id'  => $data->to_product_id,
                    'qty'         => $data->total_rice_qty
                ]);
            }
            return true;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
        
    }

    protected function remove_process_item_qty($data)
    {
        try {
            $bag = Material::find($data->bag_id);
            if($bag)
            {
                $bag->qty += $data->bag_required_qty;
                $bag->update();
            }
            $from_site_bag = SiteMaterial::where([
                ['site_id',$data->bag_site_id],
                ['location_id',$data->bag_location_id],
                ['material_id',$data->bag_id],
            ])->first();
            
            if($from_site_bag)
            {
                $from_site_bag->qty += $data->bag_required_qty;
                $from_site_bag->update();
            }
            //Subtract Product From Silo
            $from_site_product = SiteProduct::where([
                ['site_id',$data->from_site_id],
                ['location_id',$data->from_location_id],
                ['product_id',$data->from_product_id],
            ])->first();
            
            if($from_site_product)
            {
                $from_site_product->qty += $data->total_rice_qty;
                $from_site_product->update();
            }
            //Add Packet Rice Into Stock
            $to_site_product = SiteProduct::where([
                ['site_id',$data->to_site_id],
                ['location_id',$data->to_location_id],
                ['product_id',$data->to_product_id],
            ])->first();
            
            if($to_site_product)
            {
                $to_site_product->qty -= $data->total_rice_qty;
                $to_site_product->update();
            }
            return true;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
}
