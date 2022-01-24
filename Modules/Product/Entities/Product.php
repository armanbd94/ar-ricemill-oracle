<?php

namespace Modules\Product\Entities;

use App\Models\Tax;
use App\Models\Unit;
use App\Models\Category;
use App\Models\BaseModel;
use Modules\Product\Entities\ItemGroup;


class Product extends BaseModel
{

    protected $fillable = ['category_id','name', 'code', 'unit_id', 'cost','price', 'qty', 'alert_qty', 
    'tax_id', 'tax_method', 'status', 'has_opening_stock','created_by', 'modified_by','item_group_id'];

    
    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $_category_id; 
    protected $_item_group_id; 
    protected $_name; 
    protected $_code; 
    protected $_status; 

    //methods to set custom search property value
    public function setCategoryID($category_id)
    {
        $this->_category_id = $category_id;
    }
    public function setGroupID($item_group_id)
    {
        $this->_item_group_id = $item_group_id;
    }
    public function setName($name)
    {
        $this->_name = $name;
    }
    public function setCode($code)
    {
        $this->_code = $code;
    }
    public function setStatus($status)
    {
        $this->_status = $status;
    }


    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        if (permission('product-bulk-delete')){
            $this->column_order = [null,'id','name','code','item_group_id','category_id','cost','price','unit_id', 'alert_qty', 'status',null];
        }else{
            $this->column_order = ['id','name','code','item_group_id','category_id','cost','price','unit_id', 'alert_qty', 'status',null];
        }
        
        $query = self::with('unit:id,unit_name,unit_code','category:id,name','item_group:id,name');

        //search query
        if (!empty($this->_category_id)) {
            $query->where('category_id', $this->_category_id);
        }
        if (!empty($this->_item_group_id)) {
            $query->where('item_group_id', $this->_item_group_id);
        }
        if (!empty($this->_name)) {
            $query->where('name', 'like', '%' . $this->_name . '%');
        }
        if (!empty($this->_code)) {
            $query->where('code', 'like', '%' . $this->_code . '%');
        }
        if (!empty($this->_status)) {
            $query->where('status', $this->_status);
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
        return self::toBase()->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/

    /***************************************
     * * * Begin :: Model Relationship * * *
    ****************************************/
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function unit()
    {
        return $this->belongsTo(Unit::class,'unit_id','id');
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class)->orderBy('name','asc')->withDefault(['name'=>'No Tax','rate'=>0]);
    }

    public function item_group()
    {
        return $this->belongsTo(ItemGroup::class, 'item_group_id','id')->withDefault(['name'=>'']);
    }

    /***************************************
     * * * End :: Model Relationship * * *
    ****************************************/
    

}
