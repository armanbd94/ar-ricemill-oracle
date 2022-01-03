<?php

namespace Modules\Purchase\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Modules\Vendor\Entities\Vendor;
use Modules\Material\Entities\Material;
use Modules\ViaVendor\Entities\ViaVendor;

class PurchaseOrder extends BaseModel
{
    protected $fillable = [
        'memo_no', 'vendor_id', 'via_vendor_id','item', 'total_qty','grand_total', 'order_date', 'delivery_date', 'po_no', 'nos_truck', 'purchase_status','created_by','modified_by',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function via_vendor()
    {
        return $this->belongsTo(ViaVendor::class)->withDefault(['name'=>'','mobile'=>'']);
    }

    public function  materials()
    {
        return $this->belongsToMany(Material::class,'purchase_order_materials','order_id','material_id','id','id')
        ->withPivot('id', 'qty','item_class_id', 'purchase_unit_id', 'net_unit_cost', 'total', 'description')
        ->withTimeStamps(); 
    }


     /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $_memo_no;  
    protected $_from_date; 
    protected $_to_date; 
    protected $_vendor_id; 
    protected $_purchase_status; 

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
    public function setVendorID($vendor_id)
    {
        $this->_vendor_id = $vendor_id;
    }

    public function setPurchaseStatus($purchase_status)
    {
        $this->_purchase_status = $purchase_status;
    }


    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        if (permission('purchase-order-bulk-delete')){
            $this->column_order = ['id', 'id', 'memo_no', 'vendor_id', 'via_vendor_id', 'item', 'total_qty','grand_total', 'order_date', 'delivery_date', 'po_no', 'nos_truck', 'purchase_status','created_by', null];
        }else{
            $this->column_order = ['id', 'memo_no', 'vendor_id', 'via_vendor_id', 'item', 'total_qty','grand_total', 'order_date', 'delivery_date', 'po_no', 'nos_truck', 'purchase_status','created_by', null];
        }
        
        $query = DB::table('purchase_orders as po')
        ->join('vendors as v','po.vendor_id','=','v.id')
        ->leftJoin('via_vendors as vv','po.via_vendor_id','=','vv.id')
        ->selectRaw('po.id, po.memo_no, po.vendor_id,v.name as vendor_name, po.via_vendor_id,vv.name as via_vendor_name,
        po.item, po.total_qty,po.grand_total, po.order_date, po.delivery_date, 
        po.po_no, po.nos_truck, po.purchase_status, po.created_by');

        //search query
        if (!empty($this->_memo_no)) {
            $query->where('po.memo_no', 'like', '%' . $this->_memo_no . '%');
        }

        if (!empty($this->_from_date)) {
            $query->where('po.order_date', '>=',$this->_from_date);
        }
        if (!empty($this->_to_date)) {
            $query->where('po.order_date', '<=',$this->_to_date);
        }
        if (!empty($this->_vendor_id)) {
            $query->where('po.vendor_id', $this->_vendor_id);
        }

        if (!empty($this->purchase_status)) {
            $query->where('po.purchase_status', $this->purchase_status);
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
        return DB::table('purchase_orders')->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
}
