<?php

namespace Modules\Account\Http\Controllers\Report;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Modules\Bank\Entities\Bank;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\Transaction;

class BankBookController extends BaseController
{
    public function __construct(Transaction $model)
    {
        $this->model = $model;
    }


    public function index()
    {
        if(permission('bank-book-access')){
            $this->setPageData('Bank Book','Bank Book','far fa-money-bill-alt',[['name'=>'Accounts','link'=>'javascript::void(0);'],['name'=>'Report','link'=>'javascript::void(0);'],['name'=>'Bank Book']]);
            $banks      = DB::table('chart_of_accounts')->where([['code','like','1020102%'],['level',4],['status',1]])->get();
            return view('account::report.bank-book.index',compact('banks'));
        }else{
            return $this->access_blocked();
        }
    }

    public function report(Request $request)
    {
        if ($request->ajax()) {
            $start_date   = $request->start_date ? $request->start_date : date('Y-m-d');
            $end_date     = $request->end_date ? $request->end_date : date('Y-m-d');
            $bank_name = $request->bank_name;
            if($request->bank_acc_id){
                $bank_acc_id  = $request->bank_acc_id;
                
                $previous_balance = 0;
                
                $previous_balance_data = DB::table('transactions')
                                            ->selectRaw('SUM(debit) as debit, SUM(credit) as credit,approve')
                                            ->where([['voucher_date','<',$start_date],['chart_of_account_id',$bank_acc_id],['approve',1]])
                                            ->groupBy('chart_of_account_id','approve')->first();
                if($previous_balance_data)
                {
                    $previous_balance = $previous_balance_data->debit - $previous_balance_data->credit;
                }
                $report_data = DB::table('transactions as t')
                                ->selectRaw('t.id,t.voucher_no, t.voucher_type, t.voucher_date, 
                                t.debit, t.credit, t.approve, t.chart_of_account_id, coa.name as account_name, coa.parent_name, coa.type,
                                 t.description')
                                ->leftJoin('chart_of_accounts as coa','t.chart_of_account_id','=','coa.id')
                                ->whereDate('t.voucher_date','>=',$start_date)
                                ->whereDate('t.voucher_date','<=',$end_date)
                                ->where('t.chart_of_account_id',$bank_acc_id)
                                ->where('t.approve',1)
                                ->orderBy('t.voucher_date','asc')
                                ->orderBy('t.voucher_no','asc')
                                ->get();

                return view('account::report.bank-book.report',compact('start_date','end_date','previous_balance','report_data','bank_name'))->render();
            }
            
        }
    }
}
