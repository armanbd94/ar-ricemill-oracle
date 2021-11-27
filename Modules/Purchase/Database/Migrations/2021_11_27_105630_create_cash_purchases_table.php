<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCashPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_purchases', function (Blueprint $table) {
            $table->id();
            $table->string('challan_no')->unique()->index('challan_no');
            $table->string('memo_no')->nullable();
            $table->string('vendor_name')->nullable();
            $table->unsignedBigInteger('job_type_id')->nullable();
            $table->foreign('job_type_id')->references('id')->on('job_types');
            $table->string('name')->nullable();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->foreign('account_id')->references('id')->on('chart_of_accounts');
            $table->float('item',8,0);
            $table->double('total_qty',12,0);
            $table->double('grand_total',12,0);
            $table->date('receive_date')->nullable();
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
        Schema::dropIfExists('cash_purchases');
    }
}
