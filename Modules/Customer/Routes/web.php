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
    Route::get('customer', 'CustomerController@index')->name('customer');
    Route::group(['prefix' => 'customer', 'as'=>'customer.'], function () {
        Route::post('datatable-data', 'CustomerController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'CustomerController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'CustomerController@edit')->name('edit');
        Route::post('view', 'CustomerController@show')->name('view');
        Route::post('delete', 'CustomerController@delete')->name('delete');
        Route::post('bulk-delete', 'CustomerController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'CustomerController@change_status')->name('change.status');
        Route::get('group-data/{id}','CustomerController@groupData');
        Route::get('previous-balance/{id}', 'CustomerController@previous_balance');
    });
    Route::post('customer-list','CustomerController@customer_list');

    //Customer Ledger Routes
    Route::get('customer-ledger', 'CustomerLedgerController@index')->name('customer.ledger');
    Route::post('customer-ledger/datatable-data', 'CustomerLedgerController@get_datatable_data')->name('customer.ledger.datatable.data');

    //Credit Customer Routes
    Route::get('credit-customer', 'CreditCustomerController@index')->name('credit.customer');
    Route::post('credit-customer/datatable-data', 'CreditCustomerController@get_datatable_data')->name('credit.customer.datatable.data');
    Route::post('credit-customer-list', 'CreditCustomerController@credit_customers')->name('credit.customer.list');

    //Paid Customer Routes
    Route::get('paid-customer', 'PaidCustomerController@index')->name('paid.customer');
    Route::post('paid-customer/datatable-data', 'PaidCustomerController@get_datatable_data')->name('paid.customer.datatable.data');
    Route::post('paid-customer-list', 'PaidCustomerController@paid_customers')->name('paid.customer.list');

    //Customer Advance Routes
    Route::get('customer-advance', 'CustomerAdvanceController@index')->name('customer.advance');
    Route::group(['prefix' => 'customer-advance', 'as'=>'customer.advance.'], function () {
        Route::post('datatable-data', 'CustomerAdvanceController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'CustomerAdvanceController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'CustomerAdvanceController@edit')->name('edit');
        Route::post('view', 'CustomerAdvanceController@show')->name('view');
        Route::post('delete', 'CustomerAdvanceController@delete')->name('delete');
        Route::post('bulk-delete', 'CustomerAdvanceController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'CustomerAdvanceController@change_status')->name('change.status');
    });

    Route::post('area-wise-customer-list', 'CustomerAdvanceController@area_wise_customer_list')->name('area.wise.customer.list');

    //Customer Group Routes
    Route::get('customer-group', 'CustomerGroupController@index')->name('customer.group');
    Route::group(['prefix' => 'customer-group', 'as'=>'customer.group.'], function () {
        Route::post('datatable-data', 'CustomerGroupController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'CustomerGroupController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'CustomerGroupController@edit')->name('edit');
        Route::post('delete', 'CustomerGroupController@delete')->name('delete');
        Route::post('bulk-delete', 'CustomerGroupController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'CustomerGroupController@change_status')->name('change.status');
    });
});
