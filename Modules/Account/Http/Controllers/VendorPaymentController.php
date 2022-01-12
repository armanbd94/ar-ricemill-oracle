<?php

namespace Modules\Account\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Vendor\Entities\Vendor;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\Transaction;
use Modules\Account\Http\Requests\VendorPaymentFormRequest;

class VendorPaymentController extends BaseController
{
    public function __construct(Transaction $model)
    {
        $this->model = $model;
    }


    public function index()
    {
        if(permission('vendor-payment-access')){
            $this->setPageData('Supplier Payment','Supplier Payment','far fa-money-bill-alt',[['name'=>'Accounts'],['name'=>'Supplier Payment']]);
            $voucher_no = 'PM-'.date('ymd').rand(1,999);
            $vendors = Vendor::allVendors();
            return view('account::vendor-payment.index',compact('voucher_no','vendors'));
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
                    $vendor = Vendor::with('coa')->find($request->vendor_id);
                    $vtype = 'PM';
                    /****************/
                    $vendordebit = array(
                        'chart_of_account_id' => $vendor->coa->id,
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
                    }else{
                        // Bank Ledger
                        $payment = array(
                            'chart_of_account_id' => $request->account_id,
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
                    }

                    $vendor_transaction = $this->model->create($vendordebit);
                    $payment_transaction = $this->model->create($payment);
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
}
