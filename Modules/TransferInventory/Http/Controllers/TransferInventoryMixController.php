<?php

namespace Modules\TransferInventory\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Setting\Entities\Site;
use Modules\Setting\Entities\Batch;
use Modules\Product\Entities\Product;
use App\Http\Controllers\BaseController;
use App\Models\Category;
use Modules\Material\Entities\SiteMaterial;
use Modules\TransferInventory\Entities\TransferMixItem;
use Modules\TransferInventory\Entities\TransferMixInventory;
use Modules\TransferInventory\Http\Requests\TransferInventoryMixFormRequest;

class TransferInventoryMixController extends BaseController
{
    public function __construct(TransferMixInventory $model)
    {
        $this->model = $model;
    }
    
    public function index()
    {
        if(permission('transfer-inventory-mix-access')){
            $this->setPageData('Manage Transfer Inventory Mix','Manage Transfer Inventory Mix','fas fa-people-carry',[['name' => 'Manage Transfer Inventory Mix']]);
            return view('transferinventory::transfer-inventory-mix.index');
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('transfer-inventory-mix-access')){

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
                    if(permission('transfer-inventory-mix-edit')){
                        $action .= ' <a class="dropdown-item" href="'.route("transfer.inventory.mix.edit",$value->id).'">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if(permission('transfer-inventory-mix-view')){
                        $action .= ' <a class="dropdown-item view_data" href="'.route("transfer.inventory.mix.view",$value->id).'">'.self::ACTION_BUTTON['View'].'</a>';
                    }
                    if(permission('transfer-inventory-mix-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->memo_no . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }
                    
                    $row = [];
                    if(permission('transfer-inventory-mix-bulk-delete')){
                        $row[] = row_checkbox($value->id);//custom helper function to show the table each row checkbox
                    }
                    $row[] = $no;
                    $row[] = $value->memo_no;
                    $row[] = $value->batch_no;
                    $row[] = $value->product_name;
                    $row[] = $value->category_name;
                    $row[] = $value->to_site;
                    $row[] = $value->to_location;
                    $row[] = $value->item;
                    $row[] = $value->total_qty;
                    $row[] = date(config('settings.date_format'),strtotime($value->transfer_date));
                    $row[] = $value->transfer_number;
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
        if(permission('transfer-inventory-mix-add')){
            $this->setPageData('Transfer Inventory Mix Form','Transfer Inventory Mix Form','fas fa-people-carry',[['name' => 'Transfer Inventory Mix Form']]);
            $data = [
                'batches' => Batch::allBatches(),
                'sites'     => Site::allSites(),
                'products' => Product::with('category')->where('status',1)->get(),
                'categories'     => Category::allProductCategories(),
            ];
            
            return view('transferinventory::transfer-inventory-mix.create',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function store(TransferInventoryMixFormRequest $request)
    {
        if($request->ajax()){
            if(permission('transfer-inventory-mix-add')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $transferData  = $this->model->create([
                        'memo_no'         => $request->memo_no,
                        'batch_id'        => $request->batch_id,
                        'product_id'      => $request->product_id,
                        'category_id'     => $request->category_id,
                        'to_site_id'      => $request->to_site_id,
                        'to_location_id'  => $request->to_location_id,
                        'item'            => $request->item,
                        'total_qty'       => $request->total_qty,
                        'transfer_date'   => $request->transfer_date,
                        'transfer_number' => $request->transfer_number,
                        'created_by'      => auth()->user()->name
                    ]);

                    if($transferData){
                        $materials = [];
                        if($request->has('materials'))
                        {                        
                            foreach ($request->materials as $key => $value) {

                                $materials[] = [
                                    'transfer_id'      => $transferData->id,
                                    'material_id'      => $value['id'],
                                    'from_site_id'     => $value['from_site_id'],
                                    'from_location_id' => $value['from_location_id'],
                                    'qty'              => $value['qty'],
                                    'description'      => $value['description'],
                                    'created_at'       => date('Y-m-d H:i:s')
                                ];

                                $from_site_material = SiteMaterial::where([
                                    ['site_id',$value['from_site_id']],
                                    ['location_id',$value['from_location_id']],
                                    ['material_id',$value['id']],
                                ])->first();
                                
                                if($from_site_material)
                                {
                                    $from_site_material->qty -= $value['qty'];
                                    $from_site_material->update();
                                }

                                $to_site_material = SiteMaterial::where([
                                    ['site_id',$request->to_site_id],
                                    ['location_id',$request->to_location_id],
                                    ['material_id',$value['id']],
                                ])->first();
                                
                                if($to_site_material)
                                {
                                    $to_site_material->qty += $value['qty'];
                                    $to_site_material->update();
                                }else{
                                    SiteMaterial::create([
                                        'site_id'     => $request->to_site_id,
                                        'location_id' => $request->to_location_id,
                                        'material_id' => $value['id'],
                                        'qty'         => $value['qty']
                                    ]);
                                }
                            }
                            if(!empty($materials) && count($materials))
                            {
                                TransferMixItem::insert($materials);
                            }
                        }
                        $output = ['status'=>'success','message'=>'Data has been saved successfully','transfer_id'=>$transferData->id];
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
        if(permission('transfer-inventory-mix-view')){
            $this->setPageData('Transfer Inventory Mix Details','Transfer Inventory Mix Details','fas fa-file',[['name'=>'Purchase','link' => 'javascript::void();'],['name' => 'Transfer Inventory Mix Details']]);
            $transfer = $this->model->with('materials','batch','product','to_site','category','to_location')->find($id);
            $materials = TransferMixItem::with('from_site','material','from_location')->where('transfer_id',$id)->get();
            return view('transferinventory::transfer-inventory-mix.details',compact('transfer','materials'));
        }else{
            return $this->access_blocked();
        }
    }

    public function edit(int $id)
    {
        if(permission('transfer-inventory-mix-edit')){
            $this->setPageData('Edit Transfer Inventory Mix','Edit Transfer Inventory Mix','fas fa-edit',[['name' => 'Edit Transfer Inventory Mix']]);
            $data = [
                'transfer'   => $this->model->with('materials')->find($id),
                'batches'    => Batch::allBatches(),
                'sites'      => Site::allSites(),
                'products'   => Product::with('category')->where('status',1)->get(),
                'categories' => Category::allProductCategories(),
            ];
            return view('transferinventory::transfer-inventory-mix.edit',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function update(TransferInventoryMixFormRequest $request)
    {
        if($request->ajax()){
            if(permission('transfer-inventory-mix-edit')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $transferData = $this->model->with('materials')->find($request->transfer_id);

                    $transfer_data = [
                        'memo_no'         => $request->memo_no,
                        'batch_id'        => $request->batch_id,
                        'product_id'      => $request->product_id,
                        'category_id'     => $request->category_id,
                        'to_site_id'      => $request->to_site_id,
                        'to_location_id'  => $request->to_location_id,
                        'item'            => $request->item,
                        'total_qty'       => $request->total_qty,
                        'transfer_date'   => $request->transfer_date,
                        'transfer_number' => $request->transfer_number,
                        'modified_by'     => auth()->user()->name
                    ];

                    if(!$transferData->materials->isEmpty())
                    {
                        foreach ($transferData->materials as $transfer_material) {
                            $transfer_qty = $transfer_material->pivot->qty;
                            // dd($transfer_qty);
                            $from_site_material = SiteMaterial::where([
                                'site_id' => $transfer_material->pivot->from_site_id,
                                'location_id' => $transfer_material->pivot->from_location_id,
                                'material_id'  => $transfer_material->id
                                ])->first();
                            if($from_site_material){
                                $from_site_material->qty += $transfer_qty;
                                $from_site_material->update();
                            }

                            $to_site_material = SiteMaterial::where([
                                'site_id' => $transferData->to_site_id,
                                'location_id' => $transferData->to_location_id,
                                'material_id'  => $transfer_material->id
                                ])->first();
                            if($to_site_material){
                                $to_site_material->qty -= $transfer_qty;
                                $to_site_material->update();
                            }

                        }
                    }

                    $materials = [];
                    if($request->has('materials'))
                    {                        
                        foreach ($request->materials as $key => $value) {

                            $materials[$value['id']] = [
                                'from_site_id'     => $value['from_site_id'],
                                'from_location_id' => $value['from_location_id'],
                                'qty'              => $value['qty'],
                                'description'      => $value['description'],
                                'created_at'       => date('Y-m-d H:i:s')
                            ];

                            $from_site_material = SiteMaterial::where([
                                ['site_id',$value['from_site_id']],
                                ['location_id',$value['from_location_id']],
                                ['material_id',$value['id']],
                            ])->first();
                            
                            if($from_site_material)
                            {
                                $from_site_material->qty -= $value['qty'];
                                $from_site_material->update();
                            }

                            $to_site_material = SiteMaterial::where([
                                ['site_id',$request->to_site_id],
                                ['location_id',$request->to_location_id],
                                ['material_id',$value['id']],
                            ])->first();
                            
                            if($to_site_material)
                            {
                                $to_site_material->qty += $value['qty'];
                                $to_site_material->update();
                            }else{
                                SiteMaterial::create([
                                    'site_id'     => $request->to_site_id,
                                    'location_id' => $request->to_location_id,
                                    'material_id' => $value['id'],
                                    'qty'         => $value['qty']
                                ]);
                            }
                        }
                        if(!empty($materials) && count($materials))
                        {
                            $transferData->materials()->sync($materials);
                        }
                    }
                    $transfer = $transferData->update($transfer_data);
                    $output  = $this->store_message($transfer, $request->transfer_id);
                    DB::commit();
                    // return response()->json($output);
                } catch (Exception $e) {
                    DB::rollback();
                    $output = ['status' => 'error','message' => $e->getMessage()];
                    // return response()->json($output);
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
