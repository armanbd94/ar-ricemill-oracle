<?php

namespace Modules\Account\Http\Controllers;

use Exception;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Customer\Entities\Customer;
use Modules\Location\Entities\District;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\Transaction;
use Modules\Account\Http\Requests\CustomerReceiveFormRequest;

class CustomerReceiveController extends BaseController
{
    public function __construct(Transaction $model)
    {
        $this->model = $model;
    }


    public function index()
    {
        if(permission('customer-receive-access')){
            $this->setPageData('Customer Receive','Customer Receive','far fa-money-bill-alt',[['name'=>'Accounts'],['name'=>'Customer Receive']]);
            $voucher_no = 'CR-'.date('ymd').rand(1,999);
            $districts = DB::table('locations')->where([['status', 1],['parent_id',0]])->pluck('name','id');
            $warehouses = DB::table('warehouses')->where('status',1)->pluck('name','id');
            return view('account::customer-receive.index',compact('voucher_no','districts','warehouses'));
        }else{
            return $this->access_blocked();
        }
    }

    public function store(CustomerReceiveFormRequest $request)
    {
        if($request->ajax()){
            if(permission('customer-receive-access')){
                DB::beginTransaction();
                try {
                    $customer = Customer::with('coa')->find($request->customer_id);
                    $vtype = 'CR';
                    /****************/
                    $customer_credit = array(
                        'chart_of_account_id' => $customer->coa->id,
                        'warehouse_id'        => $request->warehouse_id,
                        'voucher_no'          => $request->voucher_no,
                        'voucher_type'        => $vtype,
                        'voucher_date'        => $request->voucher_date,
                        'description'         => $request->remarks,
                        'debit'               => 0,
                        'credit'              => $request->amount,
                        'posted'              => 1,
                        'approve'             => 1,
                        'created_by'          => auth()->user()->name,
                        'created_at'          => date('Y-m-d H:i:s')
                    );
                    if($request->payment_type == 1){
                        //Cah In Hand For Supplier
                        $payment = array(
                            'chart_of_account_id' => $request->account_id,
                            'warehouse_id'        => $request->warehouse_id,
                            'voucher_no'          => $request->voucher_no,
                            'voucher_type'        => $vtype,
                            'voucher_date'        => $request->voucher_date,
                            'description'         => 'Cash In Hand For ' . $customer->name,
                            'debit'               => $request->amount,
                            'credit'              => 0,
                            'posted'              => 1,
                            'approve'             => 1,
                            'created_by'          => auth()->user()->name,
                            'created_at'          => date('Y-m-d H:i:s')
                            
                        );
                    }else{
                        // Bank Ledger
                        $payment = array(
                            'chart_of_account_id' => $request->account_id,
                            'warehouse_id'        => $request->warehouse_id,
                            'voucher_no'          => $request->voucher_no,
                            'voucher_type'        => $vtype,
                            'voucher_date'        => $request->voucher_date,
                            'description'         => 'Customer Receive From ' . $customer->name,
                            'debit'               => $request->amount,
                            'credit'              => 0,
                            'posted'              => 1,
                            'approve'             => 1,
                            'created_by'          => auth()->user()->name,
                            'created_at'          => date('Y-m-d H:i:s')
                        );
                    }

                    $customer_transaction = $this->model->create($customer_credit);
                    $payment_transaction = $this->model->create($payment);
                    if($customer_transaction && $payment_transaction){
                        $output = ['status'=>'success','message' => 'Payment Data Saved Successfully'];
                        $output['customer_transaction'] = $customer_transaction->id;
                    }else{
                        $output = ['status'=>'error','message' => 'Failed To Save Payment Data'];
                    }
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
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

    public function show(int $id,int $payment_type)
    {
        if(permission('customer-receive-access')){
            $this->setPageData('Customer Receive Voucher Print','Customer Receive Voucher Print','far fa-money-bill-alt',[['name'=>'Customer Receive Voucher Print']]);
            $data = $this->model->with('coa')->find($id);
            return view('account::customer-receive.print',compact('data','payment_type'));
        }else{
            return $this->access_blocked();
        }
    }
}
