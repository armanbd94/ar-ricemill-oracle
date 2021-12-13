<?php

namespace Modules\TransferInventory\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Setting\Entities\Site;
use Modules\Setting\Entities\Batch;
use Modules\Material\Entities\Material;
use App\Http\Controllers\BaseController;
use Modules\Material\Entities\SiteMaterial;
use Modules\TransferInventory\Entities\TransferInventory;
use Modules\TransferInventory\Entities\TransferInventoryItem;
use Modules\TransferInventory\Http\Requests\TransferInventoryFormRequest;

class TransferInventoryController extends BaseController
{
    public function __construct(TransferInventory $model)
    {
        $this->model = $model;
    }
    
    public function index()
    {
        if(permission('transfer-inventory-access')){
            $this->setPageData('Manage Transfer Inventory','Manage Transfer Inventory','fas fa-people-carry',[['name' => 'Manage Transfer Inventory']]);
            return view('transferinventory::transfer-inventory.index');
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('transfer-inventory-access')){

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
                    if(permission('transfer-inventory-edit')){
                        $action .= ' <a class="dropdown-item" href="'.route("transfer.inventory.edit",$value->id).'">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if(permission('transfer-inventory-view')){
                        $action .= ' <a class="dropdown-item view_data" href="'.route("transfer.inventory.view",$value->id).'">'.self::ACTION_BUTTON['View'].'</a>';
                    }
                    if(permission('transfer-inventory-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->memo_no . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }
                    
                    $row = [];
                    if(permission('transfer-inventory-bulk-delete')){
                        $row[] = row_checkbox($value->id);//custom helper function to show the table each row checkbox
                    }
                    $row[] = $no;
                    $row[] = $value->memo_no;
                    $row[] = $value->batch_no;
                    $row[] = $value->from_site;
                    $row[] = $value->from_location;
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
        if(permission('transfer-inventory-add')){
            $this->setPageData('Transfer Inventory Form','Transfer Inventory Form','fas fa-people-carry',[['name' => 'Transfer Inventory Form']]);
            $data = [
                'batches' => Batch::allBatches(),
                'sites'     => Site::allSites(),
                'materials' => Material::with('category')->where([['status',1],['type',1]])->get(),
            ];
            
            return view('transferinventory::transfer-inventory.create',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function store(TransferInventoryFormRequest $request)
    {
        if($request->ajax()){
            if(permission('transfer-inventory-add')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $transferData  = $this->model->create([
                        'memo_no'          => $request->memo_no,
                        'batch_id'         => $request->batch_id,
                        'from_site_id'     => $request->from_site_id,
                        'from_location_id' => $request->from_location_id,
                        'to_site_id'       => $request->to_site_id,
                        'to_location_id'   => $request->to_location_id,
                        'item'             => $request->item,
                        'total_qty'        => $request->total_qty,
                        'transfer_date'    => $request->transfer_date,
                        'transfer_number'  => $request->transfer_number,
                        'created_by'       => auth()->user()->name
                    ]);

                    if($transferData){
                        $materials = [];
                        if($request->has('materials'))
                        {                        
                            foreach ($request->materials as $key => $value) {

                                $materials[] = [
                                    'transfer_id'      => $transferData->id,
                                    'material_id'      => $value['id'],
                                    'qty'              => $value['qty'],
                                    'description'      => $value['description'],
                                    'created_at'       => date('Y-m-d H:i:s')
                                ];

                                $from_site_material = SiteMaterial::where([
                                    ['site_id',$request->from_site_id],
                                    ['location_id',$request->from_location_id],
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
                                TransferInventoryItem::insert($materials);
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
        if(permission('transfer-inventory-view')){
            $this->setPageData('Transfer Inventory Details','Transfer Inventory Details','fas fa-file',[['name' => 'Transfer Inventory Details']]);
            $transfer = $this->model->with('materials','batch','from_site','to_site','from_location','to_location')->find($id);
            return view('transferinventory::transfer-inventory.details',compact('transfer'));
        }else{
            return $this->access_blocked();
        }
    }

    public function edit(int $id)
    {
        if(permission('transfer-inventory-edit')){
            $this->setPageData('Edit Transfer Inventory','Edit Transfer Inventory','fas fa-edit',[['name' => 'Edit Transfer Inventory']]);
            $data = [
                'transfer'  => $this->model->with('materials')->find($id),
                'batches'   => Batch::allBatches(),
                'sites'     => Site::allSites(),
                'materials' => Material::with('category')->where([['status',1],['type',1]])->get(),
            ];
            return view('transferinventory::transfer-inventory.edit',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function update(TransferInventoryFormRequest $request)
    {
        if($request->ajax()){
            if(permission('transfer-inventory-edit')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $transferData = $this->model->with('materials')->find($request->transfer_id);

                    $transfer_data = [
                        'memo_no'          => $request->memo_no,
                        'batch_id'         => $request->batch_id,
                        'from_site_id'     => $request->from_site_id,
                        'from_location_id' => $request->from_location_id,
                        'to_site_id'       => $request->to_site_id,
                        'to_location_id'   => $request->to_location_id,
                        'item'             => $request->item,
                        'total_qty'        => $request->total_qty,
                        'transfer_date'    => $request->transfer_date,
                        'transfer_number'  => $request->transfer_number,
                        'modified_by'      => auth()->user()->name
                    ];

                    if(!$transferData->materials->isEmpty())
                    {
                        foreach ($transferData->materials as $transfer_material) {
                            $transfer_qty = $transfer_material->pivot->qty;
                            // dd($transfer_qty);
                            $from_site_material = SiteMaterial::where([
                                'site_id' => $transferData->from_site_id,
                                'location_id' => $transferData->from_location_id,
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
                                'qty'              => $value['qty'],
                                'description'      => $value['description'],
                                'created_at'       => date('Y-m-d H:i:s')
                            ];

                            $from_site_material = SiteMaterial::where([
                                ['site_id',$request->from_site_id],
                                ['location_id',$request->from_location_id],
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
            if(permission('transfer-inventory-delete')){
                DB::beginTransaction();
                try {
                    $transferData = $this->model->with('materials')->find($request->id);
                    if(!$transferData->materials->isEmpty())
                    {
                        foreach ($transferData->materials as $transfer_material) {
                            $transfer_qty = $transfer_material->pivot->qty;
                            $from_site_material = SiteMaterial::where([
                                'site_id' => $transferData->from_site_id,
                                'location_id' => $transferData->from_location_id,
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
                        $transferData->materials()->detach();
                    }
                    $result = $transferData->delete();
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
            if(permission('transfer-inventory-bulk-delete')){
                DB::beginTransaction();
                try {
                    foreach ($request->ids as $id) {
                        $transferData = $this->model->with('materials')->find($id);
                        if(!$transferData->materials->isEmpty())
                        {
                            foreach ($transferData->materials as $transfer_material) {
                                $transfer_qty = $transfer_material->pivot->qty;
                                $from_site_material = SiteMaterial::where([
                                    'site_id' => $transferData->from_site_id,
                                    'location_id' => $transferData->from_location_id,
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
                            $transferData->materials()->detach();
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
