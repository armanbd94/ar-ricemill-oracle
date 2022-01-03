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
            $sites      = DB::table('sites')->get();
            $materials  = DB::table('materials')->where('category_id',1)->get();
            $table      = "<table id='product_table'><tbody>";
            $tbody      = $tfoot = $title = "";
            $totalCol   = [];
            $total_site = 0;
            if(!$sites->isEmpty() && !$materials->isEmpty())
            {
                $total_site = count($sites);//count total sites for footer row

                $table .= "<tr style='background: black;color: white;'>
                            <td rowspan='2'><b>Item Name</b></td>";
                foreach ($sites as $key => $value) {
                    $table .= "<td colspan='2' class='text-center'><b>$value->name</b></td>";//table header site name
                    $totalCol[$key]['qty'] = 0;//for each column qty sum
                    $totalCol[$key]['value'] = 0;//for each column value sum
                }
                $totalCol[$total_site]['qty'] = 0; //footer row total qty column sum 
                $totalCol[$total_site]['value'] = 0;//footer row total value column sum

                $table .= "<td colspan='2' class='text-center'><b>Total Paddy</b></td></tr>";
                $table .= "<tr>";
                $title .= "<tr>";
                $title .= "<td><b>Paddy (RM)</b></td>";
                foreach ($sites as $key => $value) {
                    $table .= "<td class='text-center'><b>Qty</b></td>";
                    $table .= "<td class='text-center'><b>Value</b></td>";
                    $title .= "<td></td><td></td>";
                }
                $table .= "<td class='text-center'><b>Total Qty</b></td><td class='text-center'><b>Total Value</b></td></td>";
                $title .= "<td></td><td></td></tr>";
                $table .= $title;
                $tfoot .= "<tr><td><b>Total Paddy (RM)</b></td>";
                
                foreach ($materials as $index => $item) {
                    
                    $rowQtySum = $rowValueSum = 0;//initialize row qty and value sum
                    $tbody .= "<tr>";
                    $tbody .= "<td>$item->material_name</td>";
                    foreach ($sites as $key => $value) {
                        $qty = DB::table("site_material")->where(['site_id'=>$value->id,'material_id'=>$item->id])->sum('qty');
                        $stock_value = $qty * ($item->cost ? $item->cost : 0);
                        $rowQtySum += $qty;//sum row qty
                        $rowValueSum += $stock_value;//sum row value
                        $totalCol[$key]['qty'] += $qty;//sum qty column 
                        $totalCol[$key]['value'] += $stock_value;//sum value column
                        $tbody .= "<td class='text-right'>".number_format($qty,2,'.',',')."</td>";
                        $tbody .= "<td class='text-right'>".number_format($stock_value,2,'.',',')."</td>";
                    }
                    $tbody .= "<td class='text-right'><b>".number_format($rowQtySum,2,'.',',')."</b></td>";//display total row qty sum
                    $tbody .= "<td class='text-right'><b>".number_format($rowValueSum,2,'.',',')."</b></td>";//display total row value sum
                    $tbody .= "</tr>";
                    $totalCol[$total_site]['qty'] += $rowQtySum; //total qty column sum
                    $totalCol[$total_site]['value'] += $rowValueSum;//total value column sum
                }
                if(count($totalCol) > 0)
                {
                    foreach ($totalCol as $key => $value) {
                        $tfoot .= "<td class='text-right'><b>".number_format($value['qty'],2,'.',',')."</b></td>";//display footer each qty column sum
                        $tfoot .= "<td class='text-right'><b>".number_format($value['value'],2,'.',',')."</b></td>";//display footer each value column sum
                    }
                }
                $tfoot .= "</>";
            }
            $table .= $tbody;
            $table .= $tfoot;
            $table .= "</tbody></table>";
            return view('report::wip-summary-report',compact('table'));
        }else{
            return $this->access_blocked();
        }

    }

}
