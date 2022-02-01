<?php

namespace Modules\Report\Http\Controllers\PurchaseReport;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Vendor\Entities\Vendor;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\BaseController;

class VendorSummaryReportController extends BaseController
{
    public function index()
    {
        if(permission('purchase-by-vendor-summary-access')){
            $this->setPageData('Purchase By Vendor Summary','Purchase By Vendor Summary','fas fa-file',[['name' => 'Purchase By Vendor Summary']]);
            $vendors = Vendor::allVendors();
            return view('report::purchase-report.vendor-summary.report',compact('vendors'));
        }else{
            return $this->access_blocked();
        }
    }

    public function report_data(Request $request)
    {
        $start_date = dateFormat($request->start_date);
        $end_date   = dateFormat($request->end_date);
        $vendor_id  = $request->vendor_id;

        $table = '';
        $table .= '<table style="margin-bottom:10px !important;">
                    <tr>
                        <td class="text-center">
                            <h3 class="name m-0" style="text-transform: uppercase;"><b>'.(config('settings.title') ? config('settings.title') : env('APP_NAME')).'</b></h3>
                            <p style="font-weight: normal;font-weight:bold;    margin: 10px auto 5px auto;
                            font-weight: bold;background: black;border-radius: 10px;width: 250px;color: white;text-align: center;padding:5px 0;}">Purchase By Vendor Summary</p>
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

        $vendors = DB::table('vendors')
                        ->when($vendor_id,function($q) use ($vendor_id){
                            $q->where('vendors.id',$vendor_id);
                        })
                        ->selectRaw("vendors.id,vendors.trade_name,
                        (SELECT SUM(order_received.grand_total) FROM order_received LEFT JOIN purchase_orders ON order_received.order_id = purchase_orders.id 
                        WHERE purchase_orders.vendor_id = vendors.id AND order_received.received_date BETWEEN '$start_date' and '$end_date') as total_purchase")
                        ->orderBy('vendors.id','asc')
                        ->get();
        
        if (!$vendors->isEmpty()) {
            foreach ($vendors as $vendor) {
                if($vendor->total_purchase > 0)
                {
                    $total_amount += $vendor->total_purchase;
                    $table .= "<tr>
                                <td><a href='".url('purchase-by-vendor-summary-details',Crypt::encrypt($vendor->id))."' style='text-decoration:none;color:black;cursor:pointer;'>$vendor->trade_name</a></td>
                                <td style='text-align:right;'>".number_format($vendor->total_purchase,2,'.',',')."</td>
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

    public function vendor_summary_details($vendor_id)
    {
        if(permission('purchase-by-vendor-summary-access')){
            $vendor_id = Crypt::decrypt($vendor_id);
            $vendor = Vendor::find($vendor_id);
            if($vendor)
            {
                $this->setPageData('Purchase By Vendor Summary Details','Purchase By Vendor Summary Details','fas fa-file',[['name' => 'Purchase By Vendor Summary Details']]);
                $vendors = Vendor::allVendors();
                return view('report::purchase-report.vendor-summary.details',compact('vendor','vendors'));
            }else{
                return redirect()->back();
            }
            
        }else{
            return $this->access_blocked();
        }
    }

    public function vendor_summary_details_data(Request $request)
    {
        $start_date = dateFormat($request->start_date);
        $end_date   = dateFormat($request->end_date);
        $vendor = Vendor::find($request->vendor_id);
        $table = '';
        $table .= '<table style="margin-bottom:10px !important;">
                    <tr>
                        <td class="text-center">
                            <h3 class="name m-0" style="text-transform: uppercase;"><b>'.(config('settings.title') ? config('settings.title') : env('APP_NAME')).'</b></h3>
                            <p style="font-weight: normal;font-weight:bold;    margin: 10px auto 5px auto;
                            font-weight: bold;background: black;border-radius: 10px;width: 300px;color: white;text-align: center;padding:5px 0;}">Purchase By Vendor Summary Details</p>
                            <p style="font-weight: normal;margin:0;font-weight:bold;">Date: '.$request->start_date.' to '.$request->end_date.'</p>
                        </td>
                    </tr>
                </table>';
        if($vendor)
        {
            $table .= '<table style="margin-bottom:10px !important;">
                            <tr>
                                <td class="text-center"><b>'.$vendor->trade_name.', '.$vendor->name.($vendor->address ? ', '.$vendor->address : '').'</b></td>
                            </tr>
                        </table>';
        }
        
        $table .= "<table  id='product_table'>";
        $table_head = "<tr  style='background: black;color: white;'>
                            <td><b>Type</b></td>
                            <td style='text-align:center;'><b>Date</b></td>
                            <td><b>NUM</b></td>
                            <td><b>Particular</b></td>
                            <td><b>Transport No.</b></td>
                            <td style='text-align:right;'><b>Amount</b></td>
                            <td style='text-align:right;'><b>Balance</b></td>
                        </tr>";
        $table .= $table_head;
        $total_amount = $total_balance = 0;

        $invoices = DB::table('order_received as orre')
                ->selectRaw('orre.id,orre.challan_no,orre.transport_no,orre.grand_total,orre.received_date,po.memo_no')
                ->leftJoin('purchase_orders as po','orre.order_id','=','po.id')
                ->where('po.vendor_id',$request->vendor_id)
                ->whereBetween('orre.received_date',[$start_date,$end_date])
                ->orderBy('orre.received_date','asc')
                ->get();
        if(!$invoices->isEmpty())
        {
            foreach ($invoices as $invoice) {
                $total_amount += $invoice->grand_total;
                $total_balance += $invoice->grand_total;
                $table .= "<tr>
                <td><a href='".url('purchase/received/view',$invoice->id)."' style='text-decoration:none;color:black;cursor:pointer;'>Item Receipt</a></td>
                <td style='text-align:center;'><a href='".url('purchase/received/view',$invoice->id)."' style='text-decoration:none;color:black;cursor:pointer;'>".date('d-m-Y',strtotime($invoice->received_date))."</a></td>
                <td><a href='".url('purchase/received/view',$invoice->id)."' style='text-decoration:none;color:black;cursor:pointer;'>$invoice->challan_no</a></td>
                <td><a href='".url('purchase/received/view',$invoice->id)."' style='text-decoration:none;color:black;cursor:pointer;'>$invoice->memo_no</a></td>
                <td><a href='".url('purchase/received/view',$invoice->id)."' style='text-decoration:none;color:black;cursor:pointer;'>$invoice->transport_no</a></td>
                <td style='text-align:right;'><a href='".url('purchase/received/view',$invoice->id)."' style='text-decoration:none;color:black;cursor:pointer;'>".number_format($invoice->grand_total,2,'.',',')."</a></td>
                <td style='text-align:right;'><a href='".url('purchase/received/view',$invoice->id)."' style='text-decoration:none;color:black;cursor:pointer;'>".number_format($total_balance,2,'.',',')."</a></td>
            </tr>";
            }
            if($total_balance > 0)
            {
                $table .= "<tr>
                    <td colspan='5'><b>Total</b></td>
                    <td style='text-align:right;border-top:3px solid black !important;'><b>".number_format($total_amount,2,'.',',')."</b></td>
                    <td style='text-align:right;border-top:3px solid black !important;'><b>".number_format($total_balance,2,'.',',')."</b></td>
                </tr>";
            }
            
        }else{
            $table .= "<tr>
                <td colspan='7' style='border:0 !important;color:red;text-align:center;'>No Data Found</td>
                </tr>";
        }
        return $table;
    }
}
