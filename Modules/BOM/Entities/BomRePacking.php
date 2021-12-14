<?php

namespace Modules\BOM\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Modules\Setting\Entities\Site;
use Modules\Product\Entities\Product;
use Modules\Setting\Entities\Location;
use Modules\Material\Entities\Material;

class BomRePacking extends BaseModel
{
    protected $fillable = [
        'memo_no','packing_number','from_site_id','from_location_id','from_product_id',
        'to_site_id','to_location_id','to_product_id','bag_site_id','bag_location_id','bag_id',
        'product_description','bag_description','product_qty','bag_qty','packing_date','created_by','modified_by',
    ];
    /****************************
    * Start :: Model Relation *
    ****************************/
    public function from_site()
    {
        return $this->belongsTo(Site::class,'from_site_id','id');
    }

    public function from_location()
    {
        return $this->belongsTo(Location::class,'from_location_id','id');
    }
    public function to_site()
    {
        return $this->belongsTo(Site::class,'to_site_id','id');
    }

    public function to_location()
    {
        return $this->belongsTo(Location::class,'to_location_id','id');
    }

    public function bag_site()
    {
        return $this->belongsTo(Site::class,'bag_site_id','id');
    }

    public function bag_location()
    {
        return $this->belongsTo(Location::class,'bag_location_id','id');
    }

    public function bag()
    {
        return $this->belongsTo(Material::class,'bag_id','id');
    }

    public function from_product()
    {
        return $this->belongsTo(Product::class,'from_product_id','id');
    }

    public function to_product()
    {
        return $this->belongsTo(Product::class,'to_product_id','id');
    }
    /****************************
    * End :: Model Relation *
    ****************************/

     /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    protected $order = ['bp.id' => 'desc'];
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
        if (permission('bom-re-packing-bulk-delete')){
            $this->column_order = ['bp.id', 'bp.id','bp.memo_no', 'bp.from_product_id','bp.from_site_id', 'bp.from_location_id','bp.to_product_id','bp.to_site_id', 'bp.to_location_id','bp.product_qty','bp.bag_id','bp.bag_qty','bp.process_date','bp.created_by', null];
        }else{
            $this->column_order = ['bp.id','bp.memo_no', 'bp.from_product_id','bp.from_site_id', 'bp.from_location_id','bp.to_product_id','bp.to_site_id', 'bp.to_location_id','bp.product_qty','bp.bag_id','bp.bag_qty','bp.process_date','bp.created_by', null];
        }
        
        $query = DB::table('bom_re_packings as bp')
        ->leftJoin('products as fp','bp.from_product_id','=','fp.id')
        ->leftJoin('products as tp','bp.to_product_id','=','tp.id')
        ->leftJoin('sites as fs','bp.from_site_id','=','fs.id')
        ->leftJoin('sites as ts','bp.to_site_id','=','ts.id')
        ->leftJoin('locations as fl','bp.from_location_id','=','fl.id')
        ->leftJoin('locations as tl','bp.to_location_id','=','tl.id')
        ->leftJoin('materials as m','bp.bag_id','=','m.id')
        ->select('bp.*','fp.name as from_product','tp.name as to_product','fs.name as from_site','ts.name as to_site',
        'fl.name as from_location','tl.name as to_location','m.material_name as bag_name');

        //search query
        if (!empty($this->_memo_no)) {
            $query->where('bp.memo_no', $this->_memo_no);
        }
        if (!empty($this->_from_date)) {
            $query->where('bp.packing_date', '>=',$this->_from_date);
        }
        if (!empty($this->_to_date)) {
            $query->where('bp.packing_date', '<=',$this->_to_date);
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
        return DB::table('bom_re_packings')->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/

}
