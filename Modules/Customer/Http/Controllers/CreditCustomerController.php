<?php

namespace Modules\Customer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Customer\Entities\Customer;
use App\Http\Controllers\BaseController;
use Modules\Customer\Entities\CreditCustomer;

class CreditCustomerController extends BaseController
{
    public function __construct(CreditCustomer $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('credit-customer-access')){
            $this->setPageData('Credit Customer','Credit Customer','fas fa-users',[['name'=>'Customer','link'=>route('customer')],['name'=>'Credit Customer']]);
        //     $query = DB::table('customers as c')
        //     ->selectRaw('c.id,c.name,c.trade_name,c.mobile, customer_balance from ((select sum(debit) from transactions where chart_of_account_id= b.id)-(select sum(credit) from transactions where chart_of_account_id= b.id)) as customer_balance')
        //     ->leftjoin('chart_of_accounts as b', 'c.id', '=', 'b.customer_id')
        //     ->groupBy('c.id','c.name','c.trade_name','c.mobile','b.id')
            
        // ->having('customer_balance','>',0)
        // ->get();
        // dd($query);
            
            $customers = Customer::allCustomers();
            return view('customer::credit-customer.index',compact('customers'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){

            if (!empty($request->customer_id)) {
                $this->model->setCustomerID($request->customer_id);
            }
            $this->set_datatable_default_properties($request);//set datatable default properties
            $list = $this->model->getDatatableList();//get table data
            // dd($list);
            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                
                if($value->balance > 0)
                {
                    $no++;
                    $row = [];
                    $row[] = $no;
                    $row[] = $value->name;
                    $row[] = $value->trade_name;
                    $row[] = $value->mobile;
                    $row[] = number_format(($value->balance),2, '.', ',');
                    $data[] = $row;
                }
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
            $this->model->count_filtered(), $data);
            
        }else{
            return response()->json($this->unauthorized());
        }
    }

    // public function credit_customers(Request $request)
    // {
    //     if ($request->ajax()) {
    //         $upazila_id = $request->upazila_id;
    //         $route_id   = $request->route_id;
    //         $area_id    = $request->area_id;
    //         $data       =  DB::table('customers as c')
    //                         ->selectRaw('c.*, ((select ifnull(sum(debit),0) from transactions where chart_of_account_id= b.id)-(select ifnull(sum(credit),0) from transactions where chart_of_account_id= b.id)) as balance')
    //                         ->leftjoin('chart_of_accounts as b', 'c.id', '=', 'b.customer_id')
    //                         ->where('c.district_id',auth()->user()->district_id)
    //                         ->groupBy('c.id','b.id')
    //                         ->having('balance','>',0)
    //                         ->orderBy('c.name','asc')
    //                         ->when($upazila_id, function($q) use ($upazila_id){
    //                             $q->where('c.upazila_id',$upazila_id);
    //                         })
    //                         ->when($route_id, function($q) use ($route_id){
    //                             $q->where('c.route_id',$route_id);
    //                         })
    //                         ->when($area_id, function($q) use ($area_id){
    //                             $q->where('c.area_id',$area_id);
    //                         })
    //                         ->get();
    //         return response()->json($data);
    //     }
    // }
}
