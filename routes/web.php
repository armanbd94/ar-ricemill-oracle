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

Auth::routes(['register' => false]);


Route::group(['middleware' => ['auth']], function () {
    
    Route::get('/', 'HomeController@index')->name('dashboard');
    Route::get('dashboard-data/{start_date}/{end_date}', 'HomeController@dashboard_data');
    Route::get('unauthorized', 'HomeController@unauthorized')->name('unauthorized');
    Route::get('my-profile', 'MyProfileController@index')->name('my.profile');
    Route::post('update-profile', 'MyProfileController@updateProfile')->name('update.profile');
    Route::post('update-password', 'MyProfileController@updatePassword')->name('update.password');
    Route::get('stock-notification', 'HomeController@stock_alert');
    
    //Menu Routes
    Route::get('menu', 'MenuController@index')->name('menu');
    Route::group(['prefix' => 'menu', 'as'=>'menu.'], function () {
        Route::post('datatable-data', 'MenuController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'MenuController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'MenuController@edit')->name('edit');
        Route::post('delete', 'MenuController@delete')->name('delete');
        Route::post('bulk-delete', 'MenuController@bulk_delete')->name('bulk.delete');
        
        
        //Module Routes
        Route::post('module-order/{menu}','ModuleController@orderItem')->name('module.order');
        Route::get('builder/{menu}','ModuleController@index')->name('builder');
        Route::post('items','ModuleController@get_menu_modules')->name('items');
        Route::group(['prefix' => 'module', 'as'=>'module.'], function () {
            Route::get('create/{menu}','ModuleController@create')->name('create');
            Route::post('store-or-update','ModuleController@storeOrUpdate')->name('store.or.update');
            Route::post('edit','ModuleController@edit')->name('edit');
            Route::post('delete','ModuleController@delete')->name('delete');

            //Permission Routes
            Route::get('permission', 'PermissionController@index')->name('permission');
            Route::group(['prefix' => 'menu', 'as'=>'permission.'], function () {
                Route::post('datatable-data', 'PermissionController@get_datatable_data')->name('datatable.data');
                Route::post('store', 'PermissionController@store')->name('store');
                Route::post('edit', 'PermissionController@edit')->name('edit');
                Route::post('update', 'PermissionController@update')->name('update');
                Route::post('delete', 'PermissionController@delete')->name('delete');
                Route::post('bulk-delete', 'PermissionController@bulk_delete')->name('bulk.delete');
            });

        });
    });


    //Role Routes
    Route::get('role', 'RoleController@index')->name('role');
    Route::group(['prefix' => 'role', 'as'=>'role.'], function () {
        Route::get('create', 'RoleController@create')->name('create');
        Route::post('datatable-data', 'RoleController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'RoleController@store_or_update_data')->name('store.or.update');
        Route::get('edit/{id}', 'RoleController@edit')->name('edit');
        Route::get('view/{id}', 'RoleController@show')->name('view');
        Route::post('delete', 'RoleController@delete')->name('delete');
        Route::post('bulk-delete', 'RoleController@bulk_delete')->name('bulk.delete');
    });

    //User Routes
    Route::get('user', 'UserController@index')->name('user');
    Route::group(['prefix' => 'user', 'as'=>'user.'], function () {
        Route::post('datatable-data', 'UserController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'UserController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'UserController@edit')->name('edit');
        Route::post('view', 'UserController@show')->name('view');
        Route::post('delete', 'UserController@delete')->name('delete');
        Route::post('bulk-delete', 'UserController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'UserController@change_status')->name('change.status');
    });

    //Software Settings Route
    Route::get('setting', 'SettingController@index')->name('software.setting');
    Route::post('general-setting', 'SettingController@general_setting')->name('general.setting');
    Route::post('mail-setting', 'SettingController@mail_setting')->name('mail.setting');

    //Category Routes
    Route::get('{type}/category', 'CategoryController@index')->name('category');
    Route::group(['prefix' => 'category', 'as'=>'category.'], function () {
        Route::post('datatable-data', 'CategoryController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'CategoryController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'CategoryController@edit')->name('edit');
        Route::post('delete', 'CategoryController@delete')->name('delete');
        Route::post('bulk-delete', 'CategoryController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'CategoryController@change_status')->name('change.status');
    });

    //Class Routes
    Route::get('class', 'ItemClassController@index')->name('class');
    Route::group(['prefix' => 'class', 'as'=>'class.'], function () {
        Route::post('datatable-data', 'ItemClassController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'ItemClassController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'ItemClassController@edit')->name('edit');
        Route::post('delete', 'ItemClassController@delete')->name('delete');
        Route::post('bulk-delete', 'ItemClassController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'ItemClassController@change_status')->name('change.status');
    });

    //Unit Group Routes
    Route::get('unit', 'UnitController@index')->name('unit');
    Route::group(['prefix' => 'unit', 'as'=>'unit.'], function () {
        Route::post('datatable-data', 'UnitController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'UnitController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'UnitController@edit')->name('edit');
        Route::post('delete', 'UnitController@delete')->name('delete');
        Route::post('bulk-delete', 'UnitController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'UnitController@change_status')->name('change.status');
        Route::post('base-unit', 'UnitController@base_unit')->name('base.unit');
    });
    Route::get('populate-unit/{id}','UnitController@populate_unit');


    //Tax Routes
    Route::get('tax', 'TaxController@index')->name('tax');
    Route::group(['prefix' => 'tax', 'as'=>'tax.'], function () {
        Route::post('datatable-data', 'TaxController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'TaxController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'TaxController@edit')->name('edit');
        Route::post('delete', 'TaxController@delete')->name('delete');
        Route::post('bulk-delete', 'TaxController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'TaxController@change_status')->name('change.status');
    });

});
