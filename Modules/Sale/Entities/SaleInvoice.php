<?php

namespace Modules\Sale\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Modules\Sale\Entities\SaleOrder;
use Modules\Product\Entities\Product;

class SaleInvoice extends BaseModel
{
    protected $fillable = [
        'order_id','challan_no','transport_no','truck_fare','terms','driver_mobile_no',
        'item','total_qty','grand_total','invoice_date','created_by','modified_by',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class,'sale_invoice_products','sale_id','product_id','id','id')
        ->withPivot('id', 'site_id', 'location_id','qty','net_unit_price','total','description')
        ->withTimeStamps(); 
    }
    public function order()
    {
        return $this->belongsTo(SaleOrder::class,'order_id','id');
    }

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    protected $order = ['si.id' => 'desc'];
    //custom search column property
    protected $_memo_no; 
    protected $_challan_no; 
    protected $_customer_id; 
    protected $_from_date; 
    protected $_to_date; 

    //methods to set custom search property value
    public function setMemoNo($memo_no)
    {
        $this->_memo_no = $memo_no;
    }
    public function setChallanNo($challan_no)
    {
        $this->_challan_no = $challan_no;
    }
    public function setCustomerID($customer_id)
    {
        $this->_customer_id = $customer_id;
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
        if (permission('sale-invoice-bulk-delete')){
            $this->column_order = ['si.id', 'si.id', 'si.challan_no','si.memo_no','si.customer_id','si.via_customer_id', 'si.item','si.total_qty','si.grand_total','si.invoice_date','si.transport_no','si.truck_fare','si.terms','si.driver_mobile_no','si.created_by', null];
        }else{
            $this->column_order = ['si.id','si.challan_no', 'si.memo_no','si.customer_id','si.via_customer_id', 'si.item','si.total_qty','si.grand_total','si.invoice_date','si.transport_no','si.truck_fare','si.terms','si.driver_mobile_no','si.created_by', null];
        }
        
        $query = DB::table('sale_invoices as si')
        ->leftJoin('sale_orders as sr','si.order_id','=','sr.id')
        ->leftJoin('customers as c','sr.customer_id','=','c.id')
        ->leftJoin('via_customers as vc','sr.via_customer_id','=','vc.id')
        ->select('si.id','si.challan_no','sr.memo_no','c.name as customer_name','vc.name as via_customer_name','si.item',
        'si.total_qty','si.grand_total','si.invoice_date','si.transport_no','si.truck_fare','si.terms','si.driver_mobile_no','si.created_by');


        if (!empty($this->_memo_no)) {
            $query->where('sr.memo_no', 'like', '%' . $this->_memo_no . '%');
        }
        if (!empty($this->_challan_no)) {
            $query->where('si.challan_no', 'like', '%' . $this->_challan_no . '%');
        }

        if (!empty($this->_customer_id)) {
            $query->where('sr.customer_id', $this->_customer_id);
        }

        if (!empty($this->_from_date)) {
            $query->where('si.invoice_date', '>=',$this->_from_date);
        }
        
        if (!empty($this->_to_date)) {
            $query->where('si.invoice_date', '<=',$this->_to_date);
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
        return DB::table('sale_invoices')->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
}
