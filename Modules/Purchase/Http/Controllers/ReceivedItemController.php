<?php

namespace Modules\Purchase\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Setting\Entities\Site;
use Modules\Vendor\Entities\Vendor;
use Modules\Material\Entities\Material;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\Transaction;
use Modules\Material\Entities\SiteMaterial;
use Modules\Purchase\Entities\OrderReceived;
use Modules\Purchase\Entities\PurchaseOrder;
use Modules\Purchase\Entities\OrderReceivedMaterial;
use Modules\Purchase\Entities\PurchaseOrderMaterial;
use Modules\Purchase\Http\Requests\OrderReceivedFormRequest;
use Modules\Purchase\Http\Requests\PurchaseOrderFormRequest;

class ReceivedItemController extends BaseController
{
    public function __construct(OrderReceived $model)
    {
        $this->model = $model;
    }
    
    public function index()
    {
        if(permission('purchase-received-access')){
            $this->setPageData('Manage Purchase Received','Manage Purchase Received','fas fa-truck-loading',[['name' => 'Manage Purchase Received']]);
            return view('purchase::purchase-received.index');
        }else{
            return $this->access_blocked();
        }
    }

    public function purchase_received_memo_form()
    {
        if(permission('purchase-received-add')){
            $this->setPageData('Purchase Received Form','Purchase Received Form','fas fa-truck-loading',[['name' => 'Purchase Received Form']]);
            return view('purchase::purchase-received.form');
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('purchase-received-access')){

                if (!empty($request->memo_no)) {
                    $this->model->setMemoNo($request->memo_no);
                }
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
                    if(permission('purchase-received-edit')){
                        $action .= ' <a class="dropdown-item" href="'.route("purchase.received.edit",$value->id).'">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if(permission('purchase-received-view')){
                        $action .= ' <a class="dropdown-item view_data" href="'.route("purchase.received.view",$value->id).'">'.self::ACTION_BUTTON['View'].'</a>';
                    }
                    if(permission('purchase-received-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->challan_no . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }
                    
                    $row = [];
                    if(permission('purchase-received-bulk-delete')){
                        $row[] = row_checkbox($value->id);//custom helper function to show the table each row checkbox
                    }
                    $row[] = $no;
                    $row[] = $value->challan_no;
                    $row[] = $value->memo_no;
                    $row[] = $value->vendor_name;
                    $row[] = $value->via_vendor_name;
                    $row[] = $value->transport_no;
                    $row[] = $value->item;
                    $row[] = $value->total_qty;
                    $row[] = number_format($value->grand_total,2);
                    $row[] = date(config('settings.date_format'),strtotime($value->received_date));
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

    public function create(Request $request)
    {
        if(permission('purchase-received-add')){
            if($request->memo_no){
                $purchase = PurchaseOrder::with('materials','vendor','via_vendor')->where([['memo_no',$request->memo_no],['purchase_status','!=',1]])->first();
                if($purchase){
                    $this->setPageData('Purchase Order Form','Purchase Order Form','fas fa-truck-loading',[['name' => 'Purchase Order Form']]);
                    $data = [
                        'purchase' => $purchase,
                        'sites' => Site::allSites(),
                    ];
                    return view('purchase::purchase-received.create',$data);
                }else{
                    return back()->with('error','Nothing to receive!');
                } 
            }else{
                return back()->with('error','Invalid Memo No.!');
            }
        }else{
            return $this->access_blocked();
        }
        
    }

    public function store(OrderReceivedFormRequest $request)
    {
        if($request->ajax()){
            if(permission('purchase-received-add')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    
                    $order_received  = $this->model->create([
                        'order_id'      => $request->order_id,
                        'challan_no'    => $request->challan_no,
                        'transport_no'  => $request->transport_no,
                        'item'          => $request->item,
                        'total_qty'     => $request->total_qty,
                        'grand_total'   => $request->grand_total,
                        'received_date' => $request->received_date,
                        'created_by'    => auth()->user()->name
                    ]);

                    if($order_received){
                        $total_received_qty = $this->model->where('order_id',$request->order_id)->sum('total_qty');
                        $purchase_order = PurchaseOrder::find($request->order_id);
                        if($total_received_qty >= $request->order_total_qty)
                        {
                            $purchase_order->purchase_status = 1;
                        }elseif (($total_received_qty < $request->order_total_qty) && ($total_received_qty > 0)) {
                            $purchase_order->purchase_status = 2;
                        }
                        $purchase_order->update();
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
                                    $material->qty += $value['qty'];
                                    $material->cost += $new_cost;
                                    $material->old_cost = $current_cost;
                                    $material->update();
                                }

                                $materials[] = [
                                    'order_id'         => $request->order_id,
                                    'received_id'      => $order_received->id,
                                    'material_id'      => $value['id'],
                                    'site_id'          => $value['site_id'],
                                    'location_id'      => $value['location_id'],
                                    'received_qty'     => $value['qty'],
                                    'received_unit_id' => $value['received_unit_id'],
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
                                
                                OrderReceivedMaterial::insert($materials);
                            }
                
                            Transaction::insert($this->model->transaction_data([
                                'challan_no'    => $request->challan_no,
                                'grand_total'   => $request->grand_total,
                                'vendor_coa_id' => $request->vendor_coa_id,
                                'vendor_name'   => $request->vendor_name,
                                'received_date' => $request->received_date
                            ]));
                        }
                        $output = ['status'=>'success','message'=>'Data has been saved successfully','received_id'=>$order_received->id];
                    }else{
                        $output = ['status'=>'error','message'=>'Failed to save data','received_id'=>''];
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
        if(permission('purchase-received-view')){
            $this->setPageData('Purchase Received Details','Purchase Received Details','fas fa-file',[['name'=>'Purchase','link' => 'javascript::void();'],['name' => 'Purchase Received Details']]);
            $received = $this->model->with('order')->find($id);
            $received_materials = OrderReceivedMaterial::with(['site:id,name','location:id,name','material','received_unit:id,unit_name'])->where('received_id',$id)->get();
            return view('purchase::purchase-received.details',compact('received','received_materials'));
        }else{
            return $this->access_blocked();
        }
    }
    public function edit(int $id)
    {
        if(permission('purchase-received-edit')){
            $this->setPageData('Edit Purchase Receive','Edit Purchase Receive','fas fa-edit',[['name'=>'Purchase','link' => 'javascript::void();'],['name' => 'Edit Purchase Receive']]);
            $receive = $this->model->with('received_materials')->find($id);
            $purchase = PurchaseOrder::with('materials','vendor','via_vendor')->where('memo_no',$receive->order->memo_no)->first();
            $data = [
                'purchase' => $purchase,
                'sites'    => Site::allSites(),
                'receive'  => $receive,
            ];
            return view('purchase::purchase-received.edit',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function update(OrderReceivedFormRequest $request)
    {
        if($request->ajax()){
            if(permission('purchase-received-edit')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $orderReceivedData = $this->model->with('received_materials')->find($request->receive_id);
                    $order_id =  $orderReceivedData->order_id;
                    $order_received = [
                        'challan_no'    => $request->challan_no,
                        'transport_no'  => $request->transport_no,
                        'item'          => $request->item,
                        'total_qty'     => $request->total_qty,
                        'grand_total'   => $request->grand_total,
                        'received_date' => $request->received_date,
                        'modified_by'     => auth()->user()->name
                    ];
                    if(!$orderReceivedData->received_materials->isEmpty())
                    {
                        foreach ($orderReceivedData->received_materials as $received_material) {
                            $received_qty = $received_material->pivot->received_qty;
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
                                $material->qty += $value['qty'];
                                $material->cost += $new_cost;
                                $material->old_cost = $current_cost;
                                $material->update();
                            }

                            $materials[$value['id']] = [
                                'order_id'         => $orderReceivedData->order_id,
                                'site_id'          => $value['site_id'],
                                'location_id'      => $value['location_id'],
                                'received_qty'     => $value['qty'],
                                'received_unit_id' => $value['received_unit_id'],
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
                        $orderReceivedData->received_materials()->sync($materials);
                    }
                    $purchase = $orderReceivedData->update($order_received);
                    $total_received_qty = $this->model->where('order_id',$order_id)->sum('total_qty');
                    $purchase_order = PurchaseOrder::find($order_id);
                    if($total_received_qty >= $purchase_order->order_total_qty)
                    {
                        $purchase_order->purchase_status = 1;
                    }elseif (($total_received_qty < $purchase_order->order_total_qty) && ($total_received_qty > 0)) {
                        $purchase_order->purchase_status = 2;
                    }else{
                        $purchase_order->purchase_status = 3;
                    }
                    $purchase_order->update();

                    $output  = $this->store_message($purchase, $request->receive_id);
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
            if(permission('purchase-received-delete')){
                DB::beginTransaction();
                try {
                    $orderReceivedData = $this->model->with('received_materials')->find($request->id);
                    $order_id =  $orderReceivedData->order_id;
                    if(!$orderReceivedData->received_materials->isEmpty())
                    {
                        foreach ($orderReceivedData->received_materials as $received_material) {
                            $received_qty = $received_material->pivot->received_qty;
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
                        $orderReceivedData->received_materials()->detach();
                    }
                   
                    Transaction::where(['voucher_no'=>$orderReceivedData->challan_no,'voucher_type'=>'Purchase'])->delete();
    
                    $result = $orderReceivedData->delete();
                    if($result)
                    {
                        $total_received_qty = $this->model->where('order_id',$order_id)->sum('total_qty');
                        $purchase_order = PurchaseOrder::find($order_id);
                        if($total_received_qty >= $purchase_order->order_total_qty)
                        {
                            $purchase_order->purchase_status = 1;
                        }elseif (($total_received_qty < $purchase_order->order_total_qty) && ($total_received_qty > 0)) {
                            $purchase_order->purchase_status = 2;
                        }else{
                            $purchase_order->purchase_status = 3;
                        }
                        $purchase_order->update();
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
            if(permission('purchase-received-bulk-delete')){
                DB::beginTransaction();
                try {
                    foreach ($request->ids as $id) {
                        
                        $orderReceivedData = $this->model->with('received_materials')->find($id);
                        $order_id =  $orderReceivedData->order_id;
                        if(!$orderReceivedData->received_materials->isEmpty())
                        {
                            foreach ($orderReceivedData->received_materials as $received_material) {
                                $received_qty = $received_material->pivot->received_qty;
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
                            $orderReceivedData->received_materials()->detach();
                        }
                    
                        Transaction::where(['voucher_no'=>$orderReceivedData->challan_no,'voucher_type'=>'Purchase'])->delete();
        
                        $result = $orderReceivedData->delete();
                        if($result)
                        {
                            $total_received_qty = $this->model->where('order_id',$order_id)->sum('total_qty');
                            $purchase_order = PurchaseOrder::find($order_id);
                            if($total_received_qty >= $purchase_order->order_total_qty)
                            {
                                $purchase_order->purchase_status = 1;
                            }elseif (($total_received_qty < $purchase_order->order_total_qty) && ($total_received_qty > 0)) {
                                $purchase_order->purchase_status = 2;
                            }else{
                                $purchase_order->purchase_status = 3;
                            }
                            $purchase_order->update();
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
