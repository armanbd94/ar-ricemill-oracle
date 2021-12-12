<?php

namespace Modules\Loan\Http\Controllers\PersonalLoan;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\HRM\Entities\Employee;
use Modules\Loan\Entities\LoanPeople;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\Transaction;
use Modules\Loan\Entities\LoanInstallment;
use Modules\Account\Entities\ChartOfAccount;
use Modules\Loan\Http\Requests\PersonalLoanInstallmentFormRequest;

class PersonalLoanInstallmentController extends BaseController
{    
    private const VOUCHER_PREFIX = 'PLI';
    public function __construct(LoanInstallment $model)
    {
        $this->model = $model;
    }      

    public function index()
    {
        if(permission('personal-loan-installment-access')){
            $this->setPageData('Manage Personal Loan Installment','Manage Personal Loan Installment','far fa-money-bill-alt',[['name'=>'Loan','link'=>'javascript::void();'],['name' => 'Manage Personal Loan Installment']]);
            $data = [
                'voucher_no'             => self::VOUCHER_PREFIX.'-'.date('Ymd').rand(1,999),
                'person_employees' => LoanPeople::toBase()->where('status', 1)->get(),
                'employees'        =>  Employee::toBase()->where('status', 1)->get()
                ];
            return view('loan::personal-loan-installment.index',$data);
        }else{
            return $this->access_blocked();
        }
    }    

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('personal-loan-installment-access')){

                if (!empty($request->person_id)) {
                    $this->model->setPerson($request->person_id);
                }
                $this->model->setLoanType(1);
                
                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('personal-loan-installment-edit')){
                        $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }

                    if(permission('personal-loan-installment-delete')){
                        if($value->deletable == 2){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->name . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                        }
                    }

                    $row = [];
                    if(permission('personal-loan-installment-bulk-delete')){
                        $row[] = ($value->deletable == 2) ? row_checkbox($value->id) : '';//custom helper function to show the table each row checkbox
                    }
                    $row[] = $no;
                    $row[] = $value->personDetails->name;
                    $row[] = $value->personDetails->phone;
                    $row[] = date(config('settings.date_format'),strtotime($value->installment_date));
                    $row[] = $value->loanDetails->amount;
                    $row[] = $value->installment_amount;
                    $row[] = $value->purpose;
                    $row[] = permission('personal-loan-installment-edit') ? change_status($value->id,$value->status, $value->personDetails->name) : STATUS_LABEL[$value->status];
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

    
    public function store_or_update_data(PersonalLoanInstallmentFormRequest $request)
    {
        if($request->ajax()){
            if(permission('personal-loan-installment-add')){
                $collection   = collect($request->validated());
                $collection['month_year'] = date('F-Y',strtotime($request->installment_date));
                $collection   = $this->track_data($collection,$request->update_id);
                $result       = $this->model->updateOrCreate(['id'=>$request->update_id],$collection->all());                
                

                if (empty($request->update_id)) {
                    $this->personal_loan_installment_coa($collection);
                } else {
                    Transaction::where('voucher_no',$request->voucher_no)->delete();
                    $this->personal_loan_installment_coa($collection);  
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

    private function personal_loan_installment_coa($collection)
    {        
        DB::beginTransaction();
        if(permission('personal-loan-installment-add')){
            try {
                $personInfo = LoanPeople::find($collection['person_id']);

                $credit_account = ChartOfAccount::where('name',$personInfo->id.'-'.$personInfo->name)->first();

                //Personal Loan People which is Credit for the company
                $credit_debit_voucher_transaction[] = array(
                    'chart_of_account_id' => $credit_account->id,
                    'warehouse_id'        => 1,
                    'voucher_no'          => $collection['voucher_no'],
                    'voucher_type'        => self::VOUCHER_PREFIX,
                    'voucher_date'        => $collection['installment_date'],
                    'description'         => $collection['purpose'],
                    'debit'               => $collection['installment_amount'],
                    'credit'              => 0,
                    'posted'              => 1,
                    'approve'             => 1,
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
                    'voucher_date'        => $collection['installment_date'],
                    'description'         => 'Credit Loan Installment Voucher From '.($debit_account ? $debit_account->name : ''),
                    'debit'               => 0,
                    'credit'              => $collection['installment_amount'],
                    'posted'              => 1,
                    'approve'             => 1,
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
            if(permission('personal-loan-installment-edit')){
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
            if(permission('personal-loan-installment-delete')){            
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
            if(permission('personal-loan-installment-bulk-delete')){                
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
            if(permission('personal-loan-installment-edit')){
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

}
