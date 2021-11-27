<?php

namespace Modules\HRM\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\HRM\Entities\Employee;
use App\Http\Controllers\BaseController;
use Modules\HRM\Entities\AllowanceDeductionManage;
use Modules\HRM\Http\Requests\AllowanceDeductionManageFormRequest;

class AllowanceDeductionManageController extends BaseController
{
    public function __construct(AllowanceDeductionManage $model)
    {
        $this->model = $model;
    }    

    public function index()
    {
        if(permission('salary-setup-access')){
            $this->setPageData('Salary Setup','Salary Setup','fas fa-warehouse',[['name'=>'HRM','link'=>'javascript::void();'],['name' => 'Salary Setup']]);
            $deletable = self::DELETABLE;
            $employees  = Employee::toBase()->where('status',1)->get();
            $allowances  = $this->model->allowances();
            $deducts  = $this->model->deducts();
            $allowancededucts  = $this->model->allowancededucts();
            return view('hrm::salary-setup.index',compact('employees','allowances','deducts','allowancededucts','deletable'));
        }else{
            return $this->access_blocked();
        }
    }    

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('salary-setup-access')){

                if (!empty($request->employee_id)) {
                    $this->model->setEmployeeID($request->employee_id);
                }
                if (!empty($request->allownace_id )) {
                    $this->model->setAllowanceID($request->allownace_id);
                }
                if (!empty($request->deduction_id )) {
                    $this->model->setDeductID($request->deduction_id);
                }

                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('salary-setup-edit')){
                        $action .= ' <a class="dropdown-item edit_data" href="' . url('salary.setup/edit', $value->id) . '">' . self::ACTION_BUTTON['Edit'] . '</a>';
                    }

                    if(permission('salary-setup-delete')){
                        if($value->deletable == 2){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->name . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                        }
                    }

                    $row = [];
                    if(permission('salary-setup-bulk-delete')){
                        $row[] = ($value->deletable == 2) ? row_checkbox($value->id) : '';//custom helper function to show the table each row checkbox
                    }
                    $row[] = $no;
                    $row[] = $value->employee->name;
                    $row[] = $value->allowance->name;
                    $row[] = $value->basic_salary;
                    $row[] = $value->percentage;
                    $row[] = $value->amount;
                    $row[] = $value->type;
                    //$row[] = ALLOWANCE_DEDUCTION_LABEL[$value->type];
                    $row[] = permission('salary-setup-edit') ? change_status($value->id,$value->status, $value->employee->name) : STATUS_LABEL[$value->status];
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

    
    public function store_or_update_data(AllowanceDeductionManageFormRequest $request)
    {
        if($request->ajax()){
            if(permission('salary-setup-add')){
                //  dd($request->all());
                DB::beginTransaction();
                try {
                    $benifit_insert = [];
                    if ($request->has('allowance')) {
                        foreach ($request->allowance as $key => $value) {
                            //Credit Insert
                            $last_amount = 0;
                            if(!empty($value['id']) && !empty($value['percent']))
                            {
                                $last_amount = (($request->basic_salary * $value['percent'])/100);
                                
                                $benifit_insert[$value['id']] = array(
                                    'basic_salary'        => $request->basic_salary,
                                    'percentage'       => $value['percent'] ? $value['percent'] : 0,
                                    'amount'            => $last_amount ? $last_amount : 0,
                                    'created_by'          => auth()->user()->name,
                                    'modified_by'          => auth()->user()->name,
                                );
                                
                            }
                        }
                    }
                    // dd($benifit_insert);
                    $employee = Employee::with('allowance_insert')->find($request->employee_id);
                    if(count($benifit_insert) > 0){
                        $employee->allowance_insert()->sync($benifit_insert);
                    }
                    
                    $output = $this->store_message($employee, null);
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                    $output = ['status' => 'error','message' => $e->getMessage()];
                }
            }else{
                $output = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }



        // if($request->ajax()){
        //     if(permission('salary-setup-add')){
        //         $collection   = collect($request->validated());
        //         $collection   = $this->track_data($collection,$request->update_id);
        //         $result       = $this->model->updateOrCreate(['id'=>$request->update_id],$collection->all());
        //         $output       = $this->store_message($result, $request->update_id);
        //         $this->model->flushCache();
        //     }else{
        //         $output       = $this->unauthorized();
        //     }
        //     return response()->json($output);
        // }else{
        //     return response()->json($this->unauthorized());
        // }
    }

    
    public function create()
    {
        if (permission('salary-setup-add')) {
            $this->setPageData('Salary Setup Add', 'Salary Setup Add', 'fas fa-user-secret', [['name' => 'HRM', 'link' => 'javascript::void();'], ['name' => 'Salary Setup Add']]);
            $data = [
                'deletable' => self::DELETABLE,
                'employees'  => Employee::toBase()->where('status',1)->get(),
                'allowances'  => $this->model->allowances(),
                'deducts'  => $this->model->deducts(),
                'allowancededucts'  => $this->model->allowancededucts(),
            ];
            return view('hrm::salary-setup.form', $data);
        } else {
            return $this->access_blocked();
        }
    }
    

    public function edit(Request $request)
    {
        if($request->ajax()){
            if(permission('salary-setup-edit')){
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
            if(permission('salary-setup-delete')){
                $result   = $this->model->find($request->id)->delete();
                $output   = $this->delete_message($result);
                $this->model->flushCache();
            }else{
                $output   = $this->unauthorized();

            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function bulk_delete(Request $request)
    {
        if($request->ajax()){
            if(permission('salary-setup-bulk-delete')){
                $result   = $this->model->destroy($request->ids);
                $output   = $this->bulk_delete_message($result);
                $this->model->flushCache();
            }else{
                $output   = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function change_status(Request $request)
    {
        if($request->ajax()){
            if(permission('salary-setup-edit')){
                $result   = $this->model->find($request->id)->update(['status' => $request->status]);
                $output   = $result ? ['status' => 'success','message' => 'Status Has Been Changed Successfully']
                : ['status' => 'error','message' => 'Failed To Change Status'];
                $this->model->flushCache();
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }
    public function employee_id_wise_salary_list(int $id)
    {

        $employee_basic  = Employee::toBase()->where('id', $id)->first();
        $res["list"] = array();
        return json_encode($employee_basic);
    }
}
