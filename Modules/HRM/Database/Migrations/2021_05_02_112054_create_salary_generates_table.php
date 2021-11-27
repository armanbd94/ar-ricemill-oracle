<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalaryGeneratesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salary_generates', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no')->unique();
            $table->unsignedBigInteger('employee_id');
            $table->foreign('employee_id')->references('id')->on('employees');
            $table->unsignedBigInteger('designation_id');
            $table->foreign('designation_id')->references('id')->on('designations');
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')->references('id')->on('departments');
            $table->unsignedBigInteger('division_id');
            $table->foreign('division_id')->references('id')->on('divisions');
            $table->date('date')->nullable();
            $table->string('salary_month')->nullable();
            $table->double('basic_salary')->nullable();
            $table->double('allowance_amount')->nullable();
            $table->double('deduction_amount')->nullable();
            $table->double('absent')->nullable();            
            $table->double('absent_amount')->nullable();            
            $table->double('late_count')->nullable();            
            $table->double('leave')->nullable();            
            $table->double('leave_amount')->nullable();            
            $table->double('ot_hour')->nullable();            
            $table->double('ot_day')->nullable();            
            $table->double('ot_amount')->nullable();           
            $table->double('gross_salary')->nullable();
            $table->double('add_deduct_amount')->nullable();
            $table->double('adjusted_advance_amount')->nullable();
            $table->double('adjusted_loan_amount')->nullable();          
            $table->double('net_salary')->nullable();
            $table->double('paid_amount')->nullable();
            $table->enum('salary_status',['1','2','3','4'])->default('3')->comment="1=Received,2=Partial,3=Pending,4=Ordered";
            $table->enum('payment_status',['1','2','3'])->default('3')->comment="1=Paid,2=Partial,3=Due";
            $table->enum('payment_method',['1','2','3'])->default('1')->comment="1=Cash,2=Cheque,3=Mobile";
            $table->enum('status',['1','2'])->default('1')->comment = "1=Active, 2=Inactive";
            $table->enum('deletable',['1','2'])->nullable()->default('2')->comment = "1=No, 2=Yes";
            $table->string('created_by')->nullable();
            $table->string('modified_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('salary_generates');
    }
}
