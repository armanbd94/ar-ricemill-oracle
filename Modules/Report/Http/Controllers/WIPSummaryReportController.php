<?php

namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;

class WIPSummaryReportController extends BaseController
{
    public function index()
    {
        if(permission('wip-summary-report-access')){
            $this->setPageData('WIP Summart Report','WIP Summart Report','fas fa-file',[['name' => 'WIP Summart Report']]);
            $sites = DB::table('sites')->get();
            $materials = DB::table('materials')->where('category_id',1)->get();
            $table = "<table id='product_table'><tbody>";
            $tbody = $tfoot = "";
            $totalCol = [];
            $total_site = 0;
            $total_materials = 0;
            if(!$sites->isEmpty() && !$materials->isEmpty())
            {
                $total_site = count($sites);
                $total_materials = count($materials);
                $table .= "<tr style='background: black;color: white;'>
                            <td rowspan='2'><b>Item Name</b></td>";
                foreach ($sites as $key => $value) {
                    $table .= "<td colspan='2' class='text-center'><b>$value->name</b></td>";
                    $totalCol[$key]['qty'] = 0;
                    $totalCol[$key]['value'] = 0;
                }
                $totalCol[$total_site]['qty'] = 0;
                $totalCol[$total_site]['value'] = 0;
                $table .= "<td colspan='2' class='text-center'><b>Total Paddy</b></td></tr>";

                $table .= "<tr>";
                foreach ($sites as $key => $value) {
                    $table .= "<td class='text-center'><b>Qty</b></td>";
                    $table .= "<td class='text-center'><b>Value</b></td>";
                }
                $table .= "<td class='text-center'><b>Total Qty</b></td><td class='text-center'><b>Total Value</b></td></tr>";
                
                $tfoot .= "<tr><td>Total</td>";
                
                foreach ($materials as $index => $item) {
                    
                    $rowQtySum = $rowValueSum = 0;
                    $tbody .= "<tr>";
                    $tbody .= "<td>$item->material_name</td>";
                    foreach ($sites as $key => $value) {
                        $qty = DB::table("site_material")->where(['site_id'=>$value->id,'material_id'=>$item->id])->sum('qty');
                        $stock_value = $qty * ($item->cost ? $item->cost : 0);
                        $rowQtySum += $qty;
                        $rowValueSum += $stock_value;
                        $totalCol[$key]['qty'] += $qty;
                        $totalCol[$key]['value'] += $stock_value;
                        $tbody .= "<td class='text-right'>".number_format($qty,2,'.',',')."</td>";
                        $tbody .= "<td class='text-right'>".number_format($stock_value,2,'.',',')."</td>";
                    }
                    $tbody .= "<td class='text-right'><b>".number_format($rowQtySum,2,'.',',')."</b></td>";
                    $tbody .= "<td class='text-right'><b>".number_format($rowValueSum,2,'.',',')."</b></td>";
                    $tbody .= "</tr>";
                    $totalCol[$total_site]['qty'] += $rowQtySum;
                    $totalCol[$total_site]['value'] += $rowValueSum;
                }
                if(count($totalCol) > 0)
                {
                    foreach ($totalCol as $key => $value) {
                        $tfoot .= "<td class='text-right'><b>".number_format($value['qty'],2,'.',',')."</b></td>";
                        $tfoot .= "<td class='text-right'><b>".number_format($value['value'],2,'.',',')."</b></td>";
                    }
                }
                $tfoot .= "</tr>";
            }
            $table .= $tbody;
            
            $table .= $tfoot;

            
            // dd($totalCol);
            $table .= "</tbody></table>";
            return view('report::wip-summary-report',compact('table'));
        }else{
            return $this->access_blocked();
        }

    }

}
