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

    Route::group(['prefix' => 'purchase', 'as'=>'purchase.'], function () {
        //Purchase Order Route
        Route::get('order', 'PurchaseController@index')->name('order');
        Route::group(['prefix' => 'order', 'as'=>'order.'], function () {
            Route::get('create', 'PurchaseController@create')->name('create');
            Route::post('datatable-data', 'PurchaseController@get_datatable_data')->name('datatable.data');
            Route::post('store', 'PurchaseController@store')->name('store');
            Route::post('update', 'PurchaseController@update')->name('update');
            Route::get('edit/{id}', 'PurchaseController@edit')->name('edit');
            Route::get('view/{id}', 'PurchaseController@show')->name('view');
            Route::post('delete', 'PurchaseController@delete')->name('delete');
            Route::post('bulk-delete', 'PurchaseController@bulk_delete')->name('bulk.delete');
        });

        //Received Route
        Route::get('received', 'ReceivedItemController@index')->name('received');
        Route::group(['prefix' => 'received', 'as'=>'received.'], function () {
            Route::get('form', 'ReceivedItemController@purchase_received_memo_form')->name('form');
            Route::get('create', 'ReceivedItemController@create')->name('create');
            Route::post('datatable-data', 'ReceivedItemController@get_datatable_data')->name('datatable.data');
            Route::post('store', 'ReceivedItemController@store')->name('store');
            Route::post('update', 'ReceivedItemController@update')->name('update');
            Route::get('edit/{id}', 'ReceivedItemController@edit')->name('edit');
            Route::get('view/{id}', 'ReceivedItemController@show')->name('view');
            Route::post('delete', 'ReceivedItemController@delete')->name('delete');
            Route::post('bulk-delete', 'ReceivedItemController@bulk_delete')->name('bulk.delete');
        });

        //Cash Purchase Route
        Route::get('cash', 'CashPurchaseController@index')->name('cash');
        Route::group(['prefix' => 'cash', 'as'=>'cash.'], function () {
            Route::get('create', 'CashPurchaseController@create')->name('create');
            Route::post('datatable-data', 'CashPurchaseController@get_datatable_data')->name('datatable.data');
            Route::post('store', 'CashPurchaseController@store')->name('store');
            Route::post('update', 'CashPurchaseController@update')->name('update');
            Route::get('edit/{id}', 'CashPurchaseController@edit')->name('edit');
            Route::get('view/{id}', 'CashPurchaseController@show')->name('view');
            Route::post('delete', 'CashPurchaseController@delete')->name('delete');
            Route::post('bulk-delete', 'CashPurchaseController@bulk_delete')->name('bulk.delete');
        });
    });
    
    
    
});