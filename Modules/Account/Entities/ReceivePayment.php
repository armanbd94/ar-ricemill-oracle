<?php

namespace Modules\Account\Entities;

use stdClass;
use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Modules\Account\Entities\ChartOfAccount;

class ReceivePayment extends BaseModel
{
    protected const VOUCHER_PREFIX = 'CR';

    protected $table = 'transactions';
    
    protected $fillable   = ['chart_of_account_id','voucher_no', 'voucher_type', 'voucher_date', 'description', 'debit', 
    'credit', 'is_opening','posted', 'approve', 'created_by', 'modified_by'];

    public function coa()
    {
        return $this->belongsTo(ChartOfAccount::class,'chart_of_account_id','id');
    }

    protected static function customer_payment(array $data) : object
    {
        $date        = date('Y-m-d H:i:s');
        $created_by  = auth()->user()->name;
        $customer_credit = array(
            'chart_of_account_id' => $data['customer_coa_id'],
            'voucher_no'          => $data['voucher_no'],
            'voucher_type'        => self::VOUCHER_PREFIX,
            'voucher_date'        => $data['voucher_date'],
            'description'         => $data['description'],
            'debit'               => 0,
            'credit'              => $data['amount'],
            'posted'              => 1,
            'approve'             => 1,
            'created_by'          => $created_by,
            'created_at'          => $date
        );
        if($data['payment_type'] == 1){
            //Cah In Hand For Supplier
            $payment = array(
                'chart_of_account_id' => $data['payment_account_id'],
                'voucher_no'          => $data['voucher_no'],
                'voucher_type'        => self::VOUCHER_PREFIX,
                'voucher_date'        => $data['voucher_date'],
                'description'         => $data['description'],
                'debit'               => $data['amount'],
                'credit'              => 0,
                'posted'              => 1,
                'approve'             => 1,
                'created_by'          => $created_by,
                'created_at'          => $date
                
            );
        }else{
            // Bank Ledger
            $payment = array(
                'chart_of_account_id' => $data['payment_account_id'],
                'voucher_no'          => $data['voucher_no'],
                'voucher_type'        => self::VOUCHER_PREFIX,
                'voucher_date'        => $data['voucher_date'],
                'description'         => $data['description'],
                'debit'               => $data['amount'],
                'credit'              => 0,
                'posted'              => 1,
                'approve'             => 1,
                'created_by'          => $created_by,
                'created_at'          => $date
            );
        }

        $transaction = new stdClass();
        $transaction->customer_transaction = $customer_credit;
        $transaction->payment_account_transaction = $payment;
        return $transaction;
    } 


    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    protected $order = ['t.id' => 'desc'];
    protected $_start_date; 
    protected $_end_date; 
    protected $_customer_coa_id; 

    //methods to set custom search property value
    public function setStartDate($start_date)
    {
        $this->_start_date = $start_date;
    }
    public function setEndDate($end_date)
    {
        $this->_end_date = $end_date;
    }
    public function setCustomerCOAID($customer_coa_id)
    {
        $this->_customer_coa_id = $customer_coa_id;
        
    }

    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)

        $this->column_order = ['t.id','t.voucher_date', 't.voucher_no','t.chart_of_account_id','','t.description','t.credit','t.created_by',null];
        
        
        $query = DB::table('transactions as t')
        ->join('chart_of_accounts as coa','t.chart_of_account_id','=','coa.id')
        ->leftJoin('customers as c','coa.customer_id','=','c.id')
        ->selectRaw("t.*,coa.id as coa_id,coa.customer_id,coa.name,c.trade_name")
        ->where('t.voucher_type',self::VOUCHER_PREFIX)
        ->whereNotNull('coa.customer_id');
        //search query
        if (!empty($this->_start_date)) {
            $query->where('t.voucher_date', '>=',$this->_start_date);
        }
        if (!empty($this->_end_date)) {
            $query->where('t.voucher_date', '<=',$this->_end_date);
        }
        if (!empty($this->_customer_coa_id)) {
            
            $query->where('t.chart_of_account_id', $this->_customer_coa_id);
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
        ->join('chart_of_accounts as coa','t.chart_of_account_id','=','coa.id')
        ->selectRaw("t.*,coa.id as coa_id,coa.customer_id,coa.name")
        ->where('t.voucher_type',self::VOUCHER_PREFIX)
        ->whereNotNull('coa.customer_id');

        return $query->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/

}
