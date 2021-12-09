<?php

namespace Modules\BuildDisassembly\Entities;

use App\Models\Category;
use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;

class BuildDisassembly extends BaseModel
{

    protected $fillable = [
        'memo_no', 'batch_id', 'from_site_id', 'from_location_id', 'material_id','product_id', 'build_ratio', 
        'build_qty', 'required_qty','category_id','build_date','convertion_ratio','converted_qty','bp_site_id',
        'bp_location_id','created_by','modified_by',
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

    public function material()
    {
        return $this->belongsTo(Material::class,'material_id','id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class,'product_id','id');
    }
    
    public function category()
    {
        return $this->belongsTo(Category::class,'category_id','id');
    }

    public function by_products()
    {
        return $this->belongsToMany(Product::class,'build_disassembly_by_products','disassembly_id','product_id','id','id')
        ->withPivot('id','ratio','qty')
        ->withTimeStamps(); 
    }
    /****************************
    * End :: Model Relation *
    ****************************/

     /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    protected $order = ['bd.id' => 'desc'];
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
        if (permission('build-disassembly-bulk-delete')){
            $this->column_order = ['bd.id', 'bd.id', 'bd.memo_no','bd.batch_id', 'bd.material_id','bd.product_id','bd.from_site_id', 'bd.from_location_id','convertion_ratio','converted_qty','bd.build_date','bd.created_by', null];
        }else{
            $this->column_order = ['bd.id', 'bd.memo_no','bd.batch_id', 'bd.material_id','bd.product_id','bd.from_site_id', 'bd.from_location_id','convertion_ratio','converted_qty','bd.build_date','bd.created_by', null];
        }
        
        $query = DB::table('build_disassemblies as bd')
        ->leftJoin('batches as b','bd.batch_id','=','b.id')
        ->leftJoin('materials as m','bd.material_id','=','m.id')
        ->leftJoin('products as p','bd.product_id','=','p.id')
        ->leftJoin('sites as fs','bd.from_site_id','=','fs.id')
        ->leftJoin('sites as bps','bd.bp_site_id','=','bps.id')
        ->leftJoin('locations as fl','bd.from_location_id','=','fl.id')
        ->leftJoin('locations as bpl','bd.bp_location_id','=','bpl.id')
        ->select('bd.*','b.batch_no','m.material_name','p.name as product_name','fs.name as from_site','bps.name as bp_site','fl.name as from_location','bpl.name as bp_location');

        //search query
        if (!empty($this->_memo_no)) {
            $query->where('bd.memo_no', 'like', '%' . $this->_memo_no . '%');
        }

        if (!empty($this->_batch_id)) {
            $query->where('bd.batch_id', $this->_batch_id);
        }
        if (!empty($this->_from_date)) {
            $query->where('bd.build_date', '>=',$this->_from_date);
        }

        if (!empty($this->_to_date)) {
            $query->where('bd.build_date', '<=',$this->_to_date);
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
        return DB::table('build_disassemblies')->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/

}
