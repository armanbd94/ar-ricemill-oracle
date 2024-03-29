<?php

namespace Modules\Purchase\Http\Controllers;

use Exception;
use App\Models\ItemClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Setting\Entities\Site;
use Modules\Setting\Entities\JobType;
use Modules\Material\Entities\Material;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\Transaction;
use Modules\Material\Entities\SiteMaterial;
use Modules\Purchase\Entities\CashPurchase;
use Modules\Purchase\Entities\CashPurchaseMaterial;
use Modules\Purchase\Http\Requests\CashPurchaseFormRequest;


class CashPurchaseController extends BaseController
{
    public function __construct(CashPurchase $model)
    {
        $this->model = $model;
    }
    
    public function index()
    {
        if(permission('cash-purchase-access')){
            $this->setPageData('Manage Cash Purchase','Manage Cash Purchase','fas fa-cart-arrow-down',[['name' => 'Manage Cash Purchase']]);
            return view('purchase::cash-purchase.index');
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('cash-purchase-access')){

                if (!empty($request->challan_no)) {
                    $this->model->setChallanNo($request->challan_no);
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
                    if(permission('cash-purchase-edit')){
                        $action .= ' <a class="dropdown-item" href="'.route("purchase.cash.edit",$value->id).'">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if(permission('cash-purchase-view')){
                        $action .= ' <a class="dropdown-item view_data" href="'.route("purchase.cash.view",$value->id).'">'.self::ACTION_BUTTON['View'].'</a>';
                    }
                    if(permission('cash-purchase-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->memo_no . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }
                    
                    $row = [];
                    if(permission('cash-purchase-bulk-delete')){
                        $row[] = row_checkbox($value->id);//custom helper function to show the table each row checkbox
                    }
                    $row[] = $no;
                    $row[] = $value->challan_no;
                    $row[] = $value->memo_no;
                    $row[] = $value->vendor_name;
                    $row[] = $value->job_type;
                    $row[] = $value->name;
                    $row[] = $value->item;
                    $row[] = $value->total_qty;
                    $row[] = number_format($value->grand_total,2);
                    $row[] = date(config('settings.date_format'),strtotime($value->receive_date));
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
        if(permission('cash-purchase-add')){
            $this->setPageData('Cash Purchase Form','Cash Purchase Form','fas fa-cart-arrow-down',[['name' => 'Cash Purchase Form']]);
            $data = [
                'job_types' => JobType::allJobTypes(),
                'sites'     => Site::allSites(),
                'materials' => Material::with('category')->where([['status',1]])->get(),
                'classes'   => ItemClass::allItemClass()
            ];
            
            return view('purchase::cash-purchase.create',$data);
        }else{
            return $this->access_blocked();
        }
        
    }

    public function store(CashPurchaseFormRequest $request)
    {
        if($request->ajax()){
            if(permission('cash-purchase-add')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $cashPurchase  = $this->model->create([
                        'challan_no'   => $request->challan_no,
                        'memo_no'      => $request->memo_no,
                        'vendor_name'  => $request->vendor_name,
                        'job_type_id'  => $request->job_type_id,
                        'name'         => $request->name,
                        'account_id'   => $request->account_id,
                        'item'         => $request->item,
                        'total_qty'    => $request->total_qty,
                        'grand_total'  => $request->grand_total,
                        'receive_date' => $request->receive_date,
                        'created_by'   => auth()->user()->name
                    ]);

                    if($cashPurchase){
                        $materials = [];
                        if($request->has('materials'))
                        {                        
                            foreach ($request->materials as $key => $value) {
                                $material = Material::find($value['id']);

                                $current_stock_value = ($material->qty ? $material->qty : 0) * ($material->cost ? $material->cost : 0);
                                $new_cost            = ($value['subtotal'] + $current_stock_value) / ($value['qty'] + $material->qty);
                                $current_cost        = $material->cost ? $material->cost : 0;
                                $old_cost            = $material->old_cost ? $material->old_cost : 0;
                                if($material)
                                {
                                    $material->qty     += $value['qty'];
                                    $material->cost     = $new_cost;
                                    $material->old_cost = $current_cost;
                                    $material->update();
                                }

                                $materials[] = [
                                    'cash_id'         => $cashPurchase->id,
                                    'material_id'      => $value['id'],
                                    'item_class_id'    => $value['item_class_id'],
                                    'site_id'          => $value['site_id'],
                                    'location_id'      => $value['location_id'],
                                    'qty'              => $value['qty'],
                                    'purchase_unit_id' => $value['purchase_unit_id'],
                                    'net_unit_cost'    => $value['net_unit_cost'],
                                    'old_cost'         => $old_cost,
                                    'total'            => $value['subtotal'],
                                    'description'      => $value['description'],
                                    'created_at'       => date('Y-m-d H:i:s')
                                ];

                                $site_material = SiteMaterial::where([
                                    ['site_id',$value['site_id']],
                                    ['location_id',$value['location_id']],
                                    ['material_id',$value['id']],
                                ])->first();
                                
                                if($site_material)
                                {
                                    $site_material->qty += $value['qty'];
                                    $site_material->update();
                                }else{
                                    SiteMaterial::create([
                                        'site_id'     => $value['site_id'],
                                        'location_id' => $value['location_id'],
                                        'material_id' => $value['id'],
                                        'qty'         => $value['qty']
                                    ]);
                                }
                            }
                            if(!empty($materials) && count($materials))
                            {
                                CashPurchaseMaterial::insert($materials);
                            }
                            
                        }
                        Transaction::insert($this->model->transaction_data([
                            'challan_no'    => $request->challan_no,
                            'grand_total'   => $request->grand_total,
                            'vendor_name'   => $request->vendor_name,
                            'receive_date'  => $request->receive_date
                        ]));
                        $output = ['status'=>'success','message'=>'Data has been saved successfully','purchase_id'=>$cashPurchase->id];
                    }else{
                        $output = ['status'=>'error','message'=>'Failed to save data','purchase_id'=>''];
                    }
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


    public function show(int $id)
    {
        if(permission('cash-purchase-view')){
            $this->setPageData('Cash Purchase Details','Cash Purchase Details','fas fa-file',[['name'=>'Purchase','link' => 'javascript::void();'],['name' => 'Cash Purchase Details']]);
            $purchase = $this->model->with('materials','jobType')->find($id);
            $purchase_materials = CashPurchaseMaterial::with(['site:id,name','location:id,name','material','purchase_unit:id,unit_name'])->where('cash_id',$id)->get();
            return view('purchase::cash-purchase.details',compact('purchase','purchase_materials'));
        }else{
            return $this->access_blocked();
        }
    }
    public function edit(int $id)
    {

        if(permission('cash-purchase-edit')){
            $this->setPageData('Edit Cash Purchase','Edit Cash Purchase','fas fa-edit',[['name'=>'Purchase','link' => 'javascript::void();'],['name' => 'Edit Cash Purchase']]);
            $data = [
                'purchase'  => $this->model->with('materials')->find($id),
                'job_types' => JobType::allJobTypes(),
                'sites'     => Site::allSites(),
                'materials' => Material::with('category')->where([['status',1],['type',1]])->get(),
                'classes'   => ItemClass::allItemClass()
            ];
            return view('purchase::cash-purchase.edit',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function update(CashPurchaseFormRequest $request)
    {
        if($request->ajax()){
            if(permission('cash-purchase-edit')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $purchaseData = $this->model->with('materials')->find($request->purchase_id);

                    $purchase_data = [
                        'challan_no'   => $request->challan_no,
                        'memo_no'      => $request->memo_no,
                        'vendor_name'  => $request->vendor_name,
                        'job_type_id'  => $request->job_type_id,
                        'name'         => $request->name,
                        'account_id'   => $request->account_id,
                        'item'         => $request->item,
                        'total_qty'    => $request->total_qty,
                        'grand_total'  => $request->grand_total,
                        'receive_date' => $request->receive_date,
                        'modified_by'     => auth()->user()->name
                    ];

                    if(!$purchaseData->materials->isEmpty())
                    {
                        foreach ($purchaseData->materials as $received_material) {
                            $received_qty = $received_material->pivot->qty;
                            $material = Material::find($received_material->id);
                            if($material){
                                $material->qty -= $received_qty;
                                $material->cost = $material->old_cost;
                                $material->old_cost = $received_material->pivot->old_cost;
                                $material->update();
                            }

                            $site_material = SiteMaterial::where([
                                'site_id' => $received_material->pivot->site_id,
                                'location_id' => $received_material->pivot->location_id,
                                'material_id'  => $received_material->id
                                ])->first();
                            if($site_material){
                                $site_material->qty -= $received_qty;
                                $site_material->update();
                            }

                        }
                    }

                    $materials = [];
                    if($request->has('materials'))
                    {                        
                        foreach ($request->materials as $key => $value) {

                            $material = Material::find($value['id']);

                                $current_stock_value = ($material->qty ? $material->qty : 0) * ($material->cost ? $material->cost : 0);
                                $new_cost            = ($value['subtotal'] + $current_stock_value) / ($value['qty'] + $material->qty);
                                $current_cost        = $material->cost ? $material->cost : 0;
                                $old_cost            = $material->old_cost ? $material->old_cost : 0;
                                if($material)
                                {
                                    $material->qty     += $value['qty'];
                                    $material->cost     = $new_cost;
                                    $material->old_cost = $current_cost;
                                    $material->update();
                                }

                                $materials[$value['id']] = [
                                    'item_class_id'    => $value['item_class_id'],
                                    'site_id'          => $value['site_id'],
                                    'location_id'      => $value['location_id'],
                                    'qty'              => $value['qty'],
                                    'purchase_unit_id' => $value['purchase_unit_id'],
                                    'net_unit_cost'    => $value['net_unit_cost'],
                                    'old_cost'         => $old_cost,
                                    'total'            => $value['subtotal'],
                                    'description'      => $value['description'],
                                    'created_at'       => date('Y-m-d H:i:s')
                                ];

                                $site_material = SiteMaterial::where([
                                    ['site_id',$value['site_id']],
                                    ['location_id',$value['location_id']],
                                    ['material_id',$value['id']],
                                ])->first();
                                
                                if($site_material)
                                {
                                    $site_material->qty += $value['qty'];
                                    $site_material->update();
                                }else{
                                    SiteMaterial::create([
                                        'site_id'     => $value['site_id'],
                                        'location_id' => $value['location_id'],
                                        'material_id' => $value['id'],
                                        'qty'         => $value['qty']
                                    ]);
                                }
                        }
                    }
                    if(!empty($materials) && count($materials))
                    {
                        $purchaseData->materials()->sync($materials);
                    }
                    Transaction::where(['voucher_no'=>$purchaseData->challan_no,'voucher_type'=>'Purchase'])->delete();
                    Transaction::insert($this->model->transaction_data([
                        'challan_no'    => $request->challan_no,
                        'grand_total'   => $request->grand_total,
                        'vendor_name'   => $request->vendor_name,
                        'receive_date' => $request->receive_date
                    ]));
                    $purchase = $purchaseData->update($purchase_data);
                    $output  = $this->store_message($purchase, $request->purchase_id);
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

    public function delete(Request $request)
    {
        if($request->ajax()){
            if(permission('cash-purchase-delete')){
                DB::beginTransaction();
                try {
                    $purchaseData = $this->model->with('materials')->find($request->id);
                    if(!$purchaseData->materials->isEmpty())
                    {
                        foreach ($purchaseData->materials as $received_material) {
                            $received_qty = $received_material->pivot->qty;
                            $material = Material::find($received_material->id);
                            if($material){
                                $material->qty -= $received_qty;
                                $material->cost = $material->old_cost;
                                $material->old_cost = $received_material->pivot->old_cost;
                                $material->update();
                            }

                            $site_material = SiteMaterial::where([
                                'site_id' => $received_material->pivot->site_id,
                                'location_id' => $received_material->pivot->location_id,
                                'material_id'  => $received_material->id
                                ])->first();
                            if($site_material){
                                $site_material->qty -= $received_qty;
                                $site_material->update();
                            }

                        }
                        $purchaseData->materials()->detach();
                    }
                   
                    Transaction::where(['voucher_no'=>$purchaseData->challan_no,'voucher_type'=>'Purchase'])->delete();
    
                    $result = $purchaseData->delete();
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
            if(permission('cash-purchase-bulk-delete')){
                DB::beginTransaction();
                try {
                    foreach ($request->ids as $id) {
                        $purchaseData = $this->model->with('materials')->find($id);
                        if(!$purchaseData->materials->isEmpty())
                        {
                            foreach ($purchaseData->materials as $received_material) {
                                $received_qty = $received_material->pivot->qty;
                                $material = Material::find($received_material->id);
                                if($material){
                                    $material->qty -= $received_qty;
                                    $material->cost = $material->old_cost;
                                    $material->old_cost = $received_material->pivot->old_cost;
                                    $material->update();
                                }

                                $site_material = SiteMaterial::where([
                                    'site_id' => $received_material->pivot->site_id,
                                    'location_id' => $received_material->pivot->location_id,
                                    'material_id'  => $received_material->id
                                    ])->first();
                                if($site_material){
                                    $site_material->qty -= $received_qty;
                                    $site_material->update();
                                }

                            }
                            $purchaseData->materials()->detach();
                        }
                    
                        Transaction::where(['voucher_no'=>$purchaseData->challan_no,'voucher_type'=>'Purchase'])->delete();
        
                        $result = $purchaseData->delete();
                        if($result)
                        {
                            $output = ['status' => 'success','message' => 'Data has been deleted successfully'];
                        }else{
                            $output = ['status' => 'error','message' => 'Failed to delete data'];
                        }
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
