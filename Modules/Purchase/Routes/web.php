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
    Route::get('purchase-order', 'PurchaseController@index')->name('purchase.order');
    Route::get('purchase-order/create', 'PurchaseController@create')->name('purchase.order.create');
    
    Route::get('received-item', 'ReceivedItemController@index')->name('received.item');
    Route::get('received-item/create', 'ReceivedItemController@create')->name('received.item.create');
    
});