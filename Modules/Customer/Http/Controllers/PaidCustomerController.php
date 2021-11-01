<?php

namespace Modules\Customer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use Modules\Customer\Entities\PaidCustomer;

class PaidCustomerController extends BaseController
{
    public function __construct(PaidCustomer $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('paid-customer-access')){
            $this->setPageData('Paid Customer','Paid Customer','fas fa-users',[['name'=>'Customer','link'=>route('customer')],['name'=>'Paid Customer']]);
            $locations = DB::table('locations')->where('status', 1)->get();
            return view('customer::paid-customer.index',compact('locations'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if (!empty($request->district_id)) {
                $this->model->setDistrictID($request->district_id);
            }
            if (!empty($request->upazila_id)) {
                $this->model->setUpazilaID($request->upazila_id);
            }
            if (!empty($request->route_id)) {
                $this->model->setRouteID($request->route_id);
            }
            if (!empty($request->area_id)) {
                $this->model->setAreaID($request->area_id);
            }
            if (!empty($request->customer_id)) {
                $this->model->setCustomerID($request->customer_id);
            }
            $this->set_datatable_default_properties($request);//set datatable default properties
            $list = $this->model->getDatatableList();//get table data
            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $row = [];
                $row[] = $no;
                $row[] = $value->name;
                $row[] = $value->shop_name;
                $row[] = $value->mobile;
                $row[] = $value->district_name;
                $row[] = $value->upazila_name;
                $row[] = $value->route_name;
                $row[] = $value->area_name;
                $row[] = $value->group_name;
                $row[] = number_format(($value->balance),2, '.', ',');
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
            $this->model->count_filtered(), $data);
            
        }else{
            return response()->json($this->unauthorized());
        }
    }

    protected function paid_customers(Request $request)
    {
        if ($request->ajax()) {
            $upazila_id = $request->upazila_id;
            $route_id   = $request->route_id;
            $area_id    = $request->area_id;
            $data       =  DB::table('customers as c')
                            ->selectRaw('c.*, ((select ifnull(sum(debit),0) from transactions where chart_of_account_id= b.id)-(select ifnull(sum(credit),0) from transactions where chart_of_account_id= b.id)) as balance')
                            ->leftjoin('chart_of_accounts as b', 'c.id', '=', 'b.customer_id')
                            ->where('c.district_id',auth()->user()->district_id)
                            ->groupBy('c.id','b.id')
                            ->having('balance','<=',0)
                            ->orderBy('c.name','asc')
                            ->when($upazila_id, function($q) use ($upazila_id){
                                $q->where('c.upazila_id',$upazila_id);
                            })
                            ->when($route_id, function($q) use ($route_id){
                                $q->where('c.route_id',$route_id);
                            })
                            ->when($area_id, function($q) use ($area_id){
                                $q->where('c.area_id',$area_id);
                            })
                            ->get();
            return response()->json($data);
        }
    }
}
