<?php

namespace Modules\Expense\Entities;

use App\Models\BaseModel;
use Modules\Setting\Entities\Warehouse;
use Modules\Expense\Entities\ExpenseItem;
use Modules\Account\Entities\ChartOfAccount;


class ExpenseStatement extends BaseModel
{
    protected $table = 'expenses';
    protected $fillable = ['expense_item_id', 'voucher_no', 'amount', 'date', 'payment_type', 'account_id', 'remarks', 'created_by', 'modified_by'];

    public function expense_item()
    {
        return $this->belongsTo(ExpenseItem::class);
    }
    
    public function coa()
    {
        return $this->belongsTo(ChartOfAccount::class,'account_id','id');
    }
    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $_expense_item_id; 
    protected $_start_date; 
    protected $_end_date; 

    //methods to set custom search property value
    public function setExpenseItemID($expense_item_id)
    {
        $this->_expense_item_id = $expense_item_id;
    }

    public function setStartDate($start_date)
    {
        $this->_start_date = $start_date;
    }
    public function setEndDate($end_date)
    {
        $this->_end_date = $end_date;
    }

    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        if (permission('expense-bulk-delete')){
            $this->column_order = [null,'id','date','expense_item_id','remarks','payment_type','account_id','amount',null];
        }else{
            $this->column_order = ['id','date','expense_item_id','remarks','payment_type','account_id','amount',null];
        }
        
        $query = self::with('expense_item','coa');

        //search query
        if (!empty($this->_expense_item_id)) {
            $query->where('expense_item_id', $this->_expense_item_id);
        }

        if (!empty($this->_start_date)) {
            $query->where('date', '>=',$this->_start_date);
        }
        if (!empty($this->_end_date)) {
            $query->where('date', '<=',$this->_end_date);
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
}
