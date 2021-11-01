<?php

namespace Modules\Account\Http\Controllers;


use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Setting\Entities\Warehouse;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\CashAdjustment;
use Modules\Account\Http\Requests\CashAdjustmentFormRequest;

class CashAdjustmentController extends BaseController
{
    protected const VOUCHER_PREFIX = 'CHV';
    public function __construct(CashAdjustment $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('cash-adjustment-access')){
            $this->setPageData('Cash Adjustment List','Cash Adjustment List','far fa-money-bill-alt',[['name'=>'Accounts'],['name'=>'Cash Adjustment List']]);
            $warehouses = Warehouse::where('status',1)->pluck('name','id');
            return view('account::cash-adjustment.list',compact('warehouses'));
        }else{
            return $this->access_blocked();
        }
    }

    public function create()
    {
        if(permission('cash-adjustment-add')){
            $this->setPageData('Cash Adjustment','Cash Adjustment','far fa-money-bill-alt',[['name'=>'Accounts'],['name'=>'Cash Adjustment']]);
            $voucher_no = self::VOUCHER_PREFIX.'-'.date('Ymd').rand(1,999);
            $warehouses = Warehouse::where('status',1)->pluck('name','id');
            return view('account::cash-adjustment.create',compact('voucher_no','warehouses'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('cash-adjustment-access')){

                if (!empty($request->start_date)) {
                    $this->model->setStartDate($request->start_date);
                }
                if (!empty($request->end_date)) {
                    $this->model->setEndDate($request->end_date);
                }
                if (!empty($request->warehouse_id)) {
                    $this->model->setWarehouseID($request->warehouse_id);
                }

                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('cash-adjustment-edit') && $value->approve != 1){
                        $action .= ' <a class="dropdown-item" href="'.route("cash.adjustment.edit",$value->voucher_no).'">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }

                    if(permission('cash-adjustment-delete') && $value->approve != 1){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->voucher_no . '" data-name="' . $value->voucher_no . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }

                    if($value->approve == 3 && permission('cash-adjustment-approve'))
                        {
                        $voucher_approve = '<span class="label label-success label-pill label-inline approve_voucher" data-id="' . $value->voucher_no . '" data-name="' . $value->voucher_no . '" data-status="1" style="min-width:70px !important;cursor:pointer;">Approve It</span>';
                    }else{
                        $voucher_approve = VOUCHER_APPROVE_STATUS_LABEL[$value->approve];
                    }
                    
                    $row = [];
                    $row[] = $no;
                    $row[] = $value->warehouse_name;
                    $row[] = $value->voucher_no;
                    $row[] = date('d-M-Y',strtotime($value->voucher_date));;
                    $row[] = $value->description;
                    $row[] = number_format($value->debit,2);
                    $row[] = number_format($value->credit,2);
                    $row[] = $voucher_approve;
                    $row[] = $value->created_by;
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

    public function store(CashAdjustmentFormRequest $request)
    {
        if($request->ajax()){
            if(permission('cash-adjustment-add')){
                DB::beginTransaction();
                try {
                    $data = array(
                        'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', $this->coa_head_code('cash_in_hand'))->value('id'),
                        'warehouse_id'        => $request->warehouse_id,
                        'voucher_no'          => $request->voucher_no,
                        'voucher_type'        => 'ADJUSTMENT',
                        'voucher_date'        => $request->voucher_date,
                        'description'         => $request->remarks,
                        'debit'               => ($request->type == 'debit') ? $request->amount : 0,
                        'credit'              => ($request->type == 'credit') ? $request->amount : 0,
                        'posted'              => 1,
                        'approve'             => 1,
                        'created_by'          => auth()->user()->name,
                        'created_at'          => date('Y-m-d H:i:s')
                    );
                    $result = $this->model->create($data);
                    $output = $this->store_message($result, null);
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

    public function edit(string $voucher_no)
    {
        if(permission('cash-adjustment-edit')){
            $voucher_data = $this->model->where('voucher_no',$voucher_no)->first();
            if($voucher_data)
            {
                $this->setPageData('Edit Cash Adjustment','Edit Cash Adjustment','far fa-money-bill-alt',[['name'=>'Accounts'],['name'=>'Edit Cash Adjustment']]);
                $warehouses = Warehouse::where('status',1)->pluck('name','id');
                return view('account::cash-adjustment.edit',compact('voucher_data','warehouses'));
            }else{
                return redirect()->back();
            }
        }else{
            return $this->access_blocked();
        }
    }

    public function update(CashAdjustmentFormRequest $request)
    {
        if($request->ajax()){
            if(permission('cash-adjustment-edit')){
                DB::beginTransaction();
                try {
                    $this->model->where('voucher_no',$request->voucher_no)->delete();
                    $data = array(
                        'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', $this->coa_head_code('cash_in_hand'))->value('id'),
                        'warehouse_id'        => $request->warehouse_id,
                        'voucher_no'          => $request->voucher_no,
                        'voucher_type'        => self::VOUCHER_PREFIX,
                        'voucher_date'        => $request->voucher_date,
                        'description'         => $request->remarks,
                        'debit'               => ($request->type == 'debit') ? $request->amount : 0,
                        'credit'              => ($request->type == 'credit') ? $request->amount : 0,
                        'posted'              => 1,
                        'approve'             => 3,
                        'created_by'          => auth()->user()->name,
                        'created_at'          => date('Y-m-d H:i:s')
                    );
                    $result = $this->model->create($data);
                    $output = $this->store_message($result, null);
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

    public function approve(Request $request)
    {
        if($request->ajax()){
            if(permission('cash-adjustment-approve')){
                $result   = $this->model->where('voucher_no',$request->id)->update(['approve' => $request->status]);
                $output   = $result ? ['status' => 'success','message' => 'Voucher Approved Successfully']
                : ['status' => 'error','message' => 'Failed To Approve Voucher'];
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
            if(permission('cash-adjustment-delete')){
                $result  = $this->model->where([['voucher_no',$request->id],['warehouse_id',auth()->user()->warehouse->id]])->delete();
                $output   = $this->delete_message($result);
                return response()->json($output);
            }else{
                return response()->json($this->unauthorized());
            }
        }else{
            return response()->json($this->unauthorized());
        }
    } 
}
