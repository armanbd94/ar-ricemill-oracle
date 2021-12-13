<?php

namespace Modules\Loan\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Modules\Account\Entities\ChartOfAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoanReport extends BaseModel
{
    protected $table = 'transactions';
    protected $fillable = ['chart_of_account_id','voucher_no', 'voucher_type', 'voucher_date', 'description', 'debit', 
    'credit', 'is_opening','posted', 'approve', 'created_by', 'modified_by'];

    public function coa()
    {
        return $this->belongsTo(ChartOfAccount::class,'chart_of_account_id','id');
    }
    

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    protected $order = ['t.id' => 'desc'];
    protected $_start_date; 
    protected $_end_date; 
    protected $_employee_id; 
    protected $_person_id; 

    //methods to set custom search property value
    public function setStartDate($start_date)
    {
        $this->_start_date = $start_date;
    }
    public function setEndDate($end_date)
    {
        $this->_end_date = $end_date;
    }
    public function setEmployee($employee_id)
    {
        $this->_employee_id = $employee_id;
    }
    public function setPerson($person_id)
    {
        $this->_person_id = $person_id;
    }

    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)

        $this->column_order = ['t.id', 't.voucher_no','t.voucher_date','t.descritpion','t.debit','t.credit','t.approve', 't.created_by',null];
        
        $query = DB::table('transactions as t')
        ->select("t.*,sum(t.credit) as credit,sum(t.debit) as debit,name as cname")        
        ->leftJoin('chart_of_accounts', 't.chart_of_account_id', '=', 'chart_of_accounts.id')
        ->whereIn('t.voucher_type',['PL','PLI','OL','EMPSALOLI']);
        //search query
        if (!empty($this->_start_date)) {
            $query->where('t.voucher_date', '>=',$this->_start_date);
        }
        if (!empty($this->_end_date)) {
            $query->where('t.voucher_date', '<=',$this->_end_date);
        }
        if (!empty($this->_employee_id)) {
            $query->where('t.chart_of_account_id', '=',$this->_employee_id);
        }
        if (!empty($this->_person_id)) {
            $query->where('t.chart_of_account_id', '=',$this->_person_id);
        }
        
        $query->groupBy('t.voucher_no');

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
        $query =  DB::table('transactions as t')
        ->select("t.*,sum(t.credit) as credit,sum(t.debit) as debit") 
        ->leftJoin('chart_of_accounts', 't.chart_of_account_id', '=', 'chart_of_accounts.id')
        ->whereIn('t.voucher_type',['PL','PLI','OL','EMPSALOLI'])
        ->groupBy('t.voucher_no');
        if (!empty($this->_start_date)) {
            $query->where('t.voucher_date', '>=',$this->_start_date);
        }
        if (!empty($this->_end_date)) {
            $query->where('t.voucher_date', '<=',$this->_end_date);
        }
        if (!empty($this->_chart_of_account_id)) {
            $query->where('t.chart_of_account_id', '=',$this->_chart_of_account_id);
        }

        return $query->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
}
