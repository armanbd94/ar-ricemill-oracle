<?php

namespace Modules\Report\Http\Controllers\Customers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Customer\Entities\Customer;
use App\Http\Controllers\BaseController;

class DailyCollectionReportController extends BaseController
{
    public function index()
    {
        if(permission('daily-collection-report-access')){
            $this->setPageData('Daily Collection Report','Daily Collection Report','fas fa-file',[['name' => 'Daily Collection Report']]);
            $customers = Customer::allCustomers();
            return view('report::customers-receivables.daily-collection-report',compact('customers'));
        }else{
            return $this->access_blocked();
        }
    }

    public function report_data(Request $request)
    {
        $start_date = dateFormat($request->start_date);
        $end_date   = dateFormat($request->end_date);
        $customer_id  = $request->customer_id;

        if($request->start_date == $request->end_date)
        {
            $date = $request->start_date;
        }else{
            $date = $request->start_date.' to '.$request->end_date;
        }

        $table = '';
        $table .= '<table style="margin-bottom:10px !important;">
                    <tr>
                        <td class="text-center">
                            <h3 class="name m-0" style="text-transform: uppercase;"><b>'.(config('settings.title') ? config('settings.title') : env('APP_NAME')).'</b></h3>
                            <p style="font-weight: normal;font-weight:bold;    margin: 10px auto 5px auto;
                            font-weight: bold;background: black;border-radius: 10px;width: 250px;color: white;text-align: center;padding:5px 0;}">Daily Collection Report</p>
                            <p style="font-weight: normal;margin:0;font-weight:bold;">Date: '.$date.'</p>
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

        $customers = DB::table('customers as c')
            ->leftjoin('chart_of_accounts as b', 'c.id', '=', 'b.customer_id')
            ->selectRaw("c.id,c.trade_name,b.id as coaid,b.code,
            (select sum(credit) from transactions where chart_of_account_id= b.id AND approve = 1 AND voucher_type='CR' AND voucher_date BETWEEN '$start_date' and '$end_date') as balance")
            
            ->when($customer_id,function($q) use ($customer_id){
                $q->where('c.id',$customer_id);
            })
            ->orderBy('c.id','asc')
            ->get();

        if(!$customers->isEmpty())
        {
            foreach ($customers as $key => $customer) {
                if($customer->balance > 0){
                    $total_balance += $customer->balance ?? 0;
                    $table .= "<tr>
                                <td style='text-align:center;><a href='".url('customer-collection-details/'.$customer->id.'/'.$$request->start_date.'/'.$request->end_date)."' style='text-decoration:none;color:black;cursor:pointer;'>".($key+1)."</a></td>
                                <td><a href='".url('customer-collection-details/'.$customer->id.'/'.$$request->start_date.'/'.$request->end_date)."' style='text-decoration:none;color:black;cursor:pointer;'>$customer->trade_name</a></td>
                                <td style='text-align:right;'><a href='".url('customer-collection-details/'.$customer->id.'/'.$$request->start_date.'/'.$request->end_date)."' style='text-decoration:none;color:black;cursor:pointer;'>".number_format($customer->balance,2,'.',',')."</a></td>
                            </tr>";
                }
                
            }
            if($total_balance > 0)
            {
                $table .= "<tr>
                    <td colspan='2' style='text-align:right;'><b>Total</b></td>
                    <td style='text-align:right;border-top:3px solid black !important;'><b>".number_format($total_balance,2,'.',',')."</b></td>
                </tr>";
            }else{
                $table .= "<tr>
                <td colspan='3' style='color:red;text-align:center;'>No Data Found</td>
                </tr>";
            }
            
        }else{
            $table .= "<tr>
                <td colspan='3' style='color:red;text-align:center;'>No Data Found</td>
                </tr>";
        }
        return $table;

    }
}
