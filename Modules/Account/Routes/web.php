<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => ['auth']], function () {
    Route::get('account', 'AccountController@index')->name('account');
    Route::group(['prefix' => 'account', 'as'=>'account.'], function () {
        Route::post('list', 'AccountController@account_list')->name('list');
    });

    //COA Routes
    Route::get('coa', 'COAController@index')->name('coa');
    Route::group(['prefix' => 'coa', 'as'=>'coa.'], function () {
        Route::post('datatable-data', 'COAController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'COAController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'COAController@edit')->name('edit');
        Route::post('delete', 'COAController@delete')->name('delete');
        Route::post('parent-head', 'COAController@parent_head_data')->name('parent.head');
        
    });

    //Opening Balance Routes
    Route::resource('opening-balance', 'OpeningBalanceController')->only(['index','store']);

    //Cash Adjustment Routes
    Route::get('cash-adjustment', 'CashAdjustmentController@index');
    Route::group(['prefix' => 'cash-adjustment', 'as'=>'cash.adjustment.'], function () {
        Route::get('create', 'CashAdjustmentController@create')->name('create');
        Route::post('store', 'CashAdjustmentController@store')->name('store');
        Route::get('edit/{voucher_no}', 'CashAdjustmentController@edit')->name('edit');
        Route::post('update', 'CashAdjustmentController@update')->name('update');
        Route::post('delete', 'CashAdjustmentController@delete')->name('delete');
        Route::post('datatable-data', 'CashAdjustmentController@get_datatable_data')->name('datatable.data');
        Route::post('approve', 'VoucherApprovalController@approve')->name('approve');
    });

    //Vendor Payment Route
    Route::get('vendor-payment', 'VendorPaymentController@index')->name('vendor.payment');
    Route::group(['prefix' => 'vendor-payment', 'as'=>'vendor.payment.'], function () {
        Route::get('create', 'VendorPaymentController@create')->name('create');
        Route::post('store', 'VendorPaymentController@store')->name('store');
        Route::get('{id}/{payment_type}', 'VendorPaymentController@show')->name('show');
        Route::get('edit/{id}', 'VendorPaymentController@edit')->name('edit');
        Route::post('update', 'VendorPaymentController@update')->name('update');
        Route::post('delete', 'VendorPaymentController@delete')->name('delete');
        Route::post('datatable-data', 'VendorPaymentController@get_datatable_data')->name('datatable.data');
    });

    //Receive Payment Route
    Route::resource('receive-payment', 'CustomerReceiveController')->except(['show']);
    Route::post('receive-payment/datatable-data', 'CustomerReceiveController@get_datatable_data')->name('receive.payment.datatable.data');
    Route::get('receive-payment/{id}/{payment_type}', 'CustomerReceiveController@show');

    //Debit Voucher Route
    Route::get('debit-voucher', 'DebitVoucherController@index');
    Route::group(['prefix' => 'debit-voucher', 'as'=>'debit.voucher.'], function () {
        Route::get('create', 'DebitVoucherController@create')->name('create');
        Route::post('store', 'DebitVoucherController@store')->name('store');
        Route::post('update', 'DebitVoucherController@update')->name('update');
        Route::post('view', 'DebitVoucherController@show')->name('view');
        Route::post('datatable-data', 'DebitVoucherController@get_datatable_data')->name('datatable.data');
    });

    //Credit Voucher Route
    Route::get('credit-voucher', 'CreditVoucherController@index');
    Route::group(['prefix' => 'credit-voucher', 'as'=>'credit.voucher.'], function () {
        Route::get('create', 'CreditVoucherController@create')->name('create');
        Route::post('store', 'CreditVoucherController@store')->name('store');
        Route::post('update', 'CreditVoucherController@update')->name('update');
        Route::post('view', 'CreditVoucherController@show')->name('view');
        Route::post('datatable-data', 'CreditVoucherController@get_datatable_data')->name('datatable.data');
    });


    //Contra Voucher Route
    Route::get('contra-voucher', 'ContraVoucherController@index');
    Route::group(['prefix' => 'contra-voucher', 'as'=>'contra.voucher.'], function () {
        Route::get('create', 'ContraVoucherController@create')->name('create');
        Route::post('store', 'ContraVoucherController@store')->name('store');
        Route::post('update', 'ContraVoucherController@update')->name('update');
        Route::post('view', 'ContraVoucherController@show')->name('view');
        Route::post('datatable-data', 'ContraVoucherController@get_datatable_data')->name('datatable.data');
    });

    //Journal Voucher Route
    Route::get('journal-voucher', 'JournalVoucherController@index');
    Route::group(['prefix' => 'journal-voucher', 'as'=>'journal.voucher.'], function () {
        Route::get('create', 'JournalVoucherController@create')->name('create');
        Route::post('store', 'JournalVoucherController@store')->name('store');
        Route::post('update', 'JournalVoucherController@update')->name('update');
        Route::post('view', 'JournalVoucherController@show')->name('view');
        Route::post('datatable-data', 'JournalVoucherController@get_datatable_data')->name('datatable.data');
    });

    //Voucher Approval Route
    Route::get('voucher-approval', 'VoucherApprovalController@index')->name('voucher.approval');
    Route::get('voucher-update/{voucher_no}', 'VoucherApprovalController@edit')->name('voucher.update');
    Route::post('voucher-approval/datatable-data', 'VoucherApprovalController@get_datatable_data')->name('voucher.approval.datatable.data');
    Route::post('voucher-approval/delete', 'VoucherApprovalController@delete')->name('voucher.approval.delete');
    Route::post('voucher-approval/approve', 'VoucherApprovalController@approve')->name('voucher.approval.approve');

    //Cash Book Route
    Route::get('cash-book', 'Report\CashBookController@index');
    Route::post('cash-book/report', 'Report\CashBookController@report');

    //Inventory Ledger Route
    Route::get('inventory-ledger', 'Report\InventoryLedgerController@index');
    Route::post('inventory-ledger/report', 'Report\InventoryLedgerController@report');

    //Bank Book Route
    Route::get('bank-book', 'Report\BankBookController@index');
    Route::post('bank-book/report', 'Report\BankBookController@report');

    //Mobile Bank Book Route
    Route::get('mobile-bank-book', 'Report\MobileBankBookController@index');
    Route::post('mobile-bank-book/report', 'Report\MobileBankBookController@report');

    //General Ledger Route
    Route::get('general-ledger', 'Report\GeneralLedgerController@index');
    Route::post('general-ledger/report', 'Report\GeneralLedgerController@report');

    //Trial Balance Route
    Route::get('trial-balance', 'Report\TrialBalanceController@index');
    Route::post('trial-balance/report', 'Report\TrialBalanceController@report');

    //Profit Loss Route
    Route::get('profit-loss', 'Report\ProfitLossController@index');
    Route::post('profit-loss/report', 'Report\ProfitLossController@report');

    //Cash Flow Route
    Route::get('cash-flow', 'Report\CashFlowController@index');
    Route::post('cash-flow/report', 'Report\CashFlowController@report');

    //General Ledger Route
    Route::get('general-ledger', 'Report\GeneralLedgerController@index');
    Route::post('general-ledger/report', 'Report\GeneralLedgerController@report');
    Route::post('general-ledger/transaction-heads', 'Report\GeneralLedgerController@transaction_heads');

    //Trial Balance Route
    Route::get('balance-sheet', 'Report\BalanceSheetController@index');
    Route::post('balance-sheet/report', 'Report\BalanceSheetController@report');

});
