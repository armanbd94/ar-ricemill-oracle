<?php

namespace Modules\HRM\Entities;

use DateTime;
use DatePeriod;
use DateInterval;
use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AttendanceReport extends BaseModel
{
    protected $table = 'attendances';
    protected $fillable = ['employee_id ','date_time','date', 'time', 'am_pm', 'time_str', 'time_str_am_pm', 'status','deletable ', 'created_by', 'modified_by'];

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $order = ['date' => 'desc'];
    protected $start_date; 
    protected $end_date; 
    protected $employee_id; 

    //methods to set custom search property value

    public function setEmployeeID($employee_id)
    {
        $this->employee_id = $employee_id;
    }
    public function setStartDate($start_date)
    {
        $this->start_date = $start_date;
    }
    public function setEndDate($end_date)
    {
        $this->end_date = $end_date;
    }    

    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)

        $this->column_order = ['id','date', 'date_time','employee_id','time','am_pm','time_str','time_str_am_pm'];
                

        $latestPosts = DB::table('attendances')
        ->selectRaw('employee_id,MIN(id) as min_id,
        MAX(id) as max_id,MIN(time_str_am_pm) as in_time_str,MAX(time_str_am_pm) as out_time_str,
            MIN(time) as in_time,MAX(time) as out_time,date_time as date_time,date as date,time as time,am_pm as am_pm,time_str as time_str,time_str_am_pm as time_str_am_pm,deletable')
        ->where('date', '=',date('Y-m-d'))
        ->groupBy('employee_id');

        $query = DB::table('employees')
        ->selectRaw("employees.*,attendance.*, d.name as dname, de.name as dename")
        ->leftjoin('designations as d','employees.id','=','d.id')
        ->leftjoin('departments as de','employees.id','=','de.id')
        ->leftjoinSub($latestPosts, 'attendance', function ($join) {
            $join->on('employees.id', '=', 'attendance.employee_id');
        });

         if (!empty($this->employee_id)) {
             $query->where('employee_id',$this->employee_id);
         }
        //  if (!empty($this->start_date)) {
        //      $query->where('date', '>=',$this->start_date);
        //  }
        //  if (!empty($this->end_date)) {
        //      $query->where('date', '<=',$this->end_date);
        //  }

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
        $latestPosts = DB::table('attendances')
        ->selectRaw('employee_id,MIN(id) as min_id,
        MAX(id) as max_id,MIN(time_str_am_pm) as in_time_str,MAX(time_str_am_pm) as out_time_str,
            MIN(time) as in_time,MAX(time) as out_time,date_time as date_time,date as date,time as time,am_pm as am_pm,time_str as time_str,time_str_am_pm as time_str_am_pm,deletable ')
        ->where('date', '=',date('Y-m-d'))
        ->groupBy('employee_id');

        return DB::table('employees')
        ->leftjoinSub($latestPosts, 'attendance', function ($join) {
        $join->on('employees.id', '=', 'attendance.employee_id');
        })->get()->count();

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
    protected const ALL_ATTENDANCES    = '_attandences';
    protected const ACTIVE_ATTENDANCES    = '_active_attandences';

    // public static function allDepartments(){
    //     return Cache::rememberForever(self::ALL_DEPARTMENTS, function () {
    //         return self::toBase()->get();
    //     });
    // }
    // public static function activeDepartments(){
    //     return Cache::rememberForever(self::ACTIVE_DEPARTMENTS, function () {
    //         return self::toBase()->where('status',1)->get();
    //     });
    // }


    public static function flushCache(){
        Cache::forget(self::ALL_ATTENDANCES);
        Cache::forget(self::ACTIVE_ATTENDANCES);
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

    public static function getDatesFromRange($start, $end, $current_date, $format = 'Y-m-d')
    {
        $array = array();
        $interval = new DateInterval('P1D');

        $realEnd = new DateTime($end);
        $realEnd->add($interval);

        $period = new DatePeriod(new DateTime($start), $interval, $realEnd);

        foreach ($period as $date) {
            $array[] = $date->format($format);
        }

        return in_array($current_date, $array);
    }

    public static function getAnyRowInfos($table, $col, $id, $col2 = null, $id2 = null, $col3 = null, $id3 = null){
        
        $query = \DB::table($table);
        
        if(!empty($col)){
            $query = $query->where($col, $id);
        }
        if(!empty($col2)){
            $query = $query->where($col2, $id2);
        }
        if(!empty($col3)){
            $query = $query->where($col3, $id3);
        }

        $result = $query->first();
        return $result;
    }
}
