<?php

namespace Modules\Customer\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\Auth;
use Modules\Account\Entities\ChartOfAccount;


class CustomerLedger extends BaseModel
{
    protected $table = 'transactions';
    protected $order = ['transactions.voucher_date' => 'asc'];
    protected $fillable = ['chart_of_account_id', 'warehouse_id', 'voucher_no', 'voucher_type', 'voucher_date',
     'description', 'debit', 'credit', 'is_opening', 'posted', 'approve', 'created_by', 'modified_by'];

    public function coa()
    {
        return $this->belongsTo(ChartOfAccount::class,'chart_of_account_id','id');
    }

    public function customer()
    {
        return $this->hasOneThrough(Customer::class,ChartOfAccount::class,'customer_id','chart_of_account_id','id','id');
    }

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $_district_id; 
    protected $_upazila_id; 
    protected $_route_id; 
    protected $_area_id; 
    protected $_customer_id; 
    protected $start_date; 
    protected $end_date; 

    //methods to set custom search property value
    public function setCustomerID($customer_id)
    {
        $this->_customer_id = $customer_id;
    }
    public function setStartDate($start_date)
    {
        $this->start_date = $start_date;
    }
    public function setEndDate($end_date)
    {
        $this->end_date = $end_date;
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

        $this->column_order = ['transactions.voucher_date','transactions.description', 'transactions.voucher_no','transactions.debit','transactions.credit',null];
        
        $query = self::select('transactions.*','coa.id as coa_id','coa.code','coa.name','coa.parent_name',
        'c.id as customer_id','c.shop_name','c.name','c.mobile')
        ->join('chart_of_accounts as coa','transactions.chart_of_account_id','=','coa.id')
        ->join('customers as c','coa.customer_id','c.id')
        ->where('transactions.approve',1);

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
        if (!empty($this->start_date)) {
            $query->where('transactions.voucher_date', '>=',$this->start_date);
        }
        if (!empty($this->end_date)) {
            $query->where('transactions.voucher_date', '<=',$this->end_date);
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
        return self::select('transactions.*','coa.id as coa_id','coa.code','coa.name','coa.parent_name','c.id as customer_id','c.shop_name','c.name','c.mobile')
        ->join('chart_of_accounts as coa','transactions.chart_of_account_id','=','coa.id')
        ->join('customers as c','coa.customer_id','c.id')
        ->where('transactions.approve',1)
        ->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
}
