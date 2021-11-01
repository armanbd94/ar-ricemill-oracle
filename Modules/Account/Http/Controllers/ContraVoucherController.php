<?php

namespace Modules\Account\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Setting\Entities\Warehouse;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\Transaction;
use Modules\Account\Entities\ContraVoucher;
use Modules\Account\Entities\ChartOfAccount;
use Modules\Account\Http\Requests\ContraVoucherFormRequest;

class ContraVoucherController extends BaseController
{
    private const VOUCHER_PREFIX = 'CONTRAV';
    public function __construct(ContraVoucher $model)
    {
        $this->model = $model;
    }


    public function index()
    {
        if(permission('contra-voucher-access')){
            $this->setPageData('Contra Voucher List','Contra Voucher List','far fa-money-bill-alt',[['name'=>'Accounts'],['name'=>'Contra Voucher List']]);
            $warehouses = Warehouse::where('status',1)->pluck('name','id');
            return view('account::contra-voucher.list',compact('warehouses'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('contra-voucher-access')){

                if (!empty($request->start_date)) {
                    $this->model->setStartDate($request->start_date);
                }
                if (!empty($request->end_date)) {
                    $this->model->setEndDate($request->end_date);
                }
                if (!empty($request->voucher_no)) {
                    $this->model->setVoucherNo($request->voucher_no);
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
                    if(permission('contra-voucher-view')){
                        $action .= ' <a class="dropdown-item view_data" data-id="' . $value->voucher_no . '" data-name="' . $value->voucher_no . '">'.self::ACTION_BUTTON['View'].'</a>';
                    }
                    if(permission('contra-voucher-delete') && $value->approve != 1){

                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->voucher_no . '" data-name="' . $value->voucher_no . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }
                    
                    $row = [];
                    $row[] = $no;
                    $row[] = $value->warehouse_name;
                    $row[] = $value->voucher_no;
                    $row[] = date(config('settings.date_format'),strtotime($value->voucher_date));;
                    $row[] = $value->description;
                    $row[] = number_format($value->debit,2);
                    $row[] = number_format($value->credit,2);
                    $row[] = VOUCHER_APPROVE_STATUS_LABEL[$value->approve];
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

    public function create()
    {
        if(permission('contra-voucher-add')){
            $this->setPageData('Contra Voucher','Contra Voucher','far fa-money-bill-alt',[['name'=>'Accounts'],['name'=>'Contra Voucher']]);
            $data = [
                'voucher_no'             => self::VOUCHER_PREFIX.'-'.date('ymd').rand(1,999),
                'warehouses'             => Warehouse::where('status',1)->pluck('name','id'),
                'transactional_accounts' => ChartOfAccount::where(['status'=>1,'transaction'=>1])->orderBy('id','asc')->get(),
            ];
            return view('account::contra-voucher.create',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function store(ContraVoucherFormRequest $request)
    {
        if($request->ajax()){
            if(permission('contra-voucher-add')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $contra_voucher_transaction = [];
                    if ($request->has('contra_account')) {
                        foreach ($request->contra_account as $key => $value) {
                            if(!empty($value['debit_amount']) || !empty($value['credit_amount'] ))
                            {
                                $contra_voucher_transaction[] = array(
                                    'chart_of_account_id' => $value['id'],
                                    'warehouse_id'        => $request->warehouse_id,
                                    'voucher_no'          => $request->voucher_no,
                                    'voucher_type'        => self::VOUCHER_PREFIX,
                                    'voucher_date'        => $request->voucher_date,
                                    'description'         => $request->remarks,
                                    'debit'               => $value['debit_amount'] ? $value['debit_amount'] : 0,
                                    'credit'              => $value['credit_amount'] ? $value['credit_amount'] : 0,
                                    'posted'              => 1,
                                    'approve'             => 3,
                                    'created_by'          => auth()->user()->name,
                                    'created_at'          => date('Y-m-d H:i:s')
                                );
                            }
                        }
                    }

                    $result = $this->model->insert($contra_voucher_transaction);
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

    public function show(Request $request)
    {
        if($request->ajax()){
            if(permission('contra-voucher-view')){
                $voucher = $this->model->with('coa','warehouse')->where('voucher_no',$request->id)->get();
                return view('account::contra-voucher.view-modal-data',compact('voucher'))->render();
            }
        }
    }

    public function update(ContraVoucherFormRequest $request)
    {
        if($request->ajax()){
            if(permission('voucher-edit')){
                DB::beginTransaction();
                try {
                    Transaction::where('voucher_no',$request->voucher_no)->delete();
                    $contra_voucher_transaction = [];
                    if ($request->has('contra_account')) {
                        foreach ($request->contra_account as $key => $value) {
                            //Credit Insert
                            if(!empty($value['debit_amount']) || !empty($value['credit_amount'] ))
                            {
                                $contra_voucher_transaction[] = array(
                                    'chart_of_account_id' => $value['id'],
                                    'warehouse_id'        => $request->warehouse_id,
                                    'voucher_no'          => $request->voucher_no,
                                    'voucher_type'        => self::VOUCHER_PREFIX,
                                    'voucher_date'        => $request->voucher_date,
                                    'description'         => $request->remarks,
                                    'debit'               => $value['debit_amount'] ? $value['debit_amount'] : 0,
                                    'credit'              => $value['credit_amount'] ? $value['credit_amount'] : 0,
                                    'posted'              => 1,
                                    'approve'             => 3,
                                    'created_by'          => auth()->user()->name,
                                    'created_at'          => date('Y-m-d H:i:s')
                                );
                            }
                        }
                    }

                    $result = $this->model->insert($contra_voucher_transaction);
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

}
