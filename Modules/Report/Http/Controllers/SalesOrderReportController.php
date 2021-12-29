<?php
namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Sale\Entities\Sale;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;

class SalesOrderReportController extends BaseController
{

    public function index()
    {
        if(permission('sales-report-access')){
            $this->setPageData('Sales Report','Sales Report','fas fa-file',[['name' => 'Sales Report']]);
            return view('report::sales-report.index');
        }else{
            return $this->access_blocked();
        }

    }

    public function report_data(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $report_data = Sale::with('depo','dealer','district')
        ->whereBetween('sale_date',[$start_date,$end_date])
        ->orderBy('id','asc')
        ->get();
        // dd($report_data);
        return view('report::sales-report.report',compact('report_data','start_date','end_date'))->render();

    }
}
