<?php

namespace Modules\ViaCustomer\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Customer\Entities\Customer;
use App\Http\Controllers\BaseController;
use Illuminate\Contracts\Support\Renderable;
use Modules\ViaCustomer\Entities\ViaCustomer;
use Modules\ViaCustomer\Http\Requests\ViaCustomerFormRequest;

class ViaCustomerController extends BaseController
{
    public function __construct(ViaCustomer $model)
    {
        $this->model = $model;
    }


    public function index()
    {
        if(permission('via-customer-access')){
            $this->setPageData('Via Customer','Via Customer','far fa-handshake',[['name'=>'Via Customer']]);
            $customers = Customer::allCustomers();
            return view('viacustomer::index',compact('customers'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('via-customer-access')){

                if (!empty($request->customer_id)) {
                    $this->model->setVendorID($request->customer_id);
                }
                if (!empty($request->name)) {
                    $this->model->setName($request->name);
                }
                if (!empty($request->mobile)) {
                    $this->model->setMobile($request->mobile);
                }
                if (!empty($request->trade_name)) {
                    $this->model->setTradeName($request->trade_name);
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
                    if(permission('via-customer-edit')){
                        $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if(permission('via-customer-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->name . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }

                    $row = [];
                    if(permission('via-customer-bulk-delete')){
                        $row[] = row_checkbox($value->id);
                    }
                    $row[] = $no;
                    $row[] = $value->code;
                    $row[] = $value->name;
                    $row[] = $value->trade_name;
                    $row[] = $value->mobile;
                    $row[] = $value->email;
                    $row[] = $value->address;
                    $row[] = $value->customer_trade_name;
                    $row[] = permission('via-customer-edit') ? change_status($value->id,$value->status, $value->name) : STATUS_LABEL[$value->status];
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



    public function store_or_update_data(ViaCustomerFormRequest $request)
    {
        if($request->ajax()){
            if(permission('via-customer-add')){
                DB::beginTransaction();
                try {
                    $collection = collect($request->validated());
                    $collection = $this->track_data($collection,$request->update_id);
                    if(empty($request->update_id))
                    {
                        $customer_id = DB::table('via_customers')->orderBy('id','desc')->first();
                        $code = 'VC-'.($customer_id ? explode('VC-',$customer_id->code)[1] + 1 : '1001');
                        $collection = $collection->merge(['code'=>$code]);
                    }
                    $via_vendor = $this->model->updateOrCreate(['id'=>$request->update_id],$collection->all());
                    $output     = $this->store_message($via_vendor, $request->update_id);
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
            if(permission('via-customer-edit')){
                $data   = $this->model->findOrFail($request->id);
                $output = $this->data_message($data);
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
            if(permission('via-customer-delete')){
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
            if(permission('via-customer-bulk-delete')){
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
            if(permission('via-customer-edit')){
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

    public function customer_wise_list(int $customer_id)
    {
        $via_customers = $this->model->where([['customer_id',$customer_id],['status',1]])->get();
        return json_encode($via_customers);
    }
}
