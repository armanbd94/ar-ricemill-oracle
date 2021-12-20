<?php

namespace Modules\Sale\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Setting\Entities\Site;
use Modules\Sale\Entities\SaleOrder;
use Modules\Sale\Entities\SaleInvoice;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\Transaction;
use Modules\Product\Entities\SiteProduct;
use Modules\Sale\Http\Requests\SaleInvoiceFormRequest;

class SaleInvoiceController extends BaseController
{
    public function __construct(SaleInvoice $model)
    {
        $this->model = $model;
    }
    
    public function index()
    {
        if(permission('sale-invoice-access')){
            $this->setPageData('Manage Sale Invoice','Manage Sale Invoice','fab fa-opencart',[['name' => 'Manage Sale Invoice']]);
            return view('sale::sale-invoice.index');
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('sale-invoice-access')){

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
                    if(permission('sale-invoice-edit')){
                        $action .= ' <a class="dropdown-item" href="'.route("sale.invoice.edit",$value->id).'">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if(permission('sale-invoice-view')){
                        $action .= ' <a class="dropdown-item view_data" href="'.route("sale.invoice.view",$value->id).'">'.self::ACTION_BUTTON['View'].'</a>';
                    }
                    if(permission('sale-invoice-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->memo_no . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }
                    
                    $row = [];
                    if(permission('sale-invoice-bulk-delete')){
                        $row[] = row_checkbox($value->id);//custom helper function to show the table each row checkbox
                    }
                    $row[] = $no;
                    $row[] = $value->challan_no;
                    $row[] = $value->memo_no;
                    $row[] = $value->customer_name;
                    $row[] = $value->via_customer_name;
                    $row[] = $value->item;
                    $row[] = $value->total_qty;
                    $row[] = number_format($value->grand_total,2);
                    $row[] = date(config('settings.date_format'),strtotime($value->invoice_date));
                    $row[] = $value->transport_no;
                    $row[] = $value->truck_fare;
                    $row[] = $value->terms == 1 ? 'Office Payable' : 'Customer Payable';
                    $row[] = $value->driver_mobile_no;
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
    public function sale_memo_form()
    {
        if(permission('sale-invoice-add')){
            $this->setPageData('Sale Memo Form','Sale Memo Form','fab fa-opencart',[['name' => 'Sale Memo Form']]);
            return view('sale::sale-invoice.form');
        }else{
            return $this->access_blocked();
        }
    }
    public function create(Request $request)
    {
        if(permission('sale-invoice-add')){
            if($request->memo_no){
                $sale = SaleOrder::with('products','customer','via_customer')->where([['memo_no',$request->memo_no],['order_status',2]])->first();
                if($sale){
                    $this->setPageData('Sale Invoice Form','Sale Invoice Form','fab fa-opencart',[['name' => 'Sale Invoice Form']]);
                    $data = [
                        'sale' => $sale,
                        'sites' => Site::allSites(),
                    ];
                    return view('sale::sale-invoice.create',$data);
                }else{
                    return back()->with('error','Nothing to deliver!');
                } 
            }else{
                return back()->with('error','Invalid Memo No.!');
            }
        }else{
            return $this->access_blocked();
        }
        
    }

    public function store(SaleInvoiceFormRequest $request)
    {
        if($request->ajax()){
            if(permission('sale-invoice-add')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $cashSale  = $this->model->create([
                        'memo_no'       => $request->memo_no,
                        'customer_name' => $request->customer_name,
                        'do_number'     => $request->do_number,
                        'account_id'    => $request->account_id,
                        'item'          => $request->item,
                        'total_qty'     => $request->total_qty,
                        'grand_total'   => $request->grand_total,
                        'sale_date'     => $request->sale_date,
                        'delivery_date' => $request->delivery_date,
                        'created_by'    => auth()->user()->name
                    ]);

                    if($cashSale){
                        $products = [];
                        if($request->has('products'))
                        {                        
                            foreach ($request->products as $key => $value) {

                                $products[] = [
                                    'sale_id'          => $cashSale->id,
                                    'product_id'       => $value['id'],
                                    'site_id'          => $value['site_id'],
                                    'location_id'      => $value['location_id'],
                                    'qty'              => $value['qty'],
                                    'net_unit_price'   => $value['net_unit_price'],
                                    'total'            => $value['subtotal'],
                                    'description'      => $value['description'],
                                    'created_at'       => date('Y-m-d H:i:s')
                                ];

                                $site_product = SiteProduct::where([
                                    ['site_id',$value['site_id']],
                                    ['location_id',$value['location_id']],
                                    ['product_id',$value['id']],
                                ])->first();
                                
                                if($site_product)
                                {
                                    $site_product->qty -= $value['qty'];
                                    $site_product->update();
                                }else{
                                    SiteProduct::create([
                                        'site_id'     => $value['site_id'],
                                        'location_id' => $value['location_id'],
                                        'product_id'  => $value['id'],
                                        'qty'         => $value['qty']
                                    ]);
                                }
                            }
                            if(!empty($products) && count($products))
                            {
                                CashSaleProduct::insert($products);
                            }
                        }
                        Transaction::insert($this->model->transaction_data([
                            'memo_no'       => $request->memo_no,
                            'grand_total'   => $request->grand_total,
                            'customer_name' => $request->customer_name,
                            'sale_date'     => $request->sale_date,
                            'account_id'    => $request->account_id,
                        ]));
                        $output = ['status'=>'success','message'=>'Data has been saved successfully','sale_id'=>$cashSale->id];
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
        if(permission('sale-invoice-view')){
            $this->setPageData('Sale Invoice Details','Sale Invoice Details','fas fa-file',[['name'=>'Sale','link' => 'javascript::void();'],['name' => 'Sale Invoice Details']]);
            $sale = $this->model->find($id);
            $sale_products = CashSaleProduct::with(['site:id,name','location:id,name','product'])->where('sale_id',$id)->get();
            return view('sale::sale-invoice.details',compact('sale','sale_products'));
        }else{
            return $this->access_blocked();
        }
    }
    public function edit(int $id)
    {

        if(permission('sale-invoice-edit')){
            $this->setPageData('Edit Sale Invoice','Edit Sale Invoice','fas fa-edit',[['name'=>'Sale','link' => 'javascript::void();'],['name' => 'Edit Sale Invoice']]);
            $data = [
                'sale'  => $this->model->with('products')->find($id),
                'sites'     => Site::allSites(),
            ];
            return view('sale::sale-invoice.edit',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function update(SaleInvoiceFormRequest $request)
    {
        if($request->ajax()){
            if(permission('sale-invoice-edit')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $saleData = $this->model->with('products')->find($request->sale_id);

                    $sale_data = [
                        'memo_no'       => $request->memo_no,
                        'customer_name' => $request->customer_name,
                        'do_number'     => $request->do_number,
                        'account_id'    => $request->account_id,
                        'item'          => $request->item,
                        'total_qty'     => $request->total_qty,
                        'grand_total'   => $request->grand_total,
                        'sale_date'     => $request->sale_date,
                        'delivery_date' => $request->delivery_date,
                        'modified_by'   => auth()->user()->name
                    ];

                    if(!$saleData->products->isEmpty())
                    {
                        foreach ($saleData->products as $value) {
                            $site_product = SiteProduct::where([
                                ['site_id',$value->pivot->site_id],
                                ['location_id',$value->pivot->location_id],
                                ['product_id',$value->id],
                            ])->first();
                            
                            if($site_product)
                            {
                                $site_product->qty += $value->pivot->qty;
                                $site_product->update();
                            }

                        }
                    }

                    $products = [];
                    if($request->has('products'))
                    {                        
                        foreach ($request->products as $key => $value) {

                            $products[$value['id']] = [
                                'site_id'          => $value['site_id'],
                                'location_id'      => $value['location_id'],
                                'qty'              => $value['qty'],
                                'net_unit_price'   => $value['net_unit_price'],
                                'total'            => $value['subtotal'],
                                'description'      => $value['description'],
                                'created_at'       => date('Y-m-d H:i:s')
                            ];

                            $site_product = SiteProduct::where([
                                ['site_id',$value['site_id']],
                                ['location_id',$value['location_id']],
                                ['product_id',$value['id']],
                            ])->first();
                            
                            if($site_product)
                            {
                                $site_product->qty -= $value['qty'];
                                $site_product->update();
                            }else{
                                SiteProduct::create([
                                    'site_id'     => $value['site_id'],
                                    'location_id' => $value['location_id'],
                                    'product_id'  => $value['id'],
                                    'qty'         => $value['qty']
                                ]);
                            }
                        }
                        if(!empty($products) && count($products))
                        {
                            $saleData->products()->sync($products);
                        }
                    }
                    Transaction::where(['voucher_no'=>$saleData->memo_no,'voucher_type'=>'INVOICE'])->delete();
                    Transaction::insert($this->model->transaction_data([
                        'memo_no'       => $request->memo_no,
                        'grand_total'   => $request->grand_total,
                        'customer_name' => $request->customer_name,
                        'sale_date'     => $request->sale_date,
                        'account_id'    => $request->account_id,
                    ]));
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
            if(permission('sale-invoice-delete')){
                DB::beginTransaction();
                try {
                    $saleData = $this->model->with('products')->find($request->id);
                    if(!$saleData->products->isEmpty())
                    {
                        foreach ($saleData->products as $value) {
                            $site_product = SiteProduct::where([
                                ['site_id',$value->pivot->site_id],
                                ['location_id',$value->pivot->location_id],
                                ['product_id',$value->id],
                            ])->first();
                            
                            if($site_product)
                            {
                                $site_product->qty += $value->pivot->qty;
                                $site_product->update();
                            }

                        }
                        $saleData->products()->detach();
                    }
                   
                    Transaction::where(['voucher_no'=>$saleData->memo_no,'voucher_type'=>'INVOICE'])->delete();
    
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
            if(permission('sale-invoice-bulk-delete')){
                DB::beginTransaction();
                try {
                    foreach ($request->ids as $id) {
                        $saleData = $this->model->with('products')->find($id);
                        if(!$saleData->products->isEmpty())
                        {
                            foreach ($saleData->products as $value) {
                                $site_product = SiteProduct::where([
                                    ['site_id',$value->pivot->site_id],
                                    ['location_id',$value->pivot->location_id],
                                    ['product_id',$value->id],
                                ])->first();
                                
                                if($site_product)
                                {
                                    $site_product->qty += $value->pivot->qty;
                                    $site_product->update();
                                }

                            }
                            $saleData->products()->detach();
                        }
                    
                        Transaction::where(['voucher_no'=>$saleData->memo_no,'voucher_type'=>'INVOICE'])->delete();
        
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
