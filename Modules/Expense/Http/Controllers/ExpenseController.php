<?php

namespace Modules\Expense\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Expense\Entities\Expense;
use Modules\Setting\Entities\Warehouse;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\Transaction;
use Modules\Expense\Entities\ExpenseItem;
use Modules\Expense\Http\Requests\ExpenseFormRequest;

class ExpenseController extends BaseController
{
    public function __construct(Expense $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('expense-access')){
            $this->setPageData('Expense','Expense','fas fa-money-check-alt',[['name'=>'Expense','link'=>'javascript::void();'],['name' => 'Expense']]);
            $expense_items = ExpenseItem::allExpenseItems();
            return view('expense::expense.index',compact('expense_items'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('expense-access')){

                if (!empty($request->expense_item_id)) {
                    $this->model->setExpenseItemID($request->expense_item_id);
                }

                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('expense-edit')){
                        $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }

                    if(permission('expense-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="">'.self::ACTION_BUTTON['Delete'].'</a>';

                    }

                    $row = [];
                    if(permission('expense-bulk-delete')){
                        $row[] = row_checkbox($value->id);//custom helper function to show the table each row checkbox
                    }
                    $row[] = $no;
                    $row[] = date('d-M-Y',strtotime($value->date));
                    $row[] = $value->expense_item->name;
                    $row[] = $value->remarks;
                    $row[] = SALE_PAYMENT_METHOD[$value->payment_type];
                    $row[] = $value->coa->name;
                    $row[] = number_format($value->amount,2);
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

    public function store_or_update_data(ExpenseFormRequest $request)
    {
        if($request->ajax()){
            if(permission('expense-add')){
                DB::beginTransaction();
                try {
                    
                    $collection   = collect($request->validated());
                    if($request->update_id){
                        $voucher_no = DB::table('expenses')->where('id',$request->update_id)->value('voucher_no');
                    }else{
                        $voucher_no = 'EXP-'.date('ymd').rand(1,999);
                        $collection = $collection->merge(['voucher_no' => $voucher_no]);
                    }
                    
                    $collection   = $this->track_data($collection,$request->update_id);
                    $result       = $this->model->updateOrCreate(['id'=>$request->update_id],$collection->all());
                    $output       = $this->store_message($result, $request->update_id);

                    $expense_item   = ExpenseItem::find($request->expense_item_id);
                    $head_name      = $expense_item->id.'-'.$expense_item->name;
                    $expense_coa_id = DB::table('chart_of_accounts')->where('name',$head_name)->value('id');
                    $data = [
                        'expense_coa_id' => $expense_coa_id,
                        'expense_name'   => $expense_item->name,
                        'voucher_no'     => $voucher_no,
                        'voucher_date'   => $request->date,
                        'payment_type'   => $request->payment_type,
                        'account_id'     => $request->account_id,
                        'amount'         => $request->amount,
                        'remarks'         => $request->remarks
                    ];
                    if($request->update_id){
                        Transaction::where('voucher_no',$voucher_no)->delete();
                    }
                    $this->expense_balance_add($data);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $output = ['status' => 'error','message' => $e->getMessage()];
                }
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    private function expense_balance_add(array $data) {
        $voucher_type = 'Expense';
        // customer Debit
        $expense_acc = array(
            'chart_of_account_id' => $data['expense_coa_id'],
            'voucher_no'          => $data['voucher_no'],
            'voucher_type'        => $voucher_type,
            'voucher_date'        => $data['voucher_date'],
            'description'         => $data['remarks'] ? $data['remarks'] : 'Expense amount '.$data['amount'].'Tk in '.$data['expense_name'],
            'debit'               => $data['amount'],
            'credit'              => 0,
            'posted'              => 1,
            'approve'             => 1,
            'created_by'          => auth()->user()->name,
            'created_at'          => date('Y-m-d H:i:s')
        );
        


        if($data['payment_type'] == 1){
            //Cah In Hand debit
            $payment = array(
                'chart_of_account_id' => $data['account_id'],
                'voucher_no'          => $data['voucher_no'],
                'voucher_type'        => $voucher_type,
                'voucher_date'        => $data['voucher_date'],
                'description'         => $data['remarks'] ? $data['remarks'] : 'Credit amount '.$data['amount'].'Tk from cash for expense '.$data['expense_name'],
                'debit'               => 0,
                'credit'              => $data['amount'],
                'posted'              => 1,
                'approve'             => 1,
                'created_by'          => auth()->user()->name,
                'created_at'          => date('Y-m-d H:i:s')
                
            );
        }else{
            // Bank Ledger
            $payment = array(
                'chart_of_account_id' => $data['account_id'],
                'voucher_no'          => $data['voucher_no'],
                'voucher_type'        => $voucher_type,
                'voucher_date'        => $data['voucher_date'],
                'description'         => $data['remarks'] ? $data['remarks'] : 'Credit amount '.$data['amount'].'Tk from '.DB::table('chart_of_accounts')->where('id',$data['account_id'])->value('name').' for expense '.$data['expense_name'],
                'debit'               => 0,
                'credit'              => $data['amount'],
                'posted'              => 1,
                'approve'             => 1,
                'created_by'          => auth()->user()->name,
                'created_at'          => date('Y-m-d H:i:s')
            );
        }
        Transaction::insert([$expense_acc,$payment]);

    }

    public function edit(Request $request)
    {
        if($request->ajax()){
            if(permission('expense-edit')){
                $data   = $this->model->findOrFail($request->id);
                $output = [
                    'id' => $data->id,
                    'expense_item_id' => $data->expense_item_id, 
                    'voucher_no' => $data->voucher_no, 
                    'amount' => $data->amount, 
                    'date' => date('Y-m-d',strtotime($data->date)), 
                    'payment_type' => $data->payment_type, 
                    'account_id' => $data->account_id, 
                    'remarks' => $data->remarks
                ]; //if data found then it will return data otherwise return error message
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
            if(permission('expense-delete')){
                DB::beginTransaction();
                try {
                    $expense   = $this->model->find($request->id);
                    Transaction::where('voucher_no',$expense->voucher_no)->delete();
                    $result = $expense->delete();
                    $output   = $this->delete_message($result);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $output = ['status' => 'error','message' => $e->getMessage()];
                }
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
            if(permission('expense-bulk-delete')){
                DB::beginTransaction();
                try {
                    foreach ($request->ids as $id) {
                        $expense   = $this->model->find($id);
                        Transaction::where('voucher_no',$expense->voucher_no)->delete();
                    }
                    $result   = $this->model->destroy($request->ids);
                    $output   = $this->bulk_delete_message($result);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $output = ['status' => 'error','message' => $e->getMessage()];
                }
            }else{
                $output   = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

}
