<?php

namespace Modules\Setting\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class Batch extends BaseModel
{
    protected $fillable = ['batch_start_date','batch_no','status','created_at','modified_at'];

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $_batch_start_date; 
    protected $_batch_no; 

    //methods to set custom search property value
    public function setBatchStartDate($batch_start_date)
    {
        $this->_batch_start_date = $batch_start_date;
    }
    public function setBatchNo($batch_no)
    {
        $this->_batch_no = $batch_no;
    }


    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        if (permission('wip-batch-bulk-delete')){
            $this->column_order = [null,'id','batch_start_date','batch_no','status','created_by','modified_by','created_at','updated_at',null];
        }else{
            $this->column_order = ['id','batch_start_date','batch_no','status','created_by','modified_by','created_at','updated_at',null];
        }
        
        $query = DB::table('batches');

        //search query
        if (!empty($this->_batch_no)) {
            $query->where('batch_no', 'like', '%' . $this->_batch_no . '%');
        }
        if (!empty($this->_batch_start_date)) {
            $query->where('batch_start_date',  date('Y-m-d',strtotime($this->_batch_start_date)));
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
        return DB::table('batches')->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/

    /*************************************
    * * *  Begin :: Cache Data * * *
    **************************************/
    protected const ALL_BATCHES    = '_batches';

    public static function allBatches(){
        return Cache::rememberForever(self::ALL_BATCHES, function () {
            return DB::table('batches')->where('status',1)->get();
        });
    }


    public static function flushCache(){
        Cache::forget(self::ALL_BATCHES);
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
