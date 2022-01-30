<?php

namespace Modules\Report\Http\Controllers\SalesReport;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Modules\Sale\Entities\SaleInvoice;
use Modules\Customer\Entities\Customer;
use App\Http\Controllers\BaseController;

class CustomerSummaryReportController extends BaseController
{
    public function index()
    {
        if(permission('sales-by-customer-summary-access')){
            $this->setPageData('Sales By Customer Summary','Sales By Customer Summary','fas fa-file',[['name' => 'Sales By Customer Summary']]);
            $customers = Customer::allCustomers();
            return view('report::sales-report.customer-summary.report',compact('customers'));
        }else{
            return $this->access_blocked();
        }
    }

    public function report_data(Request $request)
    {
        $start_date = dateFormat($request->start_date);
        $end_date   = dateFormat($request->end_date);
        $customer_id = $request->customer_id;

        $table = '';
        $table = '<table style="margin-bottom:10px !important;">
                    <tr>
                        <td class="text-center">
                            <h3 class="name m-0" style="text-transform: uppercase;"><b>'.(config('settings.title') ? config('settings.title') : env('APP_NAME')).'</b></h3>
                            <p style="font-weight: normal;font-weight:bold;    margin: 10px auto 5px auto;
                            font-weight: bold;background: black;border-radius: 10px;width: 250px;color: white;text-align: center;padding:5px 0;}">Sales By Customer Summary</p>
                            <p style="font-weight: normal;margin:0;font-weight:bold;">Date: '.$request->start_date.' to '.$request->end_date.'</p>
                        </td>
                    </tr>
                </table>';
        $table .= "<table  id='product_table'>";
        $table_head = "<tr  style='background: black;color: white;'>
                            <td><b>Trade Name</b></td>
                            <td style='text-align:right;'><b>Amount</b></td>
                        </tr>";
        $table .= $table_head;
        $total_amount = 0;

        $customers = DB::table('customers')
                        ->when($customer_id,function($q) use ($customer_id){
                            $q->where('customers.id',$customer_id);
                        })
                        ->selectRaw("customers.id,customers.code,customers.trade_name,
                        (SELECT SUM(sale_invoices.grand_total) FROM sale_invoices LEFT JOIN sale_orders ON sale_invoices.order_id = sale_orders.id 
                        WHERE sale_orders.customer_id = customers.id AND sale_invoices.invoice_date BETWEEN '$start_date' and '$end_date') as total_sale")
                        ->orderBy('customers.id','asc')
                        ->get();
        
        if (!$customers->isEmpty()) {
            foreach ($customers as $customer) {
                if($customer->total_sale > 0)
                {
                    $total_amount += $customer->total_sale;
                    $table .= "<tr>
                                <td><a href='".url('sales-by-customer-summary-details',Crypt::encrypt($customer->id))."' style='text-decoration:none;color:black;cursor:pointer;'>".$customer->code." ". $customer->trade_name."</a></td>
                                <td style='text-align:right;'>".number_format($customer->total_sale,2,'.',',')."</td>
                            </tr>";
                }
            }
            if($total_amount > 0)
            {
                $table .= "<tr>
                                <td><b>Total Amount</b></td>
                                <td style='text-align:right;'><b>".number_format($total_amount,2,'.',',')."</b></td>
                            </tr>";
            }else{
                $table .= "<tr><td colspan='2' style='text-align:center;color:red;'>No Data Found</td></tr>";
            }
        }else{
            $table .= "<tr><td colspan='2' style='text-align:center;color:red;'>No Data Found</td></tr>";
        }
        $table .= "</table>";
        return $table;

    }

    public function customer_summary_details($customer_id)
    {
        if(permission('sales-by-customer-summary-access')){
            $customer_id = Crypt::decrypt($customer_id);
            $customer = Customer::find($customer_id);
            if($customer)
            {
                $this->setPageData('Sales By Customer Summary Details','Sales By Customer Summary Details','fas fa-file',[['name' => 'Sales By Customer Summary Details']]);
                $customers = Customer::allCustomers();
                return view('report::sales-report.customer-summary.details',compact('customer','customers'));
            }else{
                return redirect()->back();
            }
            
        }else{
            return $this->access_blocked();
        }
    }

    public function customer_summary_details_data(Request $request)
    {
        $start_date = dateFormat($request->start_date);
        $end_date   = dateFormat($request->end_date);
        $customer = Customer::find($request->customer_id);
        $table = '';
        $table .= '<table style="margin-bottom:10px !important;">
                    <tr>
                        <td class="text-center">
                            <h3 class="name m-0" style="text-transform: uppercase;"><b>'.(config('settings.title') ? config('settings.title') : env('APP_NAME')).'</b></h3>
                            <p style="font-weight: normal;font-weight:bold;    margin: 10px auto 5px auto;
                            font-weight: bold;background: black;border-radius: 10px;width: 300px;color: white;text-align: center;padding:5px 0;}">Sales By Customer Summary Details</p>
                            <p style="font-weight: normal;margin:0;font-weight:bold;">Date: '.$request->start_date.' to '.$request->end_date.'</p>
                        </td>
                    </tr>
                </table>';
        if($customer)
        {
            $table .= '<table style="margin-bottom:10px !important;">
                            <tr>
                                <td class="text-center"><b>'.$customer->code.' '.$customer->trade_name.'</b></td>
                            </tr>
                        </table>';
        }
        
        $table .= "<table  id='product_table'>";
        $table_head = "<tr  style='background: black;color: white;'>
                            <td><b>Type</b></td>
                            <td style='text-align:center;'><b>Date</b></td>
                            <td><b>NUM</b></td>
                            <td><b>Memo</b></td>
                            <td style='text-align:right;'><b>Qty</b></td>
                            <td style='text-align:right;'><b>Amount</b></td>
                        </tr>";
        $table .= $table_head;
        $total_amount = $total_qty = 0;

        $invoices = DB::table('sale_invoices as si')
                ->selectRaw('si.id,si.challan_no,si.total_qty,si.grand_total,si.invoice_date,so.memo_no')
                ->join('sale_orders as so','si.order_id','=','so.id')
                ->where('so.customer_id',$request->customer_id)
                ->whereBetween('si.invoice_date',[$start_date,$end_date])
                ->orderBy('si.invoice_date','asc')
                ->get();
        if(!$invoices->isEmpty())
        {
            foreach ($invoices as $invoice) {
                $total_amount += $invoice->grand_total;
                $total_qty += $invoice->total_qty;
                $table .= "<tr>
                <td><a href='".url('sale/invoice/view',$invoice->id)."' style='text-decoration:none;color:black;cursor:pointer;'>Invoice</a></td>
                <td style='text-align:center;'><a href='".url('sale/invoice/view',$invoice->id)."' style='text-decoration:none;color:black;cursor:pointer;'>".date('d-m-Y',strtotime($invoice->invoice_date))."</a></td>
                <td><a href='".url('sale/invoice/view',$invoice->id)."' style='text-decoration:none;color:black;cursor:pointer;'>$invoice->challan_no</a></td>
                <td><a href='".url('sale/invoice/view',$invoice->id)."' style='text-decoration:none;color:black;cursor:pointer;'>$invoice->memo_no</a></td>
                <td style='text-align:right;'><a href='".url('sale/invoice/view',$invoice->id)."' style='text-decoration:none;color:black;cursor:pointer;'>".number_format($invoice->total_qty,2,'.',',')."</a></td>
                <td style='text-align:right;'><a href='".url('sale/invoice/view',$invoice->id)."' style='text-decoration:none;color:black;cursor:pointer;'>".number_format($invoice->grand_total,2,'.',',')."</a></td>
            </tr>";
            }
            $table .= "<tr>
                <td colspan='4'><b>Total</b></td>
                <td style='text-align:right;border-top:3px solid black !important;'><b>".number_format($total_qty,2,'.',',')."</b></td>
                <td style='text-align:right;border-top:3px solid black !important;'><b>".number_format($total_amount,2,'.',',')."</b></td>
            </tr>";
        }else{
            $table .= "<tr>
                <td colspan='6' style='border:0 !important;color:red;text-align:center;'>No Data Found</td>
                </tr>";
        }
        return $table;
    }
}
