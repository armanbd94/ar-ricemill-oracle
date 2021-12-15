<?php

namespace Modules\Sale\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Modules\Account\Entities\ChartOfAccount;

class CashSale extends BaseModel
{
    protected $fillable = [
        'memo_no','customer_name','do_number','account_id','item','total_qty', 'grand_total','sale_date','delivery_date','created_by','modified_by',
    ];

    public function product()
    {
        return $this->belongsToMany(Product::class,'cash_sale_products','sale_id','product_id','id','id')
        ->withPivot('id', 'site_id', 'location_id','qty','net_unit_price','total','description')
        ->withTimeStamps(); 
    }

    public function coa()
    {
        return $this->belongsTo(ChartOfAccount::class,'account_id','id');
    }
    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    protected $order = ['cs.id' => 'desc'];
    //custom search column property
    protected $_memo_no; 
    protected $_from_date; 
    protected $_to_date; 

    //methods to set custom search property value
    public function setMemoNo($memo_no)
    {
        $this->_memo_no = $memo_no;
    }

    public function setFromDate($from_date)
    {
        $this->_from_date = $from_date;
    }

    public function setToDate($to_date)
    {
        $this->_to_date = $to_date;
    }

    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        if (permission('cash-sale-bulk-delete')){
            $this->column_order = ['cs.id', 'cs.id', 'cs.memo_no','cs.customer_name', 'cs.item','cs.total_qty','cs.grand_total','cs.account_id','cs.sale_date','cs.delivery_date','cs.created_by', null];
        }else{
            $this->column_order = ['cs.id', 'cs.memo_no','cs.customer_name', 'cs.item','cs.total_qty','cs.grand_total','cs.account_id','cs.sale_date','cs.delivery_date','cs.created_by', null];
        }
        
        $query = DB::table('cash_sales as cs')
        ->leftJoin('chart_of_accounts as coa','cs.account_id','=','coa.id')
        ->select('cs.id','cs.memo_no','cs.customer_name','cs.account_id', 'coa.name as account_name','cs.item',
        'cs.total_qty','cs.grand_total','cs.sale_date','cs.created_by');


        if (!empty($this->_memo_no)) {
            $query->where('cs.memo_no', 'like', '%' . $this->_memo_no . '%');
        }

        if (!empty($this->_from_date)) {
            $query->where('cs.sale_date', '>=',$this->_from_date);
        }

        if (!empty($this->_to_date)) {
            $query->where('cs.sale_date', '<=',$this->_to_date);
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
        return DB::table('cash_sales')->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/

    public function transaction_data(array $data) : array
    {

        //Inventory Debit
        $inventory = array(
            'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', '10101')->value('id'),
            'voucher_no'          => $data['challan_no'],
            'voucher_type'        => 'Purchase',
            'voucher_date'        => $data['receive_date'],
            'description'         => 'Inventory debit '.$data['grand_total'].'Tk for material purchase from vendor '.$data['vendor_name'],
            'debit'               => $data['grand_total'],
            'credit'              => 0,
            'posted'              => 1,
            'approve'             => 1,
            'created_by'          => auth()->user()->name,
            'created_at'          => date('Y-m-d H:i:s')
        ); 

         // Expense for company
        $expense = array(
            'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', '402')->value('id'),
            'voucher_no'          => $data['challan_no'],
            'voucher_type'        => 'Purchase',
            'voucher_date'        => $data['receive_date'],
            'description'         => 'Company expense '.$data['grand_total'].'Tk for material purchase from vendor '.$data['vendor_name'],
            'debit'               => $data['grand_total'],
            'credit'              => 0,
            'posted'              => 1,
            'approve'             => 1,
            'created_by'          => auth()->user()->name,
            'created_at'          => date('Y-m-d H:i:s')
        ); 

        $payment = array(
            'chart_of_account_id' => $data['account_id'],
            'voucher_no'          => $data['challan_no'],
            'voucher_type'        => 'Purchase',
            'voucher_date'        => $data['receive_date'],
            'description'         => 'Cash '.$data['grand_total'].'Tk given for material purchase',
            'debit'               => 0,
            'credit'              => $data['grand_total'],
            'posted'              => 1,
            'approve'             => 1,
            'created_by'          => auth()->user()->name,
            'created_at'          => date('Y-m-d H:i:s')
            
        );

        return [$inventory,$expense,$payment];
    } 
}
