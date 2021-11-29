<?php

namespace Modules\TransferInventory\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Modules\Setting\Entities\Site;
use Modules\Setting\Entities\Location;

class TransferInventory extends BaseModel
{
    protected $table = 'transfer_inventories';
    protected $fillable = [
        'memo_no','batch_id', 'from_site_id', 'from_location_id','to_site_id','to_location_id','item',
        'total_qty','transfer_date','transfer_number','created_by','modified_by',
    ];

    public function from_site()
    {
        return $this->belongsTo(Site::class,'from_site_id','id');
    }
    public function to_site()
    {
        return $this->belongsTo(Site::class,'to_site_id','id');
    }
    public function from_location()
    {
        return $this->belongsTo(Location::class,'from_location_id','id');
    }
    public function to_location()
    {
        return $this->belongsTo(Location::class,'to_location_id','id');
    }

    public function materials()
    {
        return $this->belongsToMany(Material::class,'transfer_inventory_items','transfer_id','material_id','id','id')
        ->withPivot('id', 'qty','description')
        ->withTimeStamps(); 
    }


     /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    protected $order = ['ti.id' => 'desc'];
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
        if (permission('transfer-inventory-bulk-delete')){
            $this->column_order = ['ti.id', 'ti.id', 'ti.memo_no','ti.batch_id', 'ti.from_site_id', 'ti.from_location_id','ti.to_site_id','ti.to_location_id','ti.item','ti.total_qty','ti.transfer_date','ti.transfer_number','ti.created_by', null];
        }else{
            $this->column_order = ['ti.id', 'ti.memo_no','ti.batch_id', 'ti.from_site_id', 'ti.from_location_id','ti.to_site_id','ti.to_location_id','ti.item','ti.total_qty','ti.transfer_date','ti.transfer_number','ti.created_by', null];
        }
        
        $query = DB::table('transfer_inventories as ti')
        ->leftJoin('batches as b','ti.batch_id','=','b.id')
        ->leftJoin('sites as fs','ti.from_site_id','=','fs.id')
        ->leftJoin('sites as ts','ti.to_site_id','=','ts.id')
        ->leftJoin('locations as fl','ti.from_location_id','=','fl.id')
        ->leftJoin('locations as tl','ti.to_location_id','=','tl.id')
        ->select('ti.id', 'ti.memo_no','ti.batch_id', 'ti.from_site_id', 'ti.from_location_id','ti.to_site_id',
        'ti.to_location_id','ti.item','ti.total_qty','ti.transfer_date','ti.transfer_number','ti.created_by',
        'b.batch_no','fs.name as from_site','ts.name as to_site','fl.name as from_location','tl.name as to_location');

        //search query
        if (!empty($this->_memo_no)) {
            $query->where('ti.memo_no', 'like', '%' . $this->_memo_no . '%');
        }

        if (!empty($this->_from_date)) {
            $query->where('ti.transfer_date', '>=',$this->_from_date);
        }

        if (!empty($this->_to_date)) {
            $query->where('ti.transfer_date', '<=',$this->_to_date);
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
        return DB::table('transfer_inventories')->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/

}
