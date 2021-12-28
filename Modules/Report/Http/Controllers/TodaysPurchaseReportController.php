<?php
namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;

class TodaysPurchaseReportController extends BaseController
{
    public function index()
    {
        if(permission('todays-purchase-report-access')){
            $this->setPageData('Today\'s Purchase Report','Today\'s Purchase Report','fas fa-file',[['name' => 'Today\'s Purchase Report']]);

            return view('report::todays-purchase-report.index');
        }else{
            return $this->access_blocked();
        }
    }

    public function report_data(Request $request)
    {
        $date = date('Y-m-d');
        $report_data = Purchase::with('supplier')
        ->whereDate('purchase_date',$date)
        ->orderBy('id','asc')
        ->get();
        // dd($report_data);
        return view('report::todays-purchase-report.report',compact('report_data','date'))->render();

    }
}
