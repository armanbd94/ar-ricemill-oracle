<?php

namespace Modules\Setting\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Modules\Setting\Entities\CustomerGroup;
use Modules\Setting\Http\Requests\CustomerGroupFormRequest;


class CustomerGroupController extends BaseController
{
    public function __construct(CustomerGroup $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('customer-group-access')){
            $this->setPageData('Customer Group','Customer Group','fas fa-users',[['name' => 'Settings'],['name'=>'Customer Group']]);
            return view('setting::customer-group.index');
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if (!empty($request->group_name)) {
                $this->model->setGroupName($request->group_name);
            }

            $this->set_datatable_default_properties($request);//set datatable default properties
            $list = $this->model->getDatatableList();//get table data
            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $action = '';
                if(permission('customer-group-edit')){
                $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.self::ACTION_BUTTON['Edit'].'</a>';
                }
                if(permission('customer-group-delete')){
                    $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->group_name . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                }

                $row = [];
                if(permission('customer-group-bulk-delete')){
                    $row[] = row_checkbox($value->id);//custom helper function to show the table each row checkbox
                }
                $row[] = $no;
                $row[] = $value->group_name;
                $row[] = $value->percentage;
                $row[] = permission('customer-group-edit') ? change_status($value->id,$value->status, $value->group_name) : STATUS_LABEL[$value->status];
                $row[] = action_button($action);//custom helper function for action button
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
             $this->model->count_filtered(), $data);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function store_or_update_data(CustomerGroupFormRequest $request)
    {
        if($request->ajax()){
            if (permission('customer-group-add') || permission('customer-group-edit')){
                $collection = collect($request->validated())->except('percentage');
                $percentage = $request->percentage ? $request->percentage : 0;
                $collection = $collection->merge(compact('percentage'));
                $collection = $this->track_data($collection,$request->update_id);
                $result     = $this->model->updateOrCreate(['id'=>$request->update_id],$collection->all());
                $output     = $this->store_message($result, $request->update_id);
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function edit(Request $request)
    {
        if($request->ajax()){
            if(permission('customer-group-edit')){
                $data   = $this->model->findOrFail($request->id);
                $output = $this->data_message($data); //if data found then it will return data otherwise return error message
            }else{
                $output = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function delete(Request $request)
    {
        if($request->ajax()){
            if(permission('customer-group-delete')){
                $result = $this->model->find($request->id)->delete();
                $output = $this->delete_message($result);
            }else{
                $output = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function bulk_delete(Request $request)
    {
        if($request->ajax()){
            if(permission('customer-group-bulk-delete')){
                $result = $this->model->destroy($request->ids);
                $output = $this->bulk_delete_message($result);
            }else{
                $output = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function change_status(Request $request)
    {
        if($request->ajax()){
            if(permission('customer-group-edit')){
                $result   = $this->model->find($request->id)->update(['status' => $request->status]);
                $output   = $result ? ['status' => 'success','message' => 'Status Has Been Changed Successfully']
                : ['status' => 'error','message' => 'Failed To Change Status'];
            }else{
                $output   = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }
}
