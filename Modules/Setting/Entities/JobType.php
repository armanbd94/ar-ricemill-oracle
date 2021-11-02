<?php

namespace Modules\Setting\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;


class JobType extends BaseModel
{
    protected $fillable = ['job_type','status','created_at','modified_at'];

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $_job_type; 

    //methods to set custom search property value
    public function setJobType($job_type)
    {
        $this->_job_type = $job_type;
    }


    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        if (permission('job-type-bulk-delete')){
            $this->column_order = [null,'id','job_type','status','created_by','modified_by','created_at','updated_at',null];
        }else{
            $this->column_order = ['id','job_type','status','created_by','modified_by','created_at','updated_at',null];
        }
        
        $query = DB::table('job_types');

        //search query
        if (!empty($this->_job_type)) {
            $query->where('job_type', 'like', '%' . $this->_job_type . '%');
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
        return DB::table('job_types')->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/

    /*************************************
    * * *  Begin :: Cache Data * * *
    **************************************/
    protected const ALL_JOB_TYPES    = '_job_types';

    public static function allBatches(){
        return Cache::rememberForever(self::ALL_JOB_TYPES, function () {
            return DB::table('job_types')->where('status',1)->get();
        });
    }


    public static function flushCache(){
        Cache::forget(self::ALL_JOB_TYPES);
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
