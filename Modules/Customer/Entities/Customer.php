<?php

namespace Modules\Customer\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Modules\Account\Entities\Transaction;
use Modules\Account\Entities\ChartOfAccount;

class Customer extends BaseModel
{
    protected $fillable = [ 'code', 'name','trade_name', 'mobile', 'email', 'address', 'status', 'created_by', 'modified_by'];


    public function coa(){
        return $this->hasOne(ChartOfAccount::class,'customer_id','id');
    }
    
    public function previous_balance()
    {
        return $this->hasOneThrough(Transaction::class,ChartOfAccount::class,'customer_id','chart_of_account_id','id','id')
        ->where('voucher_type','PR Balance')->withDefault(['debit' => '']);
    }

    public static function customer_balance(int $id)
    {
        $data = DB::table('customers as c')
            ->selectRaw('c.id,b.id as coaid,b.code,((select sum(debit) from transactions where chart_of_account_id= b.id AND approve = 1)-(select sum(credit) from transactions where chart_of_account_id= b.id AND approve = 1)) as balance')
            ->leftjoin('chart_of_accounts as b', 'c.id', '=', 'b.customer_id')
            ->where('c.id',$id)->first();
        $balance = 0;
        if($data)
        {
            $balance = $data->balance ? $data->balance : 0;
        }
        return number_format($balance,2,'.','');
    }
    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $_code; 
    protected $_name; 
    protected $_trade_name; 
    protected $_mobile; 
    protected $_status; 

    //methods to set custom search property value
    public function setCode($code)
    {
        $this->_code = $code;
    }
    public function setName($name)
    {
        $this->_name = $name;
    }
    public function setTradeName($trade_name)
    {
        $this->_trade_name = $trade_name;
    }
    public function setMobile($mobile)
    {
        $this->_mobile = $mobile;
    }

    public function setStatus($status)
    {
        $this->_status = $status;
    }


    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        if(permission('customer-bulk-delete'))
        {
            $this->column_order = ['id', 'id', 'code', 'name', 'trade_name','mobile', 'email', 'address', 'status', null, null];
        }else{
            $this->column_order = ['id', 'code', 'name', 'trade_name','mobile', 'email', 'address', 'status', null, null];
        }
        
        $query = self::toBase();

        //search query
        if (!empty($this->_code)) {
            $query->where('code', 'like', '%' . $this->_code . '%');
        }
        if (!empty($this->_name)) {
            $query->where('name', 'like', '%' . $this->_name . '%');
        }
        if (!empty($this->_trade_name)) {
            $query->where('trade_name', 'like', '%' . $this->_trade_name . '%');
        }
        if (!empty($this->_mobile)) {
            $query->where('mobile', 'like', '%' . $this->_mobile . '%');
        }

        if (!empty($this->_status)) {
            $query->where('status', $this->_status);
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

    /***************************************
     * * * Begin :: Model Local Scope * * *
    ****************************************/
    public function scopeActive($query)
    {
        return $query->where(['status'=>1]);
    }

    public function scopeInactive($query)
    {
        return $query->where(['status'=>2]);
    }
    /***************************************
     * * * Begin :: Model Local Scope * * *
    ****************************************/

    /*************************************
    * * *  Begin :: Cache Data * * *
    **************************************/
    protected const ALL_CUSTOMERS    = '_customers';

    public static function allCustomers(){
        return Cache::rememberForever(self::ALL_CUSTOMERS, function () {
            return self::toBase()->orderBy('id','asc')->get();
        });
    }


    public static function flushCache(){
        Cache::forget(self::ALL_CUSTOMERS);
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


    public function coa_data(string $code,string $head_name,int $customer_id)
    {
        return [
            'code'              => $code,
            'name'              => $head_name,
            'parent_name'       => 'Customer Receivable',
            'level'             => 4,
            'type'              => 'A',
            'is_transaction'    => 1,
            'general_ledger'    => 2,
            'customer_id'       => $customer_id,
            'budget'            => 2,
            'depreciation'      => 2,
            'depreciation_rate' => '0',
            'status'            => 1,
            'created_by'        => auth()->user()->name
        ];
    }

    public function previous_balance_data($balance, int $customer_coa_id, string $customer_name)
    {
        $transaction_id = generator(10);
        $cosdr = array(
            'chart_of_account_id' => $customer_coa_id,
            'voucher_no'          => $transaction_id,
            'voucher_type'        => 'PR Balance',
            'voucher_date'        => date("Y-m-d"),
            'description'         => 'Customer debit for previous balance from '.$customer_name,
            'debit'               => $balance,
            'credit'              => 0,
            'posted'              => 1,
            'approve'             => 1,
            'created_by'          => auth()->user()->name,
            'created_at'          => date('Y-m-d H:i:s')
        );
        $inventory = array(
            'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', '10101')->value('id'),
            'voucher_no'          => $transaction_id,
            'voucher_type'        => 'PR Balance',
            'voucher_date'        => date("Y-m-d"),
            'description'         => 'Inventory credit for old sale from '.$customer_name,
            'debit'               => 0,
            'credit'              => $balance,
            'posted'              => 1,
            'approve'             => 1,
            'created_by'          => auth()->user()->name,
            'created_at'          => date('Y-m-d H:i:s')
        ); 

        return [$cosdr,$inventory];
    }
}
