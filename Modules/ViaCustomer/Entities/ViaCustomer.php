<?php

namespace Modules\ViaCustomer\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Modules\Customer\Entities\Customer;

class ViaCustomer extends BaseModel
{
    protected $fillable = [ 'customer_id', 'code','name', 'trade_name','mobile', 'email',  'address', 'status', 'created_by', 'modified_by'];

    public function customer(){
        return $this->belongsTo(Customer::class,'customer_id','id');
    }
    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    protected $order = ['vc.id' => 'desc'];
    //custom search column property
    protected $_customer_id; 
    protected $_name; 
    protected $_trade_name; 
    protected $_mobile; 
    protected $_status; 

    //methods to set custom search property value
    public function setCustomerID($customer_id)
    {
        $this->_customer_id = $customer_id;
    }
    public function setName($name)
    {
        $this->_name = $name;
    }
    public function setTradeName($trade_name)
    {
        $this->_trade_name = $trade_name;
    }
    public function setMobile($mobile)
    {
        $this->_mobile = $mobile;
    }

    public function setStatus($status)
    {
        $this->_status = $status;
    }

    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        if (permission('customer-via-bulk-delete')){
            $this->column_order = [null,'vc.id','vc.code','vc.name','vc.trade_name','vc.mobile', 'vc.email','vc.address', 'vc.customer_id','vc.status', null];
        }else{
            $this->column_order = ['vc.id','vc.code','vc.name','vc.trade_name','vc.mobile', 'vc.email','vc.address', 'vc.customer_id','vc.status', null];
        }
        
        $query = DB::table('via_customers as vc')
        ->join('customers as c','vc.customer_id','=','c.id')
        ->select('vc.*','c.trade_name as customer_trade_name');

        //search query
        if (!empty($this->_customer_id)) {
            $query->where('vc.customer_id', $this->_customer_id);
        }
        if (!empty($this->_name)) {
            $query->where('vc.name', 'like', '%' . $this->_name . '%');
        }
        if (!empty($this->_trade_name)) {
            $query->where('vc.trade_name', 'like', '%' . $this->_trade_name . '%');
        }
        if (!empty($this->_mobile)) {
            $query->where('vc.mobile', 'like', '%' . $this->_mobile . '%');
        }

        if (!empty($this->_status)) {
            $query->where('vc.status', $this->_status);
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
        return DB::table('via_customers')->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/

     /***************************************
     * * * Begin :: Model Local Scope * * *
    ****************************************/
    public function scopeActive($query)
    {
        return $query->where(['status'=>1]);
    }

    public function scopeInactive($query)
    {
        return $query->where(['status'=>2]);
    }
    /***************************************
     * * * Begin :: Model Local Scope * * *
    ****************************************/
    
}
