<?php

namespace Modules\Customer\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class PaidCustomer extends BaseModel
{
    protected $table = 'customers';

    protected $fillable = [ 'name', 'shop_name', 'mobile', 'email', 'avatar', 'customer_group_id',
     'district_id', 'upazila_id', 'route_id', 'area_id', 'address', 'status', 'created_by', 'modified_by'];


    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $_district_id;
    protected $_customer_id; 
    protected $_upazila_id; 
    protected $_route_id; 
    protected $_area_id; 

    //methods to set custom search property value
    public function setCustomerID($customer_id)
    {
        $this->_customer_id = $customer_id;
    }
    public function setDistrictID($district_id)
    {
        $this->_district_id = $district_id;
    }
    public function setUpazilaID($upazila_id)
    {
        $this->_upazila_id = $upazila_id;
    }
    public function seRouteID($route_id)
    {
        $this->_route_id = $route_id;
    }
    public function setAreaID($area_id)
    {
        $this->_area_id = $area_id;
    }
    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)

        $this->column_order = ['c.id','c.name', 'c.shop_name','c.mobile','c.upazila_id','c.route_id','c.area_id','c.customer_group_id',null];
        
        $query = DB::table('customers as c')
        ->selectRaw('c.*,d.name as district_name,u.name as upazila_name,r.name as route_name,a.name as area_name,cg.group_name, ((select ifnull(sum(debit),0) from transactions where chart_of_account_id= b.id)-(select ifnull(sum(credit),0) from transactions where chart_of_account_id= b.id)) as balance')
        ->leftjoin('chart_of_accounts as b', 'c.id', '=', 'b.customer_id')
        ->join('locations as d', 'c.district_id', '=', 'd.id')
        ->join('locations as u', 'c.upazila_id', '=', 'u.id')
        ->join('locations as r', 'c.route_id', '=', 'r.id')
        ->join('locations as a', 'c.area_id', '=', 'a.id')
        ->leftjoin('customer_groups as cg', 'c.customer_group_id', '=', 'cg.id')
        ->groupBy('c.id','b.id')
        ->having('balance','<=',0);

        //search query
        if (!empty($this->_customer_id)) {
            $query->where('c.id', $this->_customer_id);
        }
        if (!empty($this->_district_id)) {
            $query->where('c.district_id', $this->_district_id);
        }
        if (!empty($this->_upazila_id)) {
            $query->where('c.upazila_id', $this->_upazila_id);
        }
        if (!empty($this->_route_id)) {
            $query->where('c.route_id', $this->_route_id);
        }
        if (!empty($this->_area_id)) {
            $query->where('c.area_id', $this->_area_id);
        }

        //order by data fetching code
        if (isset($this->orderValue) && isset($this->dirValue)) { //orderValue is the index number of table header and dirValue is asc or desc
            $query->orderBy($this->column_order[$this->orderValue], $this->dirValue); //fetch data order by matching column
        } else if (isset($this->order)) {
            $query->orderBy(key($this->order), $this->order[key($this->order)]);
        }
        return $query;
    }

    public function getDatatableList()
    {
        $query = $this->get_datatable_query();
        if ($this->lengthVlaue != -1) {
            $query->offset($this->startVlaue)->limit($this->lengthVlaue);
        }
        return $query->get();
    }

    public function count_filtered()
    {
        $query = $this->get_datatable_query();
        return $query->get()->count();
    }

    public function count_all()
    {
        $query = DB::table('customers as c')
                ->selectRaw('c.*,u.name as upazila_name,r.name as route_name,cg.group_name, ((select ifnull(sum(debit),0) from transactions where chart_of_account_id= b.id)-(select ifnull(sum(credit),0) from transactions where chart_of_account_id= b.id)) as balance')
                ->leftjoin('chart_of_accounts as b', 'c.id', '=', 'b.customer_id')
                ->join('locations as u', 'c.upazila_id', '=', 'u.id')
                ->join('locations as r', 'c.route_id', '=', 'r.id')
                ->leftjoin('customer_groups as cg', 'c.customer_group_id', '=', 'cg.id')
                ->groupBy('c.id','b.id')
                ->having('balance','<=',0);
                if (!empty($this->_customer_id)) {
                    $query->where('c.id', $this->_customer_id);
                }
                if (!empty($this->_district_id)) {
                    $query->where('c.district_id', $this->_district_id);
                }
                if (!empty($this->_upazila_id)) {
                    $query->where('c.upazila_id', $this->_upazila_id);
                }
                if (!empty($this->_route_id)) {
                    $query->where('c.route_id', $this->_route_id);
                }
                if (!empty($this->_area_id)) {
                    $query->where('c.area_id', $this->_area_id);
                }
        return $query->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
}
