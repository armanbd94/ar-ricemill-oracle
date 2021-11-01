<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Module;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Http\Requests\PermissionStoreFormRequest;
use App\Http\Requests\PermissionUpdateFormRequest;

class ASMPermissionController extends BaseController
{
    private $erp_menu_id = 2;
    public function __construct(Permission $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('asm-permission-access')){
            $this->setPageData('ASM Permission','ASM Permission','fas fa-tasks',[['name'=>'ASM Permission']]);
            $modules = Module::module_list($this->erp_menu_id);
            return view('asm-permission.index',compact('modules'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){

            $this->model->setType($this->erp_menu_id);
            
            if (!empty($request->name)) {
                $this->model->setName($request->name);
            }
            if (!empty($request->module_id)) {
                $this->model->setModuleID($request->module_id);
            }

            $this->set_datatable_default_properties($request);//set datatable default properties
            $list = $this->model->getDatatableList();//get table data
            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $action = '';
                if(permission('asm-permission-edit')){
                $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.self::ACTION_BUTTON['Edit'].'</a>';
                }
                if(permission('asm-permission-delete')){
                $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->name . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                }

                $row = [];
                if(permission('asm-permission-bulk-delete')){
                $row[] = row_checkbox($value->id);
                }
                $row[] = $no;
                $row[] = $value->module->module_name;
                $row[] = $value->name;
                $row[] = $value->slug;
                $row[] = action_button($action);//custom helper function for action button
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
             $this->model->count_filtered(), $data);
        }else{

            return response()->json($this->unauthorized());
        }

        
    }

    public function store(PermissionStoreFormRequest $request)
    {
        if($request->ajax()){
            if(permission('asm-permission-add')){
                
                $permission_data = [];
                foreach ($request->permission as $value) {
                    $permission_data[] = [
                        'module_id'  => $request->module_id,
                        'name'       => $value['name'],
                        'slug'       => $value['slug'],
                        'created_at' => Carbon::now()
                    ];
                }
                $result = $this->model->insert($permission_data);
                $output = $this->store_message($result,null);
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
            if(permission('asm-permission-edit')){
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

    public function update(PermissionUpdateFormRequest $request)
    {
        if($request->ajax()){
            if(permission('asm-permission-edit')){
                $collection = collect($request->validated());
                $updated_at = Carbon::now();
                $collection = $collection->merge(compact('updated_at'));
                $result     = $this->model->find($request->update_id)->update($collection->all());
                $output     = $this->store_message($result, $request->update_id);
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
            if(permission('asm-permission-delete')){
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
            if(permission('asm-permission-bulk-delete')){
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

}
