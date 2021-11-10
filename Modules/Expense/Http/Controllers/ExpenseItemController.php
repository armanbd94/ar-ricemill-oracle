<?php

namespace Modules\Expense\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Modules\Expense\Entities\ExpenseItem;
use Modules\Account\Entities\ChartOfAccount;
use Modules\Expense\Http\Requests\ExpenseItemFormRequest;

class ExpenseItemController extends BaseController
{
    public function __construct(ExpenseItem $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('expense-item-access')){
            $this->setPageData('Expense Item','Expense Item','fas fa-money-check-alt',[['name'=>'Expense','link'=>'javascript::void();'],['name' => 'Expense Item']]);
            return view('expense::expense-item.index');
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('expense-item-access')){

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
                    if(permission('expense-item-edit')){
                        $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }

                    if(permission('expense-item-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->name . '">'.self::ACTION_BUTTON['Delete'].'</a>';

                    }

                    $row = [];
                    if(permission('expense-item-bulk-delete')){
                        $row[] = row_checkbox($value->id);//custom helper function to show the table each row checkbox
                    }
                    $row[] = $no;
                    $row[] = $value->name;
                    $row[] = permission('expense-item-edit') ? change_status($value->id,$value->status, $value->name) : STATUS_LABEL[$value->status];
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

    public function store_or_update_data(ExpenseItemFormRequest $request)
    {
        if($request->ajax()){
            if(permission('expense-item-add')){
                $collection   = collect($request->validated());
                $collection   = $this->track_data($collection,$request->update_id);
                $expense_item       = $this->model->updateOrCreate(['id'=>$request->update_id],$collection->all());
                $output       = $this->store_message($expense_item, $request->update_id);
                if(empty($request->update_id))
                {
                    $coa_max_code      = ChartOfAccount::where('level',1)->where('code','like','4000%')->max('code');
                    $code              = $coa_max_code ? ($coa_max_code + 1) : "4000000001";
                    $head_name         = $expense_item->id.'-'.$expense_item->name;
                    $expense_coa_data = $this->expense_coa($code,$head_name);
                    $expense_coa      = ChartOfAccount::create($expense_coa_data);
                }else{
                    $old_head_name = $request->update_id.'-'.$request->old_name;
                    $new_head_name = $request->update_id.'-'.$request->name;
                    $expense_coa = ChartOfAccount::where(['name'=>$old_head_name])->first();
                    if($expense_coa)
                    {
                        $expense_coa->update(['name'=>$new_head_name]);
                    }
                }
                $this->model->flushCache();
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    private function expense_coa(string $code,string $head_name)
    {
        return [
            'code'              => $code,
            'name'              => $head_name,
            'parent_name'       => 'Expense',
            'level'             => 1,
            'type'              => 'E',
            'is_transaction'    => 1,
            'general_ledger'    => 2,
            'customer_id'       => null,
            'supplier_id'       => null,
            'budget'            => 1,
            'depreciation'      => 1,
            'depreciation_rate' => 1,
            'status'            => 1,
            'created_by'        => auth()->user()->name
        ];
    }

    public function edit(Request $request)
    {
        if($request->ajax()){
            if(permission('expense-item-edit')){
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
            if(permission('expense-item-delete')){
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
            if(permission('expense-item-bulk-delete')){
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
            if(permission('expense-item-edit')){
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
}
