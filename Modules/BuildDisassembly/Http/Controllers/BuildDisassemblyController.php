<?php

namespace Modules\BuildDisassembly\Http\Controllers;

use Exception;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Setting\Entities\Site;
use Modules\Setting\Entities\Batch;
use Modules\Product\Entities\Product;
use Modules\Material\Entities\Material;
use App\Http\Controllers\BaseController;
use App\Models\ItemClass;
use Modules\Product\Entities\SiteProduct;
use Modules\Material\Entities\SiteMaterial;
use Modules\BuildDisassembly\Entities\SiloProduct;
use Modules\BuildDisassembly\Entities\BuildDisassembly;
use Modules\BuildDisassembly\Entities\BuildDisassemblyByProduct;
use Modules\BuildDisassembly\Http\Requests\BuildDisassemblyFormRequest;
use Modules\Setting\Entities\Location;

class BuildDisassemblyController extends BaseController
{
    public function __construct(BuildDisassembly $model)
    {
        $this->model = $model;
    }
    
    public function index()
    {
        if(permission('build-disassembly-access')){
            $this->setPageData('Manage Build Disassembly','Manage Build Disassembly','fas fa-pallet',[['name' => 'Manage Build Disassembly']]);
            $batches = Batch::whereBetween('batch_start_date',[date('Y-01-01'),date('Y-12-31')])->get();
            return view('builddisassembly::index',compact('batches'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('build-disassembly-access')){

                if (!empty($request->memo_no)) {
                    $this->model->setMemoNo($request->memo_no);
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
                    if(permission('build-disassembly-edit')){
                        $action .= ' <a class="dropdown-item" href="'.route("build.disassembly.edit",$value->id).'">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if(permission('build-disassembly-view')){
                        $action .= ' <a class="dropdown-item view_data" href="'.route("build.disassembly.view",$value->id).'">'.self::ACTION_BUTTON['View'].'</a>';
                    }
                    if(permission('build-disassembly-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->memo_no . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }
                    
                    $row = [];
                    if(permission('build-disassembly-bulk-delete')){
                        $row[] = row_checkbox($value->id);//custom helper function to show the table each row checkbox
                    }
                    $row[] = $no;
                    $row[] = $value->memo_no;
                    $row[] = $value->batch_no;
                    $row[] = $value->material_name;
                    $row[] = $value->product_name;
                    $row[] = $value->from_site;
                    $row[] = $value->from_location;
                    $row[] = $value->to_site;
                    $row[] = $value->to_location;
                    $row[] = number_format($value->required_qty,2,'.',',');
                    $row[] = number_format($value->converted_qty,2,'.',',');
                    $row[] = date(config('settings.date_format'),strtotime($value->build_date));
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
        if(permission('build-disassembly-add')){
            $this->setPageData('Build Disassembly Form','Build Disassembly Form','fas fa-pallet',[['name' => 'Build Disassembly Form']]);
            $data = [
                'batches'   => Batch::whereBetween('batch_start_date',[date('Y-01-01'),date('Y-12-31')])->get(),
                'sites'     => Site::allSites(),
                'materials' => Material::with('category')->where([['status',1],['type',1]])->get(),
                'products'  => Product::where('status',1)->whereIn('category_id',[3,4])->get(),
                'classes'   => ItemClass::allItemClass(),
            ];
            
            return view('builddisassembly::create',$data);
        }else{
            return $this->access_blocked();
        }
    } 

    public function store(BuildDisassemblyFormRequest $request)
    {
        if($request->ajax()){
            if(permission('build-disassembly-add')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $material = Material::findOrFail($request->material_id);
                    $product  = Product::findOrFail($request->product_id);
                    $product_qty             = SiteProduct::where('product_id',$request->product_id)->sum('qty');
                    $product_old_stock_value = ($product->cost ? $product->cost : 0) * ($product_qty ?? 0);
                    $converted_stock_value   = $request->fine_rice_qty * ($material->cost ? $material->cost : 0);
                    $product_new_cost        = ($product_old_stock_value + $converted_stock_value) / (($product_qty ?? 0) + $request->fine_rice_qty);

                    $buildDisassemblyData    = $this->model->create([
                        'memo_no'             => $request->memo_no,
                        'batch_id'            => $request->batch_id,
                        'from_site_id'        => $request->from_site_id,
                        'from_location_id'    => $request->from_location_id,
                        'to_site_id'          => $request->to_site_id,
                        'to_location_id'      => $request->to_location_id,
                        'material_id'         => $request->material_id,
                        'product_id'          => $request->product_id,
                        'material_cost'       => $material->cost ? $material->cost : 0,
                        'product_cost'        => $product_new_cost,
                        'product_old_cost'    => $product->cost ? $product->cost : 0,
                        'build_ratio'         => $request->build_ratio,
                        'build_qty'           => $request->build_qty,
                        'required_qty'        => $request->required_qty,
                        'item_class_id'       => $request->item_class_id,
                        'build_date'          => $request->build_date,
                        'convertion_ratio'    => $request->rice_convertion_ratio,
                        'converted_qty'       => $request->fine_rice_qty,
                        'total_milling_qty'   => $request->milling_qty,
                        'total_milling_ratio' => $request->milling_ratio,
                        'bp_site_id'          => $request->bp_site_id,
                        'bp_location_id'      => $request->bp_location_id,
                        'created_by'          => auth()->user()->name
                    ]);

                    if($buildDisassemblyData){
                        $product->cost = $product_new_cost;
                        $product->update();
                        //Subtract Material From Stock
                        $material = Material::find($request->material_id);
                        if($material)
                        {
                            $material->qty -= $request->required_qty;
                            $material->update();
                        }
                        $from_site_material = SiteMaterial::where([
                            ['site_id',$request->from_site_id],
                            ['location_id',$request->from_location_id],
                            ['material_id',$request->material_id],
                        ])->first();
                        
                        if($from_site_material)
                        {
                            $from_site_material->qty -= $request->required_qty;
                            $from_site_material->update();
                        }

                        //Add Fine Rice Into Stock
                        $site_product = SiteProduct::where([
                            ['site_id',$request->to_site_id],
                            ['location_id',$request->to_location_id],
                            ['product_id',$request->product_id],
                        ])->first();
                        if($site_product)
                        {
                            $site_product->qty += $request->fine_rice_qty;
                            $site_product->update();
                        }else{
                            SiteProduct::create([
                                'site_id'     => $request->to_site_id,
                                'location_id' => $request->to_location_id,
                                'product_id'  => $request->product_id,
                                'qty'         => $request->fine_rice_qty
                            ]);
                        }

                        //Add By Products Into Stock
                        $by_products = [];
                        if($request->has('by_products'))
                        {                        
                            foreach ($request->by_products as $key => $value) {

                                $by_products[] = [
                                    'disassembly_id' => $buildDisassemblyData->id,
                                    'product_id'     => $value['id'],
                                    'ratio'          => $value['ratio'],
                                    'qty'            => $value['qty'],
                                    'created_at'     => date('Y-m-d H:i:s')
                                ];

                                $site_by_product = SiteProduct::where([
                                    ['site_id',$request->bp_site_id],
                                    ['location_id',$request->bp_location_id],
                                    ['product_id',$value['id']],
                                ])->first();
                                
                                if($site_by_product)
                                {
                                    $site_by_product->qty += $value['qty'];
                                    $site_by_product->update();
                                }else{
                                    SiteProduct::create([
                                        'site_id'     => $request->bp_site_id,
                                        'location_id' => $request->bp_location_id,
                                        'product_id' => $value['id'],
                                        'qty'         => $value['qty']
                                    ]);
                                }
                            }
                            if(!empty($by_products) && count($by_products))
                            {
                                BuildDisassemblyByProduct::insert($by_products);
                            }
                        }
                        $output = ['status'=>'success','message'=>'Data has been saved successfully','build_id'=>$buildDisassemblyData->id];
                    }else{
                        $output = ['status'=>'error','message'=>'Failed to save data','purchase_id'=>''];
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

    public function show(int $id)
    {
        if(permission('build-disassembly-view')){
            $this->setPageData('Build Disassembly Details','Build Disassembly Details','fas fa-file',[['name' => 'Build Disassembly Details']]);
            $data = $this->model->with('by_products','batch','material','product','from_site','to_site','item_class','from_location','to_location','bp_site','bp_location')->find($id);
            return view('builddisassembly::details',compact('data'));
        }else{
            return $this->access_blocked();
        }
    }

    public function edit(int $id)
    {
        if(permission('build-disassembly-edit')){
            $this->setPageData('Build Disassembly Edit Form','Build Disassembly Edit Form','fas fa-edit',[['name' => 'Build Disassembly Edit Form']]);
            $disassembly_data = $this->model->with('by_products')->find($id);
            $data = [
                'data'      => $disassembly_data,
                'batches'   => Batch::whereBetween('batch_start_date',[date('Y-01-01'),date('Y-12-31')])->get(),
                'sites'     => Site::allSites(),
                'locations' => Location::whereIn('site_id',[$disassembly_data->from_site_id,$disassembly_data->to_site_id,$disassembly_data->bp_site_id])->get(),
                'materials' => Material::with('category')->where([['status',1],['type',1]])->get(),
                'material_qty'=>SiteMaterial::where(['site_id'=>$disassembly_data->from_site_id,'location_id'=>$disassembly_data->from_location_id,'material_id'=>$disassembly_data->material_id])->value('qty'),
                'products'  => Product::where('status',1)->whereIn('category_id',[3,4])->get(),
                'classes'   => ItemClass::allItemClass(),
            ];
            
            return view('builddisassembly::edit',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function update(BuildDisassemblyFormRequest $request)
    {
        if($request->ajax()){
            if(permission('build-disassembly-edit')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $buildDisassemblyData = $this->model->with('by_products')->find($request->build_id);
                    //Re Store Previous Data
                    $converted_product  = Product::findOrFail($buildDisassemblyData->product_id);
                    $converted_product->cost = $buildDisassemblyData->product_old_cost;
                    $converted_product->update();
                    $material = Material::find($buildDisassemblyData->material_id);
                    if($material)
                    {
                        $material->qty += $buildDisassemblyData->required_qty;
                        $material->update();
                    }
                    $from_site_material = SiteMaterial::where([
                        ['site_id',$buildDisassemblyData->from_site_id],
                        ['location_id',$buildDisassemblyData->from_location_id],
                        ['material_id',$buildDisassemblyData->material_id],
                    ])->first();
                    
                    if($from_site_material)
                    {
                        $from_site_material->qty += $buildDisassemblyData->required_qty;
                        $from_site_material->update();
                    }

                    //Subtract Product From Silo
                    $site_product = SiteProduct::where([
                        ['site_id',$buildDisassemblyData->to_site_id],
                        ['location_id',$buildDisassemblyData->to_location_id],
                        ['product_id',$buildDisassemblyData->product_id],
                    ])->first();
                    if($site_product)
                    {
                        $site_product->qty -= $buildDisassemblyData->converted_qty;
                        $site_product->update();
                    }

                    if(!$buildDisassemblyData->by_products->isEmpty())
                    {
                        foreach ($buildDisassemblyData->by_products as $by_product) {
                            $remove_qty = $by_product->pivot->qty;

                            $site_by_product = SiteProduct::where([
                                'site_id' => $buildDisassemblyData->bp_site_id,
                                'location_id' => $buildDisassemblyData->bp_location_id,
                                'product_id'  => $by_product->id
                                ])->first();
                            if($site_by_product){
                                $site_by_product->qty -= $remove_qty;
                                $site_by_product->update();
                            }
                        }
                    }

                    $material                = Material::findOrFail($request->material_id);
                    $product                 = Product::findOrFail($request->product_id);
                    $product_qty             = SiteProduct::where('product_id',$request->product_id)->sum('qty');
                    $product_old_stock_value = ($product->cost ? $product->cost : 0) * ($product_qty ?? 0);
                    $converted_stock_value   = $request->fine_rice_qty * ($material->cost ? $material->cost : 0);
                    $product_new_cost        = ($product_old_stock_value + $converted_stock_value) / (($product_qty ?? 0) + $request->fine_rice_qty);
                    $product->cost           = $product_new_cost;
                    $product->update();
                    $build_data = [
                        'memo_no'             => $request->memo_no,
                        'batch_id'            => $request->batch_id,
                        'from_site_id'        => $request->from_site_id,
                        'from_location_id'    => $request->from_location_id,
                        'to_site_id'          => $request->to_site_id,
                        'to_location_id'      => $request->to_location_id,
                        'material_id'         => $request->material_id,
                        'product_id'          => $request->product_id,
                        'material_cost'       => $material->cost ? $material->cost : 0,
                        'product_cost'        => $product_new_cost,
                        'product_old_cost'    => $product->cost ? $product->cost : 0,
                        'build_ratio'         => $request->build_ratio,
                        'build_qty'           => $request->build_qty,
                        'required_qty'        => $request->required_qty,
                        'item_class_id'       => $request->item_class_id,
                        'build_date'          => $request->build_date,
                        'convertion_ratio'    => $request->rice_convertion_ratio,
                        'converted_qty'       => $request->fine_rice_qty,
                        'total_milling_qty'   => $request->milling_qty,
                        'total_milling_ratio' => $request->milling_ratio,
                        'bp_site_id'          => $request->bp_site_id,
                        'bp_location_id'      => $request->bp_location_id,
                        'created_by'          => auth()->user()->name
                    ];
                    
                    //Subtract Material From Stock
                   
                    if($material)
                    {
                        $material->qty -= $request->required_qty;
                        $material->update();
                    }
                    $from_site_material = SiteMaterial::where([
                        ['site_id',$request->from_site_id],
                        ['location_id',$request->from_location_id],
                        ['material_id',$request->material_id],
                    ])->first();
                    
                    if($from_site_material)
                    {
                        $from_site_material->qty -= $request->required_qty;
                        $from_site_material->update();
                    }

                    //Add Fine Rice Into Silo
                    $site_product = SiteProduct::where([
                        ['site_id',$request->to_site_id],
                        ['location_id',$request->to_location_id],
                        ['product_id',$request->product_id],
                    ])->first();
                    if($site_product)
                    {
                        $site_product->qty += $request->fine_rice_qty;
                        $site_product->update();
                    }else{
                        SiteProduct::create([
                            'site_id'     => $request->to_site_id,
                            'location_id' => $request->to_location_id,
                            'product_id'  => $request->product_id,
                            'qty'         => $request->fine_rice_qty
                        ]);
                    }

                    //Add By Products Into Stock
                    $by_products = [];
                    if($request->has('by_products'))
                    {                        
                        foreach ($request->by_products as $key => $value) {

                            $by_products[$value['id']] = [
                                'ratio'          => $value['ratio'],
                                'qty'            => $value['qty'],
                                'created_at'     => date('Y-m-d H:i:s')
                            ];

                            $site_by_product = SiteProduct::where([
                                ['site_id',$request->bp_site_id],
                                ['location_id',$request->bp_location_id],
                                ['product_id',$value['id']],
                            ])->first();
                            
                            if($site_by_product)
                            {
                                $site_by_product->qty += $value['qty'];
                                $site_by_product->update();
                            }else{
                                SiteProduct::create([
                                    'site_id'     => $request->bp_site_id,
                                    'location_id' => $request->bp_location_id,
                                    'product_id' => $value['id'],
                                    'qty'         => $value['qty']
                                ]);
                            }
                        }
                        if(!empty($by_products) && count($by_products))
                        {
                            $buildDisassemblyData->by_products()->sync($by_products);
                        }
                    }

                    $build = $buildDisassemblyData->update($build_data);
                    $output  = $this->store_message($build, $request->build_id);
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
            if(permission('build-disassembly-delete')){
                DB::beginTransaction();
                try {
                    $buildDisassemblyData = $this->model->with('by_products')->find($request->id);
                    
                    $converted_product  = Product::findOrFail($buildDisassemblyData->product_id);
                    $converted_product->cost = $buildDisassemblyData->product_old_cost;
                    $converted_product->update();

                    //Re Addition Material To Old Stock
                    $material = Material::find($buildDisassemblyData->material_id);
                    if($material)
                    {
                        $material->qty += $buildDisassemblyData->required_qty;
                        $material->update();
                    }
                    $from_site_material = SiteMaterial::where([
                        ['site_id',$buildDisassemblyData->from_site_id],
                        ['location_id',$buildDisassemblyData->from_location_id],
                        ['material_id',$buildDisassemblyData->material_id],
                    ])->first();
                    
                    if($from_site_material)
                    {
                        $from_site_material->qty += $buildDisassemblyData->required_qty;
                        $from_site_material->update();
                    }

                    //Subtract Product From Silo
                   $site_product = SiteProduct::where([
                        ['site_id',$buildDisassemblyData->to_site_id],
                        ['location_id',$buildDisassemblyData->to_location_id],
                        ['product_id',$buildDisassemblyData->product_id],
                    ])->first();
                    if($site_product)
                    {
                        $site_product->qty -= $buildDisassemblyData->converted_qty;
                        $site_product->update();
                    }

                    if(!$buildDisassemblyData->by_products->isEmpty())
                    {
                        foreach ($buildDisassemblyData->by_products as $by_product) {
                            $remove_qty = $by_product->pivot->qty;

                            $site_by_product = SiteProduct::where([
                                'site_id' => $buildDisassemblyData->bp_site_id,
                                'location_id' => $buildDisassemblyData->bp_location_id,
                                'product_id'  => $by_product->id
                                ])->first();
                            if($site_by_product){
                                $site_by_product->qty -= $remove_qty;
                                $site_by_product->update();
                            }
                        }
                        $buildDisassemblyData->by_products()->detach();
                    }
                    $result = $buildDisassemblyData->delete();
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
            if(permission('build-disassembly-bulk-delete')){
                DB::beginTransaction();
                try {
                    foreach ($request->ids as $id) {
                        $buildDisassemblyData = $this->model->with('by_products')->find($id);
                        
                        $converted_product  = Product::findOrFail($buildDisassemblyData->product_id);
                        $converted_product->cost = $buildDisassemblyData->product_old_cost;
                        $converted_product->update();
                        
                        //Re Addition Material To Old Stock
                        $material = Material::find($buildDisassemblyData->material_id);
                        if($material)
                        {
                            $material->qty += $buildDisassemblyData->required_qty;
                            $material->update();
                        }
                        $from_site_material = SiteMaterial::where([
                            ['site_id',$buildDisassemblyData->from_site_id],
                            ['location_id',$buildDisassemblyData->from_location_id],
                            ['material_id',$buildDisassemblyData->material_id],
                        ])->first();
                        
                        if($from_site_material)
                        {
                            $from_site_material->qty += $buildDisassemblyData->required_qty;
                            $from_site_material->update();
                        }

                        //Subtract Product From Silo
                        $site_product = SiteProduct::where([
                            ['site_id',$buildDisassemblyData->to_site_id],
                            ['location_id',$buildDisassemblyData->to_location_id],
                            ['product_id',$buildDisassemblyData->product_id],
                        ])->first();
                        if($site_product)
                        {
                            $site_product->qty -= $buildDisassemblyData->converted_qty;
                            $site_product->update();
                        }

                        if(!$buildDisassemblyData->by_products->isEmpty())
                        {
                            foreach ($buildDisassemblyData->by_products as $by_product) {
                                $remove_qty = $by_product->pivot->qty;

                                $site_by_product = SiteProduct::where([
                                    'site_id' => $buildDisassemblyData->bp_site_id,
                                    'location_id' => $buildDisassemblyData->bp_location_id,
                                    'product_id'  => $by_product->id
                                    ])->first();
                                if($site_by_product){
                                    $site_by_product->qty -= $remove_qty;
                                    $site_by_product->update();
                                }
                            }
                            $buildDisassemblyData->by_products()->detach();
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
