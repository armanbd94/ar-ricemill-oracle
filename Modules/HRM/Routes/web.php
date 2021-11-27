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

    //Department Routes
    Route::get('department', 'DepartmentController@index')->name('department');
    Route::group(['prefix' => 'department', 'as' => 'department.'], function () {
        Route::post('datatable-data', 'DepartmentController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'DepartmentController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'DepartmentController@edit')->name('edit');
        Route::post('delete', 'DepartmentController@delete')->name('delete');
        Route::post('bulk-delete', 'DepartmentController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'DepartmentController@change_status')->name('change.status');
    });

    //Division Routes
    Route::get('division', 'DivisionController@index')->name('division');
    Route::group(['prefix' => 'division', 'as' => 'division.'], function () {
        Route::post('datatable-data', 'DivisionController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'DivisionController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'DivisionController@edit')->name('edit');
        Route::post('delete', 'DivisionController@delete')->name('delete');
        Route::post('bulk-delete', 'DivisionController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'DivisionController@change_status')->name('change.status');
    });
    Route::get('department-id-wise-division-list/{id}', 'DivisionController@department_id_wise_division_list');

    //Designation Routes
    Route::get('designation', 'DesignationController@index')->name('designation');
    Route::group(['prefix' => 'designation', 'as' => 'designation.'], function () {
        Route::post('datatable-data', 'DesignationController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'DesignationController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'DesignationController@edit')->name('edit');
        Route::post('delete', 'DesignationController@delete')->name('delete');
        Route::post('bulk-delete', 'DesignationController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'DesignationController@change_status')->name('change.status');
    });

    //Employee Routes
    Route::get('employee', 'EmployeeController@index')->name('employee');
    Route::group(['prefix' => 'employee', 'as' => 'employee.'], function () {
        Route::post('datatable-data', 'EmployeeController@get_datatable_data')->name('datatable.data');
        Route::get('add', 'EmployeeController@create')->name('add');
        Route::post('store-or-update', 'EmployeeController@store_or_update_data')->name('store.or.update');
        Route::get('details/{id}', 'EmployeeController@show')->name('details');
        Route::get('edit/{id}', 'EmployeeController@edit')->name('edit');
        Route::post('delete', 'EmployeeController@delete')->name('delete');
        Route::post('bulk-delete', 'EmployeeController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'EmployeeController@change_status')->name('change.status');
        Route::post('shift-change', 'EmployeeController@change_shift')->name('change.shift');
        Route::post('allowance-deduction-setup', 'EmployeeController@setup_allowance_deduction')->name('setup.allowance.deduction');
    });
    //Allowance Routes
    Route::get('benifits', 'AllowanceDeductionController@index')->name('benifits');
    Route::group(['prefix' => 'benifits', 'as' => 'benifits.'], function () {
        Route::post('datatable-data', 'AllowanceDeductionController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'AllowanceDeductionController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'AllowanceDeductionController@edit')->name('edit');
        Route::post('delete', 'AllowanceDeductionController@delete')->name('delete');
        Route::post('bulk-delete', 'AllowanceDeductionController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'AllowanceDeductionController@change_status')->name('change.status');
    });
    //Allowance Mange Salary Setup  Routes
    Route::get('salary-setup', 'AllowanceDeductionManageController@index')->name('salary.setup');
    Route::group(['prefix' => 'salary.setup', 'as' => 'salary.setup.'], function () {
        Route::post('datatable-data', 'AllowanceDeductionManageController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'AllowanceDeductionManageController@store_or_update_data')->name('store.or.update');
        Route::get('add', 'AllowanceDeductionManageController@create')->name('add');
        Route::get('edit/{id}', 'AllowanceDeductionManageController@edit')->name('edit');
        Route::post('delete', 'AllowanceDeductionManageController@delete')->name('delete');
        Route::post('bulk-delete', 'AllowanceDeductionManageController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'AllowanceDeductionManageController@change_status')->name('change.status');
    });
    Route::get('employee-id-wise-salary-list/{id}', 'AllowanceDeductionManageController@employee_id_wise_salary_list');

    //HoliDay Routes
    Route::get('holiday', 'HolidayController@index')->name('holiday');
    Route::group(['prefix' => 'holiday', 'as' => 'holiday.'], function () {
        Route::post('datatable-data', 'HolidayController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'HolidayController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'HolidayController@edit')->name('edit');
        Route::post('delete', 'HolidayController@delete')->name('delete');
        Route::post('bulk-delete', 'HolidayController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'HolidayController@change_status')->name('change.status');
    });
    //Weekly HoliDay Routes
    Route::get('weekly-holiday', 'WeeklyHolidayController@index')->name('weekly.holiday');
    Route::group(['prefix' => 'weekly.holiday', 'as' => 'weekly.holiday.'], function () {
        Route::post('datatable-data', 'WeeklyHolidayController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'WeeklyHolidayController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'WeeklyHolidayController@edit')->name('edit');
        Route::post('delete', 'WeeklyHolidayController@delete')->name('delete');
        Route::post('bulk-delete', 'WeeklyHolidayController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'WeeklyHolidayController@change_status')->name('change.status');
    });
    
    //Route Routes
    Route::get('route', 'RouteController@index')->name('route');
    Route::group(['prefix' => 'route', 'as' => 'route.'], function () {
        Route::post('datatable-data', 'RouteController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'RouteController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'RouteController@edit')->name('edit');
        Route::post('delete', 'RouteController@delete')->name('delete');
        Route::post('bulk-delete', 'RouteController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'RouteController@change_status')->name('change.status');
    });

    //Leave Type Routes
    Route::get('leave-type', 'LeaveController@index')->name('leave.type');
    Route::group(['prefix' => 'leave.type', 'as' => 'leave.type.'], function () {
        Route::post('datatable-data', 'LeaveController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'LeaveController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'LeaveController@edit')->name('edit');
        Route::post('delete', 'LeaveController@delete')->name('delete');
        Route::post('bulk-delete', 'LeaveController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'LeaveController@change_status')->name('change.status');
    });

    //Shift Routes
    Route::get('shift', 'ShiftController@index')->name('shift');
    Route::group(['prefix' => 'shift', 'as' => 'shift.'], function () {
        Route::post('datatable-data', 'ShiftController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'ShiftController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'ShiftController@edit')->name('edit');
        Route::post('delete', 'ShiftController@delete')->name('delete');
        Route::post('bulk-delete', 'ShiftController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'ShiftController@change_status')->name('change.status');
    });

    //Employee Leave
    Route::get('leave-application', 'LeaveApplicationManageController@index')->name('leave.application');
    Route::group(['prefix' => 'leave.application', 'as' => 'leave.application.'], function () {
        Route::post('datatable-data', 'LeaveApplicationManageController@get_datatable_data')->name('datatable.data');
        Route::get('add', 'LeaveApplicationManageController@create')->name('add');
        Route::post('store-or-update', 'LeaveApplicationManageController@store_or_update_data')->name('store.or.update');
        Route::get('details/{id}', 'LeaveApplicationManageController@show')->name('details');
        Route::get('edit/{id}', 'LeaveApplicationManageController@edit')->name('edit');
        Route::post('delete', 'LeaveApplicationManageController@delete')->name('delete');
        Route::post('bulk-delete', 'LeaveApplicationManageController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'LeaveApplicationManageController@change_status')->name('change.status');
    });
    Route::get('employee-id-wise-leave-list/{id}', 'LeaveApplicationManageController@employee_id_wise_leave_list');
    Route::get('leave-type-wise-leave/{id}', 'LeaveApplicationManageController@leave_type_wise_leave');


    //Attendance
    Route::get('attendance', 'AttendanceController@index')->name('attendance');  
    Route::group(['prefix' => 'attendance', 'as' => 'attendance.'], function () {
        Route::post('datatable-data', 'AttendanceController@get_datatable_data')->name('datatable.data');
        Route::get('add', 'AttendanceController@create')->name('add');
        Route::post('store-or-update', 'AttendanceController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'AttendanceController@edit')->name('edit');
        Route::post('delete', 'AttendanceController@delete')->name('delete');
        Route::post('bulk-delete', 'AttendanceController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'AttendanceController@change_status')->name('change.status');
    });
    Route::get('employee-id-wise-employee-details/{id}', 'EmployeeController@employee_id_wise_employee_details');

    //Attendance Report..
    Route::get('attendance-report', 'AttendanceReportController@index')->name('attendance.report');  
    Route::group(['prefix' => 'attendance-report', 'as' => 'attendance.report.'], function () {
        Route::get('datatable-data', 'AttendanceReportController@attendance_report')->name('datatable.data');
        Route::get('add', 'AttendanceReportController@create')->name('add');
        Route::post('store-or-update', 'AttendanceReportController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'AttendanceReportController@edit')->name('edit');
        Route::post('delete', 'AttendanceReportController@delete')->name('delete');
        Route::post('bulk-delete', 'AttendanceReportController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'AttendanceReportController@change_status')->name('change.status');
    });

    //Salary Generated..
    Route::get('salary-generate', 'SalaryGenerateController@index')->name('salary.generate');
    Route::group(['prefix' => 'salary-generate', 'as' => 'salary.generate.'], function () {
        Route::get('add', 'SalaryGenerateController@create')->name('add');
        Route::post('datatable-data', 'SalaryGenerateController@get_datatable_data')->name('datatable.data');
        Route::get('salary-datatable-data', 'SalaryGenerateController@salary_report')->name('report.data');
        Route::post('store', 'SalaryGenerateController@store_data')->name('store');
        Route::post('update', 'SalaryGenerateController@update')->name('update');
        Route::get('edit/{id}', 'SalaryGenerateController@edit')->name('edit');
        Route::get('view/{id}', 'SalaryGenerateController@pay_slip')->name('view');
        Route::post('delete', 'SalaryGenerateController@delete')->name('delete');
        Route::post('bulk-delete', 'SalaryGenerateController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'SalaryGenerateController@change_status')->name('change.status');
    });
    
    //Salary Generated Payment Routes
    Route::post('salary-generate-payment-store-or-update', 'SalaryGeneratePaymentController@store_or_update')->name('salary.generate.payment.store.or.update');
    Route::post('salary-generate-payment/view', 'SalaryGeneratePaymentController@show')->name('salary.generate.payment.show');
    Route::post('salary-generate-payment/edit', 'SalaryGeneratePaymentController@edit')->name('salary.generate.payment.edit');
    Route::post('salary-generate-payment/delete', 'SalaryGeneratePaymentController@delete')->name('salary.generate.payment.delete');

});
