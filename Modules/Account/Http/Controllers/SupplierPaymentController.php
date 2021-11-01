<?php

namespace Modules\Account\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Supplier\Entities\Supplier;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\Transaction;
use Modules\Account\Http\Requests\SupplierPaymentFormRequest;

class SupplierPaymentController extends BaseController
{
    public function __construct(Transaction $model)
    {
        $this->model = $model;
    }


    public function index()
    {
        if(permission('supplier-payment-access')){
            $this->setPageData('Supplier Payment','Supplier Payment','far fa-money-bill-alt',[['name'=>'Accounts'],['name'=>'Supplier Payment']]);
            $voucher_no = 'PM-'.date('ymd').rand(1,999);
            $suppliers = Supplier::where('status',1)->get();
            return view('account::supplier-payment.index',compact('voucher_no','suppliers'));
        }else{
            return $this->access_blocked();
        }
    }

    public function store(SupplierPaymentFormRequest $request)
    {
        if($request->ajax()){
            if(permission('supplier-payment-access')){
                DB::beginTransaction();
                try {
                    $supplier = Supplier::with('coa')->find($request->supplier_id);
                    $vtype = 'PM';
                    /****************/
                    $supplierdebit = array(
                        'chart_of_account_id' => $supplier->coa->id,
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
                        //Cah In Hand For Supplier
                        $payment = array(
                            'chart_of_account_id' => $request->account_id,
                            'warehouse_id'        => 1,
                            'voucher_no'          => $request->voucher_no,
                            'voucher_type'        => $vtype,
                            'voucher_date'        => $request->voucher_date,
                            'description'         => 'Paid to ' . $supplier->name,
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
                            'description'         => 'Supplier Payment To ' . $supplier->name,
                            'debit'               => 0,
                            'credit'              => $request->amount,
                            'posted'              => 1,
                            'approve'             => 1,
                            'created_by'          => auth()->user()->name,
                            'created_at'          => date('Y-m-d H:i:s')
                        );
                    }

                    $supplier_transaction = $this->model->create($supplierdebit);
                    $payment_transaction = $this->model->create($payment);
                    if($supplier_transaction && $payment_transaction){
                        $output = ['status'=>'success','message' => 'Payment Data Saved Successfully'];
                        $output['supplier_transaction'] = $supplier_transaction->id;
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
        if(permission('supplier-payment-access')){
            $this->setPageData('Supplier Payment Voucher Print','Supplier Payment Voucher Print','far fa-money-bill-alt',[['name'=>'Supplier Payment Voucher Print']]);
            $data = $this->model->with('coa')->find($id);
            return view('account::supplier-payment.print',compact('data','payment_type'));
        }else{
            return $this->access_blocked();
        }
    }
}
