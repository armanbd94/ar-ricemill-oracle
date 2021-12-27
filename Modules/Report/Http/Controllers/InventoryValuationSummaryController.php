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
                foreach ($categories as $value) {
                    if($value->id == 1) //Paddy (RM)
                    {
                        $table .= "<tr  style='background: black;color: white;text-align:center;'><td colspan='4'><b>$value->name</b></td></tr>";
                        $table .= "<tr>";
                        $table .= "<td><b>Name</b></td>";
                        $table .= "<td><b>Total Qty</b></td>";
                        $table .= "<td><b>Rate</b></td>";
                        $table .= "<td><b>Total Value</b></td>";
                        $table .= "</tr>";

                        if(!$value->materials->isEmpty())
                        {
                            foreach ($value->materials as $key => $item) {
                                $qty = DB::table("site_material")->where(['site_id'=>$value->id,'material_id'=>$item->id])->sum('qty');
                                $table .= "<tr>";
                                $table .= "<td>$item->material_name</td>";
                                $table .= "<td>".number_format($qty,4,'.',',')."</td>";
                                $table .= "<td>".number_format($item->cost,4,'.',',')."</td>";
                                $table .= "<td>".number_format(($qty * $item->cost),4,'.',',')."</td>";
                                $table .= "</tr>";
                            }
                        }
                    }elseif ($value->id == 4) { //Fine Rice
                        $table .= "<tr style='background: black;color: white;text-align:center;'><td colspan='4'><b>$value->name</b></td></tr>";
                        $table .= "<tr>";
                        $table .= "<td><b>Name</b></td>";
                        $table .= "<td><b>Total Qty</b></td>";
                        $table .= "<td><b>Rate</b></td>";
                        $table .= "<td><b>Total Value</b></td>";
                        $table .= "</tr>";

                        if(!$value->products->isEmpty())
                        {
                            foreach ($value->products as $key => $item) {
                                $qty = DB::table("site_product")->where(['site_id'=>$value->id,'product_id'=>$item->id])->sum('qty');
                                $table .= "<tr>";
                                $table .= "<td>$item->name</td>";
                                $table .= "<td>".number_format($qty,4,'.',',')."</td>";
                                $table .= "<td>".number_format($item->price,4,'.',',')."</td>";
                                $table .= "<td>".number_format(($qty * $item->price),4,'.',',')."</td>";
                                $table .= "</tr>";
                            }
                        }
                    
                    }elseif ($value->id == 5) { //Packet Rice
                        $table .= "<tr style='background: black;color: white;text-align:center;'><td colspan='4'><b>$value->name</b></td></tr>";
                        $table .= "<tr>";
                        $table .= "<td><b>Name</b></td>";
                        $table .= "<td><b>Total Qty</b></td>";
                        $table .= "<td><b>Rate</b></td>";
                        $table .= "<td><b>Total Value</b></td>";
                        $table .= "</tr>";

                        if(!$value->products->isEmpty())
                        {
                            foreach ($value->products as $key => $item) {
                                $qty = DB::table("site_product")->where(['site_id'=>$value->id,'product_id'=>$item->id])->sum('qty');
                                $table .= "<tr>";
                                $table .= "<td>$item->name</td>";
                                $table .= "<td>".number_format($qty,4,'.',',')."</td>";
                                $table .= "<td>".number_format($item->price,4,'.',',')."</td>";
                                $table .= "<td>".number_format(($qty * $item->price),4,'.',',')."</td>";
                                $table .= "</tr>";
                            }
                        }
                    
                    }elseif ($value->id == 3) { //BY Product
                        $table .= "<tr style='background: black;color: white;text-align:center;'><td colspan='4'><b>$value->name</b></td></tr>";
                        $table .= "<tr>";
                        $table .= "<td><b>Name</b></td>";
                        $table .= "<td><b>Total Qty</b></td>";
                        $table .= "<td><b>Rate</b></td>";
                        $table .= "<td><b>Total Value</b></td>";
                        $table .= "</tr>";

                        if(!$value->products->isEmpty())
                        {
                            foreach ($value->products as $key => $item) {
                                $qty = DB::table("site_product")->where(['site_id'=>$value->id,'product_id'=>$item->id])->sum('qty');
                                $table .= "<tr>";
                                $table .= "<td>$item->name</td>";
                                $table .= "<td>".number_format($qty,4,'.',',')."</td>";
                                $table .= "<td>".number_format($item->price,4,'.',',')."</td>";
                                $table .= "<td>".number_format(($qty * $item->price),4,'.',',')."</td>";
                                $table .= "</tr>";
                            }
                        }
                    }
                    
                }
                $table .= "</tbody>";
            }
            $table .= "</table>";
            return view('report::inventory-valuation-summary',compact('table'));
        }else{
            return $this->access_blocked();
        }

    }
}
