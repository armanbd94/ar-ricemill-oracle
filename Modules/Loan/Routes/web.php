<?php

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

    /*---------------------- Personal Loan Type Routes Start -----------------*/

    //personal person route
    Route::get('personal-loan-person', 'PersonalLoan\PersonalLoanPeopleController@index')->name('personal.loan.people');
    Route::group(['prefix' => 'personal-loan-person', 'as' => 'personal.loan.person.'], function () {
        Route::post('datatable-data', 'PersonalLoan\PersonalLoanPeopleController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'PersonalLoan\PersonalLoanPeopleController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'PersonalLoan\PersonalLoanPeopleController@edit')->name('edit');
        Route::post('delete', 'PersonalLoan\PersonalLoanPeopleController@delete')->name('delete');
        Route::post('bulk-delete', 'PersonalLoan\PersonalLoanPeopleController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'PersonalLoan\PersonalLoanPeopleController@change_status')->name('change.status');
    });
    Route::get('person-id-wise-person-details/{id}', 'PersonalLoan\PersonalLoanPeopleController@person_id_wise_person_details');

    //personal loan route
    Route::get('personal-loan', 'PersonalLoan\PersonalLoanController@index')->name('personal.loan');
    Route::group(['prefix' => 'personal-loan', 'as' => 'personal.loan.'], function () {
        Route::post('datatable-data', 'PersonalLoan\PersonalLoanController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'PersonalLoan\PersonalLoanController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'PersonalLoan\PersonalLoanController@edit')->name('edit');
        Route::post('delete', 'PersonalLoan\PersonalLoanController@delete')->name('delete');
        Route::post('bulk-delete', 'PersonalLoan\PersonalLoanController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'PersonalLoan\PersonalLoanController@change_status')->name('change.status');        
        //Route::post('person-id-wise-person-loan-details', 'PersonalLoan\PersonalLoanController@person_id_wise_person_loan_details')->name('person.id.wise.person.loan.details');
    });
    Route::get('person-id-wise-person-loan-details/{id}', 'PersonalLoan\PersonalLoanController@person_id_wise_person_loan_details');

    //personal loan installment route
    Route::get('personal-loan-installment', 'PersonalLoan\PersonalLoanInstallmentController@index')->name('personal.loan.installment');
    Route::group(['prefix' => 'personal-loan-installment', 'as' => 'personal.loan.installment.'], function () {
        Route::post('datatable-data', 'PersonalLoan\PersonalLoanInstallmentController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'PersonalLoan\PersonalLoanInstallmentController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'PersonalLoan\PersonalLoanInstallmentController@edit')->name('edit');
        Route::post('delete', 'PersonalLoan\PersonalLoanInstallmentController@delete')->name('delete');
        Route::post('bulk-delete', 'PersonalLoan\PersonalLoanInstallmentController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'PersonalLoan\PersonalLoanInstallmentController@change_status')->name('change.status');
    });
    /*---------------------- Personal Loan Type Routes End -----------------*/
    /*---------------------- Official Loan Type Routes Start -----------------*/
    //Official loan route
    Route::get('official-loan', 'OfficialLoan\OfficialLoanController@index')->name('official.loan');
    Route::group(['prefix' => 'official-loan', 'as' => 'official.loan.'], function () {
        Route::post('datatable-data', 'OfficialLoan\OfficialLoanController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'OfficialLoan\OfficialLoanController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'OfficialLoan\OfficialLoanController@edit')->name('edit');
        Route::post('delete', 'OfficialLoan\OfficialLoanController@delete')->name('delete');
        Route::post('bulk-delete', 'OfficialLoan\OfficialLoanController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'OfficialLoan\OfficialLoanController@change_status')->name('change.status');        
        Route::post('approve', 'OfficialLoan\OfficialLoanController@approve')->name('approve');        
        //Route::post('employee-id-wise-employee-loan-details', 'OfficialLoan\OfficialLoanControlle@employee_id_wise_employee_loan_details')->name('person.id.wise.person.loan.details');
    });
    Route::get('employee-id-wise-employee-loan-details/{id}', 'OfficialLoan\OfficialLoanController@employee_id_wise_employee_loan_details');

    //Official loan installment route
    Route::get('official-loan-installment', 'OfficialLoan\OfficialLoanInstallmentController@index')->name('official.loan.installment');
    Route::group(['prefix' => 'official-loan-installment', 'as' => 'official.loan.installment.'], function () {
        Route::post('datatable-data', 'OfficialLoan\OfficialLoanInstallmentController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'OfficialLoan\OfficialLoanInstallmentController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'OfficialLoan\OfficialLoanInstallmentController@edit')->name('edit');
        Route::post('delete', 'OfficialLoan\OfficialLoanInstallmentController@delete')->name('delete');
        Route::post('bulk-delete', 'OfficialLoan\OfficialLoanInstallmentController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'OfficialLoan\OfficialLoanInstallmentController@change_status')->name('change.status');
    });
    /*---------------------- Official Loan Type Routes End -----------------*/
    
Route::get('loan-report', 'Report\LoanReportController@index')->name('loan.report');
Route::post('loan-report/datatable-data', 'Report\LoanReportController@get_datatable_data')->name('loan.report.datatable.data');
    
Route::get('company-loan-report', 'Report\CompanyLoanReportController@index')->name('company.loan.report');
Route::post('company-loan-report/datatable-data', 'Report\CompanyLoanReportController@get_datatable_data')->name('company.loan.report.datatable.data');
});
