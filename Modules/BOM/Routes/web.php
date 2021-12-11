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


        Route::group(['prefix' => 'bom', 'as'=>'bom.'], function () {
            //BOM Process Routes
            Route::get('process', 'BOMController@index')->name('process');
            Route::group(['prefix' => 'process', 'as'=>'process.'],function(){
                Route::get('create', 'BOMController@create')->name('create');
                Route::post('datatable-data', 'BOMController@get_datatable_data')->name('datatable.data');
                Route::post('store', 'BOMController@store')->name('store');
                Route::post('update', 'BOMController@update')->name('update');
                Route::get('edit/{id}', 'BOMController@edit')->name('edit');
                Route::get('view/{id}', 'BOMController@show')->name('view');
                Route::post('delete', 'BOMController@delete')->name('delete');
                Route::post('bulk-delete', 'BOMController@bulk_delete')->name('bulk.delete');
            });
            
            //BOM Re Process Route
            Route::get('re-process', 'BOMReProcessController@index')->name('re.process');
            Route::group(['prefix' => 're-process', 'as'=>'re.process.'], function () {
                Route::get('create', 'BOMReProcessController@create')->name('create');
                Route::post('datatable-data', 'BOMReProcessController@get_datatable_data')->name('datatable.data');
                Route::post('store', 'BOMReProcessController@store')->name('store');
                Route::post('update', 'BOMReProcessController@update')->name('update');
                Route::get('edit/{id}', 'BOMReProcessController@edit')->name('edit');
                Route::get('view/{id}', 'BOMReProcessController@show')->name('view');
                Route::post('delete', 'BOMReProcessController@delete')->name('delete');
                Route::post('bulk-delete', 'BOMReProcessController@bulk_delete')->name('bulk.delete');
            });

            //BOM Re Packing Route
            Route::get('re-packing', 'BOMRePackingController@index')->name('re.packing');
            Route::group(['prefix' => 're-packing', 'as'=>'re.packing.'], function () {
                Route::get('create', 'BOMRePackingController@create')->name('create');
                Route::post('datatable-data', 'BOMRePackingController@get_datatable_data')->name('datatable.data');
                Route::post('store', 'BOMRePackingController@store')->name('store');
                Route::post('update', 'BOMRePackingController@update')->name('update');
                Route::get('edit/{id}', 'BOMRePackingController@edit')->name('edit');
                Route::get('view/{id}', 'BOMRePackingController@show')->name('view');
                Route::post('delete', 'BOMRePackingController@delete')->name('delete');
                Route::post('bulk-delete', 'BOMRePackingController@bulk_delete')->name('bulk.delete');
            });
        });

});