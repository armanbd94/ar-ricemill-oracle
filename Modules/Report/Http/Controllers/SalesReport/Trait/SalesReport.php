<?php
namespace Modules\Report\Http\Controllers\SalesReport\Trait;

use Illuminate\Support\Facades\DB;

trait SalesReport
{
    protected function credit_sales_count($start_date,$end_date,$category_id,$product_id=null,$group_id=null)
    {
        return DB::table('sale_invoice_products sip')
        ->join('products as p','sip.product_id','=','p.id')
        ->join('sale_invoices as si','sip.sale_id','=','si.id')
        ->where('p.category_id',$category_id) //5=Packet Rice
        ->whereBetween('si.invoice_date',[$start_date,$end_date])
        ->when($group_id,function($q) use ($group_id){
            $q->where('p.item_group_id',$group_id);
        })
        ->when($product_id,function($q) use ($product_id){
            $q->where('sip.product_id',$product_id);
        })
        ->count();
    }

    protected function cash_sales_count($start_date,$end_date,$category_id,$product_id=null,$group_id=null)
    {
        
        return DB::table('cash_sale_products csp')
        ->join('products as p','csp.product_id','=','p.id')
        ->join('cash_sales as cs','csp.sale_id','=','cs.id')
        ->where('p.category_id',$category_id) //5=Packet Rice
        ->whereBetween('cs.sale_date',[$start_date,$end_date])
        ->when($group_id,function($q) use ($group_id){
            $q->where('p.item_group_id',$group_id);
        })
        ->when($product_id,function($q) use ($product_id){
            $q->where('csp.product_id',$product_id);
        })
        ->count();
    }
}
