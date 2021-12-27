<?php

namespace Modules\Report\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;

class InventoryValuationSummaryController extends BaseController
{
    public function index()
    {
        if(permission('inventory-valuation-summary-access')){
            $this->setPageData('Inventory Valuation Summary','Inventory Valuation Summary','fas fa-file',[['name' => 'Inventory Valuation Summary']]);
            $categories = Category::with('products','materials')->get();
            $table = "<table id='product_table'>";
            if(!$categories->isEmpty())
            {
                $table .= "<tbody>";
                $by_product = '';
                foreach ($categories as $value) {
                    $total_qty = $total_value = 0;
                    if($value->id == 1) //Paddy (RM)
                    {
                        $table .= "<tr  style='background: black;color: white;text-align:center;'><td colspan='4'><b>$value->name</b></td></tr>";
                        $table .= "<tr>";
                        $table .= "<td><b>Name</b></td>";
                        $table .= "<td class='text-right'><b>Total Qty</b></td>";
                        $table .= "<td class='text-right'><b>Rate</b></td>";
                        $table .= "<td class='text-right'><b>Total Value</b></td>";
                        $table .= "</tr>";

                        if(!$value->materials->isEmpty())
                        {
                            foreach ($value->materials as $key => $item) {
                                $qty = DB::table("site_material")->where('material_id',$item->id)->sum('qty');
                                $table .= "<tr>";
                                $table .= "<td>$item->material_name</td>";
                                $table .= "<td class='text-right'>".number_format($qty,4,'.',',')."</td>";
                                $table .= "<td class='text-right'>".number_format($item->cost,4,'.',',')."</td>";
                                $table .= "<td class='text-right'>".number_format(($qty * $item->cost),4,'.',',')."</td>";
                                $table .= "</tr>";
                                $total_qty += $qty;
                                $total_value += $qty * $item->cost;
                            }
                            $table .= "<tr>";
                            $table .= "<td><b>Total</b></td>";
                            $table .= "<td class='text-right'><b>".number_format($total_qty,4,'.',',')."</b></td>";
                            $table .= "<td></td>";
                            $table .= "<td class='text-right'><b>".number_format($total_value,4,'.',',')."</b></td>";
                            $table .= "</tr>";
                            $table .= "<tr><td colspan='4' style='padding: 20px;border: 0 !important;'></td></tr>";
                        }else{
                            $table .= "<tr><td colspan='4' style='color:red;'>No Data Found</td></tr>";
                        }
                    }elseif ($value->id == 4) { //Fine Rice
                        $total_qty = $total_value = 0;
                        $table .= "<tr style='background: black;color: white;text-align:center;'><td colspan='4'><b>$value->name</b></td></tr>";
                        $table .= "<tr>";
                        $table .= "<td><b>Name</b></td>";
                        $table .= "<td class='text-right'><b>Total Qty</b></td>";
                        $table .= "<td class='text-right'><b>Rate</b></td>";
                        $table .= "<td class='text-right'><b>Total Value</b></td>";
                        $table .= "</tr>";

                        if(!$value->products->isEmpty())
                        {
                            foreach ($value->products as $key => $item) {
                                $qty = DB::table("site_product")->where(['product_id'=>$item->id])->sum('qty');
                                $table .= "<tr>";
                                $table .= "<td>$item->name</td>";
                                $table .= "<td class='text-right'>".number_format($qty,4,'.',',')."</td>";
                                $table .= "<td class='text-right'>".number_format($item->price,4,'.',',')."</td>";
                                $table .= "<td class='text-right'>".number_format(($qty * $item->price),4,'.',',')."</td>";
                                $table .= "</tr>";
                                $total_qty += $qty;
                                $total_value += $qty * $item->price;
                            }
                            $table .= "<tr>";
                            $table .= "<td><b>Total</b></td>";
                            $table .= "<td class='text-right'><b>".number_format($total_qty,4,'.',',')."</b></td>";
                            $table .= "<td></td>";
                            $table .= "<td class='text-right'><b>".number_format($total_value,4,'.',',')."</b></td>";
                            $table .= "</tr>";
                            $table .= "<tr><td colspan='4' style='padding: 20px;border: 0 !important;'></td></tr>";
                        }else{
                            $table .= "<tr><td colspan='4' style='color:red;'>No Data Found</td></tr>";
                        }
                    
                    }elseif ($value->id == 5) { //Packet Rice
                        $total_qty = $total_value = 0;
                        $table .= "<tr style='background: black;color: white;text-align:center;'><td colspan='4'><b>$value->name</b></td></tr>";
                        $table .= "<tr>";
                        $table .= "<td><b>Name</b></td>";
                        $table .= "<td class='text-right'><b>Total Qty</b></td>";
                        $table .= "<td class='text-right'><b>Rate</b></td>";
                        $table .= "<td class='text-right'><b>Total Value</b></td>";
                        $table .= "</tr>";

                        if(!$value->products->isEmpty())
                        {
                            foreach ($value->products as $key => $item) {
                                $qty = DB::table("site_product")->where(['product_id'=>$item->id])->sum('qty');
                                $table .= "<tr>";
                                $table .= "<td>$item->name</td>";
                                $table .= "<td class='text-right'>".number_format($qty,4,'.',',')."</td>";
                                $table .= "<td class='text-right'>".number_format($item->price,4,'.',',')."</td>";
                                $table .= "<td class='text-right'>".number_format(($qty * $item->price),4,'.',',')."</td>";
                                $table .= "</tr>";
                                $total_qty += $qty;
                                $total_value += $qty * $item->price;
                            }
                            $table .= "<tr>";
                            $table .= "<td><b>Total</b></td>";
                            $table .= "<td class='text-right'><b>".number_format($total_qty,4,'.',',')."</b></td>";
                            $table .= "<td></td>";
                            $table .= "<td class='text-right'><b>".number_format($total_value,4,'.',',')."</b></td>";
                            $table .= "</tr>";
                            $table .= "<tr><td colspan='4' style='padding: 20px;border: 0 !important;'></td></tr>";
                        }else{
                            $table .= "<tr><td colspan='4' style='color:red;'>No Data Found</td></tr>";
                        }
                    
                    }elseif ($value->id == 3) { //BY Product
                        $total_qty = $total_value = 0;
                        $by_product .= "<tr style='background: black;color: white;text-align:center;'><td colspan='4'><b>$value->name</b></td></tr>";
                        $by_product .= "<tr>";
                        $by_product .= "<td><b>Name</b></td>";
                        $by_product .= "<td class='text-right'><b>Total Qty</b></td>";
                        $by_product .= "<td class='text-right'><b>Rate</b></td>";
                        $by_product .= "<td class='text-right'><b>Total Value</b></td>";
                        $by_product .= "</tr>";

                        if(!$value->products->isEmpty())
                        {
                            foreach ($value->products as $key => $item) {
                                $qty = DB::table("site_product")->where(['product_id'=>$item->id])->sum('qty');
                                $by_product .= "<tr>";
                                $by_product .= "<td>$item->name</td>";
                                $by_product .= "<td class='text-right'>".number_format($qty,4,'.',',')."</td>";
                                $by_product .= "<td class='text-right'>".number_format($item->price,4,'.',',')."</td>";
                                $by_product .= "<td class='text-right'>".number_format(($qty * $item->price),4,'.',',')."</td>";
                                $by_product .= "</tr>";
                                $total_qty += $qty;
                                $total_value += $qty * $item->price;
                            }
                            $by_product .= "<tr>";
                            $by_product .= "<td><b>Total</b></td>";
                            $by_product .= "<td class='text-right'><b>".number_format($total_qty,4,'.',',')."</b></td>";
                            $by_product .= "<td></td>";
                            $by_product .= "<td class='text-right'><b>".number_format($total_value,4,'.',',')."</b></td>";
                            $by_product .= "</tr>";
                            $by_product .= "<tr><td colspan='4' style='padding: 20px;border: 0 !important;'></td></tr>";
                        }else{
                            $by_product .= "<tr><td colspan='4' style='color:red;'>No Data Found</td></tr>";
                        }
                    }
                    
                }
                $table .= $by_product;
                $table .= "</tbody>";
            }
            $table .= "</table>";
            return view('report::inventory-valuation-summary',compact('table'));
        }else{
            return $this->access_blocked();
        }

    }
}
