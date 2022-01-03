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
    // Route::get('production', 'ProductionController@index')->name('production');
    // Route::get('production/create', 'ProductionController@create')->name('production.create');
    
    // Route::get('received-item', 'ReceivedItemController@index')->name('received.item');
    // Route::get('received-item/create', 'ReceivedItemController@create')->name('received.item.create');
    
    // Route::get('build-disassembly', 'DisassemblyController@create')->name('build.disassembly');

    // Route::get('build-reprocess', 'BuilReProcessController@create')->name('build.reprocess');

    // Route::get('bom-process', 'BOMProcessController@create')->name('bom.process');

    // Route::get('bom-repacking', 'BOMRePackingController@create')->name('bom.repacking');
});
