<?php

namespace Modules\HRM\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\Cache;

class Division extends BaseModel
{
    protected $fillable = ['name','department_id','status', 'deletable', 'created_by', 'modified_by'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    
    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $_name; 
    protected $_department_id; 

    //methods to set custom search property value
    public function setName($name)
    {
        $this->_name = $name;
    }
    public function setDepartmentID($department_id)
    {
        $this->_department_id = $department_id;
    }


    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        if (permission('division-bulk-delete')){
            $this->column_order = [null,'id','name','department_id','status',null];
        }else{
            $this->column_order = ['id','name','department_id','status',null];
        }
        
        $query = self::with('department');

        //search query
        if (!empty($this->_name)) {
            $query->where('name', 'like', '%' . $this->_name . '%');
        }
        if (!empty($this->_department_id)) {
            $query->where('department_id', $this->_department_id);
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
    protected const ALL_DIVISIONS    = '_divisions';
    protected const ACTIVE_DIVISIONS    = '_active_divisions';

    public static function allDivisions(){
        return Cache::rememberForever(self::ALL_DIVISIONS, function () {
            return self::toBase()->get();
        });
    }
    public static function activeDivisions(){
        return Cache::rememberForever(self::ACTIVE_DIVISIONS, function () {
            return self::toBase()->where('status',1)->get();
        });
    }


    public static function flushCache(){
        Cache::forget(self::ALL_DIVISIONS);
        Cache::forget(self::ACTIVE_DIVISIONS);
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
