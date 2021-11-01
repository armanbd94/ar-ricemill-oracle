<?php

namespace Modules\ViaVendor\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Modules\Supplier\Entities\Supplier;

class ViaVendor extends BaseModel
{
    protected $fillable = [ 'supplier_id', 'name', 'company_name', 'mobile', 'email', 'phone', 'city', 'zipcode', 'address', 'status', 'created_by', 'modified_by'];

    public function supplier(){
        return $this->belongsTo(Supplier::class,'supplier_id','id');
    }
    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    protected $order = ['vv.id' => 'desc'];
    //custom search column property
    protected $_supplier_id; 
    protected $_name; 
    protected $_mobile; 
    protected $_email; 
    protected $_status; 

    //methods to set custom search property value
    public function setSupplierID($supplier_id)
    {
        $this->_supplier_id = $supplier_id;
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
            $this->column_order = [null,'id','name','company_name', 'address','mobile', 'email', 'city', 'zipcode','supplier_id','status', null];
        }else{
            $this->column_order = ['id','name','company_name', 'address','mobile', 'email', 'city', 'zipcode','supplier_id','status', null];
        }
        
        $query = DB::table('via_vendors as vv')
        ->join('suppliers as s','vv.supplier_id','=','s.id')
        ->select('vv.*','s.name as supplier_name');

        //search query
        if (!empty($this->_supplier_id)) {
            $query->where('vv.supplier_id', $this->_supplier_id);
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

    /*************************************
    * * *  Begin :: Cache Data * * *
    **************************************/
    protected const ALL_VIA_VENDORS    = '_via_vendors';

    public static function allViaVendors(){
        return Cache::rememberForever(self::ALL_VIA_VENDORS, function () {
            return self::toBase()->where('status',1)->get();
        });
    }

    public static function flushCache(){
        Cache::forget(self::ALL_VIA_VENDORS);
    }


    public static function boot(){
        parent::boot();

        static::updated(function () {
            self::flushCache();
        });

        static::created(function() {
            self::flushCache();
        });

        static::deleted(function() {
            self::flushCache();
        });
    }
    /***********************************
    * * *  Begin :: Cache Data * * *
    ************************************/
}
