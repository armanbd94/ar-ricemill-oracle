<?php

namespace Modules\Product\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Modules\Setting\Entities\Site;
use Modules\Product\Entities\Product;
use Modules\Setting\Entities\Location;


class SiteProduct extends BaseModel
{
    protected $table = "site_product";
    protected $fillable = ['site_id','location_id','product_id','qty'];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $order = ['sp.id' => 'asc'];
    protected $_product_id; 
    protected $_site_id; 
    protected $_location_id; 

    //methods to set custom search property value
    public function setProductlID($product_id)
    {
        $this->_product_id = $product_id;
    }
    public function setSiteID($site_id)
    {
        $this->_site_id = $site_id;
    }
    public function setLocationID($location_id)
    {
        $this->_location_id = $location_id;
    }

    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)

        $this->column_order = ['sp.id','s.name','l.name','p.name','p.code','p.unit_id','sp.qty',null];
    
        $query = DB::table('site_product as sp')
        ->selectRaw('sp.qty,s.name as site_name,l.name as location_name,p.id,p.product_name,p.product_code,p.cost,u.unit_name')
        ->join('site as s','sp.site_id','=','s.id')
        ->join('location as l','sp.location_id','=','l.id')
        ->join('products as p','sp.product_id','=','p.id')
        ->join('units as u','p.unit_id','=','u.id');
        if($this->_site_id != 0){
            $query->where('sp.site_id',$this->_site_id);
        }
        if($this->_location_id != 0){
            $query->where('sp.location_id',$this->_location_id);
        }
        if($this->_product_id != 0){
            $query->where('sp.product_id',$this->_product_id);
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
        return DB::table('site_product')->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
}
