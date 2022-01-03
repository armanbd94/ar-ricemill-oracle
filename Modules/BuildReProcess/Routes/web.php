<?php

<<<<<<< HEAD
=======
use Illuminate\Support\Facades\Route;

>>>>>>> 21f3983e888b56601e339fadd0d8362bb84631aa
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

<<<<<<< HEAD
Route::prefix('buildreprocess')->group(function() {
    Route::get('/', 'BuildReProcessController@index');
});
=======

Route::group(['middleware' => ['auth']], function () {

    //Build Disassembly Route
    Route::get('build-re-process', 'BuildReProcessController@index')->name('build.re.process');
    Route::group(['prefix' => 'build-re-process', 'as'=>'build.re.process.'], function () {
        Route::get('create', 'BuildReProcessController@create')->name('create');
        Route::post('datatable-data', 'BuildReProcessController@get_datatable_data')->name('datatable.data');
        Route::post('store', 'BuildReProcessController@store')->name('store');
        Route::post('update', 'BuildReProcessController@update')->name('update');
        Route::get('edit/{id}', 'BuildReProcessController@edit')->name('edit');
        Route::get('view/{id}', 'BuildReProcessController@show')->name('view');
        Route::post('delete', 'BuildReProcessController@delete')->name('delete');
        Route::post('bulk-delete', 'BuildReProcessController@bulk_delete')->name('bulk.delete');
    });

});
>>>>>>> 21f3983e888b56601e339fadd0d8362bb84631aa
