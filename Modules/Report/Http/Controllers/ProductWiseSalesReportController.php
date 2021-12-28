<?php

namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Product\Entities\Product;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\DB;

class ProductWiseSalesReportController extends BaseController
{

    public function index()
    {
        if(permission('product-wise-sales-report-access')){
            $this->setPageData('Product Wise Sales Report','Product Wise Sales Report','fas fa-file',[['name' => 'Report'],['name' => 'Product Wise Sales Report']]);
            $products = Product::toBase()->orderBy('id','asc')->pluck('name','id');
            return view('report::product-wise-sales-report.index',compact('products'));
        }else{
            return $this->access_blocked();
        }
    }

    public function report_data(Request $request)
    {

        $product_id = $request->product_id;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $product = Product::find($product_id);
        $report_data = DB::table('sale_products as sp')
        ->join('sales as s','sp.sale_id','=','s.id')
        ->join('products as p','sp.product_id','=','p.id')
        ->join('units as bu','sp.base_unit_id','=','bu.id')
        ->join('units as u','p.unit_id','=','u.id')
        ->selectRaw('p.id,p.name,bu.unit_name as sale_unit,u.unit_name as ctn_size,SUM(sp.unit_qty) as unit_qty,
        SUM(sp.qty) as qty, SUM(sp.free_qty) as free_qty,sum(sp.total) as total,s.sale_date')
        ->groupBy('s.sale_date')
        ->where(['sp.product_id'=>$product_id])
        ->whereBetween('s.sale_date',[$start_date,$end_date]) 
        ->orderBy('s.sale_date','asc')
        ->get();
        // dd($report_data);
        return view('report::product-wise-sales-report.report',compact('report_data','product','start_date','end_date'))->render();

    }
}
