<?php

namespace Modules\Loan\Entities;

use App\Models\BaseModel;
use Modules\HRM\Entities\Employee;
use Illuminate\Support\Facades\Cache;
use Modules\Loan\Entities\LoanPeople;
use Illuminate\Database\Eloquent\Model;

class Loans extends BaseModel
{
    protected $fillable = ['voucher_no','employee_id','person_id','amount','adjust_amount','purpose','total_adjusted_amount','month_year','status_changed_by','adjusted_date','loan_type','payment_method','account_id','loan_status','status', 'deletable', 'created_by', 'modified_by'];

    public function personDetails()
    {
        return $this->belongsTo(LoanPeople::class,'person_id','id');
    }
    public function employeeDetails()
    {
        return $this->belongsTo(Employee::class,'employee_id','id');
    }

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $_person_id; 
    protected $_loan_type; 

    //methods to set custom search property value
    public function setPerson($person_id)
    {
        $this->_person_id = $person_id;
    }
    public function setEmployee($employee_id)
    {
        $this->_employee_id = $employee_id;
    }
    public function setLoanType($loan_type)
    {
        $this->_loan_type = $loan_type;
    }


    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        if (permission('personal-loan-bulk-delete')){
            $this->column_order = [null,'voucher_no','employee_id','person_id','amount','adjust_amount','purpose','total_adjusted_amount','month_year','status_changed_by','adjusted_date','loan_type','payment_method','account_id','status',null];
        }else{
            $this->column_order = ['id','voucher_no','employee_id','person_id','amount','adjust_amount','purpose','total_adjusted_amount','month_year','status_changed_by','adjusted_date','loan_type','payment_method','account_id','status', null];
        }
        
        $query = self::where('loan_type',$this->_loan_type)->with('employeeDetails','personDetails');

        //search query
        if (!empty($this->_person_id)) {
            //$query->where('name', 'like', '%' . $this->_name . '%');
            $query->where('person_id',$this->_person_id);
        }
        //search query
        if (!empty($this->_employee_id)) {
            //$query->where('name', 'like', '%' . $this->_name . '%');
            $query->where('employee_id',$this->_employee_id);
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
    protected const ALL_PERSONAL_LOAN   = '_personal_loan';
    protected const ACTIVE_PERSONAL_LOAN    = '_active_personal_loan';

    public static function allPersonalLoan(){
        return Cache::rememberForever(self::ALL_PERSONAL_LOAN, function () {
            return self::toBase()->get();
        });
    }
    public static function activePersonalLoan(){
        return Cache::rememberForever(self::ACTIVE_PERSONAL_LOAN, function () {
            return self::toBase()->where('status',1)->get();
        });
    }


    public static function flushCache(){
        Cache::forget(self::ALL_PERSONAL_LOAN);
        Cache::forget(self::ACTIVE_PERSONAL_LOAN);
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
