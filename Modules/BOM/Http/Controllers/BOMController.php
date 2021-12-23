<?php

namespace Modules\BOM\Http\Controllers;

use Exception;
use App\Models\Category;
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
use Modules\BuildDisassembly\Entities\SiloProduct;
use Modules\BOM\Http\Requests\BOMProcessFormRequest;

class BOMController extends BaseController
{
    public function __construct(BomProcess $model)
    {
        $this->model = $model;
    }
    
    public function index()
    {
        if(permission('bom-process-access')){
            $this->setPageData('Manage BOM Process','Manage BOM Process','fas fa-box',[['name' => 'Manage BOM Process']]);
            $batches = Batch::whereBetween('batch_start_date',[date('Y-01-01'),date('Y-12-31')])->get();
            return view('bom::bom-process.index',compact('batches'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('bom-process-access')){

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
                    if(permission('bom-process-edit')){
                        $action .= ' <a class="dropdown-item" href="'.route("bom.process.edit",$value->id).'">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if(permission('bom-process-view')){
                        $action .= ' <a class="dropdown-item view_data" href="'.route("bom.process.view",$value->id).'">'.self::ACTION_BUTTON['View'].'</a>';
                    }
                    if(permission('bom-process-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->id . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }
                    
                    $row = [];
                    if(permission('bom-process-bulk-delete')){
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
        if(permission('bom-process-add')){
            $this->setPageData('BOM Process Form','BOM Process Form','fas fa-box',[['name' => 'BOM Process Form']]);
            $data = [
                'batches'    => Batch::whereBetween('batch_start_date',[date('Y-01-01'),date('Y-12-31')])->get(),
                'sites'      => Site::allSites(),
                'products'   => Product::where([['status',1],['category_id','!=',3]])->get(),
                'silo_products'   => DB::table('silo_products as sp')
                ->select('sp.qty','p.id','p.name')
                ->join('products as p','sp.product_id','=','p.id')->get(),
                'categories' => Category::allProductCategories(),
            ];
            return view('bom::bom-process.create',$data);
        }else{
            return $this->access_blocked();
        }
    } 

    public function store(BOMProcessFormRequest $request)
    {
        if($request->ajax()){
            if(permission('bom-process-add')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $bomProcessData  = $this->model->create([
                        'process_type'         => 1,
                        'memo_no'              => $request->memo_no,
                        'batch_id'             => $request->batch_id,
                        'process_number'       => $request->process_number,
                        'to_product_id'        => $request->to_product_id,
                        'to_site_id'           => $request->to_site_id,
                        'to_location_id'       => $request->to_location_id,
                        'from_product_id'      => $request->from_product_id,
                        'item_class_id'        => $request->item_class_id,
                        'from_site_id'         => $request->from_site_id,
                        'from_location_id'     => $request->from_location_id,
                        'product_particular'   => $request->product_particular,
                        'product_per_unit_qty' => $request->product_per_unit_qty,
                        'product_required_qty' => $request->product_required_qty,
                        'bag_site_id'          => $request->bag_site_id,
                        'bag_location_id'      => $request->bag_location_id,
                        'bag_id'               => $request->bag_id,
                        'bag_class_id'         => $request->bag_class_id,
                        'bag_particular'       => $request->bag_particular,
                        'bag_per_unit_qty'     => $request->bag_per_unit_qty,
                        'bag_required_qty'     => $request->bag_required_qty,
                        'total_rice_qty'       => $request->total_rice_qty,
                        'total_bag_qty'        => $request->total_bag_qty,
                        'process_date'         => $request->process_date,
                        'created_by'           => auth()->user()->name
                    ]);

                    if($bomProcessData){
                        //Subtract Bag From Stock
                        $bag = Material::find($bomProcessData->bag_id);
                        if($bag)
                        {
                            $bag->qty -= $bomProcessData->bag_required_qty;
                            $bag->update();
                        }
                        $from_site_bag = SiteMaterial::where([
                            ['site_id',$bomProcessData->bag_site_id],
                            ['location_id',$bomProcessData->bag_location_id],
                            ['material_id',$bomProcessData->bag_id],
                        ])->first();
                        
                        if($from_site_bag)
                        {
                            $from_site_bag->qty -= $bomProcessData->bag_required_qty;
                            $from_site_bag->update();
                        }
                        //Subtract Product From Silo
                        $from_site_product = SiteProduct::where([
                            ['site_id',$bomProcessData->from_site_id],
                            ['location_id',$bomProcessData->from_location_id],
                            ['product_id',$bomProcessData->from_product_id],
                        ])->first();
                        
                        if($from_site_product)
                        {
                            $from_site_product->qty -= $bomProcessData->total_rice_qty;
                            $from_site_product->update();
                        }
                        //Add Packet Rice Into Stock
                        $to_site_product = SiteProduct::where([
                            ['site_id',$bomProcessData->to_site_id],
                            ['location_id',$bomProcessData->to_location_id],
                            ['product_id',$bomProcessData->to_product_id],
                        ])->first();
                        
                        if($to_site_product)
                        {
                            $to_site_product->qty += $bomProcessData->total_rice_qty;
                            $to_site_product->update();
                        }else{
                            SiteProduct::create([
                                'site_id'     => $bomProcessData->to_site_id,
                                'location_id' => $bomProcessData->to_location_id,
                                'product_id'  => $bomProcessData->to_product_id,
                                'qty'         => $bomProcessData->total_rice_qty
                            ]);
                        }
                        $output = ['status'=>'success','message'=>'Data has been saved successfully','process_id'=>$bomProcessData->id];
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
        if(permission('bom-process-view')){
            $this->setPageData('BOM Process Details','BOM Process Details','fas fa-file',[['name'=>'BOM','link' => 'javascript::void();'],['name' => 'BOM Process Details']]);
            $data = $this->model->with('batch','from_site','from_location','to_site','to_location','bag_site','bag_location','bag','from_product','to_product')->find($id);
            return view('bom::bom-process.details',compact('data'));
        }else{
            return $this->access_blocked();
        }
    }

    public function edit(int $id)
    {
        if(permission('bom-process-edit')){
            $this->setPageData('BOM Process Edit Form','BOM Process Edit Form','fas fa-edit',[['name' => 'BOM Process Edit Form']]);
            $bom_process = $this->model->find($id);
            $data = [
                'data'       => $bom_process,
                'batches'    => Batch::whereBetween('batch_start_date',[date('Y-01-01'),date('Y-12-31')])->get(),
                'sites'      => Site::allSites(),
                'products'   => Product::where([['status',1],['category_id','!=',3]])->get(),
                'silo_products'   => DB::table('silo_products as sp')
                ->select('sp.qty','p.id','p.name')
                ->join('products as p','sp.product_id','=','p.id')->get(),
                'categories' => Category::allProductCategories(),
                'bags' => DB::table('site_material as sm')
                ->select('m.id','m.material_name','c.name as category_name','u.unit_name','u.unit_code','sm.qty')
                ->leftJoin('materials as m','sm.material_id','=','m.id')
                ->leftJoin('categories as c','m.category_id','=','c.id')
                ->leftJoin('units as u','m.unit_id','=','u.id')
                ->where([
                    'sm.site_id'     => $bom_process->bag_site_id,
                    'sm.location_id' => $bom_process->bag_location_id,
                    'm.type'         => 2
                ])->get()
            ];
            return view('bom::bom-process.edit',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function update(BOMProcessFormRequest $request)
    {
        if($request->ajax()){
            if(permission('bom-process-edit')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $bomProcessData = $this->model->find($request->process_id);

                    $bom_process_data = [
                        'process_type'         => 1,
                        'memo_no'              => $request->memo_no,
                        'batch_id'             => $request->batch_id,
                        'process_number'       => $request->process_number,
                        'to_product_id'        => $request->to_product_id,
                        'to_site_id'           => $request->to_site_id,
                        'to_location_id'       => $request->to_location_id,
                        'from_product_id'      => $request->from_product_id,
                        'item_class_id'        => $request->item_class_id,
                        'from_site_id'         => $request->from_site_id,
                        'from_location_id'     => $request->from_location_id,
                        'product_particular'   => $request->product_particular,
                        'product_per_unit_qty' => $request->product_per_unit_qty,
                        'product_required_qty' => $request->product_required_qty,
                        'bag_site_id'          => $request->bag_site_id,
                        'bag_location_id'      => $request->bag_location_id,
                        'bag_id'               => $request->bag_id,
                        'bag_class_id'         => $request->bag_class_id,
                        'bag_particular'       => $request->bag_particular,
                        'bag_per_unit_qty'     => $request->bag_per_unit_qty,
                        'bag_required_qty'     => $request->bag_required_qty,
                        'total_rice_qty'       => $request->total_rice_qty,
                        'total_bag_qty'        => $request->total_bag_qty,
                        'process_date'         => $request->process_date,
                        'modified_by'         => auth()->user()->name
                    ];
                    if($bomProcessData)
                    {
                        $bag = Material::find($bomProcessData->bag_id);
                        if($bag)
                        {
                            $bag->qty += $bomProcessData->bag_required_qty;
                            $bag->update();
                        }
                        $from_site_bag = SiteMaterial::where([
                            ['site_id',$bomProcessData->bag_site_id],
                            ['location_id',$bomProcessData->bag_location_id],
                            ['material_id',$bomProcessData->bag_id],
                        ])->first();
                        
                        if($from_site_bag)
                        {
                            $from_site_bag->qty += $bomProcessData->bag_required_qty;
                            $from_site_bag->update();
                        }
                        //Subtract Product From Silo
                        $from_site_product = SiteProduct::where([
                            ['site_id',$bomProcessData->from_site_id],
                            ['location_id',$bomProcessData->from_location_id],
                            ['product_id',$bomProcessData->from_product_id],
                        ])->first();
                        
                        if($from_site_product)
                        {
                            $from_site_product->qty += $bomProcessData->total_rice_qty;
                            $from_site_product->update();
                        }
                        //Add Packet Rice Into Stock
                        $to_site_product = SiteProduct::where([
                            ['site_id',$bomProcessData->to_site_id],
                            ['location_id',$bomProcessData->to_location_id],
                            ['product_id',$bomProcessData->to_product_id],
                        ])->first();
                        
                        if($to_site_product)
                        {
                            $to_site_product->qty -= $bomProcessData->total_rice_qty;
                            $to_site_product->update();
                        }
                    }

                    $result = $bomProcessData->update($bom_process_data);
                    if($result)
                    {
                        $bag = Material::find($request->bag_id);
                        if($bag)
                        {
                            $bag->qty -= $request->bag_required_qty;
                            $bag->update();
                        }
                        $from_site_bag = SiteMaterial::where([
                            ['site_id',$request->bag_site_id],
                            ['location_id',$request->bag_location_id],
                            ['material_id',$request->bag_id],
                        ])->first();
                        
                        if($from_site_bag)
                        {
                            $from_site_bag->qty -= $request->bag_required_qty;
                            $from_site_bag->update();
                        }
                        //Subtract Product From Silo
                        $from_site_product = SiteProduct::where([
                            ['site_id',$request->from_site_id],
                            ['location_id',$request->from_location_id],
                            ['product_id',$request->from_product_id],
                        ])->first();
                        
                        if($from_site_product)
                        {
                            $from_site_product->qty -= $request->total_rice_qty;
                            $from_site_product->update();
                        }
                        //Add Packet Rice Into Stock
                        $to_site_product = SiteProduct::where([
                            ['site_id',$request->to_site_id],
                            ['location_id',$request->to_location_id],
                            ['product_id',$request->to_product_id],
                        ])->first();
                        
                        if($to_site_product)
                        {
                            $to_site_product->qty += $request->total_rice_qty;
                            $to_site_product->update();
                        }else{
                            SiteProduct::create([
                                'site_id'     => $request->to_site_id,
                                'location_id' => $request->to_location_id,
                                'product_id'  => $request->to_product_id,
                                'qty'         => $request->total_rice_qty
                            ]);
                        }
                    }
                    $output  = $this->store_message($result, $request->process_id);
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
            if(permission('bom-process-delete')){
                DB::beginTransaction();
                try {
                    $bomProcessData = $this->model->find($request->id);
                    //Subtract Bag From Stock
                    $bag = Material::find($bomProcessData->bag_id);
                    if($bag)
                    {
                        $bag->qty += $bomProcessData->bag_required_qty;
                        $bag->update();
                    }
                    $from_site_bag = SiteMaterial::where([
                        ['site_id',$bomProcessData->bag_site_id],
                        ['location_id',$bomProcessData->bag_location_id],
                        ['material_id',$bomProcessData->bag_id],
                    ])->first();
                    
                    if($from_site_bag)
                    {
                        $from_site_bag->qty += $bomProcessData->bag_required_qty;
                        $from_site_bag->update();
                    }
                    //Subtract Product From Silo
                    $from_site_product = SiteProduct::where([
                        ['site_id',$bomProcessData->from_site_id],
                        ['location_id',$bomProcessData->from_location_id],
                        ['product_id',$bomProcessData->from_product_id],
                    ])->first();
                    
                    if($from_site_product)
                    {
                        $from_site_product->qty += $bomProcessData->total_rice_qty;
                        $from_site_product->update();
                    }
                    //Add Packet Rice Into Stock
                    $to_site_product = SiteProduct::where([
                        ['site_id',$bomProcessData->to_site_id],
                        ['location_id',$bomProcessData->to_location_id],
                        ['product_id',$bomProcessData->to_product_id],
                    ])->first();
                    
                    if($to_site_product)
                    {
                        $to_site_product->qty -= $bomProcessData->total_rice_qty;
                        $to_site_product->update();
                    }
                    $result = $bomProcessData->delete();
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
            if(permission('bom-process-bulk-delete')){
                DB::beginTransaction();
                try {
                    foreach ($request->ids as $id) {
                        $bomProcessData = $this->model->find($id);
                        //Subtract Bag From Stock
                        $bag = Material::find($bomProcessData->bag_id);
                        if($bag)
                        {
                            $bag->qty += $bomProcessData->bag_required_qty;
                            $bag->update();
                        }
                        $from_site_bag = SiteMaterial::where([
                            ['site_id',$bomProcessData->bag_site_id],
                            ['location_id',$bomProcessData->bag_location_id],
                            ['material_id',$bomProcessData->bag_id],
                        ])->first();
                        
                        if($from_site_bag)
                        {
                            $from_site_bag->qty += $bomProcessData->bag_required_qty;
                            $from_site_bag->update();
                        }
                        //Subtract Product From Silo
                        $from_site_product = SiteProduct::where([
                            ['site_id',$bomProcessData->from_site_id],
                            ['location_id',$bomProcessData->from_location_id],
                            ['product_id',$bomProcessData->from_product_id],
                        ])->first();
                        
                        if($from_site_product)
                        {
                            $from_site_product->qty += $bomProcessData->total_rice_qty;
                            $from_site_product->update();
                        }
                        //Add Packet Rice Into Stock
                        $to_site_product = SiteProduct::where([
                            ['site_id',$bomProcessData->to_site_id],
                            ['location_id',$bomProcessData->to_location_id],
                            ['product_id',$bomProcessData->to_product_id],
                        ])->first();
                        
                        if($to_site_product)
                        {
                            $to_site_product->qty -= $bomProcessData->total_rice_qty;
                            $to_site_product->update();
                        }
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
}
