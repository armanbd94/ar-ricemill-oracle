<?php

namespace Modules\Material\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Modules\Material\Entities\Material;
use Modules\Setting\Entities\Warehouse;

class WarehouseMaterial extends BaseModel
{
    protected $table = "warehouse_material";
    protected $fillable = ['warehouse_id','material_id','qty'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
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
    protected $_name; 
    protected $_warehouse_id; 

    //methods to set custom search property value
    public function setName($name)
    {
        $this->_name = $name;
    }

    public function setWarehouseID($warehouse_id)
    {
        $this->_warehouse_id = $warehouse_id;
    }

    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)

        $this->column_order = ['m.id','w.name','m.material_name','m.material_code','m.unit_id','m.cost','wm.qty',null];
    
        $query = DB::table('warehouse_material as wm')
        ->selectRaw('wm.qty,w.name as warehouse_name,m.id,m.material_name,m.material_code,m.cost,u.unit_name')
        ->join('warehouses as w','wm.warehouse_id','=','w.id')
        ->join('materials as m','wm.material_id','=','m.id')
        ->join('units as u','m.unit_id','=','u.id');
        if($this->_warehouse_id != 0){
            $query->where('wm.warehouse_id',$this->_warehouse_id);
        }
        
        if (!empty($this->_name)) {
            $query->where('m.material_name', 'like', '%' . $this->_name . '%');
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
        return DB::table('warehouse_material as wm')
        ->selectRaw('wm.qty,w.name as warehouse_name,m.id,m.material_name,m.material_code,u.unit_name')
        ->join('warehouses as w','wm.warehouse_id','=','w.id')
        ->join('materials as m','wm.material_id','=','m.id')
        ->join('units as u','m.unit_id','=','u.id')
        ->where('wm.warehouse_id',$this->_warehouse_id)->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
}
