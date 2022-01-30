<?php

namespace Modules\Report\Http\Controllers\SalesReport;

use DateTime;
use DatePeriod;
use DateInterval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ItemGroup;
use App\Http\Controllers\BaseController;
use Modules\Report\Http\Controllers\SalesReport\Trait\SalesReport;

class ItemDetailsReportController extends BaseController
{
    use SalesReport;
    public function index()
    {
        if(permission('sales-by-item-details-access')){
            $this->setPageData('Sales By Item Details','Sales By Item Details','fas fa-file',[['name' => 'Sales By Item Details']]);
            $products = Product::group_wise_product_list();
            return view('report::sales-report.item-summary-details',compact('products'));
        }else{
            return $this->access_blocked();
        }

    }

    public function report_data(Request $request)
    {
        
        $start_date = dateFormat($request->start_date);
        $end_date   = dateFormat($request->end_date);
        $product_id = $request->product_id;
        $group_wise_credit_product_sale_count = $this->credit_sales_count($start_date,$end_date,5, null,$request->group == 'yes' ? $product_id : null); //group=yes means search by group id else serch by product id
        $group_wise_product_cash_sale_count   = $this->cash_sales_count($start_date,$end_date,5,null,$request->group == 'yes' ? $product_id : null);

        $byproduct_credit_sale_count = $this->credit_sales_count($start_date,$end_date,5, $request->group == 'no' ? $product_id : null,null);
        $byproduct_cash_sale_count   = $this->cash_sales_count($start_date,$end_date,3,$request->group == 'no' ? $product_id : null,null);
        // dd($group_wise_credit_product_sale_count,$group_wise_product_cash_sale_count,$byproduct_cash_sale_count);
        $table = '';
        $table .= '<table style="margin-bottom:10px !important;">
                    <tr>
                        <td class="text-center">
                            <h3 class="name m-0" style="text-transform: uppercase;"><b>'.(config('settings.title') ? config('settings.title') : env('APP_NAME')).'</b></h2>
                            <p style="font-weight: normal;font-weight:bold;    margin: 10px auto 5px auto;
                            font-weight: bold;background: black;border-radius: 10px;width: 250px;color: white;text-align: center;padding:5px 0;}">Sales By Item Details</p>
                            <p style="font-weight: normal;margin:0;font-weight:bold;">Date: '.$start_date.' to '.$end_date.'</p>
                            
                        </td>
                    </tr>
                </table>';
        $table .= "<table  id='product_table'>";
        $table_head = "<tr  style='background: black;color: white;'>
                            <td style='text-align:center;'><b>Type</b></td>
                            <td style='text-align:center;'><b>Date</b></td>
                            <td style='text-align:left;'><b>Num</b></td>
                            <td style='text-align:left;'><b>Memo</b></td>
                            <td style='text-align:left;'><b>Trade Name</b></td>
                            <td style='text-align:right;'><b>Qty</b></td>
                            <td style='text-align:right;'><b>Sale Price</b></td>
                            <td style='text-align:right;'><b>Amount</b></td>
                            <td style='text-align:right;'><b>Balance</b></td>
                        </tr>
                        <tr  style='background: black;color: white;'>
                            <td colspan='9' style='text-align:left;'><b>Sales</b></td>
                        </tr>";
        $table .= $table_head;
        $grand_total_qty = $grand_total_amount = $grand_total_balance = 0;
        $start_date = $start_date . ' 00:00:01';
        $end_date = $end_date . ' 23:59:59';
        $date_period = new DatePeriod(new DateTime($start_date), new DateInterval('P1D'), new DateTime($end_date));
        $table_body_packet_rice  = '';
        if($group_wise_credit_product_sale_count > 0 || $group_wise_product_cash_sale_count > 0)
        {
            
            $groups = ItemGroup::allItemGroup();
            if(!$groups->isEmpty())
            {  
                $group_total_qty = $group_total_amount = $group_total_balance =  0;
                foreach ($groups as $group) {
                    $group_counter = $balance = $group_subtotal_qty = $group_subtotal_amount = $group_subtotal_balance = 0;
                    $group_data = '';
                    foreach ($date_period as $key => $date) {
                        
                        $credit_sales = DB::table('sale_invoice_products sip')
                        ->leftJoin('products as p','sip.product_id','=','p.id')
                        ->join('sale_invoices as si','sip.sale_id','=','si.id')
                        ->leftJoin('sale_orders as so','si.order_id','=','so.id')
                        ->leftJoin('customers as c','so.customer_id','=','c.id')
                        ->where([['p.category_id',5],['p.item_group_id',$group->id]]) //5=Packet Rice
                        ->whereBetween('si.invoice_date',[$date->format('Y-m-d'),$date->format('Y-m-d')])
                        ->selectRaw('si.challan_no,SUM(sip.qty) as qty,SUM(sip.total) as total,c.trade_name,so.memo_no')
                        ->groupBy('p.item_group_id','si.challan_no','so.memo_no','c.trade_name')
                        ->get();
                        if(!$credit_sales->isEmpty())
                        {
                            foreach ($credit_sales as $sales) {
                                $group_counter++;
                                $price = $sales->total / $sales->qty;
                                $balance += $sales->total;
                                $group_subtotal_qty += $sales->qty;
                                $group_subtotal_amount += $sales->total;
                                $group_total_qty += $sales->qty;
                                $group_total_amount += $sales->total;
                                $group_data .= "<tr>
                                        <td style='padding-left:20px;border:0 !important;'>Invoice</td>
                                        <td style='border:0 !important;text-align:center;'>".$date->format('d-m-Y')."</td>
                                        <td style='border:0 !important;'>$sales->challan_no</td>
                                        <td style='border:0 !important;'>$sales->memo_no</td>
                                        <td style='border:0 !important;'>$sales->trade_name</td>
                                        <td style='border:0 !important;text-align:right;'>".number_format($sales->qty,2,'.',',')."</td>
                                        <td style='border:0 !important;text-align:right;'>".number_format($price,2,'.',',')."</td>
                                        <td style='border:0 !important;text-align:right;'>".number_format($sales->total,2,'.',',')."</td>
                                        <td style='border:0 !important;text-align:right;'>".number_format($balance,2,'.',',')."</td>
                                        </tr>";
                            }
                        }
                        $cash_sales = DB::table('cash_sale_products sip')
                        ->leftJoin('products as p','sip.product_id','=','p.id')
                        ->join('cash_sales as si','sip.sale_id','=','si.id')
                        ->where([['p.category_id',5],['p.item_group_id',$group->id]]) //5=Packet Rice
                        ->whereBetween('si.sale_date',[$date->format('Y-m-d'),$date->format('Y-m-d')])
                        ->selectRaw('si.do_number,SUM(sip.qty) as qty,SUM(sip.total) as total,si.customer_name,si.memo_no')
                        ->groupBy('p.item_group_id','si.do_number','si.memo_no','si.customer_name')
                        ->get();
                        if(!$cash_sales->isEmpty())
                        {
                            foreach ($cash_sales as $sales) {
                                $group_counter++;
                                $price = $sales->total / $sales->qty;
                                $balance += $sales->total;
                                $group_subtotal_qty += $sales->qty;
                                $group_subtotal_amount += $sales->total;
                                $group_total_qty += $sales->qty;
                                $group_total_amount += $sales->total;
                                $group_data .= "<tr>
                                        <td style='padding-left:20px;border:0 !important;'>Sales Receipt</td>
                                        <td style='border:0 !important;text-align:center;'>".$date->format('d-m-Y')."</td>
                                        <td style='border:0 !important;'>$sales->do_number</td>
                                        <td style='border:0 !important;'>$sales->memo_no</td>
                                        <td style='border:0 !important;'>$sales->customer_name</td>
                                        <td style='border:0 !important;text-align:right;'>".number_format($sales->qty,2,'.',',')."</td>
                                        <td style='border:0 !important;text-align:right;'>".number_format($price,2,'.',',')."</td>
                                        <td style='border:0 !important;text-align:right;'>".number_format($sales->total,2,'.',',')."</td>
                                        <td style='border:0 !important;text-align:right;'>".number_format($balance,2,'.',',')."</td>
                                        </tr>";
                            }
                        }
                        
                    }
                    if($group_counter > 0){
                        $table .= "<tr><td colspan='9' style='border:0 !important;'><b>Packet Rice</b></td></tr>"; //Category Name
                        $table_body_packet_rice .= "<tr><td colspan='9' style='padding-left:20px;border:0 !important;'><b>$group->name</b></td></tr>"; //Group Name
                        $table_body_packet_rice .= $group_data;
                        $table_body_packet_rice .= "<tr>
                                                        <td style='padding-left:20px;border:0 !important;'><b>Total $group->name</b></td>
                                                        <td colspan='4' style='border:0 !important;'></td>
                                                        <td style='text-align:right;border:0 !important;border-top:2px solid black !important;'><b>".number_format($group_subtotal_qty,2,'.',',')."</b></td>
                                                        <td style='text-align:right;border:0 !important;'></td>
                                                        <td style='text-align:right;border:0 !important;border-top:2px solid  black !important;'><b>".number_format($group_subtotal_amount,2,'.',',')."</b></td>
                                                        <td style='text-align:right;border:0 !important;border-top:2px solid  black !important;'><b>".number_format($balance,2,'.',',')."</b></td>
                                                    </tr>";
                        $group_total_balance += $balance;
                    }
                    
                }
                if($group_total_qty > 0)
                {
                    $table_body_packet_rice .= "<tr>
                                                        <td style='border:0 !important;'><b>Total Packet Rice</b></td>
                                                        <td colspan='4' style='border:0 !important;'></td>
                                                        <td style='text-align:right;border:0 !important;border-top:double black !important;'><b>".number_format($group_total_qty,2,'.',',')."</b></td>
                                                        <td style='text-align:right;border:0 !important;'></td>
                                                        <td style='text-align:right;border:0 !important;border-top:double black !important;'><b>".number_format($group_total_amount,2,'.',',')."</b></td>
                                                        <td style='text-align:right;border:0 !important;border-top:double black !important;'><b>".number_format($group_total_balance,2,'.',',')."</b></td>
                                                    </tr>";
                    $grand_total_qty += $group_total_qty;
                    $grand_total_amount += $group_total_amount;
                    $grand_total_balance += $group_total_balance;
                }
                
            }
            $table .= $table_body_packet_rice;
        }


        $table_body_byproduct  = '';
        if($byproduct_credit_sale_count > 0 || $byproduct_cash_sale_count > 0)
        {
            
            $byproducts = Product::where('category_id',3)->get();
            if(!$byproducts->isEmpty())
            {  
                
                $byproduct_total_qty = $byproduct_total_amount = $byproduct_total_balance =  0;
                foreach ($byproducts as $byproduct) {
                    $byproduct_counter = $balance = $byproduct_subtotal_qty = $byproduct_subtotal_amount = $byproduct_subtotal_balance = 0;
                    $byproduct_data = '';
                    foreach ($date_period as $key => $date) {
                        
                        $credit_sales = DB::table('sale_invoice_products sip')
                        ->leftJoin('products as p','sip.product_id','=','p.id')
                        ->join('sale_invoices as si','sip.sale_id','=','si.id')
                        ->leftJoin('sale_orders as so','si.order_id','=','so.id')
                        ->leftJoin('customers as c','so.customer_id','=','c.id')
                        ->where([['p.category_id',3],['sip.product_id',$byproduct->id]]) //3=By Product
                        ->whereBetween('si.invoice_date',[$date->format('Y-m-d'),$date->format('Y-m-d')])
                        ->selectRaw('si.challan_no,SUM(sip.qty) as qty,SUM(sip.total) as total,c.trade_name,so.memo_no')
                        ->groupBy('sip.product_id','si.challan_no','so.memo_no','c.trade_name')
                        ->get();
                        if(!$credit_sales->isEmpty())
                        {
                            foreach ($credit_sales as $sales) {
                                $byproduct_counter++;
                                $price = $sales->total / $sales->qty;
                                $balance += $sales->total;
                                $byproduct_subtotal_qty += $sales->qty;
                                $byproduct_subtotal_amount += $sales->total;
                                $byproduct_total_qty += $sales->qty;
                                $byproduct_total_amount += $sales->total;
                                $byproduct_data .= "<tr>
                                        <td style='padding-left:20px;border:0 !important;'>Invoice</td>
                                        <td style='border:0 !important;text-align:center;'>".$date->format('d-m-Y')."</td>
                                        <td style='border:0 !important;'>$sales->challan_no</td>
                                        <td style='border:0 !important;'>$sales->memo_no</td>
                                        <td style='border:0 !important;'>$sales->trade_name</td>
                                        <td style='border:0 !important;text-align:right;'>".number_format($sales->qty,2,'.',',')."</td>
                                        <td style='border:0 !important;text-align:right;'>".number_format($price,2,'.',',')."</td>
                                        <td style='border:0 !important;text-align:right;'>".number_format($sales->total,2,'.',',')."</td>
                                        <td style='border:0 !important;text-align:right;'>".number_format($balance,2,'.',',')."</td>
                                        </tr>";
                            }
                        }
                        $cash_sales = DB::table('cash_sale_products sip')
                        ->leftJoin('products as p','sip.product_id','=','p.id')
                        ->join('cash_sales as si','sip.sale_id','=','si.id')
                        ->where([['p.category_id',3],['sip.product_id',$byproduct->id]]) //3=By Product
                        ->whereBetween('si.sale_date',[$date->format('Y-m-d'),$date->format('Y-m-d')])
                        ->selectRaw('si.do_number,SUM(sip.qty) as qty,SUM(sip.total) as total,si.customer_name,si.memo_no')
                        ->groupBy('sip.product_id','si.do_number','si.memo_no','si.customer_name')
                        ->get();

                        if(!$cash_sales->isEmpty())
                        {
                            foreach ($cash_sales as $sales) {
                                $byproduct_counter++;
                                $price = $sales->total / $sales->qty;
                                $balance += $sales->total;
                                $byproduct_subtotal_qty += $sales->qty;
                                $byproduct_subtotal_amount += $sales->total;
                                $byproduct_total_qty += $sales->qty;
                                $byproduct_total_amount += $sales->total;
                                $byproduct_data .= "<tr>
                                        <td style='padding-left:20px;border:0 !important;'>Sales Receipt</td>
                                        <td style='border:0 !important;text-align:center;'>".$date->format('d-m-Y')."</td>
                                        <td style='border:0 !important;'>$sales->do_number</td>
                                        <td style='border:0 !important;'>$sales->memo_no</td>
                                        <td style='border:0 !important;'>$sales->customer_name</td>
                                        <td style='border:0 !important;text-align:right;'>".number_format($sales->qty,2,'.',',')."</td>
                                        <td style='border:0 !important;text-align:right;'>".number_format($price,2,'.',',')."</td>
                                        <td style='border:0 !important;text-align:right;'>".number_format($sales->total,2,'.',',')."</td>
                                        <td style='border:0 !important;text-align:right;'>".number_format($balance,2,'.',',')."</td>
                                        </tr>";
                            }
                        }
                        
                    }
                    if($byproduct_counter > 0){
                        $table_body_byproduct .= "<tr><td colspan='9' style='padding-left:20px;border:0 !important;'><b>$byproduct->name</b></td></tr>"; //byproduct Name
                        $table_body_byproduct .= $byproduct_data;
                        $table_body_byproduct .= "<tr>
                                                        <td style='padding-left:20px;border:0 !important;'><b>Total $byproduct->name</b></td>
                                                        <td colspan='4' style='border:0 !important;'></td>
                                                        <td style='text-align:right;border:0 !important;border-top:2px solid black !important;'><b>".number_format($byproduct_subtotal_qty,2,'.',',')."</b></td>
                                                        <td style='text-align:right;border:0 !important;'></td>
                                                        <td style='text-align:right;border:0 !important;border-top:2px solid  black !important;'><b>".number_format($byproduct_subtotal_amount,2,'.',',')."</b></td>
                                                        <td style='text-align:right;border:0 !important;border-top:2px solid  black !important;'><b>".number_format($balance,2,'.',',')."</b></td>
                                                    </tr>";
                        $byproduct_total_balance += $balance;
                    }
                    
                }
                if($byproduct_total_qty > 0)
                {
                    $table .= "<tr><td colspan='9' style='border:0 !important;'><b>By Product</b></td></tr>"; //Category Name
                    $table_body_byproduct .= "<tr>
                                                        <td style='border:0 !important;'><b>Total By Product</b></td>
                                                        <td colspan='4' style='border:0 !important;'></td>
                                                        <td style='text-align:right;border:0 !important;border-top:double black !important;'><b>".number_format($byproduct_total_qty,2,'.',',')."</b></td>
                                                        <td style='text-align:right;border:0 !important;'></td>
                                                        <td style='text-align:right;border:0 !important;border-top:double black !important;'><b>".number_format($byproduct_total_amount,2,'.',',')."</b></td>
                                                        <td style='text-align:right;border:0 !important;border-top:double black !important;'><b>".number_format($byproduct_total_balance,2,'.',',')."</b></td>
                                                    </tr>";
                    $grand_total_qty += $byproduct_total_qty;
                    $grand_total_amount += $byproduct_total_amount;
                    $grand_total_balance += $byproduct_total_balance;
                }
                
            }
            $table .= $table_body_byproduct;
        }

        if($group_wise_credit_product_sale_count > 0 || $group_wise_product_cash_sale_count > 0 || 
        $byproduct_credit_sale_count > 0 || $byproduct_cash_sale_count > 0){
            $table .= "<tr><td colspan='9' style='border:0 !important;'></td></tr>
            <tr>
                <td style='border:0 !important;'><b>Total</b></td>
                <td colspan='4' style='border:0 !important;'></td>
                <td style='text-align:right;border:0 !important;border-top:double black !important;'><b>".number_format($grand_total_qty,2,'.',',')."</b></td>
                <td style='text-align:right;border:0 !important;'></td>
                <td style='text-align:right;border:0 !important;border-top:double black !important;'><b>".number_format($grand_total_amount,2,'.',',')."</b></td>
                <td style='text-align:right;border:0 !important;border-top:double black !important;'><b>".number_format($grand_total_balance,2,'.',',')."</b></td>
            </tr>";
        }else{
            $table .= "<tr><td colspan='9' style='border:0 !important;color:red;text-align:center;'>No Data Found</td></tr>";
        }

        $table .= "</table>";
        return $table;
    }


}
