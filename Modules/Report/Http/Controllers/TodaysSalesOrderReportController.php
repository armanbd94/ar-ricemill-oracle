<?php
namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Sale\Entities\Sale;
use Illuminate\Support\Facades\DB;
use Modules\Sale\Entities\SaleOrder;
use App\Http\Controllers\BaseController;

class TodaysSalesOrderReportController extends BaseController
{
    public function index()
    {
        if(permission('todays-sales-order-report-access')){
            $this->setPageData('Today\'s Sales Order Report','Today\'s Sales Order Report','fas fa-file',[['name' => 'Today\'s Sales Order Report']]);

            return view('report::todays-sales-order-report.index');
        }else{
            return $this->access_blocked();
        }
    }

    public function report_data(Request $request)
    {
        $date = date('Y-m-d');
        $report_data = SaleOrder::with('customer','via_customer','products')
        ->whereDate('order_date',$date)
        ->orderBy('id','asc')
        ->get();
        // dd($report_data);
        return view('report::todays-sales-order-report.report',compact('report_data','date'))->render();

    }
}
