<?php

namespace Modules\HRM\Entities;

use App\Models\BaseModel;
use Modules\HRM\Entities\Leave;
use Modules\HRM\Entities\Employee;
use Illuminate\Support\Facades\Cache;

class LeaveApplicationManage extends BaseModel
{
    protected $fillable = ['leave_id','employee_id','start_date','end_date','alternative_employee','number_leave','leave_type','employee_location','purpose','comments','submission','status', 'deletable', 'created_by', 'modified_by'];

    public function leaves()
    {
        return $this->belongsTo(Leave::class,'leave_id','id');
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class,'employee_id','id');
    }

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $_employee_id; 
    protected $_leave_id; 
    protected $_submission; 
    protected $_status; 

    //methods to set custom search property value
    
    public function setEmployeeID($employee_id)
    {
        $this->_employee_id = $employee_id;
    }
    public function setLeaveID($leave_id)
    {
        $this->_leave_id = $leave_id;
    }
    public function setSubmissionID($submission)
    {
        $this->_submission = $submission;
    }
    public function setStatus($status)
    {
        $this->_status = $status;
    }

    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        if (permission('leave-application-bulk-delete')){
            $this->column_order = [null,'id','leave_id','employee_id','start_date','end_date','alternative_employee','number_leave','leave_type','employee_location','purpose','comments','submission','status',null];
        }else{
            $this->column_order = ['id','leave_id','employee_id','start_date','end_date','alternative_employee','number_leave','leave_type','employee_location','purpose','comments','submission','status',null];
        }
        
        $query = self::with(['leaves','employee']);

        //search query
        
        if (!empty($this->_employee_id)) {
            $query->where('_employee_id',  $this->_employee_id);
        }
        if (!empty($this->_leave_id)) {
            $query->where('leave_id',  $this->_leave_id);
        }
        if (!empty($this->_submission)) {
            $query->where('job_status',  $this->_submission);
        }
        if (!empty($this->_status)) {
            $query->where('status',  $this->_status);
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

    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/

    /*************************************
    * * *  Begin :: Cache Data * * *
    **************************************/
    protected const ALL_MANAGE_LEAVES    = '_manage_leaves';
    protected const ACTIVE_MANAGE_LEAVES    = '_active_manage_leaves';

    public static function allLeaves(){
        return Cache::rememberForever(self::ALL_MANAGE_LEAVES, function () {
            return self::toBase()->get();
        });
    }
    public static function activeLeaves(){
        return Cache::rememberForever(self::ACTIVE_MANAGE_LEAVES, function () {
            return self::toBase()->where('status',1)->get();
        });
    }


    public static function flushCache(){
        Cache::forget(self::ALL_MANAGE_LEAVES);
        Cache::forget(self::ACTIVE_MANAGE_LEAVES);
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
