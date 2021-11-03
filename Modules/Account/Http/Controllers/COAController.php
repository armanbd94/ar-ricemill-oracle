<?php

namespace Modules\Account\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\Transaction;
use Modules\Account\Entities\ChartOfAccount;
use Modules\Account\Http\Requests\COAFormRequest;
use Modules\Customer\Http\Requests\CustomerFormRequest;

class COAController extends BaseController
{
    public function __construct(ChartOfAccount $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('coa-access')){
            $this->setPageData('Chart of Account','Chart of Account','far fa-money-bill-alt',[['name' => 'Chart of Account']]);
            $data = [
                'accounts'      => $this->model->where('status',1)->orderBy('id','asc')->get(),
            ];
            return view('account::coa.index',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function parent_head_data(Request $request)
    {
        if($request->ajax())
        {
            $coa = $this->model->find($request->coa_id);
            if($coa)
            {
                $code    = $this->model->where('level',($coa->level + 1))->where('code','like',$coa->code.'%')->max('code');
                $output['code']           = $code ? ($code+1) : $coa->code.'01';
                $output['level']          = $coa->level + 1;
                $output['type']           = $coa->type;
                $output['is_transaction']    = $coa->transaction;
                $output['general_ledger'] = $coa->general_ledger;
                return response()->json($output);
            }
        }
    }

    public function store_or_update_data(COAFormRequest $request)
    {
        if($request->ajax()){
            if(permission('coa-add') || permission('coa-edit')){
                DB::beginTransaction();
                try {
                    $collection   = collect($request->validated())->except('parent_name');
                    $parent_name  = DB::table('chart_of_accounts')->where('id',$request->parent_name)->value('name');
                    $collection   = $collection->merge([
                        'parent_name'       => $parent_name ? $parent_name : 'COA',
                        'is_transaction'    => $request->transaction ? $request->transaction : 2,
                        'general_ledger'    => $request->general_ledger ? $request->general_ledger : 2,
                        'budget'            => 2,
                        'depreciation'      => 2,
                        'depreciation_rate' => '0',
                    ]);
                    // dd($collection->all());
                    $collection   = $this->track_data($collection,$request->update_id);
                    $customer     = $this->model->updateOrCreate(['id'=>$request->update_id],$collection->all());
                    $output       = $this->store_message($customer, $request->update_id);
   
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $output = ['status' => 'error','message' => $e->getMessage()];
                }
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function edit(Request $request)
    {
        if($request->ajax()){
            if(permission('coa-edit')){
                $data   = $this->model->findOrFail($request->id);
                
                $output = $this->data_message($data); //if data found then it will return data otherwise return error message
                $output['parent'] = DB::table('chart_of_accounts')->where('name',$data->parent_name)->value('id');
                return response()->json($output);
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
            if(permission('coa-delete')){
                DB::beginTransaction();
                try {
                    $total_transaction = Transaction::where('chart_of_account_id',$request->id)->count();
                    if ($total_transaction > 0) {
                        $output = ['status'=>'error','message'=>'This account cannot delete because it is related with many transactions.'];
                    } else {
                        $result   = $this->model->find($request->id)->delete();
                        $output   = $this->delete_message($result);
                    }
                    DB::commit();
                } catch (\Exception $e) {
                   DB::rollBack();
                   $output = ['status' => 'error','message' => $e->getMessage()];
                } 
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('coa-access')){

                if (!empty($request->name)) {
                    $this->model->setName($request->name);
                }
                

                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('coa-edit')){
                        if($value->parent_name != 'COA'){
                            $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.self::ACTION_BUTTON['Edit'].'</a>';
                        }
                    }
                    if(permission('coa-delete')){
                        if($value->parent_name != 'COA'){
                            $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->name . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                        }
                    }

                    $row = [];
                    $row[] = $no;
                    $row[] = $value->name;
                    $row[] = $value->code;
                    $row[] = $value->parent_name;
                    $row[] = $value->type;
                    $row[] = $this->opening_coa_balance($value->id,$value->name);
                    // $row[] = $this->coa_balance($value->id,$value->name);
                    if($value->parent_name != 'COA'){
                        $row[] = action_button($action);//custom helper function for action button
                    }else{
                        $row[] = '';
                    }
                    $data[] = $row;
                }
                return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
                $this->model->count_filtered(), $data);
            }
        }else{
            return response()->json($this->unauthorized());
        }
    }


    /*******************************
    * Begin :: Opening COA Balance *
    ********************************/
    public function opening_coa_balance($coa_id, $coa_name)
    {
        $query = DB::table('transactions as t')
                ->selectRaw('sum(t.debit) as predebit, sum(t.credit) as precredit')
                ->where([['approve',1],['is_opening',1],['chart_of_account_id',$coa_id]]);

        $query = $query->first();
        $ass_bal = $query->predebit - $query->precredit;
        $balance = $ass_bal;
        if ($coa_name == 'Customer Receivable') {
            $balance = $this->customer_rec_opening();
        }
        if ($coa_name == 'Loan Receivable') {
            $balance = $this->loan_rec_opening();
        }
        if ($coa_name == 'Account Receivable') {
            $root_balance = $this->account_rec_opening();
            $customer_balance = $this->customer_rec_opening();
            $loan_balance = $this->loan_rec_opening();
            $balance = $root_balance + $customer_balance + $loan_balance;
        }
        if ($coa_name == 'Cash At Bank') {
            $balance = $this->bank_opening();
        }
        if ($coa_name == 'Cash At Mobile Bank') {
            $balance = $this->mobile_bank_opening();
        }
        if ($coa_name == 'Cash & Cash Equivalent') {
            $balance = $this->cash_equivalent_opening();
        }
        if ($coa_name == 'Current Asset') {
            $cash_equivalent_balance = $this->cash_equivalent_opening();
            $root_balance = $this->account_rec_opening();
            $customer_balance = $this->customer_rec_opening();
            $loan_balance = $this->loan_rec_opening();
            $balance = $root_balance + $customer_balance + $loan_balance + $cash_equivalent_balance;
        }

        if ($coa_name == 'Non Current Assets') {
            $balance = $this->non_current_ass_opening();
        }
        if ($coa_name == 'Assets') {
            $non_curopen = $this->non_current_ass_opening();
            $cash_equivalent_balance = $this->cash_equivalent_opening();
            $root_balance = $this->account_rec_opening();
            $customer_balance = $this->customer_rec_opening();
            $loan_balance = $this->loan_rec_opening();
            $balance = $root_balance + $customer_balance + $loan_balance + $cash_equivalent_balance + $non_curopen;
        }

        if ($coa_name == 'Equity') {
            $balance = $this->equity_opening();
        }

        if ($coa_name == 'Expense') {
            $balance = $this->expense_opening();
        }

        if ($coa_name == 'Income') {
            $balance = $this->income_opening();
        }
        if ($coa_name == 'Account Payable') {
            $balance = $this->acc_payable_opening();
        }

        if ($coa_name == 'Employee Ledger') {
            $balance = $this->acc_employeeledger_opening();
        }

        if ($coa_name == 'Current Liabilities') {
            $cur_balance = $this->acc_curliabilities_opening();
            $paya_balance = $this->acc_payable_opening();
            $employe_balance = $this->acc_employeeledger_opening();
            $balance = $cur_balance + $paya_balance + $employe_balance;
        }

        if ($coa_name == 'Non Current Liabilities') {
            $balance = $this->acc_non_curliabilities_opening();
        }

        if ($coa_name == 'Liabilities') {
            $non_balance = $this->acc_non_curliabilities_opening();
            $cur_balance = $this->acc_curliabilities_opening();
            $paya_balance = $this->acc_payable_opening();
            $employe_balance = $this->acc_employeeledger_opening();
            $balance = $cur_balance + $paya_balance + $employe_balance + $non_balance;
        }

        return (!empty($balance) ? number_format($balance, 2) : number_format(0, 2));
    }

    private function customer_rec_opening()
    {
        $total = 0;
        $coa = $this->coa_parent_name_based_list('Customer Receivable');
        if(!$coa->isEmpty())
        {
            foreach ($coa as $assetcoa) {
                $query = $this->coa_opening_transaction($assetcoa->id);
                $cust_bal = $query->predebit - $query->precredit;
                $total += $cust_bal;
            }
        }
        return $total;
    }

    private function loan_rec_opening()
    {
        $total = 0;
        $coa = $this->coa_parent_name_based_list('Loan Receivable');
        foreach ($coa as $assetcoa) {
            $query = $this->coa_opening_transaction($assetcoa->id);
            $cust_bal = $query->predebit - $query->precredit;
            $total += $cust_bal;
        }
        return $total;
    }

    private function account_rec_opening()
    {
        $total = 0;
        $coa = $this->coa_parent_name_based_list('Account Receivable');
        foreach ($coa as $assetcoa) {
            $query = $this->coa_opening_transaction($assetcoa->id);
            $cust_bal = $query->predebit - $query->precredit;
            $total += $cust_bal;
        }
        return $total;
    }

    private function bank_opening()
    {
        $total = 0;
        $coa = $this->coa_parent_name_based_list('Cash At Bank');
        foreach ($coa as $assetcoa) {
            $query = $this->coa_opening_transaction($assetcoa->id);
            $cust_bal = $query->predebit - $query->precredit;
            $total += $cust_bal;
        }
        return $total;
    }
    private function mobile_bank_opening()
    {
        $total = 0;
        $coa = $this->coa_parent_name_based_list('Cash At Mobile Bank');
        foreach ($coa as $assetcoa) {
            $query = $this->coa_opening_transaction($assetcoa->id);
            $cust_bal = $query->predebit - $query->precredit;
            $total += $cust_bal;
        }
        return $total;
    }

    private function cash_equivalent_opening()
    {
        $total = 0;
        $coa = $this->coa_parent_name_based_list('Cash & Cash Equivalent');
        foreach ($coa as $assetcoa) {
            $query = $this->coa_opening_transaction($assetcoa->id);
            $cust_bal = $query->predebit - $query->precredit;
            $total += $cust_bal;
        }
        return $total;
    }

    private function non_current_ass_opening()
    {
        $total = 0;
        $coa = $this->coa_parent_name_based_list('Non Current Assets');
        foreach ($coa as $assetcoa) {
            $query = $this->coa_opening_transaction($assetcoa->id);
            $cust_bal = $query->predebit - $query->precredit;
            $total += $cust_bal;
        }
        return $total;
    }

    private function equity_opening()
    {
        $total = 0;
        $coa = $this->coa_parent_name_based_list('Equity');
        foreach ($coa as $assetcoa) {
            $query = $this->coa_opening_transaction($assetcoa->id);
            $cust_bal = $query->predebit - $query->precredit;
            $total += $cust_bal;
        }
        return $total;
    }

    private function expense_opening()
    {
        $total = 0;
        $coa = $this->coa_parent_name_based_list('Expense');
        foreach ($coa as $assetcoa) {
            $query = $this->coa_opening_transaction($assetcoa->id);
            $cust_bal = $query->predebit - $query->precredit;
            $total += $cust_bal;
        }
        return $total;
    }

    private function income_opening()
    {
        $total = 0;
        $coa = $this->coa_parent_name_based_list('Income');
        foreach ($coa as $assetcoa) {
            $query = $this->coa_opening_transaction($assetcoa->id);
            $cust_bal = $query->predebit - $query->precredit;
            $total += $cust_bal;
        }
        return $total;
    }

    private function acc_payable_opening()
    {
        $total = 0;
        $coa = $this->coa_parent_name_based_list('Account Payable');
        foreach ($coa as $assetcoa) {
            $query = $this->coa_opening_transaction($assetcoa->id);
            $cust_bal = $query->predebit - $query->precredit;
            $total += $cust_bal;
        }
        return $total;
    }

    private function acc_employeeledger_opening()
    {
        $total = 0;
        $coa = $this->coa_parent_name_based_list('Employee Ledger');
        foreach ($coa as $assetcoa) {
            $query = $this->coa_opening_transaction($assetcoa->id);
            $cust_bal = $query->predebit - $query->precredit;
            $total += $cust_bal;
        }
        return $total;
    }

    private function acc_curliabilities_opening()
    {
        $total = 0;
        $coa = $this->coa_parent_name_based_list('Current Liabilities');
        foreach ($coa as $assetcoa) {
            $query = $this->coa_opening_transaction($assetcoa->id);
            $cust_bal = $query->predebit - $query->precredit;
            $total += $cust_bal;
        }
        return $total;
    }

    private function acc_non_curliabilities_opening()
    {
        $total = 0;
        $coa = $this->coa_parent_name_based_list('Non Current Liabilities');
        foreach ($coa as $assetcoa) {
            $query = $this->coa_opening_transaction($assetcoa->id);
            $cust_bal = $query->predebit - $query->precredit;
            $total += $cust_bal;
        }
        return $total;
    }

    private function coa_opening_transaction($coa_id)
    {
        $query = DB::table('transactions as t')
                ->selectRaw('sum(t.debit) as predebit, sum(t.credit) as precredit')
                ->where([['approve',1],['is_opening',1],['chart_of_account_id',$coa_id]]);
        $query = $query->first();
        return $query;
    }
    /*******************************
    * End :: Opening COA Balance *
    ********************************/
    private function coa_parent_name_based_list($parent_name)
    {
        return $this->model->where('parent_name',$parent_name)->get();
    }
    /*******************************
    * Start :: COA Balance *
    ********************************/
    private function coa_transaction($coa_id)
    {
        $query = DB::table('transactions as t')
                ->selectRaw('sum(t.debit) as predebit, sum(t.credit) as precredit')
                ->where([['approve',1],['chart_of_account_id',$coa_id]]);
        $query = $query->first();
        return $query;
    }
    
    public function coa_balance($coa_id, $coa_name)
    {
        $head_info = $this->model->find($coa_id);
        $balance = 0;
        $total_customer_rcv = 0;
        $total_loan_rcv = 0;
        $single_balance = 0;
        /*all head single(common) balance*/
        $query = $this->coa_transaction($coa_id);
        $single_bal = $query->predebit - $query->precredit;
        $single_balance += (!empty($single_bal) ? $single_bal : 0);
        $balance = $single_balance;

        /*single customer receivable balance*/
        if ($head_info->parent_name == 'Customer Receivable') {
            $query = $this->coa_transaction($coa_id);
            $cust_bal = $query->predebit - $query->precredit;
            // $customer_balance += (!empty($cust_bal) ? $cust_bal : 0);

            $balance = (!empty($cust_bal) ? $cust_bal : 0);
        }

        /*single loan receivable balance*/
        if ($head_info->parent_name == 'Loan Receivable') {
            $query = $this->coa_transaction($coa_id);
            $lnp_bal = $query->predebit - $query->precredit;
            // $loanrcv_balance += (!empty($lnp_bal) ? $lnp_bal : 0);

            $balance = (!empty($lnp_bal) ? $lnp_bal : 0);
        }

        /*total customer receivable balance*/
        if ($coa_name == 'Customer Receivable') {
            $coa = $this->coa_parent_name_based_list('Customer Receivable');
            $asset_balance = 0;
            foreach ($coa as $assetcoa) {
                $query = $this->coa_transaction($assetcoa->id);
                $ass_bal = $query->predebit - $query->precredit;
                $asset_balance += (!empty($ass_bal) ? $ass_bal : 0);
            }

            $balance = $asset_balance;

        }

        /*total Loan receivable balance*/
        if ($coa_name == 'Loan Receivable') {
            $coa = $this->coa_parent_name_based_list('Loan Receivable');
            $asset_balance = 0;
            foreach ($coa as $assetcoa) {
                $query = $this->coa_transaction($assetcoa->id);
                $ass_bal = $query->predebit - $query->precredit;
                $asset_balance += (!empty($ass_bal) ? $ass_bal : 0);
            }

            $balance = $asset_balance;
            $total_loan_rcv = $balance;

        }

        /*total cash at bank balance*/
        if ($coa_name == 'Cash At Bank') {
            $coa = $this->coa_parent_name_based_list('Cash At Bank');
            $asset_balance = 0;
            foreach ($coa as $assetcoa) {
                $query = $this->coa_transaction($assetcoa->id);
                $ass_bal = $query->predebit - $query->precredit;
                $asset_balance += (!empty($ass_bal) ? $ass_bal : 0);
            }

            $balance = $asset_balance;

        }

        /*single bank balance*/
        if ($head_info->parent_name == 'Cash At Bank') {
            $query = $this->coa_transaction($coa_id);
            $bank_bal = $query->predebit - $query->precredit;
            // $bank_balance += (!empty($bank_bal) ? $bank_bal : 0);
            $balance = (!empty($bank_bal) ? $bank_bal : 0);

        }

        /*total account receivable*/
        if ($coa_name == 'Account Receivable') {
            $coa = $this->coa_parent_name_based_list('Customer Receivable');
            $asset_balance = $loan_balance = 0;
            foreach ($coa as $assetcoa) {
                $query = $this->coa_transaction($assetcoa->id);
                $ass_bal = $query->predebit - $query->precredit;
                $asset_balance += (!empty($ass_bal) ? $ass_bal : 0);
            }

            $lncoa = $this->coa_parent_name_based_list('Loan Receivable');
            foreach ($lncoa as $lnassetcoa) {
                $lnquery = $this->coa_transaction($lnassetcoa->id);
                $ln_bal = $lnquery->predebit - $lnquery->precredit;
                $loan_balance += (!empty($ln_bal) ? $ln_bal : 0);
            }

            $single_acc_rcv = $this->coa_parent_name_based_list('Account Receivable');
            foreach ($single_acc_rcv as $singl_rcv) {
                $rcvquery = $this->coa_transaction($singl_rcv->id);
                $sreceive_bal = $rcvquery->predebit - $rcvquery->precredit;
                $single_balance += (!empty($sreceive_bal) ? $sreceive_bal : 0);
            }

            $balance = $asset_balance + $loan_balance + $single_balance;

        }

        if ($coa_name == 'Cash & Cash Equivalent') {
            $bank_balance = 0;
            $mobile_bank_balance = 0;
            $cash_balance = 0;

            $coa = $this->coa_parent_name_based_list('Cash At Bank');
            foreach ($coa as $assetcoa) {
                $query = $this->coa_transaction($assetcoa->id);
                $bank_bal = $query->predebit - $query->precredit;
                $bank_balance += (!empty($bank_bal) ? $bank_bal : 0);
            }

            $coa_mobile = $this->coa_parent_name_based_list('Cash At Mobile Bank');
            foreach ($coa_mobile as $assetcoa) {
                $query = $this->coa_transaction($assetcoa->id);
                $mobile_bank_bal = $query->predebit - $query->precredit;
                $mobile_bank_balance += (!empty($mobile_bank_bal) ? $mobile_bank_bal : 0);
            }

            $cash_other = $this->coa_parent_name_based_list('Cash & Cash Equivalent');
            foreach ($cash_other as $cashother) {
                $query = $this->coa_transaction($cashother->id);
                $cash_bal = $query->predebit - $query->precredit;
                $cash_balance += (!empty($cash_bal) ? $cash_bal : 0);
            }

            $balance = $bank_balance + $mobile_bank_balance + $cash_balance;

        }

        if ($coa_name == 'Current Asset') {

            $balance = $this->total_current_asset_balance();

        }

        if ($coa_name == 'Non Current Assets') {

            $balance = $this->total_non_current_asset_balance();

        }
        if ($coa_name == 'Assets') {
            $cur_balance = $this->total_current_asset_balance();
            $non_cure_balance = $this->total_non_current_asset_balance();
            $balance = $cur_balance + $non_cure_balance;

        }

        if ($coa_name == 'Equity') {
            $balance = $this->total_equity_balance();
        }

        if ($coa_name == 'Expense') {
            $balance = $this->total_expense_balance();
        }

        if ($coa_name == 'Income') {
            $balance = $this->total_income_balance();
        }
        if ($coa_name == 'Account Payable') {
            $balance = $this->total_acc_payable_balance();
        }
        if ($coa_name == 'Employee Ledger') {
            $balance = $this->total_acc_employee_balance();
        }
        if ($coa_name == 'Current Liabilities') {
            $balance_ac_payable = $this->total_acc_payable_balance();
            $emp_payable = $this->total_acc_employee_balance();
            $rootcur_liablities = $this->total_acc_cruliabilities_balance();
            $balance = $balance_ac_payable + $emp_payable + $rootcur_liablities;
        }

        if ($coa_name == 'Non Current Liabilities') {
            $balance = $this->total_acc_no_curliability_balance();
        }

        if ($coa_name == 'Liabilities') {
            $non_cur_balance = $this->total_acc_no_curliability_balance();
            $balance_ac_payable = $this->total_acc_payable_balance();
            $emp_payable = $this->total_acc_employee_balance();
            $rootcur_liablities = $this->total_acc_cruliabilities_balance();
            $balance = $balance_ac_payable + $emp_payable + $rootcur_liablities + $non_cur_balance;
        }

        return (!empty($balance) ? number_format($balance, 2) : number_format(0, 2));
    }

    public function total_current_asset_balance()
    {
        $asset_balance = $loan_balance = $single_balance = 0;
        $coa = $this->coa_parent_name_based_list('Customer Receivable');
        $asset_balance = 0;
        foreach ($coa as $assetcoa) {
            $query = $this->coa_transaction($assetcoa->id);
            $ass_bal = $query->predebit - $query->precredit;
            $asset_balance += (!empty($ass_bal) ? $ass_bal : 0);
        }

        $lncoa = $this->coa_parent_name_based_list('Loan Receivable');
        foreach ($lncoa as $lnassetcoa) {
            $lnquery = $this->coa_transaction($lnassetcoa->id);
            $ln_bal = $lnquery->predebit - $lnquery->precredit;
            $loan_balance += (!empty($ln_bal) ? $ln_bal : 0);
        }

        $single_acc_rcv = $this->coa_parent_name_based_list('Account Receivable');
        foreach ($single_acc_rcv as $singl_rcv) {
            $rcvquery = $this->coa_transaction($singl_rcv->id);
            $sreceive_bal = $rcvquery->predebit - $rcvquery->precredit;
            $single_balance += (!empty($sreceive_bal) ? $sreceive_bal : 0);
        }

        $bank_balance = 0;
        $mobile_bank_balance = 0;
        $cash_balance = 0;
        $coa = $this->coa_parent_name_based_list('Cash At Bank');
        foreach ($coa as $assetcoa) {
            $query = $this->coa_transaction($assetcoa->id);
            $bank_bal = $query->predebit - $query->precredit;
            $bank_balance += (!empty($bank_bal) ? $bank_bal : 0);
        }

        $coa_mobile = $this->coa_parent_name_based_list('Cash At Mobile Bank');
        foreach ($coa_mobile as $assetcoa) {
            $query = $this->coa_transaction($assetcoa->id);
            $mobile_bank_bal = $query->predebit - $query->precredit;
            $mobile_bank_balance += (!empty($mobile_bank_bal) ? $mobile_bank_bal : 0);
        }

        $cash_other = $this->coa_parent_name_based_list('Cash & Cash Equivalent');
        foreach ($cash_other as $cashother) {
            $query = $this->coa_transaction($cashother->id);
            $cash_bal = $query->predebit - $query->precredit;
            $cash_balance += (!empty($cash_bal) ? $cash_bal : 0);
        }

        return $balance = $asset_balance + $loan_balance + $single_balance + $bank_balance + $mobile_bank_balance + $cash_balance;

    }

    public function total_non_current_asset_balance()
    {
        $total = 0;
        $coa = $this->coa_parent_name_based_list('Non Current Assets');
        foreach ($coa as $assetcoa) {
            $query = $this->coa_transaction($assetcoa->id);
            $balance = $query->predebit - $query->precredit;
            $total += (!empty($balance) ? $balance : 0);
        }
        return $total;
    }

    public function total_equity_balance()
    {
        $total = 0;
        $coa = $this->coa_parent_name_based_list('Equity');
        foreach ($coa as $assetcoa) {
            $query = $this->coa_transaction($assetcoa->id);
            $balance = $query->predebit - $query->precredit;
            $total += (!empty($balance) ? $balance : 0);
        }
        return $total;
    }

    public function total_expense_balance()
    {
        $total = 0;
        $coa = $this->coa_parent_name_based_list('Expense');
        foreach ($coa as $assetcoa) {
            $query = $this->coa_transaction($assetcoa->id);
            $balance = $query->predebit - $query->precredit;
            $total += (!empty($balance) ? $balance : 0);
        }
        return $total;
    }

    public function total_income_balance()
    {
        $total = 0;
        $coa = $this->coa_parent_name_based_list('Income');
        foreach ($coa as $assetcoa) {
            $query = $this->coa_transaction($assetcoa->id);
            $balance = $query->predebit - $query->precredit;
            $total += (!empty($balance) ? $balance : 0);
        }
        // dd($total);
        return $total;
    }

    public function total_acc_payable_balance()
    {
        $total = 0;
        $coa = $this->coa_parent_name_based_list('Account Payable');
        foreach ($coa as $assetcoa) {
            $query = $this->coa_transaction($assetcoa->id);
            $balance = $query->predebit - $query->precredit;
            $total += (!empty($balance) ? $balance : 0);
        }
        return $total;
    }

    public function total_acc_employee_balance()
    {
        $total = 0;
        $coa = $this->coa_parent_name_based_list('Employee Ledger');
        foreach ($coa as $assetcoa) {
            $query = $this->coa_transaction($assetcoa->id);
            $balance = $query->predebit - $query->precredit;
            $total += (!empty($balance) ? $balance : 0);
        }
        return $total;
    }

    public function total_acc_cruliabilities_balance()
    {
        $total = 0;
        $coa = $this->coa_parent_name_based_list('Current Liabilities');
        foreach ($coa as $assetcoa) {
            $query = $this->coa_transaction($assetcoa->id);
            $balance = $query->predebit - $query->precredit;
            $total += (!empty($balance) ? $balance : 0);
        }
        return $total;
    }

    public function total_acc_no_curliability_balance()
    {
        $total = 0;
        $coa = $this->coa_parent_name_based_list('Non Current Liabilities');
        foreach ($coa as $assetcoa) {
            $query = $this->coa_transaction($assetcoa->id);
            $balance = $query->predebit - $query->precredit;
            $total += (!empty($balance) ? $balance : 0);
        }
        return $total;
    }
    /*******************************
    * End :: COA Balance *
    ********************************/

}
