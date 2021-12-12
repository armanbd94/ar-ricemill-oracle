<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoanInstallmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_installments', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no')->nullable();
            $table->unsignedBigInteger('loan_id')->nullable();
            $table->foreign('loan_id')->references('id')->on('loans');
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->foreign('employee_id')->references('id')->on('employees');
            $table->unsignedBigInteger('person_id')->nullable();
            $table->foreign('person_id')->references('id')->on('loan_people');
            $table->double('installment_amount',12,0)->nullable();
            $table->string('purpose')->nullable();
            $table->string('status_changed_by')->nullable();
            $table->date('installment_date')->nullable();
            $table->string('month_year')->nullable();
            $table->enum('loan_type',['1','2'])->comment("1=Personal,2=Official");
            $table->enum('payment_method',['1','2','3'])->default('1')->comment="1=Cash,2=Cheque,3=Mobile";
            $table->unsignedBigInteger('account_id')->nullable();
            $table->foreign('account_id')->references('id')->on('chart_of_accounts');
            $table->enum('status',['1','2'])->comment("1=Active,2=InActive");
            $table->enum('approve',['1','2'])->default('1')->comment = "1=Yes, 2=No";
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
        Schema::dropIfExists('loan_installments');
    }
}
