<?php

namespace Modules\Report\Http\Controllers\PurchaseReport;

use DateTime;
use DatePeriod;
use DateInterval;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Material\Entities\Material;
use App\Http\Controllers\BaseController;
use Modules\Report\Http\Controllers\PurchaseReport\Trait\PurchaseReport;

class ItemDetailsReportController extends BaseController
{
    use PurchaseReport;
    public function index()
    {
        if(permission('purchase-by-item-details-access')){
            $this->setPageData('Purchase By Item Details','Purchase By Item Details','fas fa-file',[['name' => 'Purchase By Item Details']]);
            $categories = Category::with('materials')->whereHas('materials')->whereIn('id',[1,2])->get();
            return view('report::purchase-report.item-summary-details',compact('categories'));
        }else{
            return $this->access_blocked();
        }

    }

    public function report_data(Request $request)
    {
        
        $start_date  = dateFormat($request->start_date);
        $end_date    = dateFormat($request->end_date);
        $material_id = $request->material_id;

        $paddy_received_count = $this->purchase_received_count($start_date,$end_date,1,$material_id);
        $paddy_cash_count     = $this->cash_purchase_count($start_date,$end_date,1,$material_id);

        $pkg_materials_received_count = $this->purchase_received_count($start_date,$end_date,2,$material_id);
        $pkg_materials_cash_count     = $this->cash_purchase_count($start_date,$end_date,2,$material_id);

        $table = '';
        $table .= '<table style="margin-bottom:10px !important;">
                    <tr>
                        <td class="text-center">
                            <h3 class="name m-0" style="text-transform: uppercase;"><b>'.(config('settings.title') ? config('settings.title') : env('APP_NAME')).'</b></h2>
                            <p style="font-weight: normal;font-weight:bold;    margin: 10px auto 5px auto;
                            font-weight: bold;background: black;border-radius: 10px;width: 250px;color: white;text-align: center;padding:5px 0;}">Purchase By Item Details</p>
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
                            <td style='text-align:left;'><b>Transport No.</b></td>
                            <td style='text-align:left;'><b>Trade Name</b></td>
                            <td style='text-align:right;'><b>Qty</b></td>
                            <td style='text-align:right;'><b>Cost Price</b></td>
                            <td style='text-align:right;'><b>Amount</b></td>
                            <td style='text-align:right;'><b>Balance</b></td>
                        </tr>
                        <tr  style='background: black;color: white;'>
                            <td colspan='10' style='text-align:left;'><b>Inventory</b></td>
                        </tr>";
        $table .= $table_head;
        $grand_total_qty = $grand_total_amount = $grand_total_balance = 0;
        $start_date = $start_date . ' 00:00:01';
        $end_date = $end_date . ' 23:59:59';
        $date_period = new DatePeriod(new DateTime($start_date), new DateInterval('P1D'), new DateTime($end_date));

        //Paddy Materials
        $table_body_paddy  = '';
        if($paddy_received_count > 0 || $paddy_cash_count > 0)
        {
            
            $paddies = Material::where('category_id',1)->when($material_id, function($q) use ($material_id){
                $q->where('id',$material_id);
            })->get();
            if(!$paddies->isEmpty())
            {  
                
                $paddy_total_qty = $paddy_total_amount = $paddy_total_balance =  0;
                foreach ($paddies as $paddy) {
                    $paddy_counter = $balance = $paddy_subtotal_qty = $paddy_subtotal_amount = $paddy_subtotal_balance = 0;
                    $paddy_data = '';
                    foreach ($date_period as $key => $date) {
                        
                        $purchase_received = DB::table('received_materials rm')
                        ->leftJoin('materials as m','rm.material_id','=','m.id')
                        ->leftJoin('order_received as ore','rm.order_id','=','ore.id')
                        ->leftJoin('purchase_orders as por','ore.order_id','=','por.id')
                        ->leftJoin('vendors as v','por.vendor_id','=','v.id')
                        ->where([['m.category_id',1],['rm.material_id',$paddy->id]]) //3=By Product
                        ->whereBetween('ore.received_date',[$date->format('Y-m-d'),$date->format('Y-m-d')])
                        ->selectRaw('ore.challan_no,ore.transport_no,SUM(rm.received_qty) as qty,SUM(rm.total) as total,v.trade_name,por.memo_no')
                        ->groupBy('rm.material_id','ore.challan_no','v.trade_name','por.memo_no','ore.transport_no')
                        ->get();
                        if(!$purchase_received->isEmpty())
                        {
                            foreach ($purchase_received as $purchase) {
                                $paddy_counter++;
                                $price                  = $purchase->total / $purchase->qty;
                                $balance               += $purchase->total;
                                $paddy_subtotal_qty    += $purchase->qty;
                                $paddy_subtotal_amount += $purchase->total;
                                $paddy_total_qty       += $purchase->qty;
                                $paddy_total_amount    += $purchase->total;
                                $paddy_data .= "<tr>
                                        <td style='padding-left:20px;border:0 !important;'>Item Receipt</td>
                                        <td style='border:0 !important;text-align:center;'>".$date->format('d-m-Y')."</td>
                                        <td style='border:0 !important;'>$purchase->challan_no</td>
                                        <td style='border:0 !important;'>$purchase->memo_no</td>
                                        <td style='border:0 !important;'>$purchase->transport_no</td>
                                        <td style='border:0 !important;'>$purchase->trade_name</td>
                                        <td style='border:0 !important;text-align:right;'>".number_format($purchase->qty,2,'.',',')."</td>
                                        <td style='border:0 !important;text-align:right;'>".number_format($price,2,'.',',')."</td>
                                        <td style='border:0 !important;text-align:right;'>".number_format($purchase->total,2,'.',',')."</td>
                                        <td style='border:0 !important;text-align:right;'>".number_format($balance,2,'.',',')."</td>
                                        </tr>";
                            }
                        }
                        $cash_purchase = DB::table('cash_purchase_materials cpm')
                        ->join('materials as m','cpm.material_id','=','m.id')
                        ->join('cash_purchases as cp','cpm.cash_id','=','cp.id')
                        ->where([['m.category_id',1],['cpm.material_id',$paddy->id]]) //3=By Product
                        ->whereBetween('cp.receive_date',[$date->format('Y-m-d'),$date->format('Y-m-d')])
                        ->selectRaw('cp.challan_no,SUM(cpm.qty) as qty,SUM(cpm.total) as total,cp.vendor_name,cp.memo_no')
                        ->groupBy('cpm.material_id','cp.challan_no','cp.memo_no','cp.vendor_name')
                        ->get();

                        if(!$cash_purchase->isEmpty())
                        {
                            foreach ($cash_purchase as $purchase) {
                                $paddy_counter++;
                                $price                         = $purchase->total / $purchase->qty;
                                $balance                      += $purchase->total;
                                $paddy_subtotal_qty    += $purchase->qty;
                                $paddy_subtotal_amount += $purchase->total;
                                $paddy_total_qty       += $purchase->qty;
                                $paddy_total_amount    += $purchase->total;
                                $paddy_data .= "<tr>
                                        <td style='padding-left:20px;border:0 !important;'>Invoice</td>
                                        <td style='border:0 !important;text-align:center;'>".$date->format('d-m-Y')."</td>
                                        <td style='border:0 !important;'>$purchase->challan_no</td>
                                        <td style='border:0 !important;'>$purchase->memo_no</td>
                                        <td style='border:0 !important;'>-</td>
                                        <td style='border:0 !important;'>$purchase->vendor_name</td>
                                        <td style='border:0 !important;text-align:right;'>".number_format($purchase->qty,2,'.',',')."</td>
                                        <td style='border:0 !important;text-align:right;'>".number_format($price,2,'.',',')."</td>
                                        <td style='border:0 !important;text-align:right;'>".number_format($purchase->total,2,'.',',')."</td>
                                        <td style='border:0 !important;text-align:right;'>".number_format($balance,2,'.',',')."</td>
                                        </tr>";
                            }
                        }
                        
                    }
                    if($paddy_counter > 0){
                        $table_body_paddy .= "<tr><td colspan='10' style='padding-left:20px;border:0 !important;'><b>$paddy->material_name</b></td></tr>"; //paddy Name
                        $table_body_paddy .= $paddy_data;
                        $table_body_paddy .= "<tr>
                                                        <td style='padding-left:20px;border:0 !important;'><b>Total $paddy->material_name</b></td>
                                                        <td colspan='5' style='border:0 !important;'></td>
                                                        <td style='text-align:right;border:0 !important;border-top:2px solid black !important;'><b>".number_format($paddy_subtotal_qty,2,'.',',')."</b></td>
                                                        <td style='text-align:right;border:0 !important;'></td>
                                                        <td style='text-align:right;border:0 !important;border-top:2px solid  black !important;'><b>".number_format($paddy_subtotal_amount,2,'.',',')."</b></td>
                                                        <td style='text-align:right;border:0 !important;border-top:2px solid  black !important;'><b>".number_format($balance,2,'.',',')."</b></td>
                                                    </tr>";
                        $paddy_total_balance += $balance;
                    }
                    
                }
                if($paddy_total_qty > 0)
                {
                    $table .= "<tr><td colspan='10' style='border:0 !important;'><b>Paddy (RM)</b></td></tr>"; //Category Name
                    $table_body_paddy .= "<tr>
                                                        <td style='border:0 !important;'><b>Total Paddy (RM)</b></td>
                                                        <td colspan='5' style='border:0 !important;'></td>
                                                        <td style='text-align:right;border:0 !important;border-top:double black !important;'><b>".number_format($paddy_total_qty,2,'.',',')."</b></td>
                                                        <td style='text-align:right;border:0 !important;'></td>
                                                        <td style='text-align:right;border:0 !important;border-top:double black !important;'><b>".number_format($paddy_total_amount,2,'.',',')."</b></td>
                                                        <td style='text-align:right;border:0 !important;border-top:double black !important;'><b>".number_format($paddy_total_balance,2,'.',',')."</b></td>
                                                    </tr>";
                    $grand_total_qty += $paddy_total_qty;
                    $grand_total_amount += $paddy_total_amount;
                    $grand_total_balance += $paddy_total_balance;
                }
                
            }
            $table .= $table_body_paddy;
        }

        //Package Materials
        $table_body_pkg_material  = '';
        if($pkg_materials_received_count > 0 || $pkg_materials_cash_count > 0)
        {
            
            $pkg_materials = Material::where('category_id',2)->when($material_id, function($q) use ($material_id){
                $q->where('id',$material_id);
            })->get();
            if(!$pkg_materials->isEmpty())
            {  
                
                $pkg_material_total_qty = $pkg_material_total_amount = $pkg_material_total_balance =  0;
                foreach ($pkg_materials as $pkg_material) {
                    $pkg_material_counter = $balance = $pkg_material_subtotal_qty = $pkg_material_subtotal_amount = $pkg_material_subtotal_balance = 0;
                    $pkg_material_data = '';
                    foreach ($date_period as $key => $date) {
                        
                        $purchase_received = DB::table('received_materials rm')
                        ->leftJoin('materials as m','rm.material_id','=','m.id')
                        ->leftJoin('order_received as ore','rm.order_id','=','ore.id')
                        ->leftJoin('purchase_orders as por','ore.order_id','=','por.id')
                        ->leftJoin('vendors as v','por.vendor_id','=','v.id')
                        ->where([['m.category_id',2],['rm.material_id',$pkg_material->id]]) //3=By Product
                        ->whereBetween('ore.received_date',[$date->format('Y-m-d'),$date->format('Y-m-d')])
                        ->selectRaw('ore.challan_no,ore.transport_no,SUM(rm.received_qty) as qty,SUM(rm.total) as total,v.trade_name,por.memo_no')
                        ->groupBy('rm.material_id','ore.challan_no','v.trade_name','por.memo_no','ore.transport_no')
                        ->get();
                        if(!$purchase_received->isEmpty())
                        {
                            foreach ($purchase_received as $purchase) {
                                $pkg_material_counter++;
                                $price                         = $purchase->total / $purchase->qty;
                                $balance                      += $purchase->total;
                                $pkg_material_subtotal_qty    += $purchase->qty;
                                $pkg_material_subtotal_amount += $purchase->total;
                                $pkg_material_total_qty       += $purchase->qty;
                                $pkg_material_total_amount    += $purchase->total;
                                $pkg_material_data .= "<tr>
                                        <td style='padding-left:20px;border:0 !important;'>Invoice</td>
                                        <td style='border:0 !important;text-align:center;'>".$date->format('d-m-Y')."</td>
                                        <td style='border:0 !important;'>$purchase->challan_no</td>
                                        <td style='border:0 !important;'>$purchase->memo_no</td>
                                        <td style='border:0 !important;'>$purchase->transport_no</td>
                                        <td style='border:0 !important;'>$purchase->trade_name</td>
                                        <td style='border:0 !important;text-align:right;'>".number_format($purchase->qty,2,'.',',')."</td>
                                        <td style='border:0 !important;text-align:right;'>".number_format($price,2,'.',',')."</td>
                                        <td style='border:0 !important;text-align:right;'>".number_format($purchase->total,2,'.',',')."</td>
                                        <td style='border:0 !important;text-align:right;'>".number_format($balance,2,'.',',')."</td>
                                        </tr>";
                            }
                        }
                        $cash_purchase = DB::table('cash_purchase_materials cpm')
                        ->join('materials as m','cpm.material_id','=','m.id')
                        ->join('cash_purchases as cp','cpm.cash_id','=','cp.id')
                        ->where([['m.category_id',2],['cpm.material_id',$pkg_material->id]]) //3=By Product
                        ->whereBetween('cp.receive_date',[$date->format('Y-m-d'),$date->format('Y-m-d')])
                        ->selectRaw('cp.challan_no,SUM(cpm.qty) as qty,SUM(cpm.total) as total,cp.vendor_name,cp.memo_no')
                        ->groupBy('cpm.material_id','cp.challan_no','cp.memo_no','cp.vendor_name')
                        ->get();

                        if(!$cash_purchase->isEmpty())
                        {
                            foreach ($cash_purchase as $purchase) {
                                $pkg_material_counter++;
                                $price                         = $purchase->total / $purchase->qty;
                                $balance                      += $purchase->total;
                                $pkg_material_subtotal_qty    += $purchase->qty;
                                $pkg_material_subtotal_amount += $purchase->total;
                                $pkg_material_total_qty       += $purchase->qty;
                                $pkg_material_total_amount    += $purchase->total;
                                $pkg_material_data .= "<tr>
                                        <td style='padding-left:20px;border:0 !important;'>Sales Receipt</td>
                                        <td style='border:0 !important;text-align:center;'>".$date->format('d-m-Y')."</td>
                                        <td style='border:0 !important;'>$purchase->challan_no</td>
                                        <td style='border:0 !important;'>$purchase->memo_no</td>
                                        <td style='border:0 !important;'>-</td>
                                        <td style='border:0 !important;'>$purchase->vendor_name</td>
                                        <td style='border:0 !important;text-align:right;'>".number_format($purchase->qty,2,'.',',')."</td>
                                        <td style='border:0 !important;text-align:right;'>".number_format($price,2,'.',',')."</td>
                                        <td style='border:0 !important;text-align:right;'>".number_format($purchase->total,2,'.',',')."</td>
                                        <td style='border:0 !important;text-align:right;'>".number_format($balance,2,'.',',')."</td>
                                        </tr>";
                            }
                        }
                        
                    }
                    if($pkg_material_counter > 0){
                        $table_body_pkg_material .= "<tr><td colspan='10' style='padding-left:20px;border:0 !important;'><b>$pkg_material->material_name</b></td></tr>"; //pkg_material Name
                        $table_body_pkg_material .= $pkg_material_data;
                        $table_body_pkg_material .= "<tr>
                                                        <td style='padding-left:20px;border:0 !important;'><b>Total $pkg_material->material_name</b></td>
                                                        <td colspan='5' style='border:0 !important;'></td>
                                                        <td style='text-align:right;border:0 !important;border-top:2px solid black !important;'><b>".number_format($pkg_material_subtotal_qty,2,'.',',')."</b></td>
                                                        <td style='text-align:right;border:0 !important;'></td>
                                                        <td style='text-align:right;border:0 !important;border-top:2px solid  black !important;'><b>".number_format($pkg_material_subtotal_amount,2,'.',',')."</b></td>
                                                        <td style='text-align:right;border:0 !important;border-top:2px solid  black !important;'><b>".number_format($balance,2,'.',',')."</b></td>
                                                    </tr>";
                        $pkg_material_total_balance += $balance;
                    }
                    
                }
                if($pkg_material_total_qty > 0)
                {
                    $table .= "<tr><td colspan='10' style='border:0 !important;'><b>Package Materials</b></td></tr>"; //Category Name
                    $table_body_pkg_material .= "<tr>
                                                        <td style='border:0 !important;'><b>Total Package Materials</b></td>
                                                        <td colspan='5' style='border:0 !important;'></td>
                                                        <td style='text-align:right;border:0 !important;border-top:double black !important;'><b>".number_format($pkg_material_total_qty,2,'.',',')."</b></td>
                                                        <td style='text-align:right;border:0 !important;'></td>
                                                        <td style='text-align:right;border:0 !important;border-top:double black !important;'><b>".number_format($pkg_material_total_amount,2,'.',',')."</b></td>
                                                        <td style='text-align:right;border:0 !important;border-top:double black !important;'><b>".number_format($pkg_material_total_balance,2,'.',',')."</b></td>
                                                    </tr>";
                    $grand_total_qty += $pkg_material_total_qty;
                    $grand_total_amount += $pkg_material_total_amount;
                    $grand_total_balance += $pkg_material_total_balance;
                }
                
            }
            $table .= $table_body_pkg_material;
        }

        if($paddy_received_count > 0 || $paddy_cash_count > 0 || 
        $pkg_materials_received_count > 0 || $pkg_materials_cash_count > 0){
            $table .= "<tr><td colspan='10' style='border:0 !important;'></td></tr>
            <tr>
                <td style='border:0 !important;'><b>Total</b></td>
                <td colspan='5' style='border:0 !important;'></td>
                <td style='text-align:right;border:0 !important;border-top:double black !important;'><b>".number_format($grand_total_qty,2,'.',',')."</b></td>
                <td style='text-align:right;border:0 !important;'></td>
                <td style='text-align:right;border:0 !important;border-top:double black !important;'><b>".number_format($grand_total_amount,2,'.',',')."</b></td>
                <td style='text-align:right;border:0 !important;border-top:double black !important;'><b>".number_format($grand_total_balance,2,'.',',')."</b></td>
            </tr>";
        }else{
            $table .= "<tr><td colspan='10' style='border:0 !important;color:red;text-align:center;'>No Data Found</td></tr>";
        }

        $table .= "</table>";
        return $table;
    }


}
