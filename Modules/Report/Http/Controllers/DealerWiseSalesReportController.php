<?php
namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Depo\Entities\Depo;
use Illuminate\Support\Facades\DB;
use Modules\Dealer\Entities\Dealer;
use App\Http\Controllers\BaseController;

class DealerWiseSalesReportController extends BaseController
{
    
    public function index()
    {
        if(permission('dealer-wise-sales-report-access')){
            $this->setPageData('Dealer Wise Sales Report','Dealer Wise Sales Report','fas fa-file',[['name' => 'Dealer Wise Sales Report']]);
            $data = [
                'dealers'  => DB::table('dealers')->where([['status',1],['type',2]])->pluck('name','id')
            ];
            return view('report::dealer-wise-sales-report.index',$data);
        }else{
            return $this->access_blocked();
        }

    }

    public function report_data(Request $request)
    {

        $dealer_id = $request->dealer_id;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $dealer = Dealer::with('district','area')->find($dealer_id);
        $report_data = DB::table('sales as s')
                    ->selectRaw('s.sale_date,sum(s.item) as total_item,sum(s.total_qty) as total_qty, sum(s.grand_total) as grand_total,
                    s.commission_rate,sum(s.total_commission) as total_commission,sum(s.net_total) as net_total')
                    ->groupBy('s.sale_date')
                    ->where('s.dealer_id',$dealer_id)
                    ->whereBetween('s.sale_date',[$start_date,$end_date]) 
                    ->get();
        return view('report::dealer-wise-sales-report.report',compact('report_data','dealer','start_date','end_date'))->render();

    }

}
