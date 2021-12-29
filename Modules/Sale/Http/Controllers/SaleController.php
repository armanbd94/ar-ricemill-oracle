<?php

namespace Modules\Sale\Http\Controllers;

use Exception;
use App\Models\Category;
use App\Models\ItemClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Sale\Entities\SaleOrder;
use Modules\Customer\Entities\Customer;
use App\Http\Controllers\BaseController;
use Modules\Sale\Entities\SaleOrderProduct;
use Modules\ViaCustomer\Entities\ViaCustomer;
use Modules\Sale\Http\Requests\SaleOrderFormRequest;

class SaleController extends BaseController
{
    public function __construct(SaleOrder $model)
    {
        $this->model = $model;
    }
    
    public function index()
    {
        if(permission('sale-order-access')){
            $this->setPageData('Manage Sale Order','Manage Sale Order','fab fa-opencart',[['name' => 'Manage Sale Order']]);
            $customers = Customer::allCustomers();
            return view('sale::sale-order.index',compact('customers'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('sale-order-access')){

                if (!empty($request->memo_no)) {
                    $this->model->setMemoNo($request->memo_no);
                }
                if (!empty($request->customer_id)) {
                    $this->model->setCustomerID($request->customer_id);
                }
                if (!empty($request->from_order_date)) {
                    $this->model->setFromOrderDate($request->from_order_date);
                }
                if (!empty($request->to_order_date)) {
                    $this->model->setToOrderDate($request->to_order_date);
                }
                if (!empty($request->from_delivery_date)) {
                    $this->model->setFromDeliveryDate($request->from_delivery_date);
                }
                if (!empty($request->to_delivery_date)) {
                    $this->model->setToDeliveryDate($request->to_delivery_date);
                }


                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('sale-order-edit')){
                        $action .= ' <a class="dropdown-item" href="'.route("sale.order.edit",$value->id).'">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if(permission('sale-order-view')){
                        $action .= ' <a class="dropdown-item view_data" href="'.route("sale.order.view",$value->id).'">'.self::ACTION_BUTTON['View'].'</a>';
                    }
                    if(permission('sale-order-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->memo_no . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }
                    
                    $row = [];
                    if(permission('sale-order-bulk-delete')){
                        $row[] = row_checkbox($value->id);//custom helper function to show the table each row checkbox
                    }
                    $row[] = $no;
                    $row[] = $value->memo_no;
                    $row[] = $value->so_no;
                    $row[] = $value->customer_name;
                    $row[] = $value->via_customer_name;
                    $row[] = $value->item;
                    $row[] = $value->total_qty;
                    $row[] = number_format($value->grand_total,2);
                    $row[] = date(config('settings.date_format'),strtotime($value->order_date));
                    $row[] = date(config('settings.date_format'),strtotime($value->delivery_date));
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
        if(permission('sale-order-add')){
            $this->setPageData('Sale Order Form','Sale Order Form','fab fa-opencart',[['name' => 'Sale Order Form']]);
            $data = [
                'customers'    => Customer::allCustomers(),
                'categories'   => Category::with('products')->whereHas('products')->where([['type',2],['id','!=',4]])->orderBy('id','desc')->get(),
                'classes'      => ItemClass::allItemClass()
            ];
            return view('sale::sale-order.create',$data);
        }else{
            return $this->access_blocked();
        }
        
    }

    public function store(SaleOrderFormRequest $request)
    {
        if($request->ajax()){
            if(permission('sale-order-add')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $saleOrder  = $this->model->create([
                        'memo_no'          => $request->memo_no,
                        'customer_id'      => $request->customer_id,
                        'via_customer_id'  => $request->via_customer_id,
                        'so_no'            => $request->so_no,
                        'item'             => $request->item,
                        'total_qty'        => $request->total_qty,
                        'grand_total'      => $request->grand_total,
                        'order_date'       => $request->order_date,
                        'delivery_date'    => $request->delivery_date,
                        'shipping_address' => $request->shipping_address,
                        'created_by'       => auth()->user()->name
                    ]);

                    if($saleOrder){
                        $products = [];
                        if($request->has('products'))
                        {                        
                            foreach ($request->products as $key => $value) {

                                $products[] = [
                                    'sale_id'          => $saleOrder->id,
                                    'product_id'       => $value['id'],
                                    'item_class_id'    => $value['item_class_id'],
                                    'qty'              => $value['qty'],
                                    'net_unit_price'   => $value['net_unit_price'],
                                    'total'            => $value['subtotal'],
                                    'description'      => $value['description'],
                                    'created_at'       => date('Y-m-d H:i:s')
                                ];
                            }
                            if(!empty($products) && count($products))
                            {
                                SaleOrderProduct::insert($products);
                            }
                        }
                        $output = ['status'=>'success','message'=>'Data has been saved successfully','sale_id'=>$saleOrder->id];
                    }else{
                        $output = ['status'=>'error','message'=>'Failed to save data','sale_id'=>''];
                    }
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollback();
                    $output = ['status' => 'error','message' => $e->getMessage()];
                }
            }else{
                $output = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }


    public function show(int $id)
    {
        if(permission('sale-order-view')){
            $this->setPageData('Sale Order Details','Sale Order Details','fas fa-file',[['name'=>'Sale','link' => 'javascript::void();'],['name' => 'Sale Order Details']]);
            $sale = $this->model->with('products','customer','via_customer')->find($id);
            return view('sale::sale-order.details',compact('sale'));
        }else{
            return $this->access_blocked();
        }
    }
    public function edit(int $id)
    {

        if(permission('sale-order-edit')){
            $this->setPageData('Edit Sale Order','Edit Sale Order','fas fa-edit',[['name'=>'Sale','link' => 'javascript::void();'],['name' => 'Edit Sale Order']]);
            $sale = $this->model->with('products','customer')->find($id);
            $data = [
                'sale'  => $sale,
                'customers'    => Customer::allCustomers(),
                'categories'   => Category::with('products')->whereHas('products')->where('type',2)->orderBy('id','desc')->get(),
                'via_customers'=> ViaCustomer::where([['customer_id',$sale->customer_id],['status',1]])->get(),
                'classes'   => ItemClass::allItemClass()
            ];
            return view('sale::sale-order.edit',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function update(SaleOrderFormRequest $request)
    {
        if($request->ajax()){
            if(permission('sale-order-edit')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $saleData = $this->model->with('products')->find($request->sale_id);

                    $sale_data = [
                        'memo_no'          => $request->memo_no,
                        'customer_id'      => $request->customer_id,
                        'via_customer_id'  => $request->via_customer_id,
                        'so_no'            => $request->so_no,
                        'item'             => $request->item,
                        'total_qty'        => $request->total_qty,
                        'grand_total'      => $request->grand_total,
                        'order_date'       => $request->order_date,
                        'delivery_date'    => $request->delivery_date,
                        'shipping_address' => $request->shipping_address,
                        'modified_by'      => auth()->user()->name
                    ];

                    $products = [];
                    if($request->has('products'))
                    {                        
                        foreach ($request->products as $key => $value) {

                            $products[$value['id']] = [
                                'item_class_id'    => $value['item_class_id'],
                                'qty'              => $value['qty'],
                                'net_unit_price'   => $value['net_unit_price'],
                                'total'            => $value['subtotal'],
                                'description'      => $value['description'],
                                'created_at'       => date('Y-m-d H:i:s')
                            ];
                        }
                        if(!empty($products) && count($products))
                        {
                            $saleData->products()->sync($products);
                        }
                    }
                    $sale = $saleData->update($sale_data);
                    $output  = $this->store_message($sale, $request->sale_id);
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
            if(permission('sale-order-delete')){
                DB::beginTransaction();
                try {
                    $saleData = $this->model->with('products')->find($request->id);
                    if(!$saleData->products->isEmpty())
                    {
                        $saleData->products()->detach();
                    }
                    $result = $saleData->delete();
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
            if(permission('sale-order-bulk-delete')){
                DB::beginTransaction();
                try {
                    foreach ($request->ids as $id) {
                        $saleData = $this->model->with('products')->find($id);
                        if(!$saleData->products->isEmpty())
                        {
                            $saleData->products()->detach();
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
