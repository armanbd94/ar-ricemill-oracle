<?php

namespace Modules\HRM\Entities;

use App\Models\BaseModel;
use Modules\HRM\Entities\Employee;
use Illuminate\Support\Facades\Cache;
use Modules\HRM\Entities\AllowanceDeduction;

class AllowanceDeductionManage extends BaseModel
{

    protected $fillable = ['allowance_deduction_id','employee_id','type','basic_salary','percentage','amount','status', 'deletable', 'created_by', 'modified_by'];

    public function employee()
    {
        return $this->belongsTo(Employee::class,'employee_id','id');
    }
    public function allowance()
    {
        return $this->belongsTo(AllowanceDeduction::class,'allowance_deduction_id','id');
    }

    public function allowances()
    {
        return AllowanceDeduction::toBase()->where('status', 1)->where('type', 1)->get();
        //return $this->belongsTo(AllowanceDeduction::class);
    }
    public function deducts()
    {
        return AllowanceDeduction::toBase()->where('status', 1)->where('type', 2)->get();
        //return $this->belongsTo(AllowanceDeduction::class);
    }
    public function allowancededucts()
    {
        return AllowanceDeduction::toBase()->where('status', 1)->get();
    }
    
    protected $_employee_id; 
    protected $_allownace_id; 
    protected $_deduction_id; 

    public function setEmployeeID($employee_id)
    {
        $this->_employee_id = $employee_id;
    }
    public function setAllowanceID($allownace_id)
    {
        $this->_allownace_id = $allownace_id;
    }
    public function setDeductID($deduction_id)
    {
        $this->_deduction_id = $deduction_id;
    }

    
    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        if (permission('salary-setup-delete')){
            $this->column_order = [null,'id','allowance_deduction_id','employee_id','type','basic_salary','percentage','amount','status',null];
        }else{
            $this->column_order = ['id','allowance_deduction_id','employee_id','type','basic_salary','percentage','amount','status',null];
        }
        
        $query = self::with(['employee','allowance']);

        //search query
        if (!empty($this->_employee_id)) {
            $query->where('employee_id ',  $this->_employee_id);
        }
        if (!empty($this->_allownace_id)) {
            $query->where('allowance_deduction_id ',  $this->_allownace_id);
        }
        if (!empty($this->_deduction_id)) {
            $query->where('allowance_deduction_id ',  $this->_deduction_id);
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
    protected const ALL_ALLOWANCES_DEDUCTION_MANAGE    = '_allowances_deduction_manage';
    protected const ACTIVE_ALLOWANCES_DEDUCTION_MANAGE    = '_active_allowances_deduction_manage';

    public static function allAllounceDeductsManage(){
        return Cache::rememberForever(self::ALL_ALLOWANCES_DEDUCTION_MANAGE, function () {
            return self::toBase()->get();
        });
    }
    public static function activeAllowances(){
        return Cache::rememberForever(self::ACTIVE_ALLOWANCES_DEDUCTION_MANAGE, function () {
            return self::toBase()->where('type',1)->where('status',1)->get();
        });
    }
    public static function activeDeducts(){
        return Cache::rememberForever(self::ACTIVE_ALLOWANCES_DEDUCTION_MANAGE, function () {
            return self::toBase()->where('type',2)->where('status',1)->get();
        });
    }


    public static function flushCache(){
        Cache::forget(self::ALL_ALLOWANCES_DEDUCTION_MANAGE);
        Cache::forget(self::ACTIVE_ALLOWANCES_DEDUCTION_MANAGE);
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
