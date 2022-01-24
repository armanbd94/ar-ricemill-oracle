<?php

namespace Modules\Product\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\Cache;
use Modules\Product\Entities\Product;


class ItemGroup extends BaseModel
{
    protected $fillable = ['name','status','created_by','modified_by'];

    public function products()
    {
        return $this->hasMany(Product::class, 'item_group_id','id');
    }
     /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $name; 
    protected $status; 

    //methods to set custom search property value
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

        if (permission('group-bulk-delete')){
            $this->column_order = [null,'id','name','status','created_by','modified_by','created_at','updated_at',null];
        }else{
            $this->column_order = ['id','name','status','created_by','modified_by','created_at','updated_at',null];
        }

        $query = self::toBase();

        //search query
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

        return self::toBase()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/


    /***************************************
     * * * Begin :: Model Local Scope * * *
    ****************************************/
    public function scopeStatus($query)
    {
        return $query->where(['status'=>1]);
    }
    /***************************************
     * * * Begin :: Model Local Scope * * *
    ****************************************/

    

    /*************************************
    * * *  Begin :: Cache Data * * *
    **************************************/
    protected const ITEM_GROUP  = '_item_group';

    public static function allItemGroup(){
        return Cache::rememberForever(self::ITEM_GROUP, function () {
            return self::status()->orderBy('id','asc')->get();
        });
    }


    public static function flushItemClassCache(){
        Cache::forget(self::ITEM_GROUP);
    }

    public static function boot(){
        parent::boot();

        static::updated(function () {
            self::flushItemClassCache();
        });

        static::created(function() {
            self::flushItemClassCache();
        });

        static::deleted(function() {
            self::flushItemClassCache(); 
        });
    }
    /***********************************
    * * *  Begin :: Cache Data * * *
    ************************************/
}
