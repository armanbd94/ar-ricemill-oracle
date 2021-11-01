<?php

namespace Modules\Setting\Entities;

use App\Models\BaseModel;

class CustomerGroup extends BaseModel
{
    protected $fillable = [ 'group_name', 'percentage', 'status', 'created_by', 'updated_by'];

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $groupName; 

    //methods to set custom search property value
    public function setGroupName($groupName)
    {
        $this->groupName = $groupName;
    }


    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        if (permission('customer-group-bulk-delete')){
            $this->column_order = [null,'id','group_name', 'percentage','status',null];
        }else{
            $this->column_order = ['id','group_name', 'percentage','status',null];
        }
        
        $query = self::toBase();

        //search query
        if (!empty($this->groupName)) {
            $query->where('group_name', 'like', '%' . $this->groupName . '%');
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
