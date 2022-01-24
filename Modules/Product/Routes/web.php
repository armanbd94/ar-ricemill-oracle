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
    //Product Routes
    Route::get('product', 'ProductController@index')->name('product');
    Route::group(['prefix' => 'product', 'as'=>'product.'], function () {
        Route::post('datatable-data', 'ProductController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'ProductController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'ProductController@edit')->name('edit');
        Route::post('view', 'ProductController@show')->name('view');
        Route::post('delete', 'ProductController@delete')->name('delete');
        Route::post('bulk-delete', 'ProductController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'ProductController@change_status')->name('change.status');
        Route::get('generate-code', 'ProductController@generateProductCode')->name('generate.code');
        Route::post('list', 'ProductController@product_list')->name('list');
        Route::post('stock-qty', 'ProductController@stock_qty')->name('stock.qty');
    });
    //Group
    Route::get('group', 'ItemGroupController@index')->name('group');
    Route::group(['prefix' => 'group', 'as'=>'group.'], function () {
        Route::post('datatable-data', 'ItemGroupController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'ItemGroupController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'ItemGroupController@edit')->name('edit');
        Route::post('delete', 'ItemGroupController@delete')->name('delete');
        Route::post('bulk-delete', 'ItemGroupController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'ItemGroupController@change_status')->name('change.status');
    });
});
