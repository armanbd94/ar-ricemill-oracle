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
    Route::get('via-customer', 'ViaCustomerController@index')->name('via.customer');
    Route::group(['prefix' => 'via-customer', 'as'=>'via.customer.'], function () {
        Route::post('datatable-data', 'ViaCustomerController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'ViaCustomerController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'ViaCustomerController@edit')->name('edit');
        Route::post('delete', 'ViaCustomerController@delete')->name('delete');
        Route::post('bulk-delete', 'ViaCustomerController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'ViaCustomerController@change_status')->name('change.status');
    });
});
