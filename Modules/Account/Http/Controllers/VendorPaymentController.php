<?php

namespace Modules\Account\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Vendor\Entities\Vendor;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\VendorPayment;
use Modules\Account\Entities\ChartOfAccount;
use Modules\Account\Http\Requests\VendorPaymentFormRequest;

class VendorPaymentController extends BaseController
{
    
    public function __construct(VendorPayment $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('vendor-payment-access')){
            $this->setPageData('Vendor Payment List','Vendor Payment List','far fa-money-bill-alt',[['name'=>'Accounts'],['name'=>'Vendor Payment List']]);
            $vendors = Vendor::with('coa')->get();
            return view('account::vendor-payment.list',compact('vendors'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('vendor-payment-access')){

                if (!empty($request->start_date)) {
                    $this->model->setStartDate($request->start_date);
                }
                if (!empty($request->end_date)) {
                    $this->model->setEndDate($request->end_date);
                }
                if (!empty($request->vendor_coa_id)) {
                    $this->model->setVendorCOAID($request->vendor_coa_id);
                }

                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('vendor-payment-edit')){
                        $action .= ' <a class="dropdown-item" href="'.url("vendor-payment/edit",$value->voucher_no).'">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }

                    if(permission('vendor-payment-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->voucher_no . '" data-name="' . $value->voucher_no . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }

                    $row = [];
                    $row[] = $no;
                    $row[] = $value->voucher_no;
                    $row[] = date('d-M-Y',strtotime($value->voucher_date));;
                    $row[] = $value->description;
                    $row[] = number_format($value->debit,2);
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
        if(permission('vendor-payment-add')){
            $this->setPageData('Vendor Payment','Vendor Payment','far fa-money-bill-alt',[['name'=>'Accounts'],['name'=>'Vendor Payment']]);
            $voucher_no = 'PM-'.date('ymd').rand(1,999);
            $vendors = Vendor::with('coa')->get();
            return view('account::vendor-payment.create',compact('voucher_no','vendors'));
        }else{
            return $this->access_blocked();
        }
    }


    public function store(VendorPaymentFormRequest $request)
    {
        if($request->ajax()){
            if(permission('vendor-payment-access')){
                DB::beginTransaction();
                try {

                    $transaction = VendorPayment::vendor_payment([
                        'vendor_coa_id'      => $request->vendor_coa_id,
                        'payment_account_id' => $request->account_id,
                        'voucher_no'         => $request->voucher_no,
                        'voucher_date'       => $request->voucher_date,
                        'description'        => $request->remarks,
                        'amount'             => $request->amount,
                        'payment_type'       => $request->payment_type
                    ]);

                    $vendor_transaction  = $this->model->create($transaction->vendor_transaction);
                    $payment_transaction = $this->model->create($transaction->payment_account_transaction);
                    if($vendor_transaction && $payment_transaction){
                        $output = ['status'=>'success','message' => 'Payment Data Saved Successfully'];
                        $output['vendor_transaction'] = $vendor_transaction->id;
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

    

    public function edit($voucher_no)
    {
        if(permission('vendor-payment-edit')){
            $voucher_data = $this->model->where('voucher_no',$voucher_no)->get();
            if($voucher_data)
            {
                $this->setPageData('Edit Cash Adjustment','Edit Cash Adjustment','far fa-money-bill-alt',[['name'=>'Accounts'],['name'=>'Edit Cash Adjustment']]);
                $vendors = Vendor::with('coa')->get();
                $due_amount = Vendor::vendor_balance($voucher_data[0]->coa->vendor_id);
                if($due_amount < 0)
                {
                    $due_amount = explode('-',$due_amount)[1];
                }
                $due_amount = $due_amount + $voucher_data[0]->debit;

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
                            $account_list .= "<option value='$account->id' ".$selected." data-balance='$balance->balance'>".$account->name." [ Balance: ".($balance ? number_format(($voucher_data[1]->chart_of_account_id == $account->id ? ($balance->balance + $voucher_data[1]->credit) : $balance->balance),2,'.',',') : '0.00')."Tk]</option>";
                        }
                    }
                }
                return view('account::vendor-payment.edit',compact('voucher_data','vendors','due_amount','payment_method','account_list'));
            }else{
                return redirect()->back();
            }
        }else{
            return $this->access_blocked();
        }
    }

    public function update(VendorPaymentFormRequest $request)
    {
        if($request->ajax()){
            if(permission('vendor-payment-edit')){
                DB::beginTransaction();
                try {
                    $this->model->where('voucher_no',$request->update_voucher_no)->delete();
                    $transaction = VendorPayment::vendor_payment([
                        'vendor_coa_id'      => $request->vendor_coa_id,
                        'payment_account_id' => $request->account_id,
                        'voucher_no'         => $request->voucher_no,
                        'voucher_date'       => $request->voucher_date,
                        'description'        => $request->remarks,
                        'amount'             => $request->amount,
                        'payment_type'       => $request->payment_type
                    ]);

                    $vendor_transaction  = $this->model->create($transaction->vendor_transaction);
                    $payment_transaction = $this->model->create($transaction->payment_account_transaction);
                    if($vendor_transaction && $payment_transaction){
                        $output = ['status'=>'success','message' => 'Payment Data Updated Successfully'];
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

    public function show(int $id,int $payment_type)
    {
        if(permission('vendor-payment-access')){
            $this->setPageData('Vendor Payment Voucher Print','Vendor Payment Voucher Print','far fa-money-bill-alt',[['name'=>'Vendor Payment Voucher Print']]);
            $data = $this->model->with('coa')->find($id);
            return view('account::vendor-payment.print',compact('data','payment_type'));
        }else{
            return $this->access_blocked();
        }
    }

    public function delete(Request $request)
    {
        if($request->ajax()){
            if(permission('vendor-payment-delete')){
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
