<?php

namespace Modules\Account\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Vendor\Entities\Vendor;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\VendorPayment;
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
                        $action .= ' <a class="dropdown-item" href="'.route("vendor.payment.edit",$value->voucher_no).'">'.self::ACTION_BUTTON['Edit'].'</a>';
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
            $vendors = Vendor::allVendors();
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
                    $vendor = Vendor::with('coa')->find($request->vendor_id);
                    $transaction = VendorPayment::vendor_payment([
                        'vendor_coa_id'      => $vendor->coa->id,
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
