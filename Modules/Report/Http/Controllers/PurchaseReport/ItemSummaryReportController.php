<?php

namespace Modules\Report\Http\Controllers\PurchaseReport;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;

class ItemSummaryReportController extends BaseController
{
    public function index()
    {
        if(permission('purchase-by-item-summary-access')){
            $this->setPageData('Purchase By Item Summary','Purchase By Item Summary','fas fa-file',[['name' => 'Purchase By Item Summary']]);
            return view('report::purchase-report.item-summary-report');
        }else{
            return $this->access_blocked();
        }
    }

    public function report_data(Request $request)
    {
        $start_date = dateFormat($request->start_date);
        $end_date   = dateFormat($request->end_date);

        $table = '';
        $table .= '<table style="margin-bottom:10px !important;">
                    <tr>
                        <td class="text-center">
                            <h3 class="name m-0" style="text-transform: uppercase;"><b>'.(config('settings.title') ? config('settings.title') : env('APP_NAME')).'</b></h3>
                            <p style="font-weight: normal;font-weight:bold;    margin: 10px auto 5px auto;
                            font-weight: bold;background: black;border-radius: 10px;width: 250px;color: white;text-align: center;padding:5px 0;}">Purchase By Item Summary</p>
                            <p style="font-weight: normal;margin:0;font-weight:bold;">Date: '.$request->start_date.' to '.$request->end_date.'</p>
                            
                        </td>
                    </tr>
                </table>';
        $table .= "<table  id='product_table'>";
        $table .= "<tr  style='background: black;color: white;'>
                            <td><b>Inventory</b></td>
                            <td style='text-align:right;'><b>Quantity</b></td>
                            <td style='text-align:right;'><b>Avg. Rate</b></td>
                            <td style='text-align:right;'><b>Amount</b></td>
                        </tr>";
        $grand_total_qty = $grand_total_amount = 0;

        //Paddy
        $materials = DB::table('materials')
                        ->where('materials.category_id',1) //1=Paddy
                        ->selectRaw("materials.id,materials.material_name,
                        (SELECT SUM(received_materials.received_qty) FROM received_materials LEFT JOIN order_received ON received_materials.order_id = order_received.id 
                        WHERE received_materials.material_id = materials.id AND order_received.received_date BETWEEN '$start_date' and '$end_date') as credit_qty,

                        (SELECT SUM(received_materials.total) FROM received_materials LEFT JOIN order_received ON received_materials.order_id = order_received.id 
                        WHERE received_materials.material_id = materials.id AND order_received.received_date BETWEEN '$start_date' and '$end_date') as credit_total,
                        
                        (SELECT SUM(cash_purchase_materials.qty) FROM cash_purchase_materials LEFT JOIN cash_purchases ON cash_purchase_materials.cash_id = cash_purchases.id 
                        WHERE cash_purchase_materials.material_id = materials.id AND cash_purchases.receive_date BETWEEN '$start_date' and '$end_date') as cash_qty,

                        (SELECT SUM(cash_purchase_materials.total) FROM cash_purchase_materials LEFT JOIN cash_purchases ON cash_purchase_materials.cash_id = cash_purchases.id 
                        WHERE cash_purchase_materials.material_id = materials.id AND cash_purchases.receive_date BETWEEN '$start_date' and '$end_date') as cash_total")
                        ->get();
        if(!$materials->isEmpty())
        {
             
            $total_material_qty = $total_material_amount = 0;
            $materials_body = '';
            foreach ($materials as $material) {
                $credit_qty = $material->credit_qty ?? 0;
                $cash_qty   = $material->cash_qty ?? 0;
                if($credit_qty > 0 || $cash_qty > 0)
                {
                    $credit_total = $material->credit_total ?? 0;
                    $cash_total   = $material->cash_total ?? 0;
                    $avg_price    = ($credit_total + $cash_total) / ($credit_qty + $cash_qty);
                    $total_material_qty    += $credit_qty + $cash_qty;
                    $total_material_amount += $credit_total + $cash_total;
                    $materials_body .= "<tr>
                                    <td style='padding-left:20px;border:0 !important;'>$material->material_name</td>
                                    <td style='text-align:right;border:0 !important;'>".number_format(($credit_qty + $cash_qty),2,'.',',')."</td>
                                    <td style='text-align:right;border:0 !important;'>".number_format($avg_price,2,'.',',')."</td>
                                    <td style='text-align:right;border:0 !important;'>".number_format(($credit_total + $cash_total),2,'.',',')."</td>
                                </tr>";
                }
            }
            if($total_material_qty > 0)
            {
                $table .= "<tr><td colspan='4' style='border:0 !important;'><b>Paddy (RM)</b></td></tr>";
                $table .= $materials_body;
                $table .= "<tr>
                    <td style='border:0 !important;'><b>Total Paddy (RM)</b></td>
                    <td style='text-align:right;border:0 !important;border-top:double black !important;'><b>".number_format($total_material_qty,2,'.',',')."</b></td>
                    <td style='text-align:right;border:0 !important;'></td>
                    <td style='text-align:right;border:0 !important;border-top:double black !important;'><b>".number_format($total_material_amount,2,'.',',')."</b></td>
                </tr>";
                $grand_total_qty += $total_material_qty;
                $grand_total_amount += $total_material_amount;
            }
        }

                //Paddy
        $packaging_materials = DB::table('materials')
                ->where('materials.category_id',2) //2=Packaging Materials
                ->selectRaw("materials.id,materials.material_name,
                (SELECT SUM(received_materials.received_qty) FROM received_materials LEFT JOIN order_received ON received_materials.order_id = order_received.id 
                WHERE received_materials.material_id = materials.id AND order_received.received_date BETWEEN '$start_date' and '$end_date') as credit_qty,

                (SELECT SUM(received_materials.total) FROM received_materials LEFT JOIN order_received ON received_materials.order_id = order_received.id 
                WHERE received_materials.material_id = materials.id AND order_received.received_date BETWEEN '$start_date' and '$end_date') as credit_total,
                
                (SELECT SUM(cash_purchase_materials.qty) FROM cash_purchase_materials LEFT JOIN cash_purchases ON cash_purchase_materials.cash_id = cash_purchases.id 
                WHERE cash_purchase_materials.material_id = materials.id AND cash_purchases.receive_date BETWEEN '$start_date' and '$end_date') as cash_qty,

                (SELECT SUM(cash_purchase_materials.total) FROM cash_purchase_materials LEFT JOIN cash_purchases ON cash_purchase_materials.cash_id = cash_purchases.id 
                WHERE cash_purchase_materials.material_id = materials.id AND cash_purchases.receive_date BETWEEN '$start_date' and '$end_date') as cash_total")
                ->get();
        if(!$packaging_materials->isEmpty())
        {
            
            $total_material_qty = $total_material_amount = 0;
            $packaging_materials_body = '';
            foreach ($packaging_materials as $material) {
                $credit_qty = $material->credit_qty ?? 0;
                $cash_qty   = $material->cash_qty ?? 0;
                if($credit_qty > 0 || $cash_qty > 0)
                {
                    $credit_total = $material->credit_total ?? 0;
                    $cash_total   = $material->cash_total ?? 0;
                    $avg_price    = ($credit_total + $cash_total) / ($credit_qty + $cash_qty);
                    $total_material_qty    += $credit_qty + $cash_qty;
                    $total_material_amount += $credit_total + $cash_total;
                    $packaging_materials_body .= "<tr>
                                    <td style='padding-left:20px;border:0 !important;'>$material->material_name</td>
                                    <td style='text-align:right;border:0 !important;'>".number_format(($credit_qty + $cash_qty),2,'.',',')."</td>
                                    <td style='text-align:right;border:0 !important;'>".number_format($avg_price,2,'.',',')."</td>
                                    <td style='text-align:right;border:0 !important;'>".number_format(($credit_total + $cash_total),2,'.',',')."</td>
                                </tr>";
                }
            }
            if($total_material_qty > 0)
            {
                $table .= "<tr><td colspan='4' style='border:0 !important;'><b>Packaging Materials (RM)</b></td></tr>"; 
                $table .= $packaging_materials_body;
                $table .= "<tr>
                    <td style='border:0 !important;'><b>Total Packaging Materials</b></td>
                    <td style='text-align:right;border:0 !important;border-top:double black !important;'><b>".number_format($total_material_qty,2,'.',',')."</b></td>
                    <td style='text-align:right;border:0 !important;'></td>
                    <td style='text-align:right;border:0 !important;border-top:double black !important;'><b>".number_format($total_material_amount,2,'.',',')."</b></td>
                </tr>";
                $grand_total_qty += $total_material_qty;
                $grand_total_amount += $total_material_amount;
            }
        }

        if($grand_total_amount > 0 ){
            $table .= "<tr><td colspan='4' style='border:0 !important;'></td></tr>
            <tr>
                <td style='border:0 !important;'><b>Total Inventory</b></td>
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
