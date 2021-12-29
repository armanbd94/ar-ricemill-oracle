<?php
namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Sale\Entities\Sale;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use Modules\Sale\Entities\SaleOrder;

class SalesOrderReportController extends BaseController
{

    public function index()
    {
        if(permission('sales-order-report-access')){
            $this->setPageData('Sales Order Report','Sales Order Report','fas fa-file',[['name' => 'Sales Order Report']]);
            return view('report::sales-order-report.index');
        }else{
            return $this->access_blocked();
        }

    }

    public function report_data(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $report_data = SaleOrder::with('customer','via_customer','products')
        ->whereBetween('order_date',[$start_date,$end_date])
        ->orderBy('id','asc')
        ->get();
        // dd($report_data);
        return view('report::sales-order-report.report',compact('report_data','start_date','end_date'))->render();

    }
}
