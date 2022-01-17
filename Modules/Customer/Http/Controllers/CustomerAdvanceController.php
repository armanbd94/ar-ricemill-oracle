<?php

namespace Modules\Customer\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Customer\Entities\Customer;
use App\Http\Controllers\BaseController;
use Modules\Customer\Entities\CustomerAdvance;
use Modules\Customer\Http\Requests\CustomerAdvanceFormRequest;

class CustomerAdvanceController extends BaseController
{
    public function __construct(CustomerAdvance $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('customer-advance-access')){
            $this->setPageData('Customer Advance','Customer Advance','fas fa-hand-holding-usd',[['name'=>'Customer','link'=>route('customer')],['name'=>'Customer Advance']]);
            $customers = Customer::with('coa')->where(['status'=>1])->orderBy('id','asc')->get();
            return view('customer::advance.index',compact('customers'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if (!empty($request->customer_id)) {
                $this->model->setCustomerID($request->customer_id);
            }
            if (!empty($request->type)) {
                $this->model->setType($request->type);
            }
            if (!empty($request->start_date)) {
                $this->model->setStartDate($request->start_date);
            }
            if (!empty($request->end_date)) {
                $this->model->setEndDate($request->end_date);
            }

            $this->set_datatable_default_properties($request);//set datatable default properties
            $list = $this->model->getDatatableList();//get table data
            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $action = '';
                if(permission('customer-advance-edit')){
                $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.self::ACTION_BUTTON['Edit'].'</a>';
                }
                if(permission('customer-advance-delete')){
                $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->voucher_no . '" data-name="' . $value->name . ' advance ">'.self::ACTION_BUTTON['Delete'].'</a>';
                }
                $account = $this->account_data($value->voucher_no);

                if($account->coa->parent_name == 'Cash & Cash Equivalent'){
                    $payment_method = 'Cash';
                }elseif ($account->coa->parent_name == 'Cash At Bank') {
                    $payment_method = 'Cheque';
                }elseif ($account->coa->parent_name == 'Cash At Mobile Bank') {
                    $payment_method = 'Mobile Bank';
                }
                $row = [];

                $row[] = $no;
                $row[] = $value->customer_name;
                $row[] = $value->trade_name;
                $row[] = $value->mobile;
                $row[] = ($value->debit != 0) ? 'Payment' : 'Receive' ;
                $row[] = ($value->debit != 0) ? number_format($value->debit,2,'.',',') : number_format($value->credit,2,'.',',');
                $row[] = date(config('settings.date_format'),strtotime($value->created_at));
                $row[] = $payment_method;
                $row[] = $account->coa->name;
                $row[] = action_button($action);//custom helper function for action button
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
            $this->model->count_filtered(), $data);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    private function account_data(string $voucher_no) : object
    {
        return $this->model->with('coa')->where('voucher_no',$voucher_no)->orderBy('id','desc')->first();

    }

    public function store_or_update_data(CustomerAdvanceFormRequest $request)
    {
        if($request->ajax()){
            if(permission('customer-advance-add') || permission('customer-advance-edit')){
                DB::beginTransaction();
                try {

                    if(empty($request->id)){
                        $result = $this->advance_add($request->type,$request->amount,$request->customer_coa_id,$request->payment_method,$request->account_id,$request->description);
                        $output = $this->store_message($result, $request->id);
                    }else{
                        $result = $this->advance_update($request->id,$request->type,$request->amount,$request->customer_coa_id,$request->payment_method,$request->account_id,$request->description);
                        $output = $this->store_message($result, $request->id);
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

    private function advance_add(string $type, $amount, int $customer_coa_id, int $payment_method, int $account_id, string $description = null) {
        if(!empty($type) && !empty($amount) && !empty($customer_coa_id)){
            $transaction_id = generator(10);

            $customer_accledger = array(
                'chart_of_account_id' => $customer_coa_id,
                'voucher_no'          => $transaction_id,
                'voucher_type'        => 'Advance',
                'voucher_date'        => date("Y-m-d"),
                'description'         => $description,
                'debit'               => ($type == 'debit') ? $amount : 0,
                'credit'              => ($type == 'credit') ? $amount : 0,
                'posted'              => 1,
                'approve'             => 1,
                'created_by'          => auth()->user()->name,
                'created_at'          => date('Y-m-d H:i:s')
            );
            $cc = array(
                'chart_of_account_id' => $account_id,
                'voucher_no'          => $transaction_id,
                'voucher_type'        => 'Advance',
                'voucher_date'        => date("Y-m-d"),
                'description'         => $description,
                'debit'               => ($type == 'debit') ? $amount : 0,
                'credit'              => ($type == 'credit') ? $amount : 0,
                'posted'              => 1,
                'approve'             => 1,
                'created_by'          => auth()->user()->name,
                'created_at'          => date('Y-m-d H:i:s')
            ); 

            return $this->model->insert([
                $customer_accledger,$cc
            ]);
        }
    }

    private function advance_update(int $transaction_id, string $type, $amount, int $customer_coa_id, int $payment_method, int $account_id, string $description = null) {
        if(!empty($type) && !empty($amount) && !empty($customer_coa_id)){

            $customer_advance_data = $this->model->find($transaction_id);

            $voucher_no = $customer_advance_data->voucher_no;

            $updated = $customer_advance_data->update([
                'description'         => $description,
                'debit'               => ($type == 'debit') ? $amount : 0,
                'credit'              => ($type == 'credit') ? $amount : 0,
                'modified_by'         => auth()->user()->name,
                'updated_at'          => date('Y-m-d H:i:s')
            ]);
            if($updated)
            {
                $account = $this->model->where('voucher_no', $voucher_no)->orderBy('id','desc')->first();
                if($account){
                    $account->update([
                        'chart_of_account_id' => $account_id,
                        'description'         => $description,
                        'debit'               => ($type == 'debit') ? $amount : 0,
                        'credit'              => ($type == 'credit') ? $amount : 0,
                        'modified_by'         => auth()->user()->name,
                        'updated_at'          => date('Y-m-d H:i:s')
                    ]);
                }
                return true;
            }else{
                return false;
            }
           
        }
    }

    public function edit(Request $request)
    {
        if($request->ajax()){
            if(permission('customer-advance-edit')){
                $data   = $this->model->select('transactions.*','coa.id as coa_id','coa.code','c.id as customer_id')
                ->join('chart_of_accounts as coa','transactions.chart_of_account_id','=','coa.id')
                ->join('customers as c','coa.customer_id','c.id')
                ->where('transactions.id',$request->id)
                ->first();
                $account = $this->account_data($data->voucher_no);
                if($account->coa->parent_name == 'Cash & Cash Equivalent'){
                    $payment_method = 1;
                }elseif ($account->coa->parent_name == 'Cash At Bank') {
                    $payment_method = 2;
                }elseif ($account->coa->parent_name == 'Cash At Mobile Bank') {
                    $payment_method = 3;
                }
                $output = []; //if data found then it will return data otherwise return error message
                if($data){
                    $output = [
                        'id'             => $data->id,
                        'customer_coa_id'    => $data->coa_id,
                        'type'           => ($data->debit != 0) ? 'debit' : 'credit',
                        'amount'         => ($data->debit != 0) ? $data->debit : $data->credit,
                        'payment_method' => $payment_method,
                        'account_id'     => $account->chart_of_account_id,
                        'description'      => $account->description,
                    ];
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
            if(permission('customer-advance-delete')){
                $result   = $this->model->where('voucher_no',$request->id)->delete();
                $output   = $this->delete_message($result);
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function bulk_delete(Request $request)
    {
        if($request->ajax()){
            if(permission('customer-advance-bulk-delete')){
                $result   = $this->model->whereIn('voucher_no',$request->ids)->delete();
                $output   = $this->bulk_delete_message($result);
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function area_wise_customer_list(Request $request)
    {
        if($request->ajax()){
            $customers  = Customer::with('coa')->where([['status',1],['area_id',$request->area_id]])->orderBy('id','asc')->get();
            $output = '';
            if (!$customers->isEmpty()){
                $output .= '<option value="">Select Please</option>';
                foreach ($customers as $customer){
                    $output .=  '<option value="'.$customer->id.'" data-coaid="'.$customer->coa->id.'" data-name="'.$customer->name.'">'. $customer->name.' - '.$customer->mobile.'</option>';
                }
            }
            return $output;
        }
    }


}
