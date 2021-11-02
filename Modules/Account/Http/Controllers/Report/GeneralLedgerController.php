<?php

namespace Modules\Account\Http\Controllers\Report;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Setting\Entities\Warehouse;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\Transaction;

class GeneralLedgerController extends BaseController
{
    public function __construct(Transaction $model)
    {
        $this->model = $model;
    }


    public function index()
    {
        if(permission('general-ledger-access')){
            $this->setPageData('General Ledger','General Ledger','far fa-money-bill-alt',[['name'=>'Accounts','link'=>'javascript::void(0);'],['name'=>'Report','link'=>'javascript::void(0);'],['name'=>'General Ledger']]);
            $general_heads      = DB::table('chart_of_accounts')->where([['general_ledger',1],['status',1]])->get();
            $warehouses = Warehouse::where('status',1)->pluck('name','id');
            return view('account::report.general-ledger.index',compact('general_heads','warehouses'));
        }else{
            return $this->access_blocked();
        }
    }

    public function transaction_heads(Request $request)
    {
        if($request->ajax())
        {
            $output = '';
            $transaction_heads = DB::table('chart_of_accounts')->where([['parent_name',$request->parent_name],['status',1]])->get();
            if(!$transaction_heads->isEmpty())
            {
                $output .= '<option value=""></option>';
                foreach ($transaction_heads as $key => $value) {
                    $output .= '<option value="'.$value->id.'" data-name="'.$value->name.'">'.$value->name.'</option>';
                }
            }
            return $output;
        }
    }

    public function report(Request $request)
    {
        if ($request->ajax()) {
            $start_date          = $request->start_date ? $request->start_date : date('Y-m-d');
            $end_date            = $request->end_date ? $request->end_date : date('Y-m-d');
            $bank_name           = $request->bank_name;
            $warehouse_id        = $request->warehouse_id;
            if($request->transaction_head){
                $report_data = DB::table('transactions as t')
                                ->selectRaw('t.id,t.voucher_no, t.voucher_type, t.voucher_date, 
                                t.debit, t.credit, t.approve, t.chart_of_account_id, coa.name as account_name, coa.parent_name,
                                 coa.type,t.description, w.name as warehouse_name')
                                ->leftJoin('chart_of_accounts as coa','t.chart_of_account_id','=','coa.id')
                                ->leftJoin('warehouses as w','t.warehouse_id','=','w.id')
                                ->whereDate('t.voucher_date','>=',$start_date)
                                ->whereDate('t.voucher_date','<=',$end_date)
                                ->where('t.chart_of_account_id',$request->transaction_head)
                                ->where('t.approve',1)
                                ->when($warehouse_id,function($q) use($warehouse_id){
                                    $q->where('t.warehouse_id',$warehouse_id);
                                })
                                ->orderBy('t.voucher_date','asc')
                                ->orderBy('t.voucher_no','asc');

                $report_data = $report_data->get();

                $pre_balance_data = DB::table('transactions')
                                    ->selectRaw('sum(debit) as predebit, sum(credit) as precredit')
                                    ->where('voucher_date','<',$start_date)
                                    ->where('chart_of_account_id',$request->transaction_head)
                                    ->where('approve',1)
                                    ->when($warehouse_id,function($q) use($warehouse_id){
                                        $q->where('warehouse_id',$warehouse_id);
                                    })
                                    ->first();

                $pre_balance = $pre_balance_data->predebit - $pre_balance_data->precredit;
                return view('account::report.general-ledger.report',compact('start_date','end_date',
                'pre_balance','report_data','bank_name'))->render();
            }
            
        }
    }
}
