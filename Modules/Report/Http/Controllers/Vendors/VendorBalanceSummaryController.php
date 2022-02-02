<?php

namespace Modules\Report\Http\Controllers\Vendors;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Vendor\Entities\Vendor;
use App\Http\Controllers\BaseController;

class VendorBalanceSummaryController extends BaseController
{
    public function index()
    {
        if(permission('vendor-balance-summary-access')){
            $this->setPageData('Vendor Balance Summary','Vendor Balance Summary','fas fa-file',[['name' => 'Vendor Balance Summary']]);
            $vendors = Vendor::allVendors();
            return view('report::vendor-payables.summary-report',compact('vendors'));
        }else{
            return $this->access_blocked();
        }
    }

    public function report_data(Request $request)
    {
        $date = dateFormat($request->to_date);
        $vendor_id  = $request->vendor_id;

        $table = '';
        $table .= '<table style="margin-bottom:10px !important;">
                    <tr>
                        <td class="text-center">
                            <h3 class="name m-0" style="text-transform: uppercase;"><b>'.(config('settings.title') ? config('settings.title') : env('APP_NAME')).'</b></h3>
                            <p style="font-weight: normal;font-weight:bold;    margin: 10px auto 5px auto;
                            font-weight: bold;background: black;border-radius: 10px;width: 250px;color: white;text-align: center;padding:5px 0;}">ALl Vendors Balance Summary</p>
                            <p style="font-weight: normal;margin:0;font-weight:bold;">Date: '.$request->to_date.'</p>
                        </td>
                    </tr>
                </table>';
        $table .= "<table  id='product_table'>";
        $table_head = "<tr  style='background: black;color: white;'>
                            <td style='text-align:center;width:100px;'><b>SL. No.</b></td>
                            <td><b>Trade Name</b></td>
                            <td style='text-align:right;'><b>Balance</b></td>
                        </tr>";
        $table .= $table_head;
        $total_balance = 0;

        $vendors = DB::table('vendors as v')
            ->leftjoin('chart_of_accounts as b', 'v.id', '=', 'b.vendor_id')
            ->selectRaw("v.id,v.trade_name,b.id as coaid,b.code,
            ((select sum(debit) from transactions where chart_of_account_id= b.id AND approve = 1 AND voucher_date <= '$date')-(select sum(credit)
            from transactions where chart_of_account_id= b.id AND approve = 1 AND voucher_date <= '$date')) as balance")
            
            ->when($vendor_id,function($q) use ($vendor_id){
                $q->where('v.id',$vendor_id);
            })
            ->orderBy('v.id','asc')
            ->get();

        if(!$vendors->isEmpty())
        {
            foreach ($vendors as $key => $vendor) {
                $total_balance += $vendor->balance ?? 0;
                $table .= "<tr>
                            <td style='text-align:center;><a href='".url('vendor-balance-details',$vendor->id)."' style='text-decoration:none;color:black;cursor:pointer;'>".($key+1)."</a></td>
                            <td><a href='".url('vendor-balance-details',$vendor->id)."' style='text-decoration:none;color:black;cursor:pointer;'>$vendor->trade_name</a></td>
                            <td style='text-align:right;'><a href='".url('vendor-balance-details',$vendor->id)."' style='text-decoration:none;color:black;cursor:pointer;'>".number_format($vendor->balance,2,'.',',')."</a></td>
                        </tr>";
            }
            $table .= "<tr>
                    <td colspan='2' style='text-align:right;'><b>Total Balance</b></td>
                    <td style='text-align:right;border-top:3px solid black !important;'><b>".number_format($total_balance,2,'.',',')."</b></td>
                </tr>";
        }else{
            $table .= "<tr>
                <td colspan='3' style='color:red;text-align:center;'>No Data Found</td>
                </tr>";
        }
        return $table;

    }
}
