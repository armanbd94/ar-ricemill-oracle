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
                    if(permission('purchase-received-edit')  && $value->purchase_status == 3){
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

    public function create($memo_no)
    {
        if(permission('purchase-received-add')){
            $purchase = PurchaseOrder::with('materials','vendor','via_vendor')->where([['memo_no',$memo_no],['purchase_status','!=',1]])->first();
            if($purchase){
                $this->setPageData('Purchase Order Form','Purchase Order Form','fas fa-truck-loading',[['name' => 'Purchase Order Form']]);
                $data = [
                    'purchase' => $purchase,
                    'sites' => Site::allSites(),
                ];
                return view('purchase::purchase-received.create',$data);
            }else{
                return redirect()->back()->with('error','No Record Found!');
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
        if(permission('purchase-received-view')){
            $this->setPageData('Purchase Order Details','Purchase Order Details','fas fa-file',[['name'=>'Purchase','link' => 'javascript::void();'],['name' => 'Purchase Order Details']]);
            $purchase = $this->model->with('materials','vendor','via_vendor')->find($id);
            return view('purchase::purchase-received.details',compact('purchase'));
        }else{
            return $this->access_blocked();
        }
    }
    public function edit(int $id)
    {

        if(permission('purchase-received-edit')){
            $this->setPageData('Edit Purchase Order','Edit Purchase Order','fas fa-edit',[['name'=>'Purchase','link' => 'javascript::void();'],['name' => 'Edit Purchase Order']]);
            $data = [
                'purchase'  => $this->model->with('materials')->find($id),
                'vendors'   => Vendor::allVendors(),
                'materials' => Material::with('category')->where([['status',1],['type',1]])->get(),
            ];
            return view('purchase::purchase-received.edit',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function update(PurchaseOrderFormRequest $request)
    {
        if($request->ajax()){
            if(permission('purchase-received-edit')){
                //dd($request->all());
                DB::beginTransaction();
                try {
                    $purchaseData = $this->model->with('materials')->find($request->purchase_id);

                    $purchase_data = [
                        'memo_no'         => $request->memo_no,
                        'vendor_id'       => $request->vendor_id,
                        'via_vendor_id'   => $request->via_vendor_id,
                        'item'            => $request->item,
                        'total_qty'       => $request->total_qty,
                        'grand_total'     => $request->grand_total,
                        'order_date'      => $request->order_date,
                        'delivery_date'   => $request->delivery_date,
                        'po_no'           => $request->po_no,
                        'nos_truck'       => $request->nos_truck,
                        'modified_by'     => auth()->user()->name
                    ];

                    $materials = [];
                    if($request->has('materials'))
                    {                        
                        foreach ($request->materials as $key => $value) {

                            $materials[$value['id']] = [
                                'qty'              => $value['qty'],
                                'purchase_unit_id' => $value['purchase_unit_id'],
                                'net_unit_cost'    => $value['net_unit_cost'],
                                'total'            => $value['subtotal'],
                                'description'      => $value['description'],
                                'created_at'       => date('Y-m-d H:i:s')
                            ];
                        }
                    }
                    if(!empty($materials) && count($materials))
                    {
                        $purchaseData->materials()->sync($materials);
                    }
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
            if(permission('purchase-received-delete')){
                DB::beginTransaction();
                try {
                    
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
