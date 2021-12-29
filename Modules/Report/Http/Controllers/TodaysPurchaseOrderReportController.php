<?php
namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Modules\Purchase\Entities\PurchaseOrder;

class TodaysPurchaseOrderReportController extends BaseController
{
    public function index()
    {
        if(permission('todays-purchase-order-report-access')){
            $this->setPageData('Today\'s Purchase Order Report','Today\'s Purchase Order Report','fas fa-file',[['name' => 'Today\'s Purchase Order Report']]);

            return view('report::todays-purchase-report.index');
        }else{
            return $this->access_blocked();
        }
    }

    public function report_data(Request $request)
    {
        $date = date('Y-m-d');
        $report_data = PurchaseOrder::with('vendor','via_vendor','materials')
        ->whereDate('order_date',$date)
        ->orderBy('id','asc')
        ->get();
        // dd($report_data);
        return view('report::todays-purchase-report.report',compact('report_data','date'))->render();

    }
}
