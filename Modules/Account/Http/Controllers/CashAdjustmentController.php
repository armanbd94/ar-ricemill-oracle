<?php

namespace Modules\Account\Http\Controllers;


use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Setting\Entities\Warehouse;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\CashAdjustment;
use Modules\Account\Entities\ChartOfAccount;
use Modules\Account\Http\Requests\CashAdjustmentFormRequest;

class CashAdjustmentController extends BaseController
{
    
    public function __construct(CashAdjustment $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('cash-adjustment-access')){
            $this->setPageData('Cash Adjustment List','Cash Adjustment List','far fa-money-bill-alt',[['name'=>'Accounts'],['name'=>'Cash Adjustment List']]);
            $accounts = ChartOfAccount::where(['parent_name' =>  'Cash & Cash Equivalent','status'=>1])->whereNotIn('code',['1020102','1020103'])->get();
            $account_list = '';
            if ($accounts) {
                foreach ($accounts as $account) {
                    $balance = DB::table('transactions')
                    ->select(DB::raw("SUM(debit) - SUM(credit) as balance"))
                    ->where([['chart_of_account_id',$account->id],['approve',1]])
                    ->first();
                    $account_list .= "<option value='$account->id'>".$account->name." [ Balance: ".($balance ? number_format($balance->balance,2,'.',',') : '0.00')."Tk]</option>";
                }
            }
            return view('account::cash-adjustment.list',compact('account_list'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('cash-adjustment-access')){

                if (!empty($request->start_date)) {
                    $this->model->setStartDate($request->start_date);
                }
                if (!empty($request->end_date)) {
                    $this->model->setEndDate($request->end_date);
                }

                if (!empty($request->account_id)) {
                    $this->model->setAccountID($request->account_id);
                }

                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('cash-adjustment-approve') && $value->approve == 3){
                        $action .= ' <a class="dropdown-item change_approve_status"  data-id="' . $value->id . '" data-name="' . $value->voucher_no . '"><i class="fas fa-check-square text-info mr-2"></i> Change Status</a>';
                    }

                    if(permission('cash-adjustment-edit') && $value->approve == 3){
                        $action .= ' <a class="dropdown-item" href="'.route("cash.adjustment.edit",$value->voucher_no).'">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }

                    if(permission('cash-adjustment-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->voucher_no . '" data-name="' . $value->voucher_no . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }
                    

                    $row = [];
                    $row[] = $no;
                    $row[] = date('d-M-Y',strtotime($value->voucher_date));
                    $row[] = $value->voucher_no;
                    $row[] = $value->account_name;
                    $row[] = $value->description;
                    $row[] = number_format($value->debit,2);
                    $row[] = number_format($value->credit,2);
                    $row[] = VOUCHER_APPROVE_STATUS_LABEL[$value->approve];
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
        if(permission('cash-adjustment-add')){
            $this->setPageData('Cash Adjustment','Cash Adjustment','far fa-money-bill-alt',[['name'=>'Accounts'],['name'=>'Cash Adjustment']]);
            $voucher_no = 'CHV-'.date('Ymd').rand(1,999);
            $accounts = ChartOfAccount::where(['parent_name' =>  'Cash & Cash Equivalent','status'=>1])->whereNotIn('code',['1020102','1020103'])->get();
            $account_list = '';
            if ($accounts) {
                foreach ($accounts as $account) {
                    $balance = DB::table('transactions')
                    ->select(DB::raw("SUM(debit) - SUM(credit) as balance"))
                    ->where([['chart_of_account_id',$account->id],['approve',1]])
                    ->first();
                    $account_list .= "<option value='$account->id'>".$account->name." [ Balance: ".($balance ? number_format($balance->balance,2,'.',',') : '0.00')."Tk]</option>";
                }
            }

            return view('account::cash-adjustment.create',compact('voucher_no','account_list'));
        }else{
            return $this->access_blocked();
        }
    }

    public function store(CashAdjustmentFormRequest $request)
    {
        if($request->ajax()){
            if(permission('cash-adjustment-add')){
                DB::beginTransaction();
                try {
                    $data = CashAdjustment::transaction_data($request);
                    $result = $this->model->create($data);
                    $output = $this->store_message($result, null);
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

    public function edit(string $voucher_no)
    {
        if(permission('cash-adjustment-edit')){
            $voucher_data = $this->model->where('voucher_no',$voucher_no)->first();
            if($voucher_data)
            {
                $this->setPageData('Edit Cash Adjustment','Edit Cash Adjustment','far fa-money-bill-alt',[['name'=>'Accounts'],['name'=>'Edit Cash Adjustment']]);
                $accounts = ChartOfAccount::where(['parent_name' =>  'Cash & Cash Equivalent','status'=>1])->whereNotIn('code',['1020102','1020103'])->get();
                $account_list = '';
                if ($accounts) {
                    foreach ($accounts as $account) {
                        $balance = DB::table('transactions')
                        ->select(DB::raw("SUM(debit) - SUM(credit) as balance"))
                        ->where([['chart_of_account_id',$account->id],['approve',1]])
                        ->first();
                        $selected = $voucher_data->chart_of_account_id == $account->id ? 'selected' : '';
                        $account_balance = 0;
                        if($voucher_data->chart_of_account_id == $account->id)
                        {
                            if($voucher_data->approve == 1){
                                if(!empty($voucher_data->debit))
                                {
                                    $account_balance = $balance ? ($balance->balance - $voucher_data->debit) : 0;
                                }elseif (!empty($voucher_data->credit)) {
                                    $account_balance = $balance ? ($balance->balance + $voucher_data->debit) : 0;
                                }
                            }else{
                                $account_balance = $balance ? $balance->balance : 0;
                            }
                            $selected = 'selected';
                        }else{
                            $account_balance = $balance ? $balance->balance : 0;
                            $selected = '';
                        }
                        
                        $account_list .= "<option value='$account->id' ".$selected.">".$account->name." [ Balance: ".number_format($account_balance,2,'.',',')."Tk]</option>";
                    }
                }
                return view('account::cash-adjustment.edit',compact('voucher_data','account_list'));
            }else{
                return redirect()->back();
            }
        }else{
            return $this->access_blocked();
        }
    }

    public function update(CashAdjustmentFormRequest $request)
    {
        if($request->ajax()){
            if(permission('cash-adjustment-edit')){
                DB::beginTransaction();
                try {

                    $data = CashAdjustment::transaction_data($request);
                    $result = $this->model->find($request->update_id)->update($data);
                    $output = $this->store_message($result, null);
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

    public function approve(Request $request)
    {
        if($request->ajax()){
            if(permission('cash-adjustment-approve')){
                $result   = $this->model->find($request->voucher_id)->update(['approve' => $request->voucher_status]);
                $output   = $result ? ['status' => 'success','message' => 'Voucher Status Changed Successfully']
                : ['status' => 'error','message' => 'Failed To Change Voucher Status'];
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
            if(permission('cash-adjustment-delete')){
                $result  = $this->model->where('voucher_no',$request->id)->delete();
                $output   = $this->delete_message($result);
                return response()->json($output);
            }else{
                return response()->json($this->unauthorized());
            }
        }else{
            return response()->json($this->unauthorized());
        }
    } 
}
