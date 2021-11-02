<?php

namespace Modules\Account\Entities;

use App\Models\BaseModel;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Modules\Account\Entities\ChartOfAccount;

class CashAdjustment extends BaseModel
{
    protected $table = 'transactions';
    protected $fillable = ['chart_of_account_id','warehouse_id','voucher_no', 'voucher_type', 'voucher_date', 'description', 'debit', 
    'credit', 'is_opening','posted', 'approve', 'created_by', 'modified_by'];

    public function coa()
    {
        return $this->belongsTo(ChartOfAccount::class,'chart_of_account_id','id');
    }
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class,'warehouse_id','id');
    }
        /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    protected $order = ['t.id' => 'desc'];
    protected $_start_date; 
    protected $_end_date; 
    protected $_warehouse_id; 

    //methods to set custom search property value
    public function setStartDate($start_date)
    {
        $this->_start_date = $start_date;
    }
    public function setEndDate($end_date)
    {
        $this->_end_date = $end_date;
    }
    public function setWarehouseID($warehouse_id)
    {
        $this->_warehouse_id = $warehouse_id;
    }

    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)

        $this->column_order = ['t.id', 't.warehouse_id','t.voucher_no','t.voucher_date','t.description','t.debit','t.credit','t.approve', 't.created_by',null];
        
        
        $query = DB::table('transactions as t')
        ->leftjoin('warehouses as w','t.warehouse_id','=','w.id')
        ->selectRaw("t.*,w.name as warehouse_name")
        ->where('t.voucher_type','CHV');
        //search query
        if (!empty($this->_start_date)) {
            $query->where('t.voucher_date', '>=',$this->_start_date);
        }
        if (!empty($this->_end_date)) {
            $query->where('t.voucher_date', '<=',$this->_end_date);
        }
        if (!empty($this->_warehouse_id)) {
            $query->where('t.warehouse_id', $this->_warehouse_id);
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
        $query =  DB::table('transactions as t')
        ->selectRaw("t.*")
        ->where('t.voucher_type','CHV');
        if (!empty($this->_start_date)) {
            $query->where('t.voucher_date', '>=',$this->_start_date);
        }
        if (!empty($this->_end_date)) {
            $query->where('t.voucher_date', '<=',$this->_end_date);
        }
        if (!empty($this->_warehouse_id)) {
            $query->where('t.warehouse_id', $this->_warehouse_id);
        }

        return $query->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
}
