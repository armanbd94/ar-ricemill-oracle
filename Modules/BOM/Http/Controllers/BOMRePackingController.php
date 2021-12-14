<?php

namespace Modules\BOM\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Setting\Entities\Site;
use Modules\Product\Entities\Product;
use Modules\BOM\Entities\BomRePacking;
use Modules\Material\Entities\Material;
use App\Http\Controllers\BaseController;
use Modules\Product\Entities\SiteProduct;
use Modules\Material\Entities\SiteMaterial;
use Modules\BOM\Http\Requests\BOMRePackingFormRequest;

class BOMRePackingController extends BaseController
{
    public function __construct(BomRePacking $model)
    {
        $this->model = $model;
    }
    
    public function index()
    {
        if(permission('bom-re-packing-access')){
            $this->setPageData('Manage BOM Re Packing','Manage BOM Re Packing','fas fa-box',[['name' => 'Manage BOM Re Packing']]);
            return view('bom::bom-re-packing.index');
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('bom-re-packing-access')){
                if (!empty($request->memo_no)) {
                    $this->model->setMemoNo($request->memo_no);
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
                    if(permission('bom-re-packing-edit')){
                        $action .= ' <a class="dropdown-item" href="'.route("bom.re.packing.edit",$value->id).'">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if(permission('bom-re-packing-view')){
                        $action .= ' <a class="dropdown-item view_data" href="'.route("bom.re.packing.view",$value->id).'">'.self::ACTION_BUTTON['View'].'</a>';
                    }
                    if(permission('bom-re-packing-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->memo_no . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }
                    
                    $row = [];
                    if(permission('bom-re-packing-bulk-delete')){
                        $row[] = row_checkbox($value->id);//custom helper function to show the table each row checkbox
                    }
                    $row[] = $no;
                    $row[] = $value->memo_no;
                    $row[] = $value->from_product;
                    $row[] = $value->from_site;
                    $row[] = $value->from_location;
                    $row[] = $value->to_product;
                    $row[] = $value->to_site;
                    $row[] = $value->to_location;
                    $row[] = $value->product_qty;
                    $row[] = $value->bag_name;
                    $row[] = $value->bag_qty;
                    $row[] = date(config('settings.date_format'),strtotime($value->packing_date));
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
        if(permission('bom-re-packing-add')){
            $this->setPageData('BOM Re Packing Form','BOM Re Packing Form','fas fa-box',[['name' => 'BOM Re Packing Form']]);
            $data = [
                'sites'      => Site::allSites(),
                'products'   => Product::where([['status',1],['category_id','!=',3]])->get(),
            ];
            return view('bom::bom-re-packing.create',$data);
        }else{
            return $this->access_blocked();
        }
    } 

    public function store(BOMRePackingFormRequest $request)
    {
        if($request->ajax()){
            if(permission('bom-re-packing-add')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $bomRePackingData  = $this->model->create([
                        'memo_no'             => $request->memo_no,
                        'packing_number'      => $request->packing_number,
                        'from_site_id'        => $request->from_site_id,
                        'from_location_id'    => $request->from_location_id,
                        'from_product_id'     => $request->from_product_id,
                        'to_site_id'          => $request->to_site_id,
                        'to_location_id'      => $request->to_location_id,
                        'to_product_id'       => $request->to_product_id,
                        'bag_site_id'         => $request->bag_site_id,
                        'bag_location_id'     => $request->bag_location_id,
                        'bag_id'              => $request->bag_id,
                        'product_description' => $request->product_description,
                        'bag_description'     => $request->bag_description,
                        'product_qty'         => $request->product_qty,
                        'bag_qty'             => $request->bag_qty,
                        'packing_date'        => $request->packing_date,
                        'created_by'          => auth()->user()->name
                    ]);

                    if($bomRePackingData){
                        //Subtract Bag From Stock
                        $bag = Material::find($bomRePackingData->bag_id);
                        if($bag)
                        {
                            $bag->qty -= $bomRePackingData->bag_qty;
                            $bag->update();
                        }
                        $from_site_bag = SiteMaterial::where([
                            ['site_id',$bomRePackingData->bag_site_id],
                            ['location_id',$bomRePackingData->bag_location_id],
                            ['material_id',$bomRePackingData->bag_id],
                        ])->first();
                        
                        if($from_site_bag)
                        {
                            $from_site_bag->qty -= $bomRePackingData->bag_qty;
                            $from_site_bag->update();
                        }
                        //Subtract Product From Silo
                        $from_site_product = SiteProduct::where([
                            ['site_id',$bomRePackingData->from_site_id],
                            ['location_id',$bomRePackingData->from_location_id],
                            ['product_id',$bomRePackingData->from_product_id],
                        ])->first();
                        
                        if($from_site_product)
                        {
                            $from_site_product->qty -= $bomRePackingData->product_qty;
                            $from_site_product->update();
                        }

                        //Add Packet Rice Into Stock
                        $to_site_product = SiteProduct::where([
                            ['site_id',$bomRePackingData->to_site_id],
                            ['location_id',$bomRePackingData->to_location_id],
                            ['product_id',$bomRePackingData->to_product_id],
                        ])->first();
                        
                        if($to_site_product)
                        {
                            $to_site_product->qty += $bomRePackingData->product_qty;
                            $to_site_product->update();
                        }else{
                            SiteProduct::create([
                                'site_id'     => $bomRePackingData->to_site_id,
                                'location_id' => $bomRePackingData->to_location_id,
                                'product_id'  => $bomRePackingData->to_product_id,
                                'qty'         => $bomRePackingData->product_qty
                            ]);
                        }
                        $output = ['status'=>'success','message'=>'Data has been saved successfully','packing_id'=>$bomRePackingData->id];
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
        if(permission('bom-re-packing-view')){
            $this->setPageData('BOM Re Packing Details','BOM Re Packing Details','fas fa-file',[['name'=>'BOM','link' => 'javascript::void();'],['name' => 'BOM Re Packing Details']]);
            $data = $this->model->with('from_site','from_location','from_product','to_site','to_location','to_product','bag_site','bag_location','bag')->find($id);
            return view('bom::bom-re-packing.details',compact('data'));
        }else{
            return $this->access_blocked();
        }
    }

    public function edit(int $id)
    {
        if(permission('bom-re-process-edit')){
            $this->setPageData('BOM Re Process Edit Form','BOM Re Process Edit Form','fas fa-edit',[['name' => 'BOM Re Process Edit Form']]);
            $bom_re_packing = $this->model->find($id);
            $data = [
                'data'          => $bom_re_packing,
                'sites'         => Site::allSites(),
                'products'      => Product::where([['status',1],['category_id','!=',3]])->get(),
                'site_products' => DB::table('site_product as sp')
                                    ->select('p.id','p.name as product_name','c.name as category_name','u.unit_name','u.unit_code','sp.qty')
                                    ->leftJoin('products as p','sp.product_id','=','p.id')
                                    ->leftJoin('categories as c','p.category_id','=','c.id')
                                    ->leftJoin('units as u','p.unit_id','=','u.id')
                                    ->where([
                                        'sp.site_id'     => $bom_re_packing->from_site_id,
                                        'sp.location_id' => $bom_re_packing->from_location_id,
                                    ])
                                    ->where('p.category_id','!=',3)
                                    ->get(),
                'bags' => DB::table('site_material as sm')
                            ->select('m.id','m.material_name','c.name as category_name','u.unit_name','u.unit_code','sm.qty')
                            ->leftJoin('materials as m','sm.material_id','=','m.id')
                            ->leftJoin('categories as c','m.category_id','=','c.id')
                            ->leftJoin('units as u','m.unit_id','=','u.id')
                            ->where([
                                'sm.site_id'     => $bom_re_packing->bag_site_id,
                                'sm.location_id' => $bom_re_packing->bag_location_id,
                                'm.type'         => 2
                            ])->get()
            ];
            return view('bom::bom-re-packing.edit',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function update(BOMRePackingFormRequest $request)
    {
        if($request->ajax()){
            if(permission('bom-re-packing-edit')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $bomRePackingData = $this->model->find($request->packing_id);

                    $bom_repacking_data = [
                        'memo_no'             => $request->memo_no,
                        'packing_number'      => $request->packing_number,
                        'from_site_id'        => $request->from_site_id,
                        'from_location_id'    => $request->from_location_id,
                        'from_product_id'     => $request->from_product_id,
                        'to_site_id'          => $request->to_site_id,
                        'to_location_id'      => $request->to_location_id,
                        'to_product_id'       => $request->to_product_id,
                        'bag_site_id'         => $request->bag_site_id,
                        'bag_location_id'     => $request->bag_location_id,
                        'bag_id'              => $request->bag_id,
                        'product_description' => $request->product_description,
                        'bag_description'     => $request->bag_description,
                        'product_qty'         => $request->product_qty,
                        'bag_qty'             => $request->bag_qty,
                        'packing_date'        => $request->packing_date,
                        'modified_by'         => auth()->user()->name
                    ];
                    if($bomRePackingData)
                    {
                        $bag = Material::find($bomRePackingData->bag_id);
                        if($bag)
                        {
                            $bag->qty += $bomRePackingData->bag_qty;
                            $bag->update();
                        }
                        $from_site_bag = SiteMaterial::where([
                            ['site_id',$bomRePackingData->bag_site_id],
                            ['location_id',$bomRePackingData->bag_location_id],
                            ['material_id',$bomRePackingData->bag_id],
                        ])->first();
                        
                        if($from_site_bag)
                        {
                            $from_site_bag->qty += $bomRePackingData->bag_qty;
                            $from_site_bag->update();
                        }
                        //Subtract Product From Silo
                        $from_site_product = SiteProduct::where([
                            ['site_id',$bomRePackingData->from_site_id],
                            ['location_id',$bomRePackingData->from_location_id],
                            ['product_id',$bomRePackingData->from_product_id],
                        ])->first();
                        
                        if($from_site_product)
                        {
                            $from_site_product->qty += $bomRePackingData->product_qty;
                            $from_site_product->update();
                        }

                        //Add Packet Rice Into Stock
                        $to_site_product = SiteProduct::where([
                            ['site_id',$bomRePackingData->to_site_id],
                            ['location_id',$bomRePackingData->to_location_id],
                            ['product_id',$bomRePackingData->to_product_id],
                        ])->first();
                        
                        if($to_site_product)
                        {
                            $to_site_product->qty -= $bomRePackingData->product_qty;
                            $to_site_product->update();
                        }
                    }

                    $result = $bomRePackingData->update($bom_repacking_data);
                    if($result)
                    {
                        $bag = Material::find($request->bag_id);
                        if($bag)
                        {
                            $bag->qty -= $request->bag_qty;
                            $bag->update();
                        }
                        $from_site_bag = SiteMaterial::where([
                            ['site_id',$request->bag_site_id],
                            ['location_id',$request->bag_location_id],
                            ['material_id',$request->bag_id],
                        ])->first();
                        
                        if($from_site_bag)
                        {
                            $from_site_bag->qty -= $request->bag_qty;
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
                            $from_site_product->qty -= $request->product_qty;
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
                            $to_site_product->qty += $request->product_qty;
                            $to_site_product->update();
                        }else{
                            SiteProduct::create([
                                'site_id'     => $request->to_site_id,
                                'location_id' => $request->to_location_id,
                                'product_id'  => $request->to_product_id,
                                'qty'         => $request->product_qty
                            ]);
                        }
                    }
                    $output  = $this->store_message($result, $request->packing_id);
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
            if(permission('bom-re-packing-delete')){
                DB::beginTransaction();
                try {
                    $bomRePackingData = $this->model->find($request->id);
                    //Subtract Bag From Stock
                    $bag = Material::find($bomRePackingData->bag_id);
                    if($bag)
                    {
                        $bag->qty += $bomRePackingData->bag_qty;
                        $bag->update();
                    }
                    $from_site_bag = SiteMaterial::where([
                        ['site_id',$bomRePackingData->bag_site_id],
                        ['location_id',$bomRePackingData->bag_location_id],
                        ['material_id',$bomRePackingData->bag_id],
                    ])->first();
                    
                    if($from_site_bag)
                    {
                        $from_site_bag->qty += $bomRePackingData->bag_qty;
                        $from_site_bag->update();
                    }
                    //Subtract Product From Silo
                    $from_site_product = SiteProduct::where([
                        ['site_id',$bomRePackingData->from_site_id],
                        ['location_id',$bomRePackingData->from_location_id],
                        ['product_id',$bomRePackingData->from_product_id],
                    ])->first();
                    
                    if($from_site_product)
                    {
                        $from_site_product->qty += $bomRePackingData->product_qty;
                        $from_site_product->update();
                    }

                    //Add Packet Rice Into Stock
                    $to_site_product = SiteProduct::where([
                        ['site_id',$bomRePackingData->to_site_id],
                        ['location_id',$bomRePackingData->to_location_id],
                        ['product_id',$bomRePackingData->to_product_id],
                    ])->first();
                    
                    if($to_site_product)
                    {
                        $to_site_product->qty -= $bomRePackingData->product_qty;
                        $to_site_product->update();
                    }
                    $result = $bomRePackingData->delete();
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
            if(permission('bom-re-packing-bulk-delete')){
                DB::beginTransaction();
                try {
                    foreach ($request->ids as $id) {
                        $bomRePackingData = $this->model->find($id);
                        //Subtract Bag From Stock
                        $bag = Material::find($bomRePackingData->bag_id);
                        if($bag)
                        {
                            $bag->qty += $bomRePackingData->bag_qty;
                            $bag->update();
                        }
                        $from_site_bag = SiteMaterial::where([
                            ['site_id',$bomRePackingData->bag_site_id],
                            ['location_id',$bomRePackingData->bag_location_id],
                            ['material_id',$bomRePackingData->bag_id],
                        ])->first();
                        
                        if($from_site_bag)
                        {
                            $from_site_bag->qty += $bomRePackingData->bag_qty;
                            $from_site_bag->update();
                        }
                        //Subtract Product From Silo
                        $from_site_product = SiteProduct::where([
                            ['site_id',$bomRePackingData->from_site_id],
                            ['location_id',$bomRePackingData->from_location_id],
                            ['product_id',$bomRePackingData->from_product_id],
                        ])->first();
                        
                        if($from_site_product)
                        {
                            $from_site_product->qty += $bomRePackingData->product_qty;
                            $from_site_product->update();
                        }

                        //Add Packet Rice Into Stock
                        $to_site_product = SiteProduct::where([
                            ['site_id',$bomRePackingData->to_site_id],
                            ['location_id',$bomRePackingData->to_location_id],
                            ['product_id',$bomRePackingData->to_product_id],
                        ])->first();
                        
                        if($to_site_product)
                        {
                            $to_site_product->qty -= $bomRePackingData->product_qty;
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
