<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Support\Facades\Cache;
use Modules\Material\Entities\Material;
use Modules\Material\Entities\WarehouseMaterial;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\WarehouseProduct;

class Category extends BaseModel
{

    protected $fillable = ['name','type','status','created_by','modified_by'];

    public function warehouse_materials()
    {
        return $this->hasManyThrough(WarehouseMaterial::class, Material::class,'category_id','material_id','id','id');
    }
    public function warehouse_products()
    {
        return $this->hasManyThrough(WarehouseProduct::class, Product::class,'category_id','product_id','id','id');
    }
    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $type; 
    protected $name; 
    protected $status; 

    //methods to set custom search property value
    public function setType($type)
    {
        $this->type = $type;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }


    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        if (!empty($this->type)) {
            if ($this->type == 1) { //for material category
                if (permission('material-category-bulk-delete')){
                    $this->column_order = [null,'id','name','status','created_by','modified_by','created_at','updated_at',null];
                }else{
                    $this->column_order = ['id','name','status','created_by','modified_by','created_at','updated_at',null];
                }
            }
        }

        if (!empty($this->type)) {
            if ($this->type == 2) { //for product category
                if (permission('finish-goods-category-bulk-delete')){
                    $this->column_order = [null,'id','name','status','created_by','modified_by','created_at','updated_at',null];
                }else{
                    $this->column_order = ['id','name','status','created_by','modified_by','created_at','updated_at',null];
                }
            }
        }
        
        $query = self::toBase();

        //search query
        if (!empty($this->type)) {
            $query->where('type', $this->type);
        }
        if (!empty($this->name)) {
            $query->where('name', 'like', '%' . $this->name . '%');
        }
        if (!empty($this->status)) {
            $query->where('status', $this->status);
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
        $query = self::toBase();
        if (!empty($this->type)) {
            $query->where('type', $this->type);
        }
        return $query->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/


    /***************************************
     * * * Begin :: Model Relationship * * *
    ****************************************/

    /***************************************
     * * * End :: Model Relationship * * *
    ****************************************/


    /***************************************
     * * * Begin :: Model Local Scope * * *
    ****************************************/
    public function scopeMaterialCategory($query)
    {
        return $query->where(['type'=>1,'status'=>1]);
    }

    public function scopeProductCategory($query)
    {
        return $query->where(['type'=>2,'status'=>1]);
    }
    /***************************************
     * * * Begin :: Model Local Scope * * *
    ****************************************/

    

    /*************************************
    * * *  Begin :: Cache Data * * *
    **************************************/
    protected const MATERIAL_CATEGORY    = '_material_categories';
    protected const PRODUCT_CATEGORY     = '_product_categories';

    public static function allMaterialCategories(){
        return Cache::rememberForever(self::MATERIAL_CATEGORY, function () {
            return self::materialcategory()->orderBy('name','asc')->get();
        });
    }

    public static function allProductCategories(){
        return Cache::rememberForever(self::PRODUCT_CATEGORY, function () {
            return self::productcategory()->orderBy('name','asc')->get();
        });
    }

    public static function flushCategoryCache(){
        Cache::forget(self::MATERIAL_CATEGORY);
        Cache::forget(self::PRODUCT_CATEGORY);
    }

    public static function boot(){
        parent::boot();

        static::updated(function () {
            self::flushCategoryCache();
        });

        static::created(function() {
            self::flushCategoryCache();
        });

        static::deleted(function() {
            self::flushCategoryCache(); 
        });
    }
    /***********************************
    * * *  Begin :: Cache Data * * *
    ************************************/

}
