<?php

namespace Modules\ViaVendor\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Modules\Vendor\Entities\Vendor;

class ViaVendor extends BaseModel
{
    protected $fillable = [ 'vendor_id', 'name', 'mobile', 'email',  'address', 'status', 'created_by', 'modified_by'];

    public function vendor(){
        return $this->belongsTo(Vendor::class,'vendor_id','id');
    }
    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    protected $order = ['vv.id' => 'desc'];
    //custom search column property
    protected $_vendor_id; 
    protected $_name; 
    protected $_mobile; 
    protected $_email; 
    protected $_status; 

    //methods to set custom search property value
    public function setSupplierID($vendor_id)
    {
        $this->_vendor_id = $vendor_id;
    }
    public function setName($name)
    {
        $this->_name = $name;
    }
    public function setMobile($mobile)
    {
        $this->_mobile = $mobile;
    }
    public function setEmail($email)
    {
        $this->_email = $email;
    }
    public function setStatus($status)
    {
        $this->_status = $status;
    }

    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        if (permission('vendor-bulk-delete')){
            $this->column_order = [null,'id','name','mobile', 'email','address', 'vendor_id','status', null];
        }else{
            $this->column_order = ['id','name','mobile', 'email','address', 'vendor_id','status', null];
        }
        
        $query = DB::table('via_vendors as vv')
        ->join('vendors as s','vv.vendor_id','=','s.id')
        ->select('vv.*','s.name as vendor_name');

        //search query
        if (!empty($this->_vendor_id)) {
            $query->where('vv.vendor_id', $this->_vendor_id);
        }
        if (!empty($this->_name)) {
            $query->where('vv.name', 'like', '%' . $this->_name . '%');
        }
        if (!empty($this->_mobile)) {
            $query->where('vv.mobile', 'like', '%' . $this->_mobile . '%');
        }
        if (!empty($this->_email)) {
            $query->where('vv.email', 'like', '%' . $this->_email . '%');
        }
        if (!empty($this->_status)) {
            $query->where('vv.status', $this->_status);
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
        return DB::table('via_vendors')->get()->count();
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
