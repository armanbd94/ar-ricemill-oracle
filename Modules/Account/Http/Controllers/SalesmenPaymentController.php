<?php

namespace Modules\Account\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\SalesMen\Entities\Salesmen;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\Transaction;
use Modules\Account\Http\Requests\SalesmenPaymentFormRequest;

class SalesmenPaymentController extends BaseController
{
    public function __construct(Transaction $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('salesmen-payment-access')){
            $this->setPageData('Salesmen Payment','Salesmen Payment','far fa-money-bill-alt',[['name'=>'Accounts'],['name'=>'Salesmen Payment']]);
            $voucher_no = 'SPM-'.date('ymd').rand(1,999);
            $salesmen = DB::table('salesmen')->where([['status',1]])->select('name','id','phone')->get() ;
            return view('account::salesmen-payment.index',compact('voucher_no','salesmen'));
        }else{
            return $this->access_blocked();
        }
    }

    public function store(SalesmenPaymentFormRequest $request)
    {
        if($request->ajax()){
            if(permission('salesmen-payment-access')){
                DB::beginTransaction();
                try {
                    $salesmen = Salesmen::with('coa')->find($request->salesmen_id);
                    $vtype = 'SPM';
                    /****************/
                    $salesmendebit = array(
                        'chart_of_account_id' => $salesmen->coa->id,
                        'warehouse_id'        => 1,
                        'voucher_no'          => $request->voucher_no,
                        'voucher_type'        => $vtype,
                        'voucher_date'        => $request->voucher_date,
                        'description'         => $request->remarks,
                        'debit'               => $request->amount,
                        'credit'              => 0,
                        'posted'              => 1,
                        'approve'             => 1,
                        'created_by'          => auth()->user()->name,
                        'created_at'          => date('Y-m-d H:i:s')
                    );
                    if($request->payment_type == 1){
                        //Cah In Hand For Salesmen
                        $payment = array(
                            'chart_of_account_id' => $request->account_id,
                            'warehouse_id'        => 1,
                            'voucher_no'          => $request->voucher_no,
                            'voucher_type'        => $vtype,
                            'voucher_date'        => $request->voucher_date,
                            'description'         => 'Paid to ' . $salesmen->name,
                            'debit'               => 0,
                            'credit'              => $request->amount,
                            'posted'              => 1,
                            'approve'             => 1,
                            'created_by'          => auth()->user()->name,
                            'created_at'          => date('Y-m-d H:i:s')
                            
                        );
                    }else{
                        // Bank Ledger
                        $payment = array(
                            'chart_of_account_id' => $request->account_id,
                            'warehouse_id'        => 1,
                            'voucher_no'          => $request->voucher_no,
                            'voucher_type'        => $vtype,
                            'voucher_date'        => $request->voucher_date,
                            'description'         => 'Salesmen Payment To ' . $salesmen->name,
                            'debit'               => 0,
                            'credit'              => $request->amount,
                            'posted'              => 1,
                            'approve'             => 1,
                            'created_by'          => auth()->user()->name,
                            'created_at'          => date('Y-m-d H:i:s')
                        );
                    }

                    $salesmen_transaction = $this->model->create($salesmendebit);
                    $payment_transaction = $this->model->create($payment);
                    if($salesmen_transaction && $payment_transaction){
                        $output = ['status'=>'success','message' => 'Payment Data Saved Successfully'];
                        $output['salesmen_transaction'] = $salesmen_transaction->id;
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
        if(permission('salesmen-payment-access')){
            $this->setPageData('Salesmen Payment Voucher Print','Salesmen Payment Voucher Print','far fa-money-bill-alt',[['name'=>'Salesmen Payment Voucher Print']]);
            $data = $this->model->with('coa')->find($id);
            return view('account::salesmen-payment.print',compact('data','payment_type'));
        }else{
            return $this->access_blocked();
        }
    }
}
