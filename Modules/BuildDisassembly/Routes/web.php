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
Route::prefix('builddisassembly')->group(function() {
    Route::get('/', 'BuildDisassemblyController@index');
});
=======

Route::group(['middleware' => ['auth']], function () {

    //Build Disassembly Route
    Route::get('build-disassembly', 'BuildDisassemblyController@index')->name('build.disassembly');
    Route::group(['prefix' => 'build-disassembly', 'as'=>'build.disassembly.'], function () {
        Route::get('create', 'BuildDisassemblyController@create')->name('create');
        Route::post('datatable-data', 'BuildDisassemblyController@get_datatable_data')->name('datatable.data');
        Route::post('store', 'BuildDisassemblyController@store')->name('store');
        Route::post('update', 'BuildDisassemblyController@update')->name('update');
        Route::get('edit/{id}', 'BuildDisassemblyController@edit')->name('edit');
        Route::get('view/{id}', 'BuildDisassemblyController@show')->name('view');
        Route::post('delete', 'BuildDisassemblyController@delete')->name('delete');
        Route::post('bulk-delete', 'BuildDisassemblyController@bulk_delete')->name('bulk.delete');
    });

});
>>>>>>> 21f3983e888b56601e339fadd0d8362bb84631aa
