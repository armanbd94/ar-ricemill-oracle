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

    /*** Start : Sales Report ***/
    //Sales By Item Summary Report
    Route::get('sales-by-item-summary', 'SalesReport\ItemSummaryReportController@index')->name('sales.item.summary');
    Route::post('sales-by-item-summary-data', 'SalesReport\ItemSummaryReportController@report_data')->name('sales.item.summary.data');
    //Sales By Item Details Report
    Route::get('sales-by-item-details', 'SalesReport\ItemDetailsReportController@index')->name('sales.item.details');
    Route::post('sales-by-item-details-data', 'SalesReport\ItemDetailsReportController@report_data')->name('sales.item.details.data');
    //Sales By Customer Summary Report
    Route::get('sales-by-customer-summary', 'SalesReport\CustomerSummaryReportController@index')->name('sales.customer.summary');
    Route::post('sales-by-customer-summary-data', 'SalesReport\CustomerSummaryReportController@report_data')->name('sales.customer.summary.data');
    Route::get('sales-by-customer-summary-details/{customer_id}', 'SalesReport\CustomerSummaryReportController@customer_summary_details')->name('sales.customer.summary.details');
    Route::post('sales-by-customer-summary-details-data', 'SalesReport\CustomerSummaryReportController@customer_summary_details_data')->name('sales.customer.summary.details.data');
    //Daily Sale Status One
    Route::get('daily-sales-status', 'SalesReport\DailySalesStatusController@index')->name('daily.sales.status');
    Route::post('daily-sales-status-data', 'SalesReport\DailySalesStatusController@report_data')->name('daily.sales.status.data');
     
    /*** End : Sales Report ***/

    /*** Start : Purchase Report ***/
    //Purchase By Item Summary
    Route::get('purchase-by-item-summary', 'PurchaseReport\ItemSummaryReportController@index')->name('purchase.item.summary');
    Route::post('purchase-by-item-summary-data', 'PurchaseReport\ItemSummaryReportController@report_data')->name('purchase.item.summary.data');
    //Sales By Customer Summary Report
    Route::get('purchase-by-vendor-summary', 'PurchaseReport\VendorSummaryReportController@index')->name('purchase.vendor.summary');
    Route::post('purchase-by-vendor-summary-data', 'PurchaseReport\VendorSummaryReportController@report_data')->name('purchase.vendor.summary.data');
    Route::get('purchase-by-vendor-summary-details/{vendor_id}', 'PurchaseReport\VendorSummaryReportController@vendor_summary_details')->name('purchase.vendor.summary.details');
    Route::post('purchase-by-vendor-summary-details-data', 'PurchaseReport\VendorSummaryReportController@vendor_summary_details_data')->name('purchase.vendor.summary.details.data');
    //Daily Purchase Status
    Route::get('daily-purchase-status', 'PurchaseReport\DailyPurchaseStatusController@index')->name('daily.purchase.status');
    Route::post('daily-purchase-status-data', 'PurchaseReport\DailyPurchaseStatusController@report_data')->name('daily.purchase.status.data');
    /*** End : Purchase Report ***/

    /*** Start :: Vendors & Payables Report ***/
    //Vendor Balance Summary
    Route::get('vendor-balance-summary', 'Vendors\VendorBalanceSummaryController@index')->name('vendor.balance.summary');
    Route::post('vendor-balance-summary-data', 'Vendors\VendorBalanceSummaryController@report_data')->name('vendor.balance.summary.data');
    //Daily Payment Report
    Route::get('daily-payment-report', 'Vendors\DailyPaymentReportController@index')->name('daily.payment.report');
    Route::post('daily-payment-report-data', 'Vendors\DailyPaymentReportController@report_data')->name('daily.payment.report.data');
    /*** End :: Vendors & Payables Report ***/

    /*** Start :: Customers & Receivables Report ***/
    //Customer Balance Summary
    Route::get('customer-balance-summary', 'Customers\CustomerBalanceSummaryController@index')->name('customer.balance.summary');
    Route::post('customer-balance-summary-data', 'Customers\CustomerBalanceSummaryController@report_data')->name('customer.balance.summary.data');
    //Daily Collection Report
    Route::get('daily-collection-report', 'Customers\DailyCollectionReportController@index')->name('daily.collection.report');
    Route::post('daily-collection-report-data', 'Customers\DailyCollectionReportController@report_data')->name('daily.collection.report.data');
    /*** End :: Customers & Receivables Report ***/

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