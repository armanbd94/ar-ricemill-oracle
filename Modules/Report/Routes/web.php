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
    //Closing Route
    Route::get('wip-summary-report', 'WIPSummaryReportController@index');
    Route::get('inventory-valuation-summary', 'InventoryValuationSummaryController@index');

});