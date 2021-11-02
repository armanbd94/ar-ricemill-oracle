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

    //Site Routes
    Route::get('site', 'JobTypeController@index')->name('site');
    Route::group(['prefix' => 'site', 'as'=>'site.'], function () {
        Route::post('datatable-data', 'JobTypeController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'JobTypeController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'JobTypeController@edit')->name('edit');
        Route::post('delete', 'JobTypeController@delete')->name('delete');
        Route::post('bulk-delete', 'JobTypeController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'SiteController@change_status')->name('change.status');
    });

    //Location Routes
    Route::get('location', 'JobTypeController@index')->name('location');
    Route::group(['prefix' => 'location', 'as'=>'location.'], function () {
        Route::post('datatable-data', 'LocationController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'LocationController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'LocationController@edit')->name('edit');
        Route::post('delete', 'LocationController@delete')->name('delete');
        Route::post('bulk-delete', 'LocationController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'LocationController@change_status')->name('change.status');
    });

    //Job Type Routes
    Route::get('job-type', 'JobTypeController@index')->name('job.type');
    Route::group(['prefix' => 'job-type', 'as'=>'job.type.'], function () {
        Route::post('datatable-data', 'JobTypeController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'JobTypeController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'JobTypeController@edit')->name('edit');
        Route::post('delete', 'JobTypeController@delete')->name('delete');
        Route::post('bulk-delete', 'JobTypeController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'JobTypeController@change_status')->name('change.status');
    });

    //WIP Batch Routes
    Route::get('wip-batch', 'BatchController@index')->name('wip.batch');
    Route::group(['prefix' => 'wip-batch', 'as'=>'wip.batch.'], function () {
        Route::post('datatable-data', 'BatchController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'BatchController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'BatchController@edit')->name('edit');
        Route::post('delete', 'BatchController@delete')->name('delete');
        Route::post('bulk-delete', 'BatchController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'BatchController@change_status')->name('change.status');
    });

});
