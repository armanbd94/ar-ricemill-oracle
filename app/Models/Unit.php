<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Support\Facades\Cache;

class Unit extends BaseModel
{
    
    protected $fillable = ['unit_code','unit_name','base_unit','operator',
    'operation_value','status','created_by','modified_by']; //fillable column name

    public function baseUnit()
    {
        return $this->belongsTo(Unit::class,'base_unit','id')
        ->withDefault(['unit_name'=>'N/A']);
    }

    public function getNameCodeAttribute()
    {
        return $this->unit_name.' ('.$this->unit_code.')';
    }

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    protected $unitName;

    public function setUnitName($unitName)
    {
        $this->unitName = $unitName;
    }


    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        if(permission('unit-bulk-delete')){
            $this->column_order = [null,'id','unit_name','code','base_unit','operator','operation_value','status',null];
        }else{
            $this->column_order = ['id','unit_name','code','base_unit','operator','operation_value','status',null];
        }
        
        $query = self::with('baseUnit');

        //search query
        if (!empty($this->unitName)) {
            $query->where('unit_name', 'like', '%' . $this->unitName . '%');
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

}
