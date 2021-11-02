<?php

namespace Modules\Account\Http\Controllers\Report;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\ChartOfAccount;
use Modules\Account\Entities\Transaction;

class BalanceSheetController extends BaseController
{
    public function __construct(ChartOfAccount $model)
    {
        $this->model = $model;
    }


    public function index()
    {
        if(permission('balance-sheet-access')){
            $this->setPageData('Balance Sheet','Balance Sheet','far fa-money-bill-alt',[['name'=>'Accounts','link'=>'javascript::void(0);'],['name'=>'Report','link'=>'javascript::void(0);'],['name'=>'Balance Sheet']]);
            return view('account::report.balance-sheet.index');
        }else{
            return $this->access_blocked();
        }
    }

    public function report(Request $request)
    {
        if ($request->ajax()) {
            $data = [
                'start_date'   => $request->start_date ? $request->start_date : date('Y-m-d'),
                'end_date'     => $request->end_date ? $request->end_date : date('Y-m-d'),
                'fixed_assets' => $this->model->where('parent_name','Assets')->orderBy('id','desc')->get(),
                'liabilities'  => $this->model->where('parent_name','Liabilities')->orderBy('id','desc')->get(),
                'incomes'  => $this->model->where('parent_name','Income')->get(),
                'expenses'  => $this->model->where('parent_name','Expense')->get(),
            ];
            // dd($data['fixed_assets']);
            return view('account::report.balance-sheet.report',$data)->render();
        }
    }
}
