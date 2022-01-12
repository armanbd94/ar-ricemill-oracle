<?php

namespace Modules\Account\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\ChartOfAccount;

class AccountController extends BaseController
{
    public function __construct(ChartOfAccount $model)
    {
        $this->model = $model;
    }

    public function account_list(Request $request)
    {
        if ($request->ajax()) {
            if($request->payment_method == 1)
            {
                $accounts = $this->model->where(['parent_name' =>  'Cash & Cash Equivalent','status'=>1])->get();

            }elseif ($request->payment_method == 2) {
                $accounts = $this->model->where('code', 'like', $this->coa_head_code('cash_at_bank').'%')->where('status',1)->get();
            }elseif ($request->payment_method == 3) {
                $accounts = $this->model->where('code', 'like', $this->coa_head_code('cash_at_mobile_bank').'%')->where('status',1)->get();
            }

            $output = '';
            if ($accounts) {
                $output .= '<option value="">Select Please</option>';
                foreach ($accounts as $account) {
                    if($account->code != 1020102 && $account->code != 1020103){
                        $balance = DB::table('transactions')
                        ->select(DB::raw("SUM(debit) - SUM(credit) as balance"))
                        ->where([['chart_of_account_id',$account->id],['approve',1]])
                        ->first();
                        $output .= "<option value='$account->id'>".$account->name." [ Balance: ".($balance ? number_format($balance->balance,2,'.',',') : '0.00')."Tk]</option>";
                    }
                }
            }

            return $output;
        }
    }
}
