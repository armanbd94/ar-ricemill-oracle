<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChartOfAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('parent_name')->nullable();
            $table->string('level')->default('0');
            $table->enum('type',['A','L','I','E'])->comment("A=Asset, L=Liabilty, I=income, E=Expense");
            $table->enum('is_transaction',['1','2'])->comment("1=Yes, 2=No");
            $table->enum('general_ledger',['1','2'])->comment("1=Yes, 2=No");
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->foreign('vendor_id')->references('id')->on('vendors');
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->foreign('bank_id')->references('id')->on('banks');
            $table->unsignedBigInteger('mobile_bank_id')->nullable();
            $table->foreign('mobile_bank_id')->references('id')->on('mobile_banks');
            $table->enum('budget',['1','2'])->nullable()->comment("1=Yes, 2=No");
            $table->enum('depreciation',['1','2'])->comment("1=Yes, 2=No");
            $table->string('depreciation_rate')->default('0');
            $table->enum('status',['1','2'])->default('1')->comment = "1=Active, 2=Inactive";
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
        Schema::dropIfExists('chart_of_accounts');
    }
}
