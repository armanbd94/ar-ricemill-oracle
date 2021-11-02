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
    Route::get('mobile-bank', 'MobileBankController@index')->name('mobilebank');
    Route::group(['prefix' => 'mobile-bank', 'as'=>'mobilebank.'], function () {
        Route::get('create', 'MobileBankController@create')->name('create');
        Route::post('datatable-data', 'MobileBankController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'MobileBankController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'MobileBankController@edit')->name('edit');
        Route::post('change-status', 'MobileBankController@change_status')->name('change.status');
        Route::post('delete', 'MobileBankController@delete')->name('delete');
    });
    Route::get('warehouse-wise-mobile-bank-list/{warehouse_id}', 'MobileBankController@warehouse_wise_mobile_bank_list')->name('warehouse.wise.mobile.bank.list');

    Route::get('mobile-bank-transaction', 'MobileBankTransactionController@index')->name('mobilebank.transaction');
    Route::post('store-mobile-bank-transaction', 'MobileBankTransactionController@store')->name('store.mobilebank.transaction');

    Route::get('mobile-bank-ledger', 'MobileBankController@bank_ledger')->name('mobilebank.ledger');
    Route::post('mobile-bank-ledger-data', 'MobileBankController@bank_ledger_data')->name('mobilebank.ledger.data');

});
