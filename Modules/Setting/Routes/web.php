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
