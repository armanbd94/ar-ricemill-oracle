<?php
namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Purchase\Entities\Purchase;
use App\Http\Controllers\BaseController;

class PurchaseReportController extends BaseController
{

    public function index()
    {
        if(permission('purchase-report-access')){
            $this->setPageData('Purchase Report','Purchase Report','fas fa-file',[['name' => 'Purchase Report']]);
            return view('report::purchase-report.index');
        }else{
            return $this->access_blocked();
        }

    }

    public function report_data(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $report_data = Purchase::with('supplier')
        ->whereBetween('purchase_date',[$start_date,$end_date])
        ->orderBy('id','asc')
        ->get();
        // dd($report_data);
        return view('report::purchase-report.report',compact('report_data','start_date','end_date'))->render();

    }
}
