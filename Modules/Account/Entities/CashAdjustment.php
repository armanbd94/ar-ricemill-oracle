<?php

namespace Modules\Account\Entities;

use App\Models\BaseModel;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Modules\Account\Entities\ChartOfAccount;

class CashAdjustment extends BaseModel
{
    protected $table = 'transactions';
    protected const VOUCHER_PREFIX = 'CHV';
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
    protected $_account_id; 

    //methods to set custom search property value
    public function setStartDate($start_date)
    {
        $this->_start_date = $start_date;
    }
    public function setEndDate($end_date)
    {
        $this->_end_date = $end_date;
    }
    public function setAccountID($account_id)
    {
        $this->_account_id = $account_id;
    }

    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)

        $this->column_order = ['t.id','t.voucher_date','t.voucher_no', 't.chart_of_account_id','t.description','t.debit','t.credit','t.approve', 't.created_by',null];
        
        
        $query = DB::table('transactions as t')
        ->leftjoin('chart_of_accounts as c','t.chart_of_account_id','=','c.id')
        ->selectRaw("t.*,c.name as account_name")
        ->where('t.voucher_type',self::VOUCHER_PREFIX);
        //search query
        if (!empty($this->_start_date)) {
            $query->where('t.voucher_date', '>=',$this->_start_date);
        }
        if (!empty($this->_end_date)) {
            $query->where('t.voucher_date', '<=',$this->_end_date);
        }
        if (!empty($this->_account_id)) {
            $query->where('t.chart_of_account_id', $this->_account_id);
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
        return DB::table('transactions as t')
        ->leftjoin('chart_of_accounts as c','t.chart_of_account_id','=','c.id')
        ->selectRaw("t.*,c.name as account_name")
        ->where('t.voucher_type',self::VOUCHER_PREFIX)
        ->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/

    protected static function transaction_data(object $data) : array 
    {
        $transaction_data =  [
            'chart_of_account_id' => $data->account_id,
            'voucher_no'          => $data->voucher_no,
            'voucher_type'        => self::VOUCHER_PREFIX,
            'voucher_date'        => $data->voucher_date,
            'description'         => $data->remarks,
            'debit'               => ($data->type == 'debit') ? $data->amount : 0,
            'credit'              => ($data->type == 'credit') ? $data->amount : 0,
            'posted'              => 1,
            'approve'             => 3,

        ];

        if(empty($data->update_id))
        {
            $transaction_data['created_by'] = auth()->user()->name;
            $transaction_data['created_at'] = date('Y-m-d H:i:s');
        }else{
            $transaction_data['modified_by'] = auth()->user()->name;
            $transaction_data['updated_at'] = date('Y-m-d H:i:s');
        }
        return $transaction_data;
    }
}
