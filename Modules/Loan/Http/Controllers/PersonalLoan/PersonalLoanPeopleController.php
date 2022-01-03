<?php

namespace Modules\Loan\Http\Controllers\PersonalLoan;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Loan\Entities\LoanPeople;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\ChartOfAccount;
use Modules\Loan\Http\Requests\PersonalLoanPeopleFormRequest;

class PersonalLoanPeopleController extends BaseController
{
    
    public function __construct(LoanPeople $model)
    {
        $this->model = $model;
    }      

    public function index()
    {
        if(permission('personal-loan-person-access')){
            $this->setPageData('Manage Company','Manage Company','far fa-money-bill-alt',[['name'=>'Loan','link'=>'javascript::void();'],['name' => 'Manage Company']]);
            $deletable = self::DELETABLE;
            return view('loan::personal-loan-person.index',compact('deletable'));
        }else{
            return $this->access_blocked();
        }
    }    

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('personal-loan-person-access')){

                if (!empty($request->name)) {
                    $this->model->setName($request->name);
                }

                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('personal-loan-person-edit')){
                        $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }

                    if(permission('personal-loan-person-delete')){
                        if($value->deletable == 2){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->name . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                        }
                    }

                    $row = [];
                    if(permission('personal-loan-person-bulk-delete')){
                        $row[] = ($value->deletable == 2) ? row_checkbox($value->id) : '';//custom helper function to show the table each row checkbox
                    }
                    $row[] = $no;
                    $row[] = $value->name;
                    $row[] = $value->phone;
                    $row[] = $value->address;
                    $row[] = permission('personal-loan-person-edit') ? change_status($value->id,$value->status, $value->name) : STATUS_LABEL[$value->status];
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

    
    public function store_or_update_data(PersonalLoanPeopleFormRequest $request)
    {
        if($request->ajax()){ 
            if(permission('personal-loan-person-add')){
                $collection   = collect($request->validated());
                $collection   = $this->track_data($collection,$request->update_id);
                $result       = $this->model->updateOrCreate(['id'=>$request->update_id],$collection->all());  
                if (empty($request->update_id)) {
                    if($request->loan_term_type == 1){
                        $coa_max_code      = ChartOfAccount::where('level', 3)->where('code', 'like', '502040201%')->max('code');
                        $code              = $coa_max_code ? ($coa_max_code + 1) : 502040201;
                        $head_name         = $result->id . '-' . $request->name;
                        $parent_head_name  = 'Loan Payable Short Term';

                    }else{
                        $coa_max_code      = ChartOfAccount::where('level', 3)->where('code', 'like', '502040101%')->max('code');
                        $code              = $coa_max_code ? ($coa_max_code + 1) : 502040101;
                        $head_name         = $result->id . '-' . $request->name;
                        $parent_head_name  = 'Loan Payable Long Term';
                    }
                    $person_coa_data = $this->person_coa($code, $head_name, $parent_head_name, $result->id);
                    $person_coa      = ChartOfAccount::create($person_coa_data);
                } else {
                    $old_head_name = $request->update_id . '-' . $request->old_name;
                    $new_head_name = $request->update_id . '-' . $request->name;
                    $person_coa  = ChartOfAccount::where(['name' => $old_head_name])->first();
                    if ($person_coa) {
                        $person_coa->update(['name' => $new_head_name]);
                    }
                }                
                $output       = $this->store_message($result, $request->update_id);
                $this->model->flushCache();
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }    
    
    private function person_coa(string $code, string $head_name, string $parent_head_name, int $loan_people_id)
    {
        return [
            'code'              => $code,
            'name'              => $head_name,
            'parent_name'       => $parent_head_name,
            'level'             => 4,
            'type'              => 'L',
            'is_transaction'       => 2,
            'general_ledger'    => 1,
            'budget'            => 2,
            'depreciation'      => 2,
            'depreciation_rate' => '0',
            'status'            => 1,
            'created_by'        => auth()->user()->name,
            'loan_people_id'       => $loan_people_id,
            'employee_id'       => null,
        ];
    }

    public function edit(Request $request)
    {
        if($request->ajax()){
            if(permission('personal-loan-person-edit')){
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
            if(permission('personal-loan-person-delete')){            
                $get_person_info = $this->model->where('id',$request->id)->first();
                $old_head_name = $get_person_info->id.'-'.$get_person_info->name;
                $person_coa  = ChartOfAccount::where(['name' => $old_head_name])->delete();
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
            if(permission('personal-loan-person-bulk-delete')){
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
            if(permission('personal-loan-person-edit')){
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

    public function person_id_wise_person_details(int $id)
    {
        $person_details = $this->model->where('id',$id)->first();

        $persondata=[
            'id' => $person_details->id, 
            'phone' => $person_details->phone, 
            'address' => $person_details->address 
        ];

        return json_encode($persondata);
    }
}
