<?php

namespace Modules\Supplier\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Supplier\Entities\Supplier;
use App\Http\Controllers\BaseController;
use Modules\Supplier\Entities\SupplierAdvance;
use Modules\Supplier\Http\Requests\SupplierAdvanceFormRequest;

class SupplierAdvanceController extends BaseController
{
    public function __construct(SupplierAdvance $model)
    {
        $this->model = $model;
    }


    public function index()
    {
        if(permission('vendor-advance-access')){
            $this->setPageData('Supplier Advance','Supplier Advance','fas fa-hand-holding-usd',[['name'=>'Supplier','link'=>route('supplier')],['name'=>'Supplier Advance']]);
            $suppliers = Supplier::with('coa')->where(['status'=>1])->orderBy('name','asc')->get();
            return view('supplier::advance.index',compact('suppliers'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('vendor-advance-access')){

                if (!empty($request->supplier_id)) {
                    $this->model->setSupplierID($request->supplier_id);
                }
                if (!empty($request->type)) {
                    $this->model->setType($request->type);
                }

                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('vendor-advance-edit')){
                        $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if(permission('vendor-advance-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->voucher_no . '" data-name="' . $value->name . ' advance ">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }
                    if(permission('vendor-advance-approve')){
                        if($value->approve != 1){
                            $action .= ' <a class="dropdown-item change_status"  data-id="' . $value->voucher_no . '" data-name="' . $value->voucher_no . '" data-status="'.$value->approve.'"><i class="fas fa-check-circle text-success mr-2"></i> Change Status</a>';
                        }
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
                    if(permission('vendor-advance-bulk-delete')){
                        $row[] = row_checkbox($value->voucher_no);
                    }
                    $row[] = $no;
                    $row[] = $value->name.' - '.$value->mobile;
                    $row[] = ($value->debit != 0) ? 'Payment' : 'Receive' ;
                    $row[] = ($value->debit != 0) ? $value->debit : $value->credit;
                    $row[] = APPROVE_STATUS_LABEL[$value->approve];
                    $row[] = date(config('settings.date_format'),strtotime($value->created_at));
                    $row[] = $payment_method;
                    $row[] = $account->coa->name;
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

    private function account_data(string $voucher_no) : object
    {
        return $this->model->with('coa')->where('voucher_no',$voucher_no)->orderBy('id','desc')->first();

    }

    public function store_or_update_data(SupplierAdvanceFormRequest $request)
    {
        if($request->ajax()){
            if(permission('vendor-advance-add') || permission('vendor-advance-edit')){
                DB::beginTransaction();
                try {
                    // dd($request->all());
                    if(empty($request->id)){
                        $result = $this->advance_add($request->type,$request->amount,$request->supplier_coaid,$request->supplier_name,$request->payment_method,$request->account_id,$request->cheque_number);
                        $output = $this->store_message($result, $request->id);
                    }else{
                        $result = $this->advance_update($request->id,$request->type,$request->amount,$request->supplier_coaid,$request->supplier_name,$request->payment_method,$request->account_id,$request->cheque_number);
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

    private function advance_add(string $type, $amount, int $supplier_coa_id, string $supplier_name,int $payment_method, int $account_id, string $cheque_number = null) {
        if(!empty($type) && !empty($amount) && !empty($supplier_coa_id) && !empty($supplier_name)){
            $transaction_id = generator(10);

            $supplier_accledger = array(
                'warehouse_id'        => 1,
                'chart_of_account_id' => $supplier_coa_id,
                'voucher_no'          => $transaction_id,
                'voucher_type'        => 'Advance',
                'voucher_date'        => date("Y-m-d"),
                'description'         => 'Supplier Advance For '.$supplier_name,
                'debit'               => ($type == 'debit') ? $amount : 0,
                'credit'              => ($type == 'credit') ? $amount : 0,
                'posted'              => 1,
                'approve'             => 2,
                'created_by'          => auth()->user()->name,
                'created_at'          => date('Y-m-d H:i:s')
            );
            if($payment_method == 1){
                $note = 'Cash in Hand For '.$supplier_name;
            }elseif ($payment_method == 2) {
                $note = $cheque_number;
            }else{
                $note = 'Cash at Mobile Bank For '.$supplier_name;
            }
            
            $cc = array(
                'warehouse_id'        => 1,
                'chart_of_account_id' => $account_id,
                'voucher_no'          => $transaction_id,
                'voucher_type'        => 'Advance',
                'voucher_date'        => date("Y-m-d"),
                'description'         => $note,
                'debit'               => ($type == 'debit') ? $amount : 0,
                'credit'              => ($type == 'credit') ? $amount : 0,
                'posted'              => 1,
                'approve'             => 2,
                'created_by'          => auth()->user()->name,
                'created_at'          => date('Y-m-d H:i:s')
            ); 

            return $this->model->insert([
                $supplier_accledger,$cc
            ]);
        }
    }

    private function advance_update(int $transaction_id, string $type, $amount, int $supplier_coa_id, string $supplier_name,int $payment_method, int $account_id, string $cheque_number = null) {
        if(!empty($type) && !empty($amount) && !empty($supplier_coa_id) && !empty($supplier_name)){
           
            $supplier_advance_data = $this->model->find($transaction_id);

            $voucher_no = $supplier_advance_data->voucher_no;

            $updated = $supplier_advance_data->update([
                'description'         => 'Supplier Advance For '.$supplier_name,
                'debit'               => ($type == 'debit') ? $amount : 0,
                'credit'              => ($type == 'credit') ? $amount : 0,
                'modified_by'         => auth()->user()->name,
                'updated_at'          => date('Y-m-d H:i:s')
            ]);
            if($updated)
            {
                if($payment_method == 1){
                    $note = 'Cash in Hand For '.$supplier_name;
                }elseif ($payment_method == 2) {
                    $note = $cheque_number;
                }else{
                    $note = 'Cash at Mobile Bank For '.$supplier_name;
                }
                
                $account = $this->model->where('voucher_no', $voucher_no)->orderBy('id','desc')->first();
                if($account){
                    $account->update([
                        'chart_of_account_id' => $account_id,
                        'description'         => $note,
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
            if(permission('vendor-advance-edit')){
                $data   = $this->model->select('transactions.*','coa.id as coa_id','coa.code','s.id as supplier_id','s.name','s.mobile')->join('chart_of_accounts as coa','transactions.chart_of_account_id','=','coa.id')
                ->join('suppliers as s','coa.supplier_id','s.id')->where('transactions.id',$request->id)->first();
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
                        'supplier_id'    => $data->supplier_id,
                        'type'           => ($data->debit != 0) ? 'debit' : 'credit',
                        'amount'         => ($data->debit != 0) ? $data->debit : $data->credit,
                        'payment_method' => $payment_method,
                        'account_id'     => $account->chart_of_account_id,
                        'cheque_no'      => ($payment_method == 2) ? $account->description : '',
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
            if(permission('vendor-advance-delete')){
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
            if(permission('vendor-advance-bulk-delete')){
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

    public function change_approval_status(Request $request)
    {
        if($request->ajax())
        {
            if(permission('vendor-advance-approve'))
            {
                DB::beginTransaction();
                    try {
                        $result = $this->model->where('voucher_no',$request->approve_id)->update([
                            'approve'     => $request->approval_status,
                            'modified_by' => auth()->user()->name,
                            'updated_at'  => date('Y-m-d H:i:s')
                            ]);
                        if($result)
                        {
                            $output = ['status'=>'success','message'=>'Approval Status Changed Successfully'];
                        }else{
                            $output = ['status'=>'error','message'=>'Failed To Change Approval Status'];
                        }
                        DB::commit();
                    } catch (\Throwable $th) {
                        DB::rollback();
                        $output = ['status'=>'error','message'=>$th->getMessage()];
                    }
                    return response()->json($output);
            }else{
                return response()->json($this->unauthorized());
            }
        }
    }

}
