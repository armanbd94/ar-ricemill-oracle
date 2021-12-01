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

        //Transfer Inventory Route
        Route::get('transfer-inventory', 'TransferInventoryController@index')->name('transfer.inventory');
        Route::group(['prefix' => 'transfer-inventory', 'as'=>'transfer.inventory.'], function () {
            Route::get('create', 'TransferInventoryController@create')->name('create');
            Route::post('datatable-data', 'TransferInventoryController@get_datatable_data')->name('datatable.data');
            Route::post('store', 'TransferInventoryController@store')->name('store');
            Route::post('update', 'TransferInventoryController@update')->name('update');
            Route::get('edit/{id}', 'TransferInventoryController@edit')->name('edit');
            Route::get('view/{id}', 'TransferInventoryController@show')->name('view');
            Route::post('delete', 'TransferInventoryController@delete')->name('delete');
            Route::post('bulk-delete', 'TransferInventoryController@bulk_delete')->name('bulk.delete');

            //Transfer Inventory Mix Route
            Route::get('mix', 'TransferInventoryMixController@index')->name('mix');
            Route::group(['prefix' => 'mix', 'as'=>'mix.'], function () {
                Route::get('create', 'TransferInventoryMixController@create')->name('create');
                Route::post('datatable-data', 'TransferInventoryMixController@get_datatable_data')->name('datatable.data');
                Route::post('store', 'TransferInventoryMixController@store')->name('store');
                Route::post('update', 'TransferInventoryMixController@update')->name('update');
                Route::get('edit/{id}', 'TransferInventoryMixController@edit')->name('edit');
                Route::get('view/{id}', 'TransferInventoryMixController@show')->name('view');
                Route::post('delete', 'TransferInventoryMixController@delete')->name('delete');
                Route::post('bulk-delete', 'TransferInventoryMixController@bulk_delete')->name('bulk.delete');
            });
        });

});