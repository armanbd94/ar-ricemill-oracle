<?php

namespace Modules\BOM\Entities;

use App\Models\BaseModel;
use App\Models\ItemClass;
use Illuminate\Support\Facades\DB;
use Modules\Setting\Entities\Site;
use Modules\Setting\Entities\Batch;
use Modules\Product\Entities\Product;
use Modules\Setting\Entities\Location;
use Modules\Material\Entities\Material;

class BomProcess extends BaseModel
{
    protected $fillable = [
        'memo_no','batch_id','process_number','to_product_id',
        'to_site_id', 'to_location_id','from_product_id','item_class_id','from_site_id','from_location_id','product_particular','product_per_unit_qty',
        'product_required_qty','bag_site_id','bag_location_id','bag_id','bag_class_id','bag_particular','bag_per_unit_qty',
        'bag_required_qty','total_rice_qty','total_bag_qty','process_date','created_by','modified_by','process_type',
        'from_product_cost','to_product_cost','to_product_old_cost','bag_cost','per_unit_cost'
    ];

    /****************************
    * Start :: Model Relation *
    ****************************/
    public function batch()
    {
        return $this->belongsTo(Batch::class,'batch_id','id');
    }

    public function to_site()
    {
        return $this->belongsTo(Site::class,'to_site_id','id');
    }

    public function to_location()
    {
        return $this->belongsTo(Location::class,'to_location_id','id');
    }
    public function from_site()
    {
        return $this->belongsTo(Site::class,'from_site_id','id');
    }

    public function from_location()
    {
        return $this->belongsTo(Location::class,'from_location_id','id');
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
    public function bag_class()
    {
        return $this->belongsTo(ItemClass::class,'bag_class_id','id')->withDefault(['name'=>'']);
    }

    public function product_class()
    {
        return $this->belongsTo(ItemClass::class,'item_class_id','id')->withDefault(['name'=>'']);
    }
    /****************************
    * End :: Model Relation *
    ****************************/


    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    protected $order = ['bp.id' => 'desc'];
    //custom search column property
    protected $_batch_id; 
    protected $_from_date; 
    protected $_to_date; 
    protected $_process_type; 

    //methods to set custom search property value
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

    public function setProcessType($process_type)
    {
        $this->_process_type = $process_type;
    }


    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        if (permission('bom-process-bulk-delete') || permission('bom-re-process-bulk-delete')){
            $this->column_order = ['bp.id', 'bp.id','bp.batch_id', 'bp.to_product_id','bp.to_site_id', 'bp.to_location_id','bp.total_rice_qty','bp.total_bag_qty','bp.process_date','bp.created_by', null];
        }else{
            $this->column_order = ['bp.id','bp.batch_id', 'bp.to_product_id','bp.to_site_id', 'bp.to_location_id','bp.total_rice_qty','bp.total_bag_qty','bp.process_date','bp.created_by', null];
        }
        
        $query = DB::table('bom_processes as bp')
        ->leftJoin('batches as b','bp.batch_id','=','b.id')
        ->leftJoin('products as p','bp.to_product_id','=','p.id')
        ->leftJoin('sites as s','bp.to_site_id','=','s.id')
        ->leftJoin('locations as l','bp.to_location_id','=','l.id')
        ->select('bp.*','b.batch_no','p.name as product_name','s.name as storage_site','l.name as storage_location')
        ->where('bp.process_type',$this->_process_type);

        //search query
        if (!empty($this->_batch_id)) {
            $query->where('bp.batch_id', $this->_batch_id);
        }
        if (!empty($this->_from_date)) {
            $query->where('bp.process_date', '>=',$this->_from_date);
        }

        if (!empty($this->_to_date)) {
            $query->where('bp.process_date', '<=',$this->_to_date);
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
        return DB::table('bom_processes')->where('process_type',$this->_process_type)->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/

    protected static function bom_process_data(object $data,int $process_type, object $from_product, object $to_product, object $bag, $to_product_new_cost) : array
    {
        $bom_process_data = [
            'process_type'         => $process_type,
            'memo_no'              => $data->memo_no,
            'batch_id'             => $data->batch_id,
            'process_number'       => $data->process_number,
            'to_product_id'        => $data->to_product_id,
            'to_site_id'           => $data->to_site_id,
            'to_location_id'       => $data->to_location_id,
            'from_product_id'      => $data->from_product_id,
            'item_class_id'        => $data->item_class_id,
            'from_site_id'         => $data->from_site_id,
            'from_location_id'     => $data->from_location_id,
            'product_particular'   => $data->product_particular,
            'product_per_unit_qty' => $data->product_per_unit_qty,
            'product_required_qty' => $data->product_required_qty,
            'bag_site_id'          => $data->bag_site_id,
            'bag_location_id'      => $data->bag_location_id,
            'bag_id'               => $data->bag_id,
            'bag_class_id'         => $data->bag_class_id,
            'bag_particular'       => $data->bag_particular,
            'bag_per_unit_qty'     => $data->bag_per_unit_qty,
            'bag_required_qty'     => $data->bag_required_qty,
            'total_rice_qty'       => $data->total_rice_qty,
            'total_bag_qty'        => $data->total_bag_qty,
            'process_date'         => $data->process_date,
            'from_product_cost'    => $from_product->cost ? $from_product->cost : 0,
            'to_product_cost'      => $to_product_new_cost,
            'to_product_old_cost'  => $to_product->cost ? $to_product->cost : 0,
            'bag_cost'             => $bag->cost ? $bag->cost : 0,
            'per_unit_cost'        => $data->per_unit_cost,
            
        ];
        if(empty($data->process_id))
        {
            $bom_process_data['created_by'] = auth()->user()->name;
        }else{
            $bom_process_data['modified_by'] = auth()->user()->name;
        }
        return $bom_process_data;
    }
}
