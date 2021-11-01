<?php

namespace Modules\Account\Http\Controllers\Report;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\ChartOfAccount;
use Modules\Account\Entities\Transaction;

class TrialBalanceController extends BaseController
{
    public function __construct(Transaction $model)
    {
        $this->model = $model;
    }


    public function index()
    {
        if(permission('trial-balance-access')){
            $this->setPageData('Trial Balance','Trial Balance','far fa-money-bill-alt',[['name'=>'Accounts','link'=>'javascript::void(0);'],['name'=>'Report','link'=>'javascript::void(0);'],['name'=>'Trial Balance']]);
            return view('account::report.trial-balance.index');
        }else{
            return $this->access_blocked();
        }
    }

    public function report(Request $request)
    {
        if ($request->ajax()) {
            $data = [
                'start_date'   => $request->start_date ? $request->start_date : date('Y-m-d'),
                'end_date'    => $request->end_date ? $request->end_date : date('Y-m-d'),
                'with_details' => $request->with_details,
                'transactional_accounts' => ChartOfAccount::where(['general_ledger'=>1,'status'=>1])->whereIn('type',['A','L'])->orderBy('code','asc')->get(),
                'income_expense_accounts' => ChartOfAccount::where(['general_ledger'=>1,'status'=>1])->whereIn('type',['I','E'])->orderBy('code','asc')->get(),
            ];
            // dd($data);
            return view('account::report.trial-balance.report',$data)->render();
        }
    }
}
