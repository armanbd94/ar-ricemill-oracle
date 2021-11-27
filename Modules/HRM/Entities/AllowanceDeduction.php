<?php

namespace Modules\HRM\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\Cache;

class AllowanceDeduction extends BaseModel
{
    protected $fillable = ['name','short_name','type','status', 'deletable', 'created_by', 'modified_by'];

     /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $_name; 

    //methods to set custom search property value
    public function setName($name)
    {
        $this->_name = $name;
    }


    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        if (permission('benifits-delete')){
            $this->column_order = [null,'id','name','short_name','type','status',null];
        }else{
            $this->column_order = ['id','name','short_name','type','status',null];
        }
        
        $query = self::toBase();

        //search query
        if (!empty($this->_name)) {
            $query->where('name', 'like', '%' . $this->_name . '%');
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
        return self::toBase()->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/

    /*************************************
    * * *  Begin :: Cache Data * * *
    **************************************/
    protected const ALL_ALLOWANCES_DEDUCTION    = '_allowances_deduction';
    protected const ACTIVE_ALLOWANCES_DEDUCTION    = '_active_allowances_deduction';

    public static function allAllounceDeducts(){
        return Cache::rememberForever(self::ALL_ALLOWANCES_DEDUCTION, function () {
            return self::toBase()->get();
        });
    }
    public static function activeAllowances(){
        return Cache::rememberForever(self::ACTIVE_ALLOWANCES_DEDUCTION, function () {
            return self::toBase()->where('status',1)->get();
        });
    }
    public static function activeDeducts(){
        return Cache::rememberForever(self::ACTIVE_ALLOWANCES_DEDUCTION, function () {
            return self::toBase()->where('status',1)->get();
        });
    }


    public static function flushCache(){
        Cache::forget(self::ALL_ALLOWANCES_DEDUCTION);
        Cache::forget(self::ACTIVE_ALLOWANCES_DEDUCTION);
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
