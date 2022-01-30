<?php

namespace Modules\Report\Http\Controllers\SalesReport;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use App\Http\Controllers\BaseController;
use Modules\Product\Entities\ItemGroup;

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
        $start_date = dateFormat($request->start_date);
        $end_date   = dateFormat($request->end_date);
        $product_id = $request->product_id;

        $category_wise_credit_product_sale_count = DB::table('sale_invoice_products sip')
        ->join('products as p','sip.product_id','=','p.id')
        ->join('sale_invoices as si','sip.sale_id','=','si.id')
        ->where('p.category_id',5) //5=Packet Rice
        ->whereBetween('si.invoice_date',[$start_date,$end_date])
        ->when($product_id,function($q) use ($product_id){
            $q->where('sip.product_id',$product_id);
        })
        ->count();
        
        $category_wise_cash_product_sale_count = DB::table('cash_sale_products csp')
        ->join('products as p','csp.product_id','=','p.id')
        ->join('cash_sales as cs','csp.sale_id','=','cs.id')
        ->where('p.category_id',5) //5=Packet Rice
        ->whereBetween('cs.sale_date',[$start_date,$end_date])
        ->when($product_id,function($q) use ($product_id){
            $q->where('csp.product_id',$product_id);
        })
        ->count();

        $category_wise_credit_byproduct_sale_count = DB::table('sale_invoice_products sip')
        ->join('products as p','sip.product_id','=','p.id')
        ->join('sale_invoices as si','sip.sale_id','=','si.id')
        ->where('p.category_id',3) //5=Packet Rice
        ->whereBetween('si.invoice_date',[$start_date,$end_date])
        ->when($product_id,function($q) use ($product_id){
            $q->where('sip.product_id',$product_id);
        })
        ->count();

        $category_wise_cash_byproduct_sale_count = DB::table('cash_sale_products csp')
        ->join('products as p','csp.product_id','=','p.id')
        ->join('cash_sales as cs','csp.sale_id','=','cs.id')
        ->where('p.category_id',3) //5=Packet Rice
        ->whereBetween('cs.sale_date',[$start_date,$end_date])
        ->when($product_id,function($q) use ($product_id){
            $q->where('csp.product_id',$product_id);
        })
        ->count();

        $report_data = '';
        $table = '';
        $table = '<table style="margin-bottom:10px !important;">
                    <tr>
                        <td class="text-center">
                            <h3 class="name m-0" style="text-transform: uppercase;"><b>'.(config('settings.title') ? config('settings.title') : env('APP_NAME')).'</b></h3>
                            <p style="font-weight: normal;font-weight:bold;    margin: 10px auto 5px auto;
                            font-weight: bold;background: black;border-radius: 10px;width: 250px;color: white;text-align: center;padding:5px 0;}">Sales By Item Summary</p>
                            <p style="font-weight: normal;margin:0;font-weight:bold;">Date: '.$request->start_date.' to '.$request->end_date.'</p>
                            
                        </td>
                    </tr>
                </table>';
        $table .= "<table  id='product_table'>";
        $table_head = "<tr  style='background: black;color: white;'>
                            <td><b>Sales Item</b></td>
                            <td style='text-align:right;'><b>Quantity</b></td>
                            <td style='text-align:right;'><b>Avg. Price</b></td>
                            <td style='text-align:right;'><b>Amount</b></td>
                        </tr>";
        $table .= $table_head;
        $grand_total_qty = $grand_total_amount = 0;
        //Packet Rice
        $table_body_packet_rice  = '';
        if($category_wise_credit_product_sale_count > 0 || $category_wise_cash_product_sale_count > 0){
            
            $table_body_packet_rice .= "<tr><td colspan='4' style='border:0 !important;'><b>Packet Rice</b></td></tr>"; //Category Name
            $groups = ItemGroup::allItemGroup();
            if(!$groups->isEmpty())
            {   
                $total_category_qty = $total_category_amount = 0;
                foreach ($groups as $group) {
                    $group_wise_credit_product_sale_count = DB::table('sale_invoice_products sip')
                    ->join('products as p','sip.product_id','=','p.id')
                    ->join('sale_invoices as si','sip.sale_id','=','si.id')
                    ->where('p.item_group_id',$group->id)
                    ->whereBetween('si.invoice_date',[$start_date,$end_date])
                    ->when($product_id,function($q) use ($product_id){
                        $q->where('sip.product_id',$product_id);
                    })
                    ->count();
                    
                    $group_wise_cash_product_sale_count = DB::table('cash_sale_products csp')
                    ->join('products as p','csp.product_id','=','p.id')
                    ->join('cash_sales as cs','csp.sale_id','=','cs.id')
                    ->where('p.item_group_id',$group->id)
                    ->whereBetween('cs.sale_date',[$start_date,$end_date])
                    ->when($product_id,function($q) use ($product_id){
                        $q->where('csp.product_id',$product_id);
                    })
                    ->count();

                    if($group_wise_credit_product_sale_count > 0 || $group_wise_cash_product_sale_count > 0)
                    {
                        $table_body_packet_rice .= "<tr><td colspan='4' style='padding-left:20px;border:0 !important;'><b>$group->name</b></td></tr>"; //Group Name
                        $products = DB::table('products')
                        ->where([['products.category_id',5],['products.item_group_id',$group->id]])
                        ->when($product_id,function($q) use ($product_id){
                            $q->where('products.id',$product_id);
                        })
                        ->selectRaw("products.id,products.name,
                        (SELECT SUM(sale_invoice_products.qty) FROM sale_invoice_products LEFT JOIN sale_invoices ON sale_invoice_products.sale_id = sale_invoices.id 
                        WHERE sale_invoice_products.product_id = products.id AND sale_invoices.invoice_date BETWEEN '$start_date' and '$end_date') as credit_qty,

                        (SELECT SUM(sale_invoice_products.total) FROM sale_invoice_products LEFT JOIN sale_invoices ON sale_invoice_products.sale_id = sale_invoices.id 
                        WHERE sale_invoice_products.product_id = products.id AND sale_invoices.invoice_date BETWEEN '$start_date' and '$end_date') as credit_total,

                        (SELECT SUM(cash_sale_products.qty) FROM cash_sale_products LEFT JOIN cash_sales ON cash_sale_products.sale_id = cash_sales.id 
                        WHERE cash_sale_products.product_id = products.id AND cash_sales.sale_date BETWEEN '$start_date' and '$end_date') as cash_qty,

                        (SELECT SUM(cash_sale_products.total) FROM cash_sale_products LEFT JOIN cash_sales ON cash_sale_products.sale_id = cash_sales.id 
                        WHERE cash_sale_products.product_id = products.id AND cash_sales.sale_date BETWEEN '$start_date' and '$end_date') as cash_total")
                        ->get();
                        if(!$products->isEmpty())
                        {
                            $total_group_qty = $total_group_amount = 0;
                            foreach ($products as $product) {
                                $credit_qty = $product->credit_qty ?? 0;
                                $cash_qty = $product->cash_qty ?? 0;
                                if($credit_qty > 0 || $cash_qty > 0)
                                {
                                    $credit_total = $product->credit_total ?? 0;
                                    $cash_total = $product->cash_total ?? 0;
                                    $avg_price = ($credit_total + $cash_total) / ($credit_qty + $cash_qty);
                                    $total_group_qty += $credit_qty + $cash_qty;
                                    $total_group_amount += $credit_total + $cash_total;
                                    $table_body_packet_rice .= "<tr>
                                        <td style='padding-left:40px;border:0 !important;'>$product->name</td>
                                        <td style='text-align:right;border:0 !important;'>".number_format(($credit_qty + $cash_qty),2,'.',',')."</td>
                                        <td style='text-align:right;border:0 !important;'>".number_format($avg_price,2,'.',',')."</td>
                                        <td style='text-align:right;border:0 !important;'>".number_format(($credit_total + $cash_total),2,'.',',')."</td>
                                    </tr>";
                                }
                            }
                            $table_body_packet_rice .= "<tr>
                                <td style='padding-left:20px;border:0 !important;'><b>Total $group->name</b></td>
                                <td style='text-align:right;border:0 !important;border-top:2px solid black !important;'><b>".number_format($total_group_qty,2,'.',',')."</b></td>
                                <td style='text-align:right;border:0 !important;'></td>
                                <td style='text-align:right;border:0 !important;border-top:2px solid black !important;'><b>".number_format($total_group_amount,2,'.',',')."</b></td>
                            </tr>";
                            $total_category_qty += $total_group_qty;
                            $total_category_amount += $total_group_amount;
                        }
                    }
                }
                $table_body_packet_rice .= "<tr>
                    <td style='border:0 !important;'><b>Total Packet Rice</b></td>
                    <td style='text-align:right;border:0 !important;border-top:double black !important;'><b>".number_format($total_category_qty,2,'.',',')."</b></td>
                    <td style='text-align:right;border:0 !important;'></td>
                    <td style='text-align:right;border:0 !important;border-top:double black !important;'><b>".number_format($total_category_amount,2,'.',',')."</b></td>
                </tr>";
                $grand_total_qty += $total_category_qty;
                $grand_total_amount += $total_category_amount;
            }
            
        }


        //By Product
        $table_body_byproduct  = '';
        if($category_wise_credit_byproduct_sale_count > 0 || $category_wise_cash_byproduct_sale_count > 0){
            
            $table_body_byproduct .= "<tr><td colspan='4' style='border:0 !important;'></td></tr>";
            $table_body_byproduct .= "<tr><td colspan='4' style='border:0 !important;'><b>By Product</b></td></tr>"; //Category Name
            $products = DB::table('products')
                        ->where('products.category_id',3)
                        ->when($product_id,function($q) use ($product_id){
                            $q->where('products.id',$product_id);
                        })
                        ->selectRaw("products.id,products.name,
                        (SELECT SUM(sale_invoice_products.qty) FROM sale_invoice_products LEFT JOIN sale_invoices ON sale_invoice_products.sale_id = sale_invoices.id 
                        WHERE sale_invoice_products.product_id = products.id AND sale_invoices.invoice_date BETWEEN '$start_date' and '$end_date') as credit_qty,

                        (SELECT SUM(sale_invoice_products.total) FROM sale_invoice_products LEFT JOIN sale_invoices ON sale_invoice_products.sale_id = sale_invoices.id 
                        WHERE sale_invoice_products.product_id = products.id AND sale_invoices.invoice_date BETWEEN '$start_date' and '$end_date') as credit_total,

                        (SELECT SUM(cash_sale_products.qty) FROM cash_sale_products LEFT JOIN cash_sales ON cash_sale_products.sale_id = cash_sales.id 
                        WHERE cash_sale_products.product_id = products.id AND cash_sales.sale_date BETWEEN '$start_date' and '$end_date') as cash_qty,

                        (SELECT SUM(cash_sale_products.total) FROM cash_sale_products LEFT JOIN cash_sales ON cash_sale_products.sale_id = cash_sales.id 
                        WHERE cash_sale_products.product_id = products.id AND cash_sales.sale_date BETWEEN '$start_date' and '$end_date') as cash_total")
                        ->get();
            if(!$products->isEmpty())
            {
                $total_category_qty = $total_category_amount = 0;
                foreach ($products as $product) {
                    $credit_qty = $product->credit_qty ?? 0;
                    $cash_qty = $product->cash_qty ?? 0;
                    if($credit_qty > 0 || $cash_qty > 0)
                    {
                        $credit_total = $product->credit_total ?? 0;
                        $cash_total = $product->cash_total ?? 0;
                        $avg_price = ($credit_total + $cash_total) / ($credit_qty + $cash_qty);
                        $total_category_qty += $credit_qty + $cash_qty;
                        $total_category_amount += $credit_total + $cash_total;
                        $table_body_byproduct .= "<tr>
                                        <td style='padding-left:20px;border:0 !important;'>$product->name</td>
                                        <td style='text-align:right;border:0 !important;'>".number_format(($credit_qty + $cash_qty),2,'.',',')."</td>
                                        <td style='text-align:right;border:0 !important;'>".number_format($avg_price,2,'.',',')."</td>
                                        <td style='text-align:right;border:0 !important;'>".number_format(($credit_total + $cash_total),2,'.',',')."</td>
                                    </tr>";
                    }
                }
                $table_body_byproduct .= "<tr>
                    <td style='border:0 !important;'><b>Total By Product</b></td>
                    <td style='text-align:right;border:0 !important;border-top:double black !important;'><b>".number_format($total_category_qty,2,'.',',')."</b></td>
                    <td style='text-align:right;border:0 !important;'></td>
                    <td style='text-align:right;border:0 !important;border-top:double black !important;'><b>".number_format($total_category_amount,2,'.',',')."</b></td>
                </tr>";
                $grand_total_qty += $total_category_qty;
                $grand_total_amount += $total_category_amount;
            }
        }

        
        $table .= $table_body_packet_rice;
        $table .= $table_body_byproduct;

        if($category_wise_credit_product_sale_count > 0 || $category_wise_cash_product_sale_count > 0 || 
        $category_wise_credit_byproduct_sale_count > 0 || $category_wise_cash_byproduct_sale_count > 0){
            $table .= "<tr><td colspan='4' style='border:0 !important;'></td></tr>
            <tr>
                <td style='border:0 !important;'><b>Grand Total</b></td>
                <td style='text-align:right;border:0 !important;border-top:double black !important;'><b>".number_format($grand_total_qty,2,'.',',')."</b></td>
                <td style='text-align:right;border:0 !important;'></td>
                <td style='text-align:right;border:0 !important;border-top:double black !important;'><b>".number_format($grand_total_amount,2,'.',',')."</b></td>
            </tr>";
        }else{
            $table .= "<tr><td colspan='4' style='border:0 !important;color:red;text-align:center;'>No Data Found</td></tr>";
        }

            
        $table .= "</table>";
        return $table;

    }
}
