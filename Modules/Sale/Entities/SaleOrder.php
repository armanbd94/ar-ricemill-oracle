<?php

namespace Modules\Sale\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use Modules\Customer\Entities\Customer;
use Modules\ViaCustomer\Entities\ViaCustomer;

class SaleOrder extends BaseModel
{
    protected $fillable = [
        'memo_no','customer_id','via_customer_id','so_no','item','total_qty', 'grand_total','order_date','delivery_date','shipping_address','created_by','modified_by','order_status'
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class,'sale_order_products','sale_id','product_id','id','id')
        ->withPivot('id','item_class_id', 'qty','net_unit_price','total','description')
        ->withTimeStamps(); 
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class,'customer_id','id');
    }
    public function via_customer()
    {
        return $this->belongsTo(ViaCustomer::class,'via_customer_id','id')->withDefault(['name'=>'','trade_name'=>'','code'=>'']);
    }
    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    protected $order = ['sr.id' => 'desc'];
    //custom search column property
    protected $_memo_no; 
    protected $_customer_id; 
    protected $_from_order_date; 
    protected $_to_order_date; 
    protected $_from_delivery_date; 
    protected $_to_delivery_date; 

    //methods to set custom search property value
    public function setMemoNo($memo_no)
    {
        $this->_memo_no = $memo_no;
    }
    public function setCustomerID($customer_id)
    {
        $this->_customer_id = $customer_id;
    }

    public function setFromOrderDate($from_order_date)
    {
        $this->_from_order_date = $from_order_date;
    }

    public function setToOrderDate($to_order_date)
    {
        $this->_to_order_date = $to_order_date;
    }

    public function setFromDeliveryDate($from_delivery_date)
    {
        $this->_from_delivery_date = $from_delivery_date;
    }

    public function setToDeliveryDate($to_delivery_date)
    {
        $this->_to_delivery_date = $to_delivery_date;
    }

    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        if (permission('sale-order-bulk-delete')){
            $this->column_order = ['sr.id', 'sr.id', 'sr.memo_no','sr.so_no','sr.customer_id','sr.via_customer_id', 'sr.item','sr.total_qty','sr.grand_total','sr.order_date','sr.delivery_date','sr.created_by', null];
        }else{
            $this->column_order = ['sr.id', 'sr.memo_no','sr.so_no','sr.customer_id','sr.via_customer_id', 'sr.item','sr.total_qty','sr.grand_total','sr.order_date','sr.delivery_date','sr.created_by', null];
        }
        
        $query = DB::table('sale_orders as sr')
        ->leftJoin('customers as c','sr.customer_id','=','c.id')
        ->leftJoin('via_customers as vc','sr.via_customer_id','=','vc.id')
        ->select('sr.id','sr.memo_no','sr.so_no','c.name as customer_name','vc.name as via_customer_name','sr.item',
        'sr.total_qty','sr.grand_total','sr.order_date','sr.delivery_date','sr.created_by');


        if (!empty($this->_memo_no)) {
            $query->where('sr.memo_no', 'like', '%' . $this->_memo_no . '%');
        }

        if (!empty($this->_customer_id)) {
            $query->where('sr.customer_id', $this->_customer_id);
        }

        if (!empty($this->_from_order_date)) {
            $query->where('sr.order_date', '>=',$this->_from_order_date);
        }
        
        if (!empty($this->_to_order_date)) {
            $query->where('sr.order_date', '<=',$this->_to_order_date);
        }

        if (!empty($this->_from_delivery_date)) {
            $query->where('sr.delivery_date', '>=',$this->_from_delivery_date);
        }
        
        if (!empty($this->_to_delivery_date)) {
            $query->where('sr.delivery_date', '<=',$this->_to_delivery_date);
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
        return DB::table('sale_orders')->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
}
