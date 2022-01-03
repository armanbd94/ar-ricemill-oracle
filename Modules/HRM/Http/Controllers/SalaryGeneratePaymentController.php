<?php

namespace Modules\HRM\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\HRM\Entities\Employee;
use App\Http\Controllers\BaseController;
use Modules\HRM\Entities\SalaryGenerate;
use Modules\Account\Entities\Transaction;
use Illuminate\Contracts\Support\Renderable;
use Modules\Account\Entities\ChartOfAccount;
use Modules\Purchase\Entities\PurchasePayment;
use Modules\HRM\Entities\SalaryGeneratePayment;
use Modules\HRM\Http\Requests\SalaryGeneratePaymentRequestForm;

class SalaryGeneratePaymentController extends BaseController
{
    private const VOUCHER_PREFIX = 'EMPSAL';
    public function store_or_update(SalaryGeneratePaymentRequestForm $request)
    {
        if($request->ajax())
        {           
            //dd($request->all());
            if(permission('salary-payment-access')){
                DB::beginTransaction();
                try {
                    $salary_data = SalaryGenerate::find($request->purchase_id);
                    //dd($salary_data);
                    if($salary_data){
                        $head_name = "Employee Salary";
                        $expense_salary_coa_id = DB::table('chart_of_accounts')->where('name',$head_name)->value('id');                        
                        //dd($expense_salary_coa_id);
                        $payment_data = [
                            'payment_id'      => $request->payment_id,
                            'account_id'      => $request->account_id,
                            'expense_salary_coa_id' => $expense_salary_coa_id,
                            'employee_name'   => DB::table('employees')->where('id',$salary_data->employee_id)->value('name'),
                            'voucher_no'     => $salary_data->voucher_no,
                            'voucher_date'   => date('Y-m-d'),
                            'salary_id'     => $request->purchase_id,
                            'payment_type'   => $request->payment_method,
                            'cheque_no'       => $request->payment_method == 2 ? $request->cheque_number : null,
                            'account_id'     => $request->account_id,
                            'amount'         => $request->amount
                        ];                    
                        
                        $payment_data_insert = [
                            'salary_generated_id' => $salary_data->id,
                            'account_id'      => $request->account_id,
                            'transaction_id' => $expense_salary_coa_id,
                            'employee_transaction_id' => '',
                            'voucher_no'     => $salary_data->voucher_no,
                            'voucher_date'   => date('Y-m-d'),
                            'month'     => $salary_data->salary_month,
                            'amount'         => $request->amount,
                            'payment_method'   => $request->payment_method,
                            'cheque_no'       => $request->payment_method == 2 ? $request->cheque_number : null,
                            'payment_note'         => $request->payment_note,
                            'created_by'          => auth()->user()->name,
                            'created_at'          => date('Y-m-d H:i:s')
                        ];

                        if(empty($request->payment_id)){
                            $salary_data->paid_amount += $request->amount;
                            $balance = $salary_data->net_salary - $salary_data->paid_amount;
                            if($balance == 0)
                            {
                                $salary_data->payment_status = 1;//paid
                                $salary_data->salary_status = 1;//recieved
                            }else if($balance == $salary_data->net_salary)
                            {
                                $salary_data->payment_status = 3;//due
                            }else{
                                $salary_data->payment_status = 2;//partial
                            }
                            $payment_data['supplier_debit_transaction_id'] = $salary_data->employee_id;
                            $payment_data['transaction_id'] = '';
                        }else{
                            $payment = SalaryGeneratePayment::find($request->payment_id);
                            $amount_diff = $payment->amount - $request->amount;
                            $salary_data->paid_amount -= $amount_diff;
                            $balance = $salary_data->net_salary - $salary_data->paid_amount;
                            if($balance == 0)
                            {
                                $salary_data->payment_status = 1;//paid
                                $salary_data->salary_status = 1;//recieved
                            }else if($balance == $salary_data->net_salary)
                            {
                                $salary_data->payment_status = 3;//due
                            }else{
                                $salary_data->payment_status = 2;//partial
                            }

                            $payment_data['supplier_debit_transaction_id'] = $payment->employee_transaction_id;
                            $payment_data['transaction_id'] = $payment->transaction_id;
                        }                      
                       
                        $salary_data->modified_by = auth()->user()->name;
                        $salary_data->update();
                        $result = $this->payment_balance_add($payment_data,$payment_data_insert);

                        //dd($result);
                        //dd($payment_data_insert);
                        $output = $result ? ['status'=>'success','message'=> 'Payment Data Saved Successfully'] : ['status'=>'error','message'=> 'Failed to Save Payment Data'];
                        
                    }
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollback();
                    $output = ['status'=>'error','message'=>$e->getMessage()];
                }
                return response()->json($output);

            }else{
                return response()->json(['status'=>'error','message'=>'Unauthorized Access Blocked']);
            }
        }
    }

    private function payment_balance_add(array $data, array $payment_data_insert) {
        
        $voucher_type = 'Employee Salary';
        $employeeInfo = Employee::where('id',$data['supplier_debit_transaction_id'])->first();
        $debit_account = ChartOfAccount::where('name',$employeeInfo->id.'-'.$employeeInfo->name.'-E')->first();

        $payment_data_insert['employee_transaction_id'] = $debit_account->id;
        // dd($payment_data_insert);
        $result = SalaryGeneratePayment::create($payment_data_insert);
        if($result){
            //dd($debit_account);
            if($data['payment_type'] == 1){
                //Cah In Hand debit
                $payment = array(
                    'chart_of_account_id' => $data['account_id'],
                    'voucher_no'          => $data['voucher_no'],
                    'voucher_type'        => self::VOUCHER_PREFIX,
                    'voucher_date'        => $data['voucher_date'],
                    'description'         => $data['employee_name'].' Salary Expense '.$data['voucher_no'],
                    'debit'               => 0,
                    'credit'              => $data['amount'],
                    'posted'              => 1,
                    'approve'             => 1,
                    'created_by'          => auth()->user()->name,
                    'created_at'          => date('Y-m-d H:i:s')
                    
                );
            }else{
                // Bank Ledger
                $payment = array(
                    'chart_of_account_id' => $data['account_id'],
                    'voucher_no'          => $data['voucher_no'],
                    'voucher_type'        => self::VOUCHER_PREFIX,
                    'voucher_date'        => $data['voucher_date'],
                    'description'         => DB::table('chart_of_accounts')->where('id',$data['account_id'])->value('name').' Salary Expense '.$data['voucher_no'],
                    'debit'               => 0,
                    'credit'              => $data['amount'],
                    'posted'              => 1,
                    'approve'             => 1,
                    'created_by'          => auth()->user()->name,
                    'created_at'          => date('Y-m-d H:i:s')
                );
            }
            
            // company expense Debit
            $expense_acc = array(
                'chart_of_account_id' => $debit_account->id,
                'voucher_no'          => $data['voucher_no'],
                'voucher_type'        => self::VOUCHER_PREFIX,
                'voucher_date'        => $data['voucher_date'],
                'description'         => $data['employee_name'].' Expense '.$data['voucher_no'],
                'debit'               => $data['amount'],
                'credit'              => 0,
                'posted'              => 1,
                'approve'             => 1,
                'created_by'          => auth()->user()->name,
                'created_at'          => date('Y-m-d H:i:s')
            ); 
    
            $expense_acc = array(
                'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', $this->coa_head_code('employee_salary'))->value('id'),
                'voucher_no'          => $data['voucher_no'],
                'voucher_type'        => $voucher_type,
                'voucher_date'        => $data['voucher_date'],
                'description'         => 'Company Debit For Employee ' . $data['employee_name'],
                'debit'               => $data['amount'],
                'credit'              => 0,
                'posted'              => 1,
                'approve'             => 1,
                'created_by'          => auth()->user()->name,
                'created_at'          => date('Y-m-d H:i:s')
            );
            
            Transaction::insert($expense_acc);
            $payment_transaction = Transaction::updateOrCreate(['id'=> $data['transaction_id']],$payment);

            return 1;
        }

    }
}
