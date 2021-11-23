<?php

namespace Modules\Purchase\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Modules\Material\Entities\Material;
use Modules\Purchase\Entities\PurchaseOrder;

class OrderReceived extends BaseModel
{
    protected $table = 'order_received';
    protected $fillable = ['order_id', 'challan_no', 'transport_no', 'item', 'total_qty', 'grand_total', 'received_date', 'created_by', 'modified_by'];
    
    public function order()
    {
        return $this->belongsTo(PurchaseOrder::class,'order_id','id');
    }

    public function received_materials()
    {
        return $this->belongsToMany(Material::class,'received_materials','received_id','material_id','id','id')
        ->withPivot('id', 'order_id', 'site_id', 'location_id','received_qty','received_unit_id','net_unit_cost','total','description')
        ->withTimeStamps(); 
    }


     /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    protected $order = ['orcv.id' => 'desc'];
    //custom search column property
    protected $_memo_no; 
    protected $_challan_no; 
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


    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        if (permission('purchase-received-bulk-delete')){
            $this->column_order = ['orcv.id', 'orcv.id', 'orcv.challan_no', 'po.memo_no','po.vendor_id','po.via_vendor_id',  'orcv.transport_no', 'orcv.item', 'orcv.total_qty', 'orcv.grand_total', 'orcv.received_date', 'orcv.created_by', null];
        }else{
            $this->column_order = ['orcv.id', 'orcv.challan_no', 'po.memo_no','po.vendor_id','po.via_vendor_id',  'orcv.transport_no', 'orcv.item', 'orcv.total_qty', 'orcv.grand_total', 'orcv.received_date', 'orcv.created_by', null];
        }
        
        $query = DB::table('order_received as orcv')
        ->join('purchase_orders as po','orcv.order_id','=','po.id')
        ->join('vendors as v','po.vendor_id','=','v.id')
        ->leftJoin('via_vendors as vv','po.via_vendor_id','=','vv.id')
        ->selectRaw('orcv.id, orcv.challan_no,po.memo_no, po.vendor_id,v.name as vendor_name, po.via_vendor_id,vv.name as via_vendor_name,
        orcv.transport_no, orcv.item, orcv.total_qty,orcv.grand_total, orcv.received_date, orcv.created_by');

        //search query
        if (!empty($this->_memo_no)) {
            $query->where('po.memo_no', 'like', '%' . $this->_memo_no . '%');
        }

        if (!empty($this->_challan_no)) {
            $query->where('orcv.challan_no', 'like', '%' . $this->_challan_no . '%');
        }

        if (!empty($this->_from_date)) {
            $query->where('orcv.received_date', '>=',$this->_from_date);
        }

        if (!empty($this->_to_date)) {
            $query->where('orcv.received_date', '<=',$this->_to_date);
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
        return DB::table('order_received')->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
}
