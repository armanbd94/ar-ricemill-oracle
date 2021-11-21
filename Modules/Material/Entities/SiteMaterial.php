<?php

namespace Modules\Material\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Modules\Material\Entities\Material;
use Modules\Setting\Entities\Location;
use Modules\Setting\Entities\Site;

class SiteMaterial extends BaseModel
{
    protected $table = "site_material";
    protected $fillable = ['site_id','location_id','material_id','qty'];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $order = ['m.id' => 'asc'];
    protected $_material_id; 
    protected $_site_id; 
    protected $_location_id; 

    //methods to set custom search property value
    public function setMaterialID($material_id)
    {
        $this->_material_id = $material_id;
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

        $this->column_order = ['m.id','w.name','m.material_name','m.material_code','m.unit_id','m.cost','wm.qty',null];
    
        $query = DB::table('site_material as sm')
        ->selectRaw('sm.qty,s.name as site_name,l.name as location_name,m.id,m.material_name,m.material_code,m.cost,u.unit_name')
        ->join('site as s','sm.site_id','=','s.id')
        ->join('location as l','sm.location_id','=','l.id')
        ->join('materials as m','sm.material_id','=','m.id')
        ->join('units as u','m.unit_id','=','u.id');
        if($this->_site_id != 0){
            $query->where('sm.site_id',$this->_site_id);
        }
        if($this->_location_id != 0){
            $query->where('sm.location_id',$this->_location_id);
        }
        if($this->_material_id != 0){
            $query->where('sm.material_id',$this->_material_id);
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
        return DB::table('site_material')->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
}
