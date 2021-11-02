<?php

namespace Modules\Supplier\Http\Controllers;

use Exception;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Supplier\Entities\Supplier;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\Transaction;
use Modules\Account\Entities\ChartOfAccount;
use Modules\Supplier\Http\Requests\SupplierFormRequest;


class SupplierController extends BaseController
{
    public function __construct(Supplier $model)
    {
        $this->model = $model;
    }


    public function index()
    {
        if(permission('vendor-access')){
            $this->setPageData('Supplier','Supplier','fas fa-th-list',[['name'=>'Supplier']]);
            return view('supplier::index');
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('vendor-access')){

                if (!empty($request->name)) {
                    $this->model->setName($request->name);
                }
                if (!empty($request->mobile)) {
                    $this->model->setMobile($request->mobile);
                }
                if (!empty($request->email)) {
                    $this->model->setEmail($request->email);
                }

                if (!empty($request->status)) {
                    $this->model->setStatus($request->status);
                }

                $this->set_datatable_default_properties($request);//set datatable default properties
                $list              = $this->model->getDatatableList();              //get table data
                $data              = [];
                $no                = $request->input('start');
                $currency_symbol   = config('settings.currency_symbol');
                $currency_position = config('settings.currency_position');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('vendor-edit')){
                        $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if(permission('vendor-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->name . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }

                    $row = [];
                    if(permission('vendor-bulk-delete')){
                        $row[] = row_checkbox($value->id);
                    }
                    $balance = $this->model->supplier_balance($value->id);
                    $balance = ($currency_position == 1) ? $currency_symbol.' '.$balance : $balance.' '.$currency_symbol;
                    $row[] = $no;
                    $row[] = $value->company_name ? $value->name.' ('.$value->company_name.')' : $value->name;
                    $row[] = $value->address;
                    $row[] = $value->mobile;
                    $row[] = $value->email;
                    $row[] = $value->city;
                    $row[] = $value->zipcode;
                    $row[] = permission('vendor-edit') ? change_status($value->id,$value->status, $value->name) : STATUS_LABEL[$value->status];
                    $row[] = $balance;
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



    public function store_or_update_data(SupplierFormRequest $request)
    {
        if($request->ajax()){
            if(permission('vendor-add')){
                DB::beginTransaction();
                try {
                    $collection = collect($request->validated())->except('previous_balance');
                    $collection = $this->track_data($collection,$request->update_id);
                    $supplier   = $this->model->updateOrCreate(['id'=>$request->update_id],$collection->all());
                    $output     = $this->store_message($supplier, $request->update_id);
                    if(empty($request->update_id))
                    {
                        $coa_max_code      = ChartOfAccount::where('level',3)->where('code','like','50201%')->max('code');
                        $code              = $coa_max_code ? ($coa_max_code + 1) : $this->coa_head_code('default_supplier');
                        $head_name         = $supplier->id.'-'.$supplier->name;
                        $supplier_coa_data = $this->supplier_coa($code,$head_name,$supplier->id);
                        
                        $supplier_coa      = ChartOfAccount::create($supplier_coa_data);
                        if(!empty($request->previous_balance))
                        {
                            if($supplier_coa){
                                $this->previous_balance_add($request->previous_balance,$supplier_coa->id,$supplier->name);
                            }
                        }
                    }else{
                        $old_head_name = $request->update_id.'-'.$request->old_name;
                        $new_head_name = $request->update_id.'-'.$request->name;
                        $supplier_coa = ChartOfAccount::where(['name'=>$old_head_name,'supplier_id'=>$request->update_id])->first();
                        if($supplier_coa)
                        {
                            $supplier_coa_id = $supplier_coa->id;
                            $supplier_coa->update(['name'=>$new_head_name]);
                        }
                    }
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
    }
    

    private function supplier_coa(string $code,string $head_name,int $supplier_id)
    {
        return [
            'code'              => $code,
            'name'              => $head_name,
            'parent_name'       => 'Account Payable',
            'level'             => 3,
            'type'              => 'L',
            'transaction'       => 1,
            'general_ledger'    => 2,
            'customer_id'       => null,
            'supplier_id'       => $supplier_id,
            'budget'            => 2,
            'depreciation'      => 2,
            'depreciation_rate' => '0',
            'status'            => 1,
            'created_by'        => auth()->user()->name
        ];
    }

    private function previous_balance_add($balance, int $supplier_coa_id, string $supplier_name) {
        if(!empty($balance) && !empty($supplier_coa_id) && !empty($supplier_name)){
            $transaction_id = generator(10);
            // supplier debit for previous balance
            $cosdr = array(
                'warehouse_id'        => 1,
                'chart_of_account_id' => $supplier_coa_id,
                'voucher_no'          => $transaction_id,
                'voucher_type'        => 'PR Balance',
                'voucher_date'        => date("Y-m-d"),
                'description'         => 'Supplier credit For previous balance '.$supplier_name,
                'debit'               => 0,
                'credit'              => $balance,
                'posted'              => 1,
                'approve'             => 1,
                'created_by'          => auth()->user()->name,
                'created_at'          => date('Y-m-d H:i:s')
            );
            $inventory = array(
                'warehouse_id'        => 1,
                'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', $this->coa_head_code('inventory'))->value('id'),
                'voucher_no'          => $transaction_id,
                'voucher_type'        => 'PR Balance',
                'voucher_date'        => date("Y-m-d"),
                'description'         => 'Inventory credit for old sale for '.$supplier_name,
                'debit'               => 0,
                'credit'              => $balance,
                'posted'              => 1,
                'approve'             => 1,
                'created_by'          => auth()->user()->name,
                'created_at'          => date('Y-m-d H:i:s')
            ); 

            Transaction::insert([
                $cosdr,$inventory
            ]);
        }
    }

    public function edit(Request $request)
    {
        if($request->ajax()){
            if(permission('vendor-edit')){
                $data   = $this->model->with('previous_balance')->findOrFail($request->id);
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
            if(permission('vendor-delete')){
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
            if(permission('vendor-bulk-delete')){
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
            if(permission('vendor-edit')){
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

    public function due_amount(int $id)
    {
        $due_amount = $this->model->supplier_balance($id);

        if($due_amount < 0)
        {
            $due_amount = explode('-',$due_amount)[1];
        }
        return response()->json($due_amount);
    }
   
}
