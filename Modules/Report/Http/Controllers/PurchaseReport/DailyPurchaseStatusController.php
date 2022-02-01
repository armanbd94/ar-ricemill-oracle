<?php

namespace Modules\Report\Http\Controllers\PurchaseReport;

use Illuminate\Http\Request;
use Modules\Material\Entities\Material;
use App\Http\Controllers\BaseController;
use Modules\Purchase\Entities\OrderReceived;

class DailyPurchaseStatusController extends BaseController
{
    public function index()
    {
        if(permission('daily-purchase-status-access')){
            $this->setPageData('Daily Purchase Status','Daily Purchase Status','fas fa-file',[['name' => 'Daily Purchase Status']]);
            $materials = Material::where('category_id',1)->orderBy('id','asc')->get();
            return view('report::purchase-report.daily-status',compact('materials'));
        }else{
            return $this->access_blocked();
        }
    }

    public function report_data(Request $request)
    {
        $start_date = dateFormat($request->start_date);
        $end_date   = dateFormat($request->end_date);
        $material_id = $request->material_id;

        $table = '';
        $table .= '<table style="margin-bottom:10px !important;">
                    <tr>
                        <td class="text-center">
                            <h3 class="name m-0" style="text-transform: uppercase;"><b>'.(config('settings.title') ? config('settings.title') : env('APP_NAME')).'</b></h3>
                            <p style="font-weight: normal;font-weight:bold; margin: 10px auto 5px auto;
                            font-weight: bold;background: black;border-radius: 10px;width: 250px;color: white;text-align: center;padding:5px 0;}">Daily Purchase Status</p>
                            <p style="font-weight: normal;margin:0;font-weight:bold;">Date: '.$request->start_date.' to '.$request->end_date.'</p>
                        </td>
                    </tr>
                </table>';
        
        
        $table .= "<table  id='product_table'>
                    <tr>
                    <td><b>SL.</b></td>
                    <td><b>Name</b></td>
                    <td><b>Address</b></td>
                    <td class='text-center'><b>Date</b></td>
                    <td class='text-center'><b>DC. No.</b></td>
                    <td><b>Memo</b></td>
                    <td class='text-right'><b>Qty</b></td>
                    <td class='text-right'><b>Amount</b></td>
                    <td class='text-center'><b>Carrier No.</b></td>
                    <td class='text-center'><b>Carr. Rent</b></td>
                    </tr>";
        $invoices = OrderReceived::with('order','received_materials');
        if($material_id)
        {
            $invoices->whereHas('received_materials',function($q) use ($material_id){
                $q->where('material_id',$material_id);
            });
        }
        $invoices = $invoices->whereBetween('received_date',[$start_date,$end_date])
                    ->orderBy('received_date','asc')
                    ->get();
        
        $total_amount = $total_qty = $total_car_rent = 0;
        if(!$invoices->isEmpty())
        {
            foreach ($invoices as $key =>  $invoice) {
                $total_amount   += $invoice->grand_total;
                $total_qty      += $invoice->total_qty;
                $total_car_rent += $invoice->truck_fare ?? 0;
                $table .= "<tr>
                <td><a href='".url('purchase/received/view',$invoice->id)."' style='text-decoration:none;color:black;cursor:pointer;'>".($key+1)."</a></td>
                <td><a href='".url('purchase/received/view',$invoice->id)."' style='text-decoration:none;color:black;cursor:pointer;'>".$invoice->order->vendor->trade_name."</a></td>
                <td><a href='".url('purchase/received/view',$invoice->id)."' style='text-decoration:none;color:black;cursor:pointer;'>".$invoice->order->vendor->address."</a></td>
                <td style='text-align:center;'><a href='".url('purchase/received/view',$invoice->id)."' style='text-decoration:none;color:black;cursor:pointer;'>".date('d-m-Y',strtotime($invoice->received_date))."</a></td>
                <td><a href='".url('purchase/received/view',$invoice->id)."' style='text-decoration:none;color:black;cursor:pointer;'>$invoice->challan_no</a></td>
                <td><a href='".url('purchase/received/view',$invoice->id)."' style='text-decoration:none;color:black;cursor:pointer;'>".$invoice->order->memo_no."</a></td>
                <td style='text-align:right;'><a href='".url('purchase/received/view',$invoice->id)."' style='text-decoration:none;color:black;cursor:pointer;'>".number_format($invoice->total_qty,2,'.',',')."</a></td>
                <td style='text-align:right;'><a href='".url('purchase/received/view',$invoice->id)."' style='text-decoration:none;color:black;cursor:pointer;'>".number_format($invoice->grand_total,2,'.',',')."</a></td>
                <td><a href='".url('purchase/received/view',$invoice->id)."' style='text-decoration:none;color:black;cursor:pointer;'>$invoice->transport_no</a></td>
                <td style='text-align:right;'><a href='".url('purchase/received/view',$invoice->id)."' style='text-decoration:none;color:black;cursor:pointer;'>".($invoice->truck_fare ? number_format($invoice->truck_fare,2,'.',',') : 'Own')."</a></td>
            </tr>";
            }
            $table .= "<tr>
                <td colspan='6'><b>Total</b></td>
                <td style='text-align:right;border-top:3px solid black !important;'><b>".number_format($total_qty,2,'.',',')."</b></td>
                <td style='text-align:right;border-top:3px solid black !important;'><b>".number_format($total_amount,2,'.',',')."</b></td>
                <td></td>
                <td style='text-align:right;border-top:3px solid black !important;'><b>".number_format($total_car_rent,2,'.',',')."</b></td>
                </tr>";
        }else{
            $table .= "<tr>
                <td colspan='10' style='color:red;text-align:center;'>No Data Found</td>
                </tr>";
        }
        $table .= "</table>";
        return $table;
    }
}
