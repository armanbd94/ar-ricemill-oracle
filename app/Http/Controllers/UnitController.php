<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Unit;
use Illuminate\Http\Request;
use App\Http\Requests\UnitFormRequest;
use App\Http\Controllers\BaseController;

class UnitController extends BaseController
{
    public function __construct(Unit $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('unit-access')){
            $this->setPageData('Unit','Unit','fas fa-balance-scale',[['name' => 'Unit']]);
            return view('unit.index');
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('unit-access')){

                if (!empty($request->unit_name)) {
                    $this->model->setUnitName($request->unit_name);
                }

                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('unit-edit')){
                        $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if(permission('unit-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->unit_name . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }


                    $row = [];
                    if(permission('unit-bulk-delete')){
                        $row[] = row_checkbox($value->id);
                    }
                    $row[] = $no;
                    $row[] = $value->unit_name;
                    $row[] = $value->unit_code;
                    $row[] = $value->baseUnit->unit_name;
                    $row[] = $value->operator;
                    $row[] = $value->operation_value;
                    $row[] = permission('unit-edit') ? change_status($value->id,$value->status,$value->unit_name) : STATUS_LABEL[$value->status];
                    $row[] = action_button($action);//custom helper function for action button
                    $data[] = $row;
                }
                return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
                $this->model->count_filtered(), $data);
            }
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function store_or_update_data(UnitFormRequest $request)
    {
        if($request->ajax()){
            if(permission('unit-add')){
                $collection      = collect($request->validated())->except(['operator','operation_value']);
                $base_unit       = $request->base_unit ? $request->base_unit : null;
                $operator        = $request->operator ? $request->operator : '*';
                $operation_value = $request->operation_value ? $request->operation_value : 1;
                $collection      = $collection->merge(compact('base_unit','operator','operation_value'));
                $collection      = $this->track_data($collection,$request->update_id);
                $result          = $this->model->updateOrCreate(['id'=>$request->update_id],$collection->all());
                $output          = $this->store_message($result, $request->update_id);
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
            if(permission('unit-edit')){
                $data   = $this->model->findOrFail($request->id);
                $output = $this->data_message($data); //if data found then it will return data otherwise return error message
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function delete(Request $request)
    {
        if($request->ajax()){
            if(permission('unit-delete')){
                $result   = $this->model->find($request->id)->delete();
                $output   = $this->delete_message($result);
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function bulk_delete(Request $request)
    {
        if($request->ajax()){
            if(permission('unit-bulk-delete')){
                $result   = $this->model->destroy($request->ids);
                $output   = $this->bulk_delete_message($result);
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function change_status(Request $request)
    {
        if($request->ajax()){
            if (permission('unit-edit')) {
                $result = $this->model->find($request->id)->update(['status'=>$request->status]);
                $output = $result ? ['status'=>'success','message'=>'Status has been changed successfully']
                : ['status'=>'error','message'=>'Failed to change status'];
            }else{
                $output = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function base_unit(Request $request)
    {
        if($request->ajax())
        {
            $units = $this->model->where(['base_unit'=>null,'status'=>1])->get();
            $output = '<option value="">Select Please</option>';
            if (!$units->isEmpty()){
                foreach ($units as $unit){
                    $output .=  '<option value="'.$unit->id .'">'. $unit->unit_name.'('.$unit->unit_code.')</option>';
                }
            }
            return $output;
        }
    }

    public function populate_unit(int $id)
    {
        $units = $this->model->where('base_unit',$id)->orWhere('id',$id)->get()->pluck('name_code','id');
        return json_encode($units);
    }


}
