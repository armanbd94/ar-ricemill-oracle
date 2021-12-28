<?php

namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Product\Entities\Product;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\DB;

class ProductSalesReportController extends BaseController
{

    public function index()
    {
        if(permission('product-sales-report-access')){
            $this->setPageData('Product Sales Report','Product Sales Report','fas fa-file',[['name' => 'Report'],['name' => 'Product Sales Report']]);
            $products = Product::toBase()->orderBy('id','asc')->pluck('name','id');
            return view('report::product-sales-report.index',compact('products'));
        }else{
            return $this->access_blocked();
        }
    }

    public function report_data(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $report_data = DB::table('products as p')
        ->select('p.id','p.name','u.unit_name as ctn_size', 'saleData.*')
        ->leftJoin('units as u','p.unit_id','=','u.id')
        ->leftJoin(DB::raw("(SELECT sp.product_id,ifnull(SUM(sp.unit_qty),0) as unit_qty,
        ifnull(SUM(sp.qty),0) as qty, ifnull(SUM(sp.free_qty),0) as free_qty,ifnull(sum(sp.total),0) as total FROM sale_products as sp 
        INNER JOIN sales as s ON sp.sale_id = s.id  WHERE s.sale_date BETWEEN '$start_date' AND '$end_date' GROUP BY sp.product_id) as saleData"), 
        function($join)
        {
           $join->on('p.id', '=', 'saleData.product_id');
        })
        ->orderBy('p.id', 'ASC')
        ->get();
        
        return view('report::product-sales-report.report',compact('report_data','start_date','end_date'))->render();

    }
}
