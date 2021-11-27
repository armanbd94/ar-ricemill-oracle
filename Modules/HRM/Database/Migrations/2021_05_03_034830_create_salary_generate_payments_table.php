<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalaryGeneratePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salary_generate_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('salary_generated_id');
            $table->foreign('salary_generated_id')->references('id')->on('salary_generates');
            $table->unsignedBigInteger('account_id');
            $table->foreign('account_id')->references('id')->on('chart_of_accounts');
            $table->unsignedBigInteger('transaction_id');
            $table->foreign('transaction_id')->references('id')->on('transactions');
            $table->unsignedBigInteger('employee_transaction_id');
            $table->foreign('employee_transaction_id')->references('id')->on('transactions');
            $table->string('voucher_no')->nullable();
            $table->date('voucher_date')->nullable();
            $table->string('month')->nullable();
            $table->double('amount');
            $table->enum('payment_method',['1','2','3'])->comment="1=Cash,2=Cheque,3=Mobile";
            $table->string('cheque_no')->nullable();
            $table->text('payment_note')->nullable();
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
        Schema::dropIfExists('salary_generate_payments');
    }
}
