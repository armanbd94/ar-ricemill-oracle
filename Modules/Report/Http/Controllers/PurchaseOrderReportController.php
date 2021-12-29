<?php
namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Modules\Purchase\Entities\PurchaseOrder;

class PurchaseOrderReportController extends BaseController
{

    public function index()
    {
        if(permission('purchase-order-report-access')){
            $this->setPageData('Purchase Order Report','Purchase Order Report','fas fa-file',[['name' => 'Purchase Order Report']]);
            return view('report::purchase-order-report.index');
        }else{
            return $this->access_blocked();
        }

    }

    public function report_data(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $report_data = PurchaseOrder::with('vendor','via_vendor','materials')
        ->whereBetween('order_date',[$start_date,$end_date])
        ->orderBy('id','asc')
        ->get();
        return view('report::purchase-order-report.report',compact('report_data','start_date','end_date'))->render();

    }
}
