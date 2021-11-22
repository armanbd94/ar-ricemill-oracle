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
    Route::get('via-vendor', 'ViaVendorController@index')->name('via.vendor');
    Route::group(['prefix' => 'via-vendor', 'as'=>'via.vendor.'], function () {
        Route::post('datatable-data', 'ViaVendorController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'ViaVendorController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'ViaVendorController@edit')->name('edit');
        Route::post('delete', 'ViaVendorController@delete')->name('delete');
        Route::post('bulk-delete', 'ViaVendorController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'ViaVendorController@change_status')->name('change.status');
        
    });
    Route::get('vendor-wise-list/{id}', 'ViaVendorController@vendor_wise_list');
});
