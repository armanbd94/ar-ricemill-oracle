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

    //Expense Item Routes
    Route::get('expense-item', 'ExpenseItemController@index')->name('expense.item');
    Route::group(['prefix' => 'expense-item', 'as'=>'expense.item.'], function () {
        Route::post('datatable-data', 'ExpenseItemController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'ExpenseItemController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'ExpenseItemController@edit')->name('edit');
        Route::post('delete', 'ExpenseItemController@delete')->name('delete');
        Route::post('bulk-delete', 'ExpenseItemController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'ExpenseItemController@change_status')->name('change.status');
    });

    //Expense  Routes
    Route::get('expense', 'ExpenseController@index')->name('expense');
    Route::group(['prefix' => 'expense', 'as'=>'expense.'], function () {
        Route::post('datatable-data', 'ExpenseController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'ExpenseController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'ExpenseController@edit')->name('edit');
        Route::post('delete', 'ExpenseController@delete')->name('delete');
        Route::post('bulk-delete', 'ExpenseController@bulk_delete')->name('bulk.delete');
    });

    //Expense Statement
    Route::get('expense-statement', 'ExpenseStatementController@index')->name('expense.statement');
    Route::post('expense-statement/datatable-data', 'ExpenseStatementController@get_datatable_data')->name('expense.statement.datatable.data');
});
