<?php

namespace Modules\Report\Http\Controllers\SalesReport;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Customer\Entities\Customer;
use App\Http\Controllers\BaseController;

class DailySalesStatusController extends BaseController
{
    public function index()
    {
        if(permission('daily-sales-status-access')){
            $this->setPageData('Daily Sales Status','Daily Sales Status','fas fa-file',[['name' => 'Daily Sales Status']]);
            $customers = Customer::allCustomers();
            $sales_type = [
                '0' => 'All',
                '1' => 'Credit Sale',
                '2' => 'Cash Sale',
                '3' => 'Sales Return',
                '4' => 'Business Promotion',
            ];
            return view('report::sales-report.daily-sales-status-one',compact('customers','sales_type'));
        }else{
            return $this->access_blocked();
        }

    }

    public function report_data(Request $request)
    {
        $start_date = dateFormat($request->start_date);
        $end_date   = dateFormat($request->end_date);
        $customer_id = $request->customer_id;
        $type        = $request->type;

        $table = '';
        $table .= '<table style="margin-bottom:10px !important;">
                    <tr>
                        <td class="text-center">
                            <h3 class="name m-0" style="text-transform: uppercase;"><b>'.(config('settings.title') ? config('settings.title') : env('APP_NAME')).'</b></h3>
                            <p style="font-weight: normal;font-weight:bold;    margin: 10px auto 5px auto;
                            font-weight: bold;background: black;border-radius: 10px;width: 250px;color: white;text-align: center;padding:5px 0;}">Daily Sales Status</p>
                            <p style="font-weight: normal;margin:0;font-weight:bold;">Date: '.$request->start_date.' to '.$request->end_date.'</p>
                        </td>
                    </tr>
                </table>';
        
        

        if($type == 0 || $type == 1)
        {
            $total_credit_amount = $total_credit_qty = 0;
            $table .= "<table  id='product_table'>
                        <tr  style='background: black;color: white;'>
                            <td colspan='10' style='text-align:center;'><b>Credit Sales</b></td>
                        </tr>
                        <tr>
                        <td><b>SL.</b></td>
                        <td><b>Name</b></td>
                        <td><b>Address</b></td>
                        <td><b>Date</b></td>
                        <td><b>DC. No.</b></td>
                        <td><b>Memo</b></td>
                        <td><b>Qty</b></td>
                        <td><b>Amount</b></td>
                        <td><b>Carrier No.</b></td>
                        <td><b>Rent</b></td>
                        </tr>";
            $invoices =  DB::table('sale_invoices as si')
            ->selectRaw('si.*,so.memo_no,so.shipping_address,c.trade_name')
            ->join('sale_orders as so','si.order_id','=','so.id')
            ->join('customers as c','so.customer_id','=','c.id')
            ->when($customer_id,function($q) use ($customer_id){
                $q->where('so.customer_id',$customer_id);
            })
            ->whereBetween('si.invoice_date',[$start_date,$end_date])
            ->orderBy('si.invoice_date','asc')
            ->get();
            if(!$invoices->isEmpty())
            {
                foreach ($invoices as $key =>  $invoice) {
                    $total_credit_amount += $invoice->grand_total;
                    $total_credit_qty += $invoice->total_qty;
                    $table .= "<tr>
                    <td><a href='".url('sale/invoice/view',$invoice->id)."' style='text-decoration:none;color:black;cursor:pointer;'>".($key+1)."</a></td>
                    <td><a href='".url('sale/invoice/view',$invoice->id)."' style='text-decoration:none;color:black;cursor:pointer;'>$invoice->trade_name</a></td>
                    <td><a href='".url('sale/invoice/view',$invoice->id)."' style='text-decoration:none;color:black;cursor:pointer;'>$invoice->shipping_address</a></td>
                    <td style='text-align:center;'><a href='".url('sale/invoice/view',$invoice->id)."' style='text-decoration:none;color:black;cursor:pointer;'>".date('d-m-Y',strtotime($invoice->invoice_date))."</a></td>
                    <td><a href='".url('sale/invoice/view',$invoice->id)."' style='text-decoration:none;color:black;cursor:pointer;'>$invoice->challan_no</a></td>
                    <td><a href='".url('sale/invoice/view',$invoice->id)."' style='text-decoration:none;color:black;cursor:pointer;'>$invoice->memo_no</a></td>
                    <td style='text-align:right;'><a href='".url('sale/invoice/view',$invoice->id)."' style='text-decoration:none;color:black;cursor:pointer;'>".number_format($invoice->total_qty,2,'.',',')."</a></td>
                    <td style='text-align:right;'><a href='".url('sale/invoice/view',$invoice->id)."' style='text-decoration:none;color:black;cursor:pointer;'>".number_format($invoice->grand_total,2,'.',',')."</a></td>
                    <td><a href='".url('sale/invoice/view',$invoice->id)."' style='text-decoration:none;color:black;cursor:pointer;'>$invoice->transport_no</a></td>
                    <td><a href='".url('sale/invoice/view',$invoice->id)."' style='text-decoration:none;color:black;cursor:pointer;'>".($invoice->truck_fare ? $invoice->truck_fare.'/C.P.' : 'Own')."</a></td>
                </tr>";
                }
                $table .= "<tr>
                    <td colspan='6'><b>Total</b></td>
                    <td style='text-align:right;border-top:3px solid black !important;'><b>".number_format($total_credit_qty,2,'.',',')."</b></td>
                    <td style='text-align:right;border-top:3px solid black !important;'><b>".number_format($total_credit_amount,2,'.',',')."</b></td>
                    <td colspan='2'></td>
                    </tr>";
            }else{
                $table .= "<tr>
                    <td colspan='10' style='border:0 !important;color:red;text-align:center;'>No Data Found</td>
                    </tr>";
            }
            $table .= "</table>";

        }


        return $table;

    }
}
