<?php

namespace Modules\BuildReProcess\Http\Controllers;


use Exception;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Setting\Entities\Site;
use Modules\Setting\Entities\Batch;
use Modules\Product\Entities\Product;
use App\Http\Controllers\BaseController;
use Modules\Product\Entities\SiteProduct;
use Modules\BuildDisassembly\Entities\SiloProduct;
use Modules\BuildReProcess\Entities\BuildReProcess;
use Modules\BuildReProcess\Entities\BuildReProcessByProduct;
use Modules\BuildReProcess\Http\Requests\BuildReProcessFormRequest;

class BuildReProcessController extends BaseController
{
    public function __construct(BuildReProcess $model)
    {
        $this->model = $model;
    }
    
    public function index()
    {
        if(permission('build-re-process-access')){
            $this->setPageData('Manage Build Re Process','Manage Build Re Process','fas fa-retweet',[['name' => 'Manage Build Re Process']]);
            $batches = Batch::whereBetween('batch_start_date',[date('Y-01-01'),date('Y-12-31')])->get();
            return view('buildreprocess::index',compact('batches'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('build-re-process-access')){

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
                    if(permission('build-re-process-edit')){
                        $action .= ' <a class="dropdown-item" href="'.route("build.re.process.edit",$value->id).'">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if(permission('build-re-process-view')){
                        $action .= ' <a class="dropdown-item view_data" href="'.route("build.re.process.view",$value->id).'">'.self::ACTION_BUTTON['View'].'</a>';
                    }
                    if(permission('build-re-process-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->batch_no . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }
                    
                    $row = [];
                    if(permission('build-re-process-bulk-delete')){
                        $row[] = row_checkbox($value->id);//custom helper function to show the table each row checkbox
                    }
                    $row[] = $no;
                    $row[] = $value->memo_no;
                    $row[] = $value->batch_no;
                    $row[] = $value->from_site;
                    $row[] = $value->from_location;
                    $row[] = $value->from_product;
                    $row[] = $value->to_product;
                    $row[] = $value->convertion_ratio;
                    $row[] = $value->converted_qty;
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
        if(permission('build-re-process-add')){
            $this->setPageData('Build Disassembly Form','Build Disassembly Form','fas fa-pallet',[['name' => 'Build Disassembly Form']]);
            $data = [
                'batches'   => Batch::whereBetween('batch_start_date',[date('Y-01-01'),date('Y-12-31')])->get(),
                'sites'     => Site::allSites(),
                'products'  => Product::where('status',1)->get(),
                'categories'     => Category::allProductCategories(),
            ];
            
            return view('buildreprocess::create',$data);
        }else{
            return $this->access_blocked();
        }
    } 

    public function store(BuildReProcessFormRequest $request)
    {
        if($request->ajax()){
            if(permission('build-re-process-add')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $buildReProcessData  = $this->model->create([
                        'memo_no'             => $request->memo_no,
                        'batch_id'            => $request->batch_id,
                        'from_site_id'        => $request->from_site_id,
                        'from_location_id'    => $request->from_location_id,
                        'from_product_id'     => $request->from_product_id,
                        'to_product_id'       => $request->to_product_id,
                        'build_ratio'         => $request->build_ratio,
                        'build_qty'           => $request->build_qty,
                        'required_qty'        => $request->required_qty,
                        'category_id'         => $request->category_id,
                        'build_date'          => $request->build_date,
                        'convertion_ratio'    => $request->rice_convertion_ratio,
                        'converted_qty'       => $request->fine_rice_qty,
                        'total_milling_qty'   => $request->milling_qty,
                        'total_milling_ratio' => $request->milling_ratio,
                        'bp_site_id'          => $request->bp_site_id,
                        'bp_location_id'      => $request->bp_location_id,
                        'created_by'          => auth()->user()->name
                    ]);

                    if($buildReProcessData){
                        //Subtract Material From Stock $material->update();
                        
                        $from_site_material = SiteProduct::where([
                            ['site_id',$buildReProcessData->from_site_id],
                            ['location_id',$buildReProcessData->from_location_id],
                            ['product_id',$buildReProcessData->from_product_id],
                        ])->first();
                        
                        if($from_site_material)
                        {
                            $from_site_material->qty -= $buildReProcessData->required_qty;
                            $from_site_material->update();
                        }

                        //Add Fine Rice Into Silo
                        $silo_product = SiloProduct::where('product_id',$buildReProcessData->to_product_id)->first();
                        
                        if($silo_product)
                        {
                            $silo_product->qty += $buildReProcessData->converted_qty;
                            $silo_product->update();
                        }else{
                            SiloProduct::create([
                                'product_id' => $buildReProcessData->to_product_id,
                                'qty'        => $buildReProcessData->converted_qty
                            ]);
                        }

                        //Add By Products Into Stock
                        $by_products = [];
                        if($request->has('by_products'))
                        {                        
                            foreach ($request->by_products as $key => $value) {

                                $by_products[] = [
                                    'process_id' => $buildReProcessData->id,
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
                                BuildReProcessByProduct::insert($by_products);
                            }
                        }
                        $output = ['status'=>'success','message'=>'Data has been saved successfully','build_id'=>$buildReProcessData->id];
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
        if(permission('build-re-process-view')){
            $this->setPageData('Build Re Process Details','Build Re Process Details','fas fa-file',[['name' => 'Build Re Process Details']]);
            $data = $this->model->with('by_products','batch','from_product','to_product','from_site','category','from_location','bp_site','bp_location')->find($id);
            return view('buildreprocess::details',compact('data'));
        }else{
            return $this->access_blocked();
        }
    }

    public function edit(int $id)
    {
        if(permission('build-re-process-edit')){
            $this->setPageData('Build Re Process Edit Form','Build Re Process Edit Form','fas fa-edit',[['name' => 'Build Re Process Edit Form']]);
            $build_data = $this->model->with('by_products')->find($id);
            $data = [
                'data'       => $build_data,
                'batches'    => Batch::whereBetween('batch_start_date',[date('Y-01-01'),date('Y-12-31')])->get(),
                'sites'      => Site::allSites(),
                'site_products' => DB::table('site_product as sp')
                                    ->select('p.id','p.name as product_name','c.name as category_name','u.unit_name','u.unit_code','sp.qty')
                                    ->leftJoin('products as p','sp.product_id','=','p.id')
                                    ->leftJoin('categories as c','p.category_id','=','c.id')
                                    ->leftJoin('units as u','p.unit_id','=','u.id')
                                    ->where([
                                        'sp.site_id'     => $build_data->from_site_id,
                                        'sp.location_id' => $build_data->from_location_id,
                                    ])
                                    ->where('p.category_id','!=',3)
                                    ->get(),
                'products'   => Product::where('status',1)->get(),
                'categories' => Category::allProductCategories(),
            ];
            return view('buildreprocess::edit',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function update(BuildReProcessFormRequest $request)
    {
        if($request->ajax()){
            if(permission('build-re-process-edit')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $buildReProcessData = $this->model->with('by_products')->find($request->build_id);

                    $build_data = [
                        'memo_no'             => $request->memo_no,
                        'batch_id'            => $request->batch_id,
                        'from_site_id'        => $request->from_site_id,
                        'from_location_id'    => $request->from_location_id,
                        'from_product_id'     => $request->from_product_id,
                        'to_product_id'       => $request->to_product_id,
                        'build_ratio'         => $request->build_ratio,
                        'build_qty'           => $request->build_qty,
                        'required_qty'        => $request->required_qty,
                        'category_id'         => $request->category_id,
                        'build_date'          => $request->build_date,
                        'convertion_ratio'    => $request->rice_convertion_ratio,
                        'converted_qty'       => $request->fine_rice_qty,
                        'total_milling_qty'   => $request->milling_qty,
                        'total_milling_ratio' => $request->milling_ratio,
                        'bp_site_id'          => $request->bp_site_id,
                        'bp_location_id'      => $request->bp_location_id,
                        'modified_by'         => auth()->user()->name
                    ];

                    $from_site_material = SiteProduct::where([
                        ['site_id',$buildReProcessData->from_site_id],
                        ['location_id',$buildReProcessData->from_location_id],
                        ['product_id',$buildReProcessData->from_product_id],
                    ])->first();
                    
                    if($from_site_material)
                    {
                        $from_site_material->qty += $buildReProcessData->required_qty;
                        $from_site_material->update();
                    }

                    //Subtract Product From Silo
                    $silo_product = SiloProduct::where('product_id',$buildReProcessData->to_product_id)->first();
                        
                    if($silo_product)
                    {
                        $silo_product->qty -= $buildReProcessData->converted_qty;
                        $silo_product->update();
                    }

                    if(!$buildReProcessData->by_products->isEmpty())
                    {
                        foreach ($buildReProcessData->by_products as $by_product) {
                            $remove_qty = $by_product->pivot->qty;

                            $site_by_product = SiteProduct::where([
                                'site_id' => $buildReProcessData->bp_site_id,
                                'location_id' => $buildReProcessData->bp_location_id,
                                'product_id'  => $by_product->id
                                ])->first();
                            if($site_by_product){
                                $site_by_product->qty -= $remove_qty;
                                $site_by_product->update();
                            }
                        }
                    }



                    //Subtract Material From Stock
                    $from_site_material = SiteProduct::where([
                        ['site_id',$request->from_site_id],
                        ['location_id',$request->from_location_id],
                        ['product_id',$request->from_product_id],
                    ])->first();
                    
                    if($from_site_material)
                    {
                        $from_site_material->qty -= $request->required_qty;
                        $from_site_material->update();
                    }

                    //Add Fine Rice Into Silo
                    $silo_product = SiloProduct::where('product_id',$request->to_product_id)->first();
                    
                    if($silo_product)
                    {
                        $silo_product->qty += $request->converted_qty;
                        $silo_product->update();
                    }else{
                        SiloProduct::create([
                            'product_id' => $request->to_product_id,
                            'qty'        => $request->converted_qty
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
                            $buildReProcessData->by_products()->sync($by_products);
                        }
                    }

                    $build = $buildReProcessData->update($build_data);
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
            if(permission('build-re-process-delete')){
                DB::beginTransaction();
                try {
                    $buildReProcessData = $this->model->with('by_products')->find($request->id);
                    //Re Addition Material To Old Stock
                    $from_site_material = SiteProduct::where([
                        ['site_id',$buildReProcessData->from_site_id],
                        ['location_id',$buildReProcessData->from_location_id],
                        ['product_id',$buildReProcessData->from_product_id],
                    ])->first();
                    
                    if($from_site_material)
                    {
                        $from_site_material->qty += $buildReProcessData->required_qty;
                        $from_site_material->update();
                    }

                    //Subtract Product From Silo
                    $silo_product = SiloProduct::where('product_id',$buildReProcessData->to_product_id)->first();
                        
                    if($silo_product)
                    {
                        $silo_product->qty -= $buildReProcessData->converted_qty;
                        $silo_product->update();
                    }

                    if(!$buildReProcessData->by_products->isEmpty())
                    {
                        foreach ($buildReProcessData->by_products as $by_product) {
                            $remove_qty = $by_product->pivot->qty;

                            $site_by_product = SiteProduct::where([
                                'site_id' => $buildReProcessData->bp_site_id,
                                'location_id' => $buildReProcessData->bp_location_id,
                                'product_id'  => $by_product->id
                                ])->first();
                            if($site_by_product){
                                $site_by_product->qty -= $remove_qty;
                                $site_by_product->update();
                            }
                        }
                        $buildReProcessData->by_products()->detach();
                    }
                    $result = $buildReProcessData->delete();
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
            if(permission('build-re-process-bulk-delete')){
                DB::beginTransaction();
                try {
                    foreach ($request->ids as $id) {
                        $buildReProcessData = $this->model->with('by_products')->find($id);
                        //Re Addition Material To Old Stock
                        $from_site_material = SiteProduct::where([
                            ['site_id',$buildReProcessData->from_site_id],
                            ['location_id',$buildReProcessData->from_location_id],
                            ['product_id',$buildReProcessData->from_product_id],
                        ])->first();
                        
                        if($from_site_material)
                        {
                            $from_site_material->qty += $buildReProcessData->required_qty;
                            $from_site_material->update();
                        }
    
                        //Subtract Product From Silo
                        $silo_product = SiloProduct::where('product_id',$buildReProcessData->to_product_id)->first();
                            
                        if($silo_product)
                        {
                            $silo_product->qty -= $buildReProcessData->converted_qty;
                            $silo_product->update();
                        }

                        if(!$buildReProcessData->by_products->isEmpty())
                        {
                            foreach ($buildReProcessData->by_products as $by_product) {
                                $remove_qty = $by_product->pivot->qty;

                                $site_by_product = SiteProduct::where([
                                    'site_id' => $buildReProcessData->bp_site_id,
                                    'location_id' => $buildReProcessData->bp_location_id,
                                    'product_id'  => $by_product->id
                                    ])->first();
                                if($site_by_product){
                                    $site_by_product->qty -= $remove_qty;
                                    $site_by_product->update();
                                }
                            }
                            $buildReProcessData->by_products()->detach();
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
