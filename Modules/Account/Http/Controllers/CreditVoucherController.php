<?php

namespace Modules\Account\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\ChartOfAccount;
use Modules\Account\Entities\CreditVoucher;
use Modules\Account\Http\Requests\CreditVoucherFormRequest;

class CreditVoucherController extends BaseController
{
    private const VOUCHER_PREFIX = 'CV';
    public function __construct(CreditVoucher $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('credit-voucher-access')){
            $this->setPageData('Credit Voucher List','Credit Voucher List','far fa-money-bill-alt',[['name'=>'Accounts'],['name'=>'Credit Voucher List']]);
            return view('account::credit-voucher.list',compact('warehouses'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('credit-voucher-access')){

                if (!empty($request->start_date)) {
                    $this->model->setStartDate($request->start_date);
                }
                if (!empty($request->end_date)) {
                    $this->model->setEndDate($request->end_date);
                }
                if (!empty($request->voucher_no)) {
                    $this->model->setVoucherNo($request->voucher_no);
                }

                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('credit-voucher-view')){
                        $action .= ' <a class="dropdown-item view_data" data-id="' . $value->voucher_no . '" data-name="' . $value->voucher_no . '">'.self::ACTION_BUTTON['View'].'</a>';
                    }
                    if(permission('credit-voucher-delete') && $value->approve != 1){

                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->voucher_no . '" data-name="' . $value->voucher_no . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }
                    
                    $row = [];
                    $row[] = $no;
                    $row[] = date('d-M-Y',strtotime($value->voucher_date));
                    $row[] = $value->voucher_no;
                    
                    $row[] = $value->description;
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
        if(permission('credit-voucher-access')){
            $this->setPageData('Credit Voucher','Credit Voucher','far fa-money-bill-alt',[['name'=>'Accounts'],['name'=>'Credit Voucher']]);
            $data = [
            'voucher_no'             => self::VOUCHER_PREFIX.'-'.date('Ymd').rand(1,999),
            'coas' => ChartOfAccount::accounts(),
            // 'transactional_accounts' => ChartOfAccount::where(['status'=>1,'transaction'=>1])->orderBy('id','asc')->get(),
            // 'debit_accounts'         => ChartOfAccount::where(['status'=>1,'transaction'=>1])
            //                             ->where('code','like','1020101')
            //                             ->orWhere('code','like','10201020%')
            //                             ->orWhere('code','like','10201030%')
            //                             ->orderBy('id','asc')
            //                             ->get()
            ];
            return view('account::credit-voucher.create',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function store(CreditVoucherFormRequest $request)
    {
        if($request->ajax()){
            if(permission('credit-voucher-access')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $credit_voucher_transaction = [];
                    if ($request->has('credit_account')) {
                        foreach ($request->credit_account as $key => $value) {
                            //Credit Insert
                            $credit_voucher_transaction[] = array(
                                'chart_of_account_id' => $value['id'],
                                'voucher_no'          => $request->voucher_no,
                                'voucher_type'        => self::VOUCHER_PREFIX,
                                'voucher_date'        => $request->voucher_date,
                                'description'         => $request->remarks,
                                'debit'               => 0,
                                'credit'              => $value['amount'],
                                'posted'              => 1,
                                'approve'             => 3,
                                'created_by'          => auth()->user()->name,
                                'created_at'          => date('Y-m-d H:i:s')
                            );

                            //Cash In Insert
                            $debit_account = ChartOfAccount::find($request->debit_account_id);
                            $credit_voucher_transaction[] = array(
                                'chart_of_account_id' => $request->debit_account_id,
                                'voucher_no'          => $request->voucher_no,
                                'voucher_type'        => self::VOUCHER_PREFIX,
                                'voucher_date'        => $request->voucher_date,
                                'description'         => 'Credit Voucher From '.($debit_account ? $debit_account->name : ''),
                                'debit'               => $value['amount'],
                                'credit'              => 0,
                                'posted'              => 1,
                                'approve'             => 3,
                                'created_by'          => auth()->user()->name,
                                'created_at'          => date('Y-m-d H:i:s')
                            );
                        }
                    }

                    // dd($credit_voucher_transaction);
                    $result = $this->model->insert($credit_voucher_transaction);
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
            if(permission('credit-voucher-view')){
                $debit_voucher = DB::table('transactions as t')
                ->join('chart_of_accounts as coa','t.chart_of_account_id','=','coa.id')
                ->join('warehouses as w','t.warehouse_id','=','w.id')
                ->select('t.*','coa.name','w.name as warehouse_name')
                ->where(['voucher_no'=>$request->id,'credit'=>'0'])
                ->groupBy('t.chart_of_account_id')
                ->first();
                $credit_vouchers = DB::table('transactions as t')
                ->join('chart_of_accounts as coa','t.chart_of_account_id','=','coa.id')
                ->join('warehouses as w','t.warehouse_id','=','w.id')
                ->select('t.*','coa.name','w.name as warehouse_name')
                ->where(['voucher_no'=>$request->id,'debit'=>'0'])
                ->get();
                return view('account::credit-voucher.view-modal-data',compact('debit_voucher','credit_vouchers'))->render();
            }
        }
    }


    public function update(CreditVoucherFormRequest $request)
    {
        if($request->ajax()){
            if(permission('edit-voucher')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $this->model->where('voucher_no',$request->voucher_no)->delete();
                    $credit_voucher_transaction = [];
                    if ($request->has('credit_account')) {
                        foreach ($request->credit_account as $key => $value) {
                            //Credit Insert
                            $credit_voucher_transaction[] = array(
                                'chart_of_account_id' => $value['id'],
                                'warehouse_id'        => $request->warehouse_id,
                                'voucher_no'          => $request->voucher_no,
                                'voucher_type'        => self::VOUCHER_PREFIX,
                                'voucher_date'        => $request->voucher_date,
                                'description'         => $request->remarks,
                                'debit'               => 0,
                                'credit'              => $value['amount'],
                                'posted'              => 1,
                                'approve'             => 3,
                                'created_by'          => auth()->user()->name,
                                'created_at'          => date('Y-m-d H:i:s')
                            );

                            //Cash In Insert
                            $debit_account = ChartOfAccount::find($request->debit_account_id);
                            $credit_voucher_transaction[] = array(
                                'chart_of_account_id' => $request->debit_account_id,
                                'warehouse_id'        => $request->warehouse_id,
                                'voucher_no'          => $request->voucher_no,
                                'voucher_type'        => self::VOUCHER_PREFIX,
                                'voucher_date'        => $request->voucher_date,
                                'description'         => 'Credit Voucher From '.($debit_account ? $debit_account->name : ''),
                                'debit'               => $value['amount'],
                                'credit'              => 0,
                                'posted'              => 1,
                                'approve'             => 3,
                                'created_by'          => auth()->user()->name,
                                'created_at'          => date('Y-m-d H:i:s')
                            );
                        }
                    }

                    // dd($credit_voucher_transaction);
                    $result = $this->model->insert($credit_voucher_transaction);
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
