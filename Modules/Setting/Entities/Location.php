<?php

namespace Modules\Setting\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;

class Location extends BaseModel
{
    protected $fillable = ['name','site_id','status','created_at','modified_at'];

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    protected $order = ['l.id' => 'desc'];
    //custom search column property
    protected $_name; 
    protected $_site_id; 

    //methods to set custom search property value
    public function setName($name)
    {
        $this->_name = $name;
    }
    public function setSiteID($site_id)
    {
        $this->_site_id = $site_id;
    }


    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        if (permission('location-bulk-delete')){
            $this->column_order = [null,'l.id','l.name','l.site_id','l.status','l.created_by','l.modified_by','l.created_at','l.updated_at',null];
        }else{
            $this->column_order = ['l.id','l.name','l.site_id','l.status','l.created_by','l.modified_by','l.created_at','l.updated_at',null];
        }
        
        $query = DB::table('locations as l')
        ->join('sites as s','l.site_id','=','s.id')
        ->select('l.*','s.name as site_name');

        //search query
        if (!empty($this->_name)) {
            $query->where('name', 'like', '%' . $this->_name . '%');
        }
        if (!empty($this->_site_id)) {
            $query->where('site_id', $this->_site_id);
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
        return DB::table('locations')->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/


}
