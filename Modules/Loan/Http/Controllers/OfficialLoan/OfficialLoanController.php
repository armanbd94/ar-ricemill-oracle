<?php

namespace Modules\Loan\Http\Controllers\OfficialLoan;

use Exception;
use Illuminate\Http\Request;
use Modules\Loan\Entities\Loans;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\HRM\Entities\Employee;
use Modules\Loan\Entities\LoanPeople;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\Transaction;
use Modules\Account\Entities\ChartOfAccount;
use Modules\Loan\Http\Requests\OfficialLoanFormRequest;
use Modules\Loan\Http\Requests\PersonalLoanFormRequest;

class OfficialLoanController extends BaseController
{ 
    private const VOUCHER_PREFIX = 'EMPSALOL';
    public function __construct(Loans $model)
    {
        $this->model = $model;
    }      

    public function index()
    {
        if(permission('official-loan-access')){
            $this->setPageData('Manage Office Loan','Manage Office Loan','far fa-money-bill-alt',[['name'=>'Loan','link'=>'javascript::void();'],['name' => 'Manage Office Loan']]);
            $data = [
                'voucher_no'             => self::VOUCHER_PREFIX.'-'.date('Ymd').rand(1,999),
                'employees'        =>  Employee::toBase()->where('status', 1)->get()
                ];
            return view('loan::official-loan.index',$data);
        }else{
            return $this->access_blocked();
        }
    }    

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('official-loan-access')){

                if (!empty($request->employee_id)) {
                    $this->model->setEmployee($request->employee_id);
                }
                $this->model->setLoanType(2);
                
                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('official-loan-edit')){
                        if($value->approve == 2){
                            $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.self::ACTION_BUTTON['Edit'].'</a>';
                        }
                    }

                    if(permission('official-loan-delete')){
                        if($value->deletable == 2 && $value->approve == 2){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->name . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                        }
                    }

                    $row = [];
                    if(permission('official-loan-bulk-delete')){
                        $row[] = ($value->deletable == 2) ? row_checkbox($value->id) : '';//custom helper function to show the table each row checkbox
                    }
                    $row[] = $no;
                    $row[] = $value->voucher_no;
                    $row[] = $value->employeeDetails->name;
                    $row[] = $value->employeeDetails->phone;
                    $row[] = date(config('settings.date_format'),strtotime($value->adjusted_date));
                    $row[] = $value->amount;
                    $row[] = $value->adjust_amount;
                    $row[] = $value->purpose;
                    $row[] = permission('official-loan-edit') ? change_status($value->id,$value->status, $value->employeeDetails->name) : STATUS_LABEL[$value->status];                    
                    $row[] = permission('voucher-approve') ? '<span class="label label-success label-pill label-inline approve_voucher" data-id="' . $value->voucher_no . '" data-name="' . $value->voucher_no . '" data-status="1" style="min-width:70px !important;cursor:pointer;">Is Approved?</span>' : 'Not Approved Yet';

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

    
    public function store_or_update_data(OfficialLoanFormRequest $request)
    {
        if($request->ajax()){
            if(permission('official-loan-add')){
                $collection   = collect($request->validated());
                $collection['month_year'] = date('F-Y',strtotime($request->adjusted_date));
                $collection['loan_type'] = 2;
                $collection   = $this->track_data($collection,$request->update_id);
                $result       = $this->model->updateOrCreate(['id'=>$request->update_id],$collection->all());                
                
                if (empty($request->update_id)) {
                    $coa_max_code      = ChartOfAccount::where('level', 4)->where('code', 'like', '1020202001%')->max('code');
                    $code              = $coa_max_code ? ($coa_max_code + 1) : 1020202001;
                    //dd($collection);
                    $head_name         = $collection['employee_id'] . '-' . Employee::where('id',$collection['employee_id'])->first()->name. '-OLR';
                    if(empty(ChartOfAccount::where('name',$head_name)->first()->id)){
                        $official_coa_data = $this->official_person_coa($code, $head_name);                    
                        $official_coa      = ChartOfAccount::create($official_coa_data);
                    }
                    $this->official_loan_coa($result);
                } else {
                    Transaction::where('voucher_no',$request->voucher_no)->delete();
                    $this->official_loan_coa($result);  
                }              
                $output       = $this->store_message($result, $request->update_id);
                $this->model->flushCache();
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }    

    
    private function official_person_coa(string $code, string $head_name)
    {
        return [
            'code'              => $code,
            'name'              => $head_name,
            'parent_name'       => 'Loan Receivable',
            'level'             => 4,
            'type'              => 'A',
            'is_transaction'       => 2,
            'general_ledger'    => 1,
            'budget'            => 2,
            'depreciation'      => 2,
            'depreciation_rate' => '0',
            'status'            => 1,
            'created_by'        => auth()->user()->name
        ];
    }


    private function official_loan_coa($collection)
    {        
        DB::beginTransaction();
        if(permission('official-loan-add')){
            try {
                $employeeInfo = Employee::find($collection['employee_id']);
                //dd($reault);

                $crdit_account = ChartOfAccount::where('name',$employeeInfo->id.'-'.$employeeInfo->name.'-OLR')->first();
                //Official Loan People which is Crdit for the Employee
                $credit_debit_voucher_transaction[] = array(
                    'chart_of_account_id' => $crdit_account->id,
                    'warehouse_id'        => 1,
                    'voucher_no'          => $collection['voucher_no'],
                    'voucher_type'        => self::VOUCHER_PREFIX,
                    'voucher_date'        => $collection['adjusted_date'],
                    'description'         => $collection['purpose'],
                    'debit'               => $collection['amount'],
                    'credit'              => 0,
                    'posted'              => 1,
                    'approve'             => 2,
                    'created_by'          => auth()->user()->name,
                    'created_at'          => date('Y-m-d H:i:s')
                );                    
                
                //Cash In Insert which is Debit for the company
                $debit_account = ChartOfAccount::find($collection['account_id']);
                $credit_debit_voucher_transaction[] = array(
                    'chart_of_account_id' => $debit_account->id,
                    'warehouse_id'        => 1,
                    'voucher_no'          => $collection['voucher_no'],
                    'voucher_type'        => self::VOUCHER_PREFIX,
                    'voucher_date'        => $collection['adjusted_date'],
                    'description'         => 'Credit Loan Voucher From '.($debit_account ? $debit_account->name : ''),
                    'debit'               => 0,
                    'credit'              => $collection['amount'],
                    'posted'              => 1,
                    'approve'             => 2,
                    'created_by'          => auth()->user()->name,
                    'created_at'          => date('Y-m-d H:i:s')
                );
                //dd($debit_credit_voucher_transaction);
                $result = Transaction::insert($credit_debit_voucher_transaction);
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
            }
        }else{
            $output = $this->unauthorized();
        }
    }

    public function edit(Request $request)
    {
        if($request->ajax()){
            if(permission('official-loan-edit')){
                $data   = $this->model->findOrFail($request->id);
                $output = $this->data_message($data); //if data found then it will return data otherwise return error message
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
            if(permission('official-loan-delete')){            
                $get_personal_loan_info = $this->model->where('id',$request->id)->first();
                Transaction::where('voucher_no',$get_personal_loan_info->voucher_no)->delete();
                $result   = $this->model->find($request->id)->delete();    
                $output   = $this->delete_message($result);
                $this->model->flushCache();
            }else{
                $output   = $this->unauthorized();

            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }
    public function bulk_delete(Request $request)
    {
        if($request->ajax()){
            if(permission('official-loan-bulk-delete')){                
                $get_personal_loan_info = $this->model->where('id',$request->ids)->first();
                Transaction::where('voucher_no',$get_personal_loan_info->voucher_no)->delete();
                $result   = $this->model->destroy($request->ids);
                $output   = $this->bulk_delete_message($result);
                $this->model->flushCache();
            }else{
                $output   = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function change_status(Request $request)
    {
        if($request->ajax()){
            if(permission('official-loan-edit')){
                $result   = $this->model->find($request->id)->update(['status' => $request->status]);
                $output   = $result ? ['status' => 'success','message' => 'Status Has Been Changed Successfully']
                : ['status' => 'error','message' => 'Failed To Change Status'];
                $this->model->flushCache();
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }   

    public function employee_id_wise_employee_loan_details(int $id)
    {        
        $loans = $this->model->where('employee_id',$id)->get();
        //dd($loans);
        $output = '';
        if ($loans) {
            $output .= '<option value="">Select Please</option>';
            foreach ($loans as $loan) {
                if($loan->amount >= $loan->total_adjusted_amount){
                    $output .= "<option value='$loan->id'>$loan->amount</option>";
                }
            }
        }
        return json_encode($output);
    }

    public function approve(Request $request)
    {
        if($request->ajax()){
            if(permission('voucher-approve')){
                $result   = $this->model->where('voucher_no',$request->id)->update(['approve' => $request->status]);
                $result   = Transaction::where('voucher_no',$request->id)->update(['approve' => $request->status]);
                $output   = $result ? ['status' => 'success','message' => 'Voucher Approved Successfully']
                : ['status' => 'error','message' => 'Failed To Approve Voucher'];
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }
}
