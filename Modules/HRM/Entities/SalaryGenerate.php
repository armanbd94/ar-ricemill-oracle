<?php

namespace Modules\HRM\Entities;

use DateTime;
use DatePeriod;
use DateInterval;
use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Modules\HRM\Entities\Division;
use Modules\HRM\Entities\Employee;
use Modules\HRM\Entities\Department;
use Modules\HRM\Entities\Designation;
use Modules\HRM\Entities\SalaryGeneratePayment;

class SalaryGenerate extends BaseModel
{
    protected $fillable = ['employee_id','designation_id','department_id','division_id','date', 'salary_month', 'basic_salary', 'allowance_amount', 'deduction_amount', 'absent', 'absent_amount', 'late_count', 'leave', 'leave_amount', 'ot_hour', 'ot_day', 'ot_amount', 'gross_salary', 'add_deduct_amount', 'adjusted_advance_amount', 'adjusted_loan_amount', 'net_salary', 'paid_amount', 'payment_status', 'payment_method', 'status','deletable ', 'created_by', 'modified_by'];

    public function employee_get()
    {
        return $this->belongsTo(Employee::class,'employee_id','id');
    }
    public function salary_payments()
    {
        return $this->hasMany(SalaryGeneratePayment::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class,'department_id','id');
    }
    public function division()
    {
        return $this->belongsTo(Division::class,'division_id','id');
    }
    public function current_designation()
    {
        return $this->belongsTo(Designation::class,'designation_id','id');
    }

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $_salary_month; 
    protected $_from_date; 
    protected $_to_date;  
    protected $_employee_id; 
    protected $_salary_status; 
    protected $_payment_status; 

    //methods to set custom search property value
    public function setSalaryMonth($salary_month)
    {
        $this->_salary_month = $salary_month;
    }

    public function setFromDate($from_date)
    {
        $this->_from_date = $from_date;
    }
    public function setToDate($to_date)
    {
        $this->_to_date = $to_date;
    }
    public function setEmployeeID($employee_id)
    {
        $this->_employee_id = $employee_id;
    }
    public function setSalaryStatus($salary_status)
    {
        $this->_salary_status = $salary_status;
    }
    public function setPaymentStatus($payment_status)
    {
        $this->_payment_status = $payment_status;
    }
    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        if (permission('salary-generate-bulk-delete')){
            $this->column_order = [null,'id','employee_id','department_id','division_id','date', 'salary_month', 'basic_salary', 'allowance_amount', 'deduction_amount', 'absent', 'absent_amount', 'late_count', 'leave', 'leave_amount', 'ot_hour', 'ot_day', 'ot_amount', 'gross_salary', 'add_deduct_amount', 'adjusted_advance_amount', 'adjusted_loan_amount', 'net_salary', 'paid_amount', 'payment_status', 'payment_method', 'status', null];
        }else{
            $this->column_order = ['id','employee_id','department_id','division_id','date', 'salary_month', 'basic_salary', 'allowance_amount', 'deduction_amount', 'absent', 'absent_amount', 'late_count', 'leave', 'leave_amount', 'ot_hour', 'ot_day', 'ot_amount', 'gross_salary', 'add_deduct_amount', 'adjusted_advance_amount', 'adjusted_loan_amount', 'net_salary', 'paid_amount', 'payment_status', 'payment_method', 'status', null];
        }
        
        $query = self::toBase();

        //search query
        if (!empty($this->_salary_month)) {
            //$query->where('salary_month', 'like', '%' . $this->_salary_month . '%');
            $query->where('salary_month', '=',$this->_salary_month);
        }

        if (!empty($this->_from_date)) {
            $query->where('date', '>=',$this->_from_date);
        }
        if (!empty($this->_to_date)) {
            $query->where('date', '<=',$this->_to_date);
        }
        if (!empty($this->_employee_id)) {
            $query->where('employee_id', $this->_employee_id);
        }
        if (!empty($this->_salary_status)) {
            $query->where('salary_status', $this->_salary_status);
        }
        if (!empty($this->_payment_status)) {
            $query->where('payment_status', $this->_payment_status);
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

    public static function get_company_condition()
    {
        $data['absent_condition'] = '2';//basic er 2 gun taka katbe assent e.......
        //$data['absent_condition'] = '3';//basic er 3 gun taka katbe assent e.......
        $data['late_condition'] = '1';//basic er 1 gun taka katbe assent e.......
        $data['casual_leave_condition'] = '1';//basic er 1 gun taka katbe assent e.......

        return $data;
    }

    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/

    

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

    public static function getNumberOfActiveDays(){
        //return 30;
        return 26;
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

    public static function get_current_day_second_in_time($table, $col, $id, $col2 = null, $id2 = null, $col3 = null, $id3 = null){
        
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
        return $result->time;
    }
}
