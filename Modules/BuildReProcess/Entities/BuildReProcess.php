<?php

namespace Modules\BuildReProcess\Entities;

use App\Models\BaseModel;
use App\Models\ItemClass;
use Illuminate\Support\Facades\DB;
use Modules\Setting\Entities\Site;
use Modules\Setting\Entities\Batch;
use Modules\Product\Entities\Product;
use Modules\Setting\Entities\Location;


class BuildReProcess extends BaseModel
{
    protected $fillable = [
        'memo_no', 'batch_id', 'from_site_id', 'from_location_id', 'from_product_id','to_product_id', 'build_ratio', 
        'build_qty', 'required_qty','item_class_id','build_date','convertion_ratio','converted_qty','total_milling_qty','total_milling_ratio','bp_site_id',
        'bp_location_id','created_by','modified_by','product_type','bp_rate','from_product_cost','to_product_cost','to_product_old_cost','bag_cost','per_unit_cost'
    ];
    /****************************
    * Start :: Model Relation *
    ****************************/
    public function batch()
    {
        return $this->belongsTo(Batch::class,'batch_id','id');
    }

    public function from_site()
    {
        return $this->belongsTo(Site::class,'from_site_id','id');
    }

    public function bp_site()
    {
        return $this->belongsTo(Site::class,'bp_site_id','id');
    }

    public function from_location()
    {
        return $this->belongsTo(Location::class,'from_location_id','id');
    }

    public function bp_location()
    {
        return $this->belongsTo(Location::class,'bp_location_id','id');
    }

    public function from_product()
    {
        return $this->belongsTo(Product::class,'from_product_id','id');
    }

    public function to_product()
    {
        return $this->belongsTo(Product::class,'to_product_id','id');
    }
    
    public function item_class()
    {
        return $this->belongsTo(ItemClass::class,'item_class_id','id');
    }

    public function by_products()
    {
        return $this->belongsToMany(Product::class,'build_re_process_by_products','process_id','product_id','id','id')
        ->withPivot('id','ratio','qty')
        ->withTimeStamps(); 
    }
    /****************************
    * End :: Model Relation *
    ****************************/

     /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    protected $order = ['brc.id' => 'desc'];
    //custom search column property
    protected $_memo_no; 
    protected $_batch_id; 
    protected $_from_date; 
    protected $_to_date; 

    //methods to set custom search property value
    public function setMemoNo($memo_no)
    {
        $this->_memo_no = $memo_no;
    }
    public function setBatchID($batch_id)
    {
        $this->_batch_id = $batch_id;
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
        if (permission('build-re-process-bulk-delete')){
            $this->column_order = ['brc.id', 'brc.id', 'brc.memo_no','brc.batch_id', 'brc.from_site_id', 'brc.from_location_id','brc.from_product_id','brc.to_product_id','convertion_ratio','converted_qty','brc.build_date','brc.created_by', null];
        }else{
            $this->column_order = ['brc.id', 'brc.memo_no','brc.batch_id', 'brc.from_site_id', 'brc.from_location_id','brc.from_product_id','brc.to_product_id','convertion_ratio','converted_qty','brc.build_date','brc.created_by', null];
        }
        
        $query = DB::table('build_re_processes as brc')
        ->leftJoin('batches as b','brc.batch_id','=','b.id')
        ->leftJoin('products as fp','brc.from_product_id','=','fp.id')
        ->leftJoin('products as tp','brc.to_product_id','=','tp.id')
        ->leftJoin('sites as fs','brc.from_site_id','=','fs.id')
        ->leftJoin('sites as bps','brc.bp_site_id','=','bps.id')
        ->leftJoin('locations as fl','brc.from_location_id','=','fl.id')
        ->leftJoin('locations as bpl','brc.bp_location_id','=','bpl.id')
        ->select('brc.*','b.batch_no','fp.name as from_product','tp.name as to_product','fs.name as from_site','bps.name as bp_site','fl.name as from_location','bpl.name as bp_location');

        //search query
        if (!empty($this->_memo_no)) {
            $query->where('brc.memo_no', 'like', '%' . $this->_memo_no . '%');
        }

        if (!empty($this->_batch_id)) {
            $query->where('brc.batch_id', $this->_batch_id);
        }
        if (!empty($this->_from_date)) {
            $query->where('brc.build_date', '>=',$this->_from_date);
        }

        if (!empty($this->_to_date)) {
            $query->where('brc.build_date', '<=',$this->_to_date);
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
        return DB::table('build_re_processes')->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
}
