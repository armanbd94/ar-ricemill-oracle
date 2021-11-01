<?php

namespace Modules\MobileBank\Http\Controllers;


use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\Transaction;
use Modules\MobileBank\Entities\MobileBank;
use Modules\Account\Entities\ChartOfAccount;
use Modules\MobileBank\Http\Requests\MobileBankFormRequest;

class MobileBankController extends BaseController
{
    private const CASH_AT_MOBILE_BANK = 'Cash At Mobile Bank';
    public function __construct(MobileBank $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('mobile-bank-access')){
            $this->setPageData('Mobile Bank List','Mobile Bank List','fas fa-mobile-alt',[['name' => 'Mobile Bank List']]);
            $warehouses = DB::table('warehouses')->where('status',1)->pluck('name','id');
            return view('mobilebank::index',compact('warehouses'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('mobile-bank-access')){

                if (!empty($request->bank_name)) {
                    $this->model->setBankName($request->bank_name);
                }
                if (!empty($request->account_name)) {
                    $this->model->setAccountName($request->account_name);
                }
                if (!empty($request->account_number)) {
                    $this->model->setAccountNumber($request->account_number);
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
                    if(permission('mobile-bank-edit')){
                        $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if(permission('mobile-bank-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->bank_name . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }
                    $row = [];
                    $row[] = $no;
                    $row[] = $value->bank_name;
                    $row[] = $value->account_name;
                    $row[] = $value->account_number;
                    $row[] = $value->warehouse->name;
                    $row[] = config('settings.currency_position') == '1' ? config('settings.currency_symbol').number_format($this->bank_balance($value->bank_name),2)
                                : number_format($this->bank_balance($value->bank_name),2).config('settings.currency_symbol');
                    $row[] = permission('mobile-bank-edit') ? change_status($value->id,$value->status, $value->bank_name) : STATUS_LABEL[$value->status];
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

    public function store_or_update_data(MobileBankFormRequest $request)
    {
        if($request->ajax()){
            if(permission('mobile-bank-add') || permission('mobile-bank-edit')){
                DB::beginTransaction();
                try {
                    $collection = collect($request->validated());
                    $collection = $this->track_data($collection,$request->update_id);
                    $result   = $this->model->updateOrCreate(['id'=>$request->update_id],$collection->all());
                    $coa_code        = ChartOfAccount::mobileBankHeadCode();
                    if($coa_code){
                        $headcode = $coa_code + 1;
                    }else{
                        $headcode = "102010301";
                    }
                    $bank_coa['name'] = $request->bank_name;;
                    if(empty($request->update_id)){
                        $bank_coa['code']              = $headcode;
                        $bank_coa['parent_name']       = self::CASH_AT_MOBILE_BANK;
                        $bank_coa['level']             = 4;
                        $bank_coa['type']              = 'A';
                        $bank_coa['transaction']       = 1;               //1=yes,2=no
                        $bank_coa['general_ledger']    = 2;               //1=yes,2=no
                        $bank_coa['mobile_bank_id']    = $result->id;               //1=yes,2=no
                        $bank_coa['budget']            = 2;               //1=yes,2=no
                        $bank_coa['depreciation']      = 2;               //1=yes,2=no
                        $bank_coa['depreciation_rate'] = 0;
                    
                    }
                    $bank_coa = collect($bank_coa);
                    $bank_coa = $this->track_data($bank_coa,$request->update_id); 
                    
                    ChartOfAccount::updateOrCreate(['name'=>$request->bank_old_name],$bank_coa->all());
                    $output       = $this->store_message($result, $request->update_id);
                    
                    $this->model->flushCache();
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollback();
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

    public function edit(Request $request)
    {
        if($request->ajax()){
            if(permission('mobile-bank-edit')){
                $data   = $this->model->findOrFail($request->id);
                $output = $this->data_message($data); //if data found then it will return data otherwise return error message
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
            if(permission('mobile-bank-delete')){
                DB::beginTransaction();
                try {
                    $result = false;
                    $bank   = $this->model->find($request->id);
                    if($bank)
                    {
                        $coa = ChartOfAccount::where(['name' => $bank->bank_name])->first();
                        if($coa)
                        {
                            $coa_transactions = Transaction::where('chart_of_account_id',$coa->id)->get();
                            if($coa_transactions)
                            {
                                foreach ($coa_transactions as $value) {
                                    Transaction::where(['voucher_no'=>$value->voucher_no,'voucher_type' => 'Mobile Bank Transaction'])->delete();
                                }
                            }
                            $coa->delete();
                        }
                    $result = $bank->delete();
                    }
                    $output   = $result ? ['status' => 'success','message' => 'Data has been deleted successfully']
                    : ['status' => 'error','message' => 'Failed to delete data'];
                    $this->model->flushCache();
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollback();
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
            if(permission('mobile-bank-edit')){
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

    private function bank_balance(string $bank_name){
        $data = DB::table('transactions as t')
                ->leftjoin('chart_of_accounts as coa','t.chart_of_account_id','=','coa.id')
                ->select(DB::raw("SUM(t.debit) - SUM(t.credit) as balance"),'coa.name')
                ->groupBy('coa.name')
                ->where('coa.name',$bank_name)
                ->where('t.approve',1)
                ->first();
        return !empty($data) ? $data->balance : 0;
    }

    public function bank_ledger()
    {
        if(permission('mobile-bank-ledger-access')){
            $this->setPageData('Mobile Bank Ledger','Mobile Bank Ledger','fas fa-file-invoice-dollar',[['name' => 'Bank Ledger']]);
            $banks = MobileBank::allMobileBankList();
            return view('mobilebank::ledger',compact('banks'));
        }else{
            return $this->access_blocked();
        }
    }

    public function bank_ledger_data(Request $request)
    {
        if ($request->ajax()) {

            $from_date = !empty($request->from_date) ? $request->from_date : date('Y-m-d');
            $to_date   = !empty($request->to_date) ? $request->to_date : date('Y-m-d');

            $query = DB::table('transactions as t')
                ->select('t.id','t.chart_of_account_id', 't.voucher_no', 't.voucher_type', 't.voucher_date', 't.description', 't.debit', 't.credit', 't.posted', 't.approve','coa.name')
                ->leftjoin('chart_of_accounts as coa','t.chart_of_account_id','=','coa.id')
                ->where('coa.parent_name',self::CASH_AT_MOBILE_BANK);
            if(!empty($request->bank_name)){
                $query =  $query->where('coa.name',$request->bank_name);
            }
            
            $ledger_data = $query->where('t.voucher_date','>=',$from_date)
                                ->where('t.voucher_date','<=',$to_date)
                                ->where('t.approve',1)
                                ->orderBy('t.voucher_date','desc')
                                ->get();
            $ledger_data = $ledger_data->toArray();
            // dd($ledger_data);
            $total_credit = $total_debit = $balance = 0;

            if (!empty($ledger_data)) {
                foreach ($ledger_data as $index => $value) {
                        $ledger_data[$index]->debit_amount = $value->debit;
                        $total_debit += $ledger_data[$index]->debit_amount;
    
                        $ledger_data[$index]->balance = $balance + ($value->debit - $value->credit);
                        $ledger_data[$index]->credit_amount  = $value->credit;
                        $total_credit += $ledger_data[$index]->credit_amount;
                        $balance = $ledger_data[$index]->balance;
                  
                }
            }

            $data = [
                'ledger'       => $ledger_data,
                'total_debit'  => number_format($total_debit,2),
                'total_credit' => number_format($total_credit,2),
                'balance'      => number_format($balance,2)
            ];

            return view('mobilebank::ledger-data',$data)->render();
        }
    }

    public function warehouse_wise_mobile_bank_list(int $warehouse_id)
    {
        $banks = $this->model->where('warehouse_id',$warehouse_id)->get();
        return json_encode($banks);
    }
}
