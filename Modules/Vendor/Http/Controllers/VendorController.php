<?php

namespace Modules\Vendor\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Vendor\Entities\Vendor;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\Transaction;
use Modules\Account\Entities\ChartOfAccount;
use Modules\Vendor\Http\Requests\VendorFormRequest;

class VendorController extends BaseController
{
    public function __construct(Vendor $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('vendor-access')){
            $this->setPageData('Manage Vendor','Manage Vendor','fas fa-th-list',[['name'=>'Manage Vendor']]);
            return view('vendor::index');
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

                    $row[] = $no;
                    $row[] = $value->name;
                    $row[] = $value->trade_name;
                    $row[] = $value->mobile;
                    $row[] = $value->email;
                    $row[] = $value->address;
                    $row[] = permission('vendor-edit') ? change_status($value->id,$value->status, $value->name) : STATUS_LABEL[$value->status];
                    $row[] = $this->model->vendor_balance($value->id).' Tk';
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



    public function store_or_update_data(VendorFormRequest $request)
    {
        if($request->ajax()){
            if(permission('vendor-add')){
                DB::beginTransaction();
                try {
                    $collection = collect($request->validated())->except('previous_balance');
                    $collection = $this->track_data($collection,$request->update_id);
                    $vendor     = $this->model->updateOrCreate(['id'=>$request->update_id],$collection->all());
                    $output     = $this->store_message($vendor, $request->update_id);
                    if(empty($request->update_id))
                    {
                        $coa_max_code      = ChartOfAccount::where('level',3)->where('code','like','50201%')->max('code');
                        $code              = $coa_max_code ? ($coa_max_code + 1) : '50201000001';
                        $head_name         = $vendor->id.'-'.$vendor->trade_name;
                        $vendor_coa      = ChartOfAccount::create($this->model->coa_data($code,$head_name,$vendor->id));
                        if(!empty($request->previous_balance))
                        {
                            if($vendor_coa){
                                Transaction::insert($this->model->previous_balance_data($request->previous_balance,$vendor_coa->id,$vendor->trade_name));
                            }
                        }
                    }else{
                        $old_head_name = $request->update_id.'-'.$request->old_trade_name;
                        $new_head_name = $request->update_id.'-'.$request->trade_name;
                        $vendor_coa = ChartOfAccount::where(['name'=>$old_head_name,'vendor_id'=>$request->update_id])->first();
                        if($vendor_coa)
                        {
                            $vendor_coa->update(['name'=>$new_head_name]);
                        }
                    }
                    $this->model->flushCache();
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
                DB::beginTransaction();
                try {
                    $total_purchase_data = DB::table('purchase_orders')->where('vendor_id',$request->id)->get()->count();
                    if ($total_purchase_data > 0) {
                        $output = ['status'=>'error','message'=>'This data cannot delete because it is related with others data.'];
                    } else {
                        $vendor_coa_id = ChartOfAccount::where('vendor_id',$request->id)->first();
                        if($vendor_coa_id){
                            Transaction::where('chart_of_account_id',$vendor_coa_id->id)->delete();
                            $vendor_coa_id->delete();
                        }
                        $result   = $this->model->find($request->id)->delete();
                        $output   = $this->delete_message($result);
                    }
                    DB::commit();
                    $this->model->flushCache();
                } catch (Exception $e) {
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

    public function bulk_delete(Request $request)
    {
        if($request->ajax()){
            if(permission('vendor-bulk-delete')){
                DB::beginTransaction();
                try {
                    foreach ($request->ids as $id) {
                        $total_purchase_data = DB::table('purchase_orders')->where('vendor_id',$id)->get()->count();
                        if ($total_purchase_data > 0) {
                            $output = ['status'=>'error','message'=>'This data cannot delete because it is related with others data.'];
                        } else {
                            $vendor_coa_id = ChartOfAccount::where('vendor_id',$id)->first();
                            if($vendor_coa_id){
                                Transaction::where('chart_of_account_id',$vendor_coa_id->id)->delete();
                                $vendor_coa_id->delete();
                            }
                            
                        }
                    }
                    $result   = $this->model->destroy($request->ids);
                    $output   = $this->delete_message($result);
                    DB::commit();
                    $this->model->flushCache();
                } catch (Exception $e) {
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

    public function change_status(Request $request)
    {
        if($request->ajax()){
            if(permission('vendor-edit')){
                $result   = $this->model->find($request->id)->update(['status' => $request->status]);
                $output   = $result ? ['status' => 'success','message' => 'Status Has Been Changed Successfully']
                : ['status' => 'error','message' => 'Failed To Change Status'];
                $this->model->flushCache();
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
        $due_amount = $this->model->vendor_balance($id);

        if($due_amount < 0)
        {
            $due_amount = explode('-',$due_amount)[1];
        }
        return response()->json($due_amount);
    }
}
