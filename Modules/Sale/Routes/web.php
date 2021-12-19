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

    Route::group(['prefix' => 'sale', 'as'=>'sale.'], function () {
        //Sale Order Route
        Route::get('order', 'SaleController@index')->name('order');
        Route::group(['prefix' => 'order', 'as'=>'order.'], function () {
            Route::get('create', 'SaleController@create')->name('create');
            Route::post('datatable-data', 'SaleController@get_datatable_data')->name('datatable.data');
            Route::post('store', 'SaleController@store')->name('store');
            Route::post('update', 'SaleController@update')->name('update');
            Route::get('edit/{id}', 'SaleController@edit')->name('edit');
            Route::get('view/{id}', 'SaleController@show')->name('view');
            Route::post('delete', 'SaleController@delete')->name('delete');
            Route::post('bulk-delete', 'SaleController@bulk_delete')->name('bulk.delete');
        });

        //Sale Invoice Route
        Route::get('invoice', 'SaleInvoiceController@index')->name('invoice');
        Route::group(['prefix' => 'invoice', 'as'=>'invoice.'], function () {
            Route::get('form', 'SaleInvoiceController@purchase_received_memo_form')->name('form');
            Route::get('create', 'SaleInvoiceController@create')->name('create');
            Route::post('datatable-data', 'SaleInvoiceController@get_datatable_data')->name('datatable.data');
            Route::post('store', 'SaleInvoiceController@store')->name('store');
            Route::post('update', 'SaleInvoiceController@update')->name('update');
            Route::get('edit/{id}', 'SaleInvoiceController@edit')->name('edit');
            Route::get('view/{id}', 'SaleInvoiceController@show')->name('view');
            Route::post('delete', 'SaleInvoiceController@delete')->name('delete');
            Route::post('bulk-delete', 'SaleInvoiceController@bulk_delete')->name('bulk.delete');
        });

        //Cash Sale Route
        Route::get('cash', 'CashSaleController@index')->name('cash');
        Route::group(['prefix' => 'cash', 'as'=>'cash.'], function () {
            Route::get('create', 'CashSaleController@create')->name('create');
            Route::post('datatable-data', 'CashSaleController@get_datatable_data')->name('datatable.data');
            Route::post('store', 'CashSaleController@store')->name('store');
            Route::post('update', 'CashSaleController@update')->name('update');
            Route::get('edit/{id}', 'CashSaleController@edit')->name('edit');
            Route::get('view/{id}', 'CashSaleController@show')->name('view');
            Route::post('delete', 'CashSaleController@delete')->name('delete');
            Route::post('bulk-delete', 'CashSaleController@bulk_delete')->name('bulk.delete');
        });
    });
    
    
    
});