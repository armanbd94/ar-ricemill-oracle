<?php

namespace Modules\Account\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Customer\Entities\Customer;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\ChartOfAccount;
use Modules\Account\Entities\ReceivePayment;
use Modules\Account\Http\Requests\CustomerReceiveFormRequest;

class CustomerReceiveController extends BaseController
{
    public function __construct(ReceivePayment $model)
    {
        $this->model = $model;
    }


    public function index()
    {
        if(permission('receive-payment-access')){
            $this->setPageData('Receive Payment List','Receive Payment List','far fa-money-bill-alt',[['name'=>'Accounts'],['name'=>'Receive Payment List']]);
            $customers = Customer::with('coa')->get();
            return view('account::receive-payment.list',compact('customers'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('receive-payment-access')){

                if (!empty($request->start_date)) {
                    $this->model->setStartDate($request->start_date);
                }
                if (!empty($request->end_date)) {
                    $this->model->setEndDate($request->end_date);
                }
                if (!empty($request->customer_coa_id)) {
                    $this->model->setCustomerCOAID($request->customer_coa_id);
                }

                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('receive-payment-edit')){
                        $action .= ' <a class="dropdown-item" href="'.url("receive-payment/edit",$value->voucher_no).'">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }

                    if(permission('receive-payment-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->voucher_no . '" data-name="' . $value->voucher_no . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }

                    $row = [];
                    $row[] = $no;
                    $row[] = $value->voucher_no;
                    $row[] = date('d-M-Y',strtotime($value->voucher_date));;
                    $row[] = $value->description;
                    $row[] = number_format($value->credit,2);
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
        if(permission('receive-payment-access')){
            $this->setPageData('Customer Receive','Customer Receive','far fa-money-bill-alt',[['name'=>'Accounts'],['name'=>'Customer Receive']]);
            $voucher_no = 'CR-'.date('ymd').rand(1,999);
            $customers = Customer::with('coa')->get();
            return view('account::receive-payment.create',compact('voucher_no','customers'));
        }else{
            return $this->access_blocked();
        }
    }

    public function store(CustomerReceiveFormRequest $request)
    {
        if($request->ajax()){
            if(permission('receive-payment-access')){
                DB::beginTransaction();
                try {
                    $customer = Customer::with('coa')->find($request->customer_id);
                    $transaction = ReceivePayment::customer_payment([
                        'customer_coa_id'    => $customer->coa->id,
                        'payment_account_id' => $request->account_id,
                        'voucher_no'         => $request->voucher_no,
                        'voucher_date'       => $request->voucher_date,
                        'description'        => $request->remarks,
                        'amount'             => $request->amount,
                        'payment_type'       => $request->payment_type
                    ]);

                    $customer_transaction = $this->model->create($transaction->customer_transaction);
                    $payment_transaction  = $this->model->create($transaction->payment_account_transaction);
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
        if(permission('receive-payment-access')){
            $this->setPageData('Customer Receive Voucher Print','Customer Receive Voucher Print','far fa-money-bill-alt',[['name'=>'Customer Receive Voucher Print']]);
            $data = $this->model->with('coa')->find($id);
            return view('account::receive-payment.print',compact('data','payment_type'));
        }else{
            return $this->access_blocked();
        }
    }

    public function edit($voucher_no)
    {
        if(permission('receive-payment-edit')){
            $voucher_data = $this->model->where('voucher_no',$voucher_no)->get();
            if($voucher_data)
            {
                $this->setPageData('Edit Receive Payment','Edit Receive Payment','far fa-money-bill-alt',[['name'=>'Accounts'],['name'=>'Edit Receive Payment']]);
                $customers = Customer::with('coa')->get();
                $due_amount = Customer::customer_balance($voucher_data[0]->coa->customer_id);

                $due_amount = $due_amount + $voucher_data[0]->credit;

                if($voucher_data[1]->coa->parent_name == 'Cash & Cash Equivalent'){
                    $payment_method = 1;
                    $accounts = ChartOfAccount::where(['parent_name' =>  'Cash & Cash Equivalent','status'=>1])->get();
                }elseif ($voucher_data[1]->coa->parent_name == 'Cash At Bank') {
                    $payment_method = 2;
                    $accounts = ChartOfAccount::where('code', 'like', $this->coa_head_code('cash_at_bank').'%')->where('status',1)->get();
                }elseif ($voucher_data[1]->coa->parent_name == 'Cash At Mobile Bank') {
                    $payment_method = 3;
                    $accounts = ChartOfAccount::where('code', 'like', $this->coa_head_code('cash_at_mobile_bank').'%')->where('status',1)->get();
                }
                $account_list = '';
                if ($accounts) {
                    foreach ($accounts as $account) {
                        if($account->code != 1020102 && $account->code != 1020103){
                            $balance = DB::table('transactions')
                            ->select(DB::raw("SUM(debit) - SUM(credit) as balance"))
                            ->where([['chart_of_account_id',$account->id],['approve',1]])
                            ->first();
                            $selected = $voucher_data[1]->chart_of_account_id == $account->id ? 'selected' : '';
                            $account_list .= "<option value='$account->id' ".$selected." data-balance='$balance->balance'>".$account->name." [ Balance: ".($balance ? number_format(($voucher_data[1]->chart_of_account_id == $account->id ? ($balance->balance - $voucher_data[1]->debit) : $balance->balance),2,'.',',') : '0.00')."Tk]</option>";
                        }
                    }
                }
                return view('account::receive-payment.edit',compact('voucher_data','customers','due_amount','payment_method','account_list'));
            }else{
                return redirect()->back();
            }
        }else{
            return $this->access_blocked();
        }
    }


    public function update(CustomerReceiveFormRequest $request)
    {
        if($request->ajax()){
            if(permission('receive-payment-edit')){
                DB::beginTransaction();
                try {
                    $this->model->where('voucher_no',$request->update_voucher_no)->delete();
                    $customer = Customer::with('coa')->find($request->customer_id);
                    $transaction = ReceivePayment::customer_payment([
                        'customer_coa_id'    => $customer->coa->id,
                        'payment_account_id' => $request->account_id,
                        'voucher_no'         => $request->voucher_no,
                        'voucher_date'       => $request->voucher_date,
                        'description'        => $request->remarks,
                        'amount'             => $request->amount,
                        'payment_type'       => $request->payment_type
                    ]);

                    $customer_transaction = $this->model->create($transaction->customer_transaction);
                    $payment_transaction  = $this->model->create($transaction->payment_account_transaction);
                    if($customer_transaction && $payment_transaction){
                        $output = ['status'=>'success','message' => 'Payment Data Updated Successfully'];
                        $output['customer_transaction'] = $customer_transaction->id;
                    }else{
                        $output = ['status'=>'error','message' => 'Failed To Update Payment Data'];
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


    public function delete(Request $request)
    {
        if($request->ajax()){
            if(permission('receive-payment-delete')){
                try {
                    $result  = $this->model->where('voucher_no',$request->id)->delete();
                    $output   = $this->delete_message($result);
                } catch (\Throwable $th) {
                    $output = ['status' => 'error','message' => $th->getMessage()];
                }
                return response()->json($output);
            }else{
                return response()->json($this->unauthorized());
            }
        }else{
            return response()->json($this->unauthorized());
        }
    } 
}
