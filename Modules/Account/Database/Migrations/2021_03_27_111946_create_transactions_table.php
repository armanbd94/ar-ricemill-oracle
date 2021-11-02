<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chart_of_account_id')->nullable();
            $table->foreign('chart_of_account_id')->references('id')->on('chart_of_accounts');
            $table->string('voucher_no');
            $table->string('voucher_type');
            $table->string('voucher_date');
            $table->text('description')->nullable();
            $table->double('debit')->nullable();
            $table->double('credit')->nullable();
            $table->enum('is_opening',['1','2'])->default('2')->comment = "1=Yes, 2=No";
            $table->enum('posted',['1','2'])->default('1')->comment = "1=Yes, 2=No";
            $table->enum('approve',['1','2'])->default('1')->comment = "1=Yes, 2=No";
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
        Schema::dropIfExists('transactions');
    }
}
