<?php

namespace Modules\BOM\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Setting\Entities\Site;
use Modules\Setting\Entities\Batch;
use Modules\Product\Entities\Product;
use App\Http\Controllers\BaseController;

class BOMController extends BaseController
{
    public function create()
    {
        if(permission('bom-process-add')){
            $this->setPageData('BOM Process Form','BOM Process Form','fas fa-box',[['name' => 'BOM Process Form']]);
            $data = [
                'batches'    => Batch::allBatches(),
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
            if(permission('build-disassembly-add')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $buildDisassemblyData  = $this->model->create([
                        'memo_no'             => $request->memo_no,
                        'batch_id'            => $request->batch_id,
                        'from_site_id'        => $request->from_site_id,
                        'from_location_id'    => $request->from_location_id,
                        'material_id'         => $request->material_id,
                        'product_id'          => $request->product_id,
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

                    if($buildDisassemblyData){
                        //Subtract Material From Stock
                        $material = Material::find($buildDisassemblyData->material_id);
                        if($material)
                        {
                            $material->qty -= $buildDisassemblyData->required_qty;
                            $material->update();
                        }
                        $from_site_material = SiteMaterial::where([
                            ['site_id',$buildDisassemblyData->from_site_id],
                            ['location_id',$buildDisassemblyData->from_location_id],
                            ['material_id',$buildDisassemblyData->material_id],
                        ])->first();
                        
                        if($from_site_material)
                        {
                            $from_site_material->qty -= $buildDisassemblyData->required_qty;
                            $from_site_material->update();
                        }

                        //Add Fine Rice Into Silo
                        $silo_product = SiloProduct::where('product_id',$buildDisassemblyData->product_id)->first();
                        
                        if($silo_product)
                        {
                            $silo_product->qty += $buildDisassemblyData->converted_qty;
                            $silo_product->update();
                        }else{
                            SiloProduct::create([
                                'product_id' => $buildDisassemblyData->product_id,
                                'qty'        => $buildDisassemblyData->converted_qty
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
}
