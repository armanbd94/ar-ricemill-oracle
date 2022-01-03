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
    //WIP Summary Report Route
    Route::get('wip-summary-report', 'WIPSummaryReportController@index');

    //Inventory Valuation Summary Report Route
    Route::get('inventory-valuation-summary', 'InventoryValuationSummaryController@index');

    //Todays Purchase Order Report Route
    Route::get('todays-purchase-order-report', 'TodaysPurchaseOrderReportController@index')->name('todays.purchase.order.report');
    Route::post('todays-purchase-order-report-data', 'TodaysPurchaseOrderReportController@report_data')->name('todays.purchase.order.report.data');

    //Purchase Order Report Route
    Route::get('purchase-order-report', 'PurchaseOrderReportController@index')->name('purchase.order.report');
    Route::post('purchase-order-report-data', 'PurchaseOrderReportController@report_data')->name('purchase.order.report.data');

    //Todays Sales Order Report Route
    Route::get('todays-sales-order-report', 'TodaysSalesOrderReportController@index')->name('todays.sales.order.report');
    Route::post('todays-sales-order-report-data', 'TodaysSalesOrderReportController@report_data')->name('todays.sales.order.report.data');

    //Sales Order Report Route
    Route::get('sales-order-report', 'SalesOrderReportController@index')->name('sales.order.report');
    Route::post('sales-order-report-data', 'SalesOrderReportController@report_data')->name('sales.order.report.data');
        
});