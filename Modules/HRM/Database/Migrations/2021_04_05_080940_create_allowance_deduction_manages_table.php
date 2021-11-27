<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAllowanceDeductionManagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('allowance_deduction_manages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('allowance_deduction_id');
            $table->foreign('allowance_deduction_id')->references('id')->on('allowance_deductions');
            $table->unsignedBigInteger('employee_id');
            $table->foreign('employee_id')->references('id')->on('employees');
            $table->integer('type')->nullable()->comment = "1=Alowance, 2=Deduction, 3=Others";
            $table->double('basic_salary');
            $table->integer('percentage');
            $table->double('amount');
            $table->enum('status',['1','2','3'])->default('1')->comment = "1=Active, 2=Inactive, 3=Cancel";
            $table->enum('deletable',['1','2'])->default('2')->comment = "1=No, 2=Yes";
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
        Schema::dropIfExists('allowance_deduction_manages');
    }
}
