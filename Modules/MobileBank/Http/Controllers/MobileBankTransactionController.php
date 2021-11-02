<?php

namespace Modules\MobileBank\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\Transaction;
use Modules\MobileBank\Entities\MobileBank;
use Modules\Account\Entities\ChartOfAccount;
use Modules\MobileBank\Http\Requests\MobileBankTransactionFormRequest;

class MobileBankTransactionController extends BaseController
{
    public function __construct(Transaction $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('mobile-bank-transaction-access')){
            $this->setPageData('Mobile Bank Transaction','Mobile Bank Transaction','far fa-money-bill-alt',[['name' => 'Bank Transaction']]);
            $warehouses = DB::table('warehouses')->where('status',1)->pluck('name','id');
            return view('mobilebank::transaction',compact('warehouses'));
        }else{
            return $this->access_blocked();
        }
    }

    public function store(MobileBankTransactionFormRequest $request)
    {
        if ($request->ajax()) {
            if (permission('mobile-bank-transaction-access')) {
                DB::beginTransaction();
                try {
                    $collection = collect($request->validated())->only(['voucher_date','voucher_no','description']);
                    $collection = $this->track_data($collection,$request->update_id);
                    if($request->account_type == 'Debit(+)')
                    {
                        $debit  = $request->amount;
                        $credit = 0;
                    }else{
                        $debit  = 0;
                        $credit = $request->amount;
                    }
                    $coa_mobile_bank_transaction = $collection->merge([
                        'chart_of_account_id' => ChartOfAccount::account_id_by_name($request->bank_name),//get chart of account(coa) id
                        'warehouse_id'        => $request->warehouse_id,
                        'voucher_type'        => 'Mobile Bank Transaction',
                        'debit'               => $debit,
                        'credit'              => $credit,
                        'posted'              => 1,
                        'approve'             => 1,
                    ]);
                    $bank_transaction = $this->model->create($coa_mobile_bank_transaction->all());
                    $coa_cash_transaction = $collection->merge([
                        'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', $this->coa_head_code('cash_in_hand'))->value('id'),//get chart of account(coa) id
                        'warehouse_id'        => $request->warehouse_id,
                        'voucher_type'        => 'Mobile Bank Transaction',
                        'debit'               => $credit,
                        'credit'              => $debit,
                        'posted'              => 1,
                        'approve'             => 1,
                    ]); 
                    $cash_transaction = $this->model->create($coa_cash_transaction->all());
                    if($bank_transaction && $cash_transaction)
                    {
                        $output = ['status'=>'success','message'=>'Data saved successfully'];
                    }else{
                        $output = ['status'=>'error','message'=>'Failed to data'];
                    } 
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollback();
                    $output = ['status' => 'error','message' => $e->getMessage()];
                }
            }else{
                $output = $this->unauthorized();
            }
            return response()->json($output);
        }
    }
}
