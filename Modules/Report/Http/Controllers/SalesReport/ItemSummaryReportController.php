<?php

namespace Modules\Report\Http\Controllers\SalesReport;

use Illuminate\Http\Request;
use Modules\Product\Entities\Product;
use App\Http\Controllers\BaseController;

class ItemSummaryReportController extends BaseController
{
    public function index()
    {
        if(permission('sales-by-item-summary-access')){
            $this->setPageData('Sales By Item Summary','Sales By Item Summary','fas fa-file',[['name' => 'Sales By Item Summary']]);
            $products = Product::with('category:id,name')->whereIn('category_id',[5,3])->orderBy('category_id','desc')->orderBy('id','asc')->get();//3=By Product,5=Packet Rice
            return view('report::sales-report.item-summary-report',compact('products'));
        }else{
            return $this->access_blocked();
        }

    }

    public function report_data(Request $request)
    {
        $start_date = $request->start_date;
        $end_date   = $request->end_date;
        $product_id = $request->product_id;
        $report_data = '';

        // dd($report_data);
        return view('report::sales-order-report.report',compact('report_data','start_date','end_date'))->render();

    }
}
