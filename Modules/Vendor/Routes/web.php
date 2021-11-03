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
    Route::get('vendor', 'SupplierController@index')->name('vendor');
    Route::group(['prefix' => 'vendor', 'as'=>'vendor.'], function () {
        Route::post('datatable-data', 'SupplierController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'SupplierController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'SupplierController@edit')->name('edit');
        Route::post('view', 'SupplierController@show')->name('view');
        Route::post('delete', 'SupplierController@delete')->name('delete');
        Route::post('bulk-delete', 'SupplierController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'SupplierController@change_status')->name('change.status');
        Route::get('due-amount/{id}', 'SupplierController@due_amount');

        Route::get('advance', 'SupplierAdvanceController@index')->name('advance');
        Route::group(['prefix' => 'advance', 'as'=>'advance.'], function () {
            Route::post('datatable-data', 'SupplierAdvanceController@get_datatable_data')->name('datatable.data');
            Route::post('store-or-update', 'SupplierAdvanceController@store_or_update_data')->name('store.or.update');
            Route::post('edit', 'SupplierAdvanceController@edit')->name('edit');
            Route::post('view', 'SupplierAdvanceController@show')->name('view');
            Route::post('delete', 'SupplierAdvanceController@delete')->name('delete');
            Route::post('bulk-delete', 'SupplierAdvanceController@bulk_delete')->name('bulk.delete');
            Route::post('change-approval-status', 'SupplierAdvanceController@change_approval_status')->name('change.approval.status');
        });

        Route::get('ledger', 'SupplierLedgerController@index')->name('ledger');
        Route::post('ledger/datatable-data', 'SupplierLedgerController@get_datatable_data')->name('ledger.datatable.data');
    });

    
});
