<?php

namespace Modules\HRM\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Modules\HRM\Entities\Leave;
use Modules\Loan\Entities\Loans;
use Illuminate\Support\Facades\DB;
use Modules\HRM\Entities\Employee;
use Modules\HRM\Entities\Department;
use Modules\HRM\Entities\Designation;
use Modules\HRM\Entities\EmployeeRoute;
use App\Http\Controllers\BaseController;
use Modules\HRM\Entities\SalaryGenerate;
use Illuminate\Support\Facades\Validator;
use Modules\Account\Entities\Transaction;
use Modules\Loan\Entities\LoanInstallment;
use Modules\Account\Entities\ChartOfAccount;
use Modules\HRM\Http\Requests\SalaryGenerateRequestForm;

class SalaryGenerateController extends BaseController
{
    private const VOUCHER_PREFIX = 'EMPSAL';
    public function __construct(SalaryGenerate $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('salary-generate-access')) {
            $this->setPageData('Salary Generate Manage', 'Salary Generate Manage', 'fas fa-shopping-cart', [['name' => 'Salary Generate Manage']]);
            $employees = Employee::where('status', 1)->get();
            $employees_route = EmployeeRoute::toBase()->where('status', 1)->get();
            return view('hrm::salary-generate.index', compact('employees', 'employees_route'));
        } else {
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax()) {
            if (permission('salary-generate-access')) {

                if (!empty($request->salary_month)) {
                    $this->model->setSalaryMonth($request->salary_month);
                }
                if (!empty($request->from_date)) {
                    $this->model->setFromDate($request->from_date);
                }
                if (!empty($request->to_date)) {
                    $this->model->setToDate($request->to_date);
                }
                if (!empty($request->employee_id)) {
                    $this->model->setEmployeeID($request->employee_id);
                }
                if (!empty($request->salary_status)) {
                    $this->model->setSalaryStatus($request->salary_status);
                }
                if (!empty($request->payment_status)) {
                    $this->model->setPaymentStatus($request->payment_status);
                }


                $this->set_datatable_default_properties($request); //set datatable default properties
                $list = $this->model->getDatatableList(); //get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    $employeename = SalaryGenerate::getAnyRowInfos('employees', 'id', $value->employee_id)->name;

                    if (permission('salary-payment-approve')) {
                        if ($value->status != 1) {
                            $action .= ' <a class="dropdown-item change_status"  data-id="' . $value->id . '" data-name="' . $employeename . '" data-status="' . $value->status . '"><i class="fas fa-check-circle text-success mr-2"></i> Change Status</a>';
                        }
                    }
                    if (permission('salary-payment-access')) {
                        if ($value->payment_status != 1) {
                            $action .= ' <a class="dropdown-item add_payment" data-id="' . $value->id . '" data-due="' . ($value->net_salary - $value->paid_amount) . '"><i class="fas fa-plus-square text-info mr-2"></i> Add Payment</a>';
                        }
                    }
                    if (permission('salary-payment-access')) {
                        if ($value->payment_status == 1) {
                            $action .= ' <a class="dropdown-item view_data" href="' . url('salary-generate/view', $value->id) . '"><i class="fas fa-file-invoice-dollar text-dark mr-2"></i> Generate Slip</a>';
                        }
                    }

                    if (permission('salary-generate-delete')) {
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $employeename . '">' . self::ACTION_BUTTON['Delete'] . '</a>';
                    }

                    $row = [];
                    if (permission('salary-generate-bulk-delete')) {
                        $row[] = row_checkbox($value->id); //custom helper function to show the table each row checkbox
                    }
                    $row[] = $no;
                    $row[] = date('F Y',strtotime($value->salary_month));
                    $row[] = $employeename;
                    $row[] = $value->allowance_amount ? number_format($value->allowance_amount, 2) : 0;
                    $row[] = $value->deduction_amount ? number_format($value->deduction_amount, 2) : 0;
                    $row[] = $value->basic_salary ? number_format($value->basic_salary, 2) : 0;
                    $row[] = $value->gross_salary ? number_format($value->gross_salary, 2) : 0;
                    $row[] = number_format($value->paid_amount, 2);
                    $row[] = number_format(($value->net_salary - $value->paid_amount), 2);
                    $row[] = date(config('settings.date_format'), strtotime($value->date));
                    $row[] = APPROVE_STATUS_LABEL[$value->status];
                    $row[] = PURCHASE_STATUS_LABEL[$value->salary_status];
                    $row[] = PAYMENT_STATUS_LABEL[$value->payment_status];
                    $row[] = action_button($action); //custom helper function for action button
                    $data[] = $row;
                }
                return $this->datatable_draw(
                    $request->input('draw'),
                    $this->model->count_all(),
                    $this->model->count_filtered(),
                    $data
                );
            }
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function create()
    {
        if (permission('salary-generate-add')) {
            $this->setPageData('Add Salary Generate', 'Add Salary Generate', 'fas fa-shopping-cart', [['name' => 'Add Salary Generate']]);

            $data = [
                'deletable' => self::DELETABLE,
                'employees'  => Employee::where('status', 1)->get(),
                'designations'  => Designation::activeDesignations(),
                'departments'  => Department::activeDepartments(),
                'leaves'  => Leave::activeLeaves(),
                'employees_route'    => EmployeeRoute::toBase()->where('status', 1)->get()
            ];
            return view('hrm::salary-generate.create', $data);
        } else {
            return $this->access_blocked();
        }
    }

    public function salary_report(Request $request)
    {
        if (permission('salary-generate-access')) {
            $v = Validator::make($request->all(), [
                'start_date' => 'required',
                'end_date' => 'required',
            ]);
            $this->setPageData('Add Salary Generate', 'Add Salary Generate', 'fas fa-shopping-cart', [['name' => 'Add Salary Generate']]);
            $data = [
                'deletable' => self::DELETABLE,
                'employees'  => Employee::where('status', 1)->get(),
                'designations'  => Designation::activeDesignations(),
                'departments'  => Department::activeDepartments(),
                'leaves'  => Leave::activeLeaves(),
                'company_condition'  => SalaryGenerate::get_company_condition(),
                'employees_route'    => EmployeeRoute::toBase()->where('status', 1)->get()
            ];
            if ($v->fails()) {
                $data['start_date'] = date('Y-m-d');
                $data['end_date'] = date('Y-m-d');
                return view('hrm::salary-generate.create', $data);
            } else {
                $employee_id = $request->employee_id;
                $department_id = $request->department_id;
                $designation_id = $request->designation_id;
                $start_date = $request->start_date;
                $end_date = $request->end_date;

                $data['start_date'] = $start_date;
                $data['end_date'] = $end_date;

                $query = Employee::toBase();

                if (!empty($employee_id)) {
                    $query->where('id', '=', $employee_id);
                }
                if (!empty($department_id)) {
                    $query->where('department_id', '=', $department_id);
                }
                if (!empty($designation_id)) {
                    $query->where('designation_id', '=', $designation_id);
                }
                $data['all_employee'] = $query->get();
                //print_r($data['all_employee']);


                return view('hrm::salary-generate.store', $data);
            }
        } else {
            return $this->access_blocked();
        }
    }

    public function store_data(SalaryGenerateRequestForm $request)
    {

        //dd($request->all());
        if ($request->ajax()) {
            if (permission('salary-generate-add')) {
                $voucher_no = self::VOUCHER_PREFIX . '-' . date('Ymd') . rand(1, 999);
                //dd($request->all());
                DB::beginTransaction();
                try {
                    $employee_count = count($request->employee_code);
                    $count = 0;
                    $dates = date('Y-m-d');
                    $salary_month = date('Y-m', strtotime($request->sd_date));
                    $existing = SalaryGenerate::where('salary_month', $salary_month)->get();
                    $voucher_no = $voucher_no;
                    if (empty(count($existing))) {
                        while ($count < $employee_count) {
                            $salary_generate_transaction[] = array(
                                'voucher_no'        => $voucher_no . $request->employee_code[$count],
                                'employee_id'        => $request->employee_code[$count],
                                'designation_id'     => $request->designation[$count],
                                'department_id'        => $request->department[$count],
                                'division_id'          => $request->division[$count],
                                'date'                  => $dates,
                                'salary_month'       => $salary_month,
                                'basic_salary'       => $request->rate[$count],
                                'allowance_amount'       => $request->allowance_amount[$count],
                                'deduction_amount'       => $request->deduction_amount[$count],
                                'absent'       => $request->absent[$count],
                                'absent_amount'       => $request->total_absent_amount[$count],
                                'late_count'       => $request->late_count[$count],
                                'leave'       => $request->total_leave[$count],
                                'leave_amount'       => $request->total_leave_amount[$count],
                                'ot_hour'       => $request->total_ot_hour[$count],
                                'ot_day'       => $request->total_ot_day[$count],
                                'ot_amount'       => $request->total_ot_amount[$count],
                                'gross_salary'       => $request->grossrate[$count],
                                'add_deduct_amount'       => $request->designation[$count],
                                'adjusted_advance_amount'       => $request->designation[$count],
                                'adjusted_loan_amount'       => $request->adjusted_loan_amount[$count],
                                'net_salary'       => $request->total_salary[$count],
                                'created_at'          => date('Y-m-d H:i:s')
                            );

                            $count++;

                        }
                        $results = $this->model->insert($salary_generate_transaction);


                        $emp_code = $request->employee_code;

                        if (!empty($emp_code)) {
                            if (isset($request->total_salary)) {
                                $count_employee = count($emp_code);
                                for ($ss = 0; $ss < $count_employee; $ss++) {
                                    $this->salary_installment_coa($emp_code[$ss], $dates, $request->total_salary[$ss], $voucher_no);
                                }
                            }

                        }

                        $loan_id_adjust_amount = $request->loan_id_adjust_amount;

                        if (!empty($emp_code)) {
                            if (isset($request->loan_id_adjust_amount)) {
                                $count_employee = count($emp_code);
                                for ($i = 0; $i < $count_employee; $i++) {
                                    $check_loan_info = Loans::where('employee_id', $emp_code[$i])->where('loan_status', 2)->get();

                                    if (!empty($check_loan_info)) {
                                        $loan_id_adjust_amount = $request->loan_id_adjust_amount;

                                        for ($k = 0; $k < count($loan_id_adjust_amount); $k++) {
                                            $loan_id_adjust_amount_data = explode("-", $loan_id_adjust_amount[$k]);
                                            $loan_id_adjust_amount = $request->loan_id_adjust_amount;
                                            foreach ($check_loan_info as $check_loan_infos) :
                                                if ($loan_id_adjust_amount_data[0] == $check_loan_infos->id) {
                                                    $total_adjusted_loan_amount = $check_loan_infos->total_adjusted_amount + $loan_id_adjust_amount_data[1];
                                                    if ($total_adjusted_loan_amount >= $check_loan_infos->amount) {
                                                        $loan_status = '1';
                                                    } else {
                                                        $loan_status = '2';
                                                    }
                                                    if (!empty($loan_id_adjust_amount_data[0])) {
                                                        $loans_update = Loans::where('id', $check_loan_infos->id)->update(['total_adjusted_amount' => $total_adjusted_loan_amount, 'adjusted_date' =>  date('Y-m-d'), 'loan_status' => $loan_status]);
                                                    }
                                                    if ($loans_update) {
                                                        $this->official_loan_installment($emp_code[$i], date('Y-m-d'), $loan_id_adjust_amount_data[1], $voucher_no, $loan_id_adjust_amount_data[0]);

                                                        $this->official_loan_installment_coa($emp_code[$i], date('Y-m-d'), $loan_id_adjust_amount_data[1], $voucher_no);
                                                    }
                                                }
                                            endforeach;
                                        }
                                    }
                                }
                            }
                        }

                        /*if(isset($request->loan_id_adjust_amount)){  
                            for ($k = 0; $k < count($loan_id_adjust_amount); $k++) {
                                $loan_id_adjust_amount_data = explode("-", $loan_id_adjust_amount[$k]);
                                $count_employee = count($emp_code);
                                 for ($i = 0; $i < $count_employee; $i++) {
                                     $check_loan_info = Loans::where('employee_id',$emp_code[$i])->where('loan_status',2)->get();
                                     if (!empty($check_loan_info)) {
                                        $loan_id_adjust_amount = $request->loan_id_adjust_amount;
                                        foreach($check_loan_info as $check_loan_infos):
                                            if ($loan_id_adjust_amount_data[0] == $check_loan_infos->id) {
                                                $total_adjusted_loan_amount = $check_loan_infos->total_adjusted_amount + $loan_id_adjust_amount_data[1];
                                                    if ($total_adjusted_loan_amount >= $check_loan_infos->amount) {
                                                        $loan_status = '1';
                                                    } else {
                                                        $loan_status = '2';
                                                    }
                                                    if(!empty($loan_id_adjust_amount_data[0])){
                                                        $loans_udate = Loans::where('id',$check_loan_infos->id)->update(['total_adjusted_amount' => $total_adjusted_loan_amount, 'adjusted_date' =>  date('Y-m-d'), 'loan_status' => $loan_status]);
                                                    }
                                                    if($loans_udate){
                                                        $voucher_no = self::VOUCHER_PREFIX.'-'.date('Ymd').rand(1,999);

                                                        $getAcc = $this->official_loan_installment($emp_code[$i],date('Y-m-d'),$total_adjusted_loan_amount,$voucher_no,$loan_id_adjust_amount_data[0]);
                                                        
                                                        if($getAcc){
                                                            $this->official_loan_installment_coa($emp_code[$i],date('Y-m-d'),$total_adjusted_loan_amount,$voucher_no);
                                                        }
                                                    }
                                            }
                                        endforeach;
                                     }
                                 }
                            }
                        }*/

                        $output = $this->store_message($results, null);
                        DB::commit();
                    } else {
                        $output = $this->store_message(2, null);
                    }
                } catch (Exception $e) {
                    DB::rollBack();
                    $output = ['status' => 'error', 'message' => $e->getMessage()];
                }
            } else {
                $output = $this->unauthorized();
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }   


    private function salary_installment_coa($employee_id, $installment_date, $installment_amount, $voucher_no)
    {
        DB::beginTransaction();
        if (permission('official-loan-installment-add')) {
            try {
                $employeeInfo = Employee::find($employee_id);

                //$debit_account = ChartOfAccount::where('name', $employeeInfo->id . '-' . $employeeInfo->name . '-' . $employeeInfo->wallet_number)->first();
                //$credit_account = ChartOfAccount::where('name',$employeeInfo->id.'-'.$employeeInfo->name.'-'.$employeeInfo->wallet_number)->first();
                $credit_account = ChartOfAccount::where('name',$employeeInfo->id.'-'.$employeeInfo->name.'-E')->first();
                //Official Loan People which is Debit for the company
                $credit_voucher_transaction[] = array(
                    'chart_of_account_id' => $credit_account->id,
                    'voucher_no'          => $voucher_no,
                    'voucher_type'        => self::VOUCHER_PREFIX,
                    'voucher_date'        => $installment_date,
                    'description'         => "Credit From Employee Cash",
                    'debit'               => 0,
                    'credit'              => $installment_amount,
                    'posted'              => 1,
                    'approve'             => 1,
                    'created_by'          => auth()->user()->name,
                    'created_at'          => date('Y-m-d H:i:s')
                );
                $result = Transaction::insert($credit_voucher_transaction);
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
            }
        } else {
            $output = $this->unauthorized();
        }
    }

    private function official_loan_installment($employee_id, $installment_date, $installment_amount, $voucher_no, $loan_id_adjust_amount_data)
    {
        DB::beginTransaction();
        if (permission('official-loan-installment-add')) {
            try {
                $employeeInfo = Employee::find($employee_id);
                $account_id = 23;
                $debit_credit_voucher_transaction[] = array(
                    'voucher_no'          => $voucher_no,
                    'loan_id'          => $loan_id_adjust_amount_data,
                    'employee_id'        => $employeeInfo->id,
                    'installment_amount'        => $installment_amount,
                    'installment_date'        => $installment_date,
                    'purpose'         => "Debit From Employee Cash",
                    'month_year'               => date('F-Y', strtotime($installment_amount)),
                    'loan_type'              => 2,
                    'payment_method'         => 1,
                    'account_id'             => $account_id,
                    'created_by'          => auth()->user()->name,
                    'created_at'          => date('Y-m-d H:i:s')
                );
                $result = LoanInstallment::insert($debit_credit_voucher_transaction);
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
            }
        } else {
            $output = $this->unauthorized();
        }
    }

    private function official_loan_installment_coa($employee_id, $installment_date, $installment_amount, $voucher_no)
    {
        DB::beginTransaction();
        if (permission('official-loan-installment-add')) {
            try {
                $employeeInfo = Employee::find($employee_id);

                //$debit_account = ChartOfAccount::where('name', $employeeInfo->id . '-' . $employeeInfo->name . '-' . $employeeInfo->wallet_number)->first();
                $debit_account = ChartOfAccount::where('name',$employeeInfo->id.'-'.$employeeInfo->name.'-OLR')->first();
                $account_id = 23;
                //Official Loan People which is Debit for the company
                $debit_credit_voucher_transaction[] = array(
                    'chart_of_account_id' => $debit_account->id,
                    'voucher_no'          => $voucher_no,
                    'voucher_type'        => 'EMPSALOLI',
                    'voucher_date'        => $installment_date,
                    'description'         => "Debit From Employee Cash",
                    'debit'               => 0,
                    'credit'              => $installment_amount,
                    'posted'              => 1,
                    'approve'             => 1,
                    'created_by'          => auth()->user()->name,
                    'created_at'          => date('Y-m-d H:i:s')
                );

                //Cash In Insert which is Credit for the company
                $credit_account = ChartOfAccount::find($account_id);
                $debit_credit_voucher_transaction[] = array(
                    'chart_of_account_id' => $credit_account->id,
                    'voucher_no'          => $voucher_no,
                    'voucher_type'        => 'EMPSALOLI',
                    'voucher_date'        => $installment_date,
                    'description'         => 'Credit Loan Installment Voucher From ' . ($credit_account ? $credit_account->name : ''),
                    'debit'               => $installment_amount,
                    'credit'              => 0,
                    'posted'              => 1,
                    'approve'             => 1,
                    'created_by'          => auth()->user()->name,
                    'created_at'          => date('Y-m-d H:i:s')
                );

                // Expense for company
                /*$debit_credit_voucher_transaction[] = array(
                    'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', $this->coa_head_code('employee_salary'))->value('id'),
                    'voucher_no'          => $voucher_no,
                    'voucher_type'        => 'Loan Receivable',
                    'voucher_date'        => $installment_date,
                    'description'         => 'Company Credit For Employee ' . $employeeInfo->nam,
                    'debit'               => $installment_amount,
                    'credit'              => 0,
                    'posted'              => 1,
                    'approve'             => 1,
                    'created_by'          => auth()->user()->name,
                    'created_at'          => date('Y-m-d H:i:s')
                );*/
                //dd($debit_credit_voucher_transaction);
                $result = Transaction::insert($debit_credit_voucher_transaction);
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
            }
        } else {
            $output = $this->unauthorized();
        }
    }

    public function pay_slip(int $id)
    {
        if (permission('salary-generate-access')) {
            $this->setPageData('Generated Slip', 'Generated Slip', 'fas fa-file', [['name' => 'Purchase', 'link' => route('salary.generate')], ['name' => 'Generated Slip']]);
            $data['payslip'] = $this->model->find($id);
            return view('hrm::salary-generate.generate-slip', $data);
        } else {
            return $this->access_blocked();
        }
    }


    public function delete(Request $request)
    {
        if ($request->ajax()) {
            if (permission('salary-generate-delete')) {
                DB::beginTransaction();
                try {
                    $salaryData = SalaryGenerate::where('id', $request->id)->first();
                    Transaction::where('voucher_no', (string) $salaryData->voucher_no)->where('voucher_type', (string) "Transport")->delete();
                    $result = $salaryData->delete();

                    $output = $result ? ['status' => 'success', 'message' => 'Data has been deleted successfully'] : ['status' => 'error', 'message' => 'failed to delete data'];
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                    $output = ['status' => 'error', 'message' => $e->getMessage()];
                }
                return response()->json($output);
            } else {
                $output = $this->access_blocked();
            }
            return response()->json($output);
        } else {
            return response()->json($this->access_blocked());
        }
    }


    public function bulk_delete(Request $request)
    {
        if ($request->ajax()) {
            if (permission('salary-generate-bulk-delete')) {
                foreach ($request->ids as $id) {
                    DB::beginTransaction();
                    try {
                        $salaryData = SalaryGenerate::where('id', $id)->first();
                        Transaction::where('voucher_no', (string) $salaryData->voucher_no)->where('voucher_type', (string) "Transport")->delete();
                        $result = $salaryData->delete();

                        $output = $result ? ['status' => 'success', 'message' => 'Data has been deleted successfully'] : ['status' => 'error', 'message' => 'failed to delete data'];
                        DB::commit();
                    } catch (Exception $e) {
                        DB::rollBack();
                        $output = ['status' => 'error', 'message' => $e->getMessage()];
                    }
                    return response()->json($output);
                }
            } else {
                $output = $this->access_blocked();
            }
            return response()->json($output);
        } else {
            return response()->json($this->access_blocked());
        }
    }
}
