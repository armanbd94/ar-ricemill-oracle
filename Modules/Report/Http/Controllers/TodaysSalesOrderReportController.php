<?php
namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Sale\Entities\Sale;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;

class TodaysSalesOrderReportController extends BaseController
{
    public function index()
    {
        if(permission('todays-sales-report-access')){
            $this->setPageData('Today\'s Sales Report','Today\'s Sales Report','fas fa-file',[['name' => 'Today\'s Sales Report']]);

            return view('report::todays-sales-report.index');
        }else{
            return $this->access_blocked();
        }
    }

    public function report_data(Request $request)
    {
        $date = date('Y-m-d');
        $report_data = Sale::with('depo','dealer','district')
        ->whereDate('sale_date',$date)
        ->orderBy('id','asc')
        ->get();
        // dd($report_data);
        return view('report::todays-sales-report.report',compact('report_data','date'))->render();

    }
}
