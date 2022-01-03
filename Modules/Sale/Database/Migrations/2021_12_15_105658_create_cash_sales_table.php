<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCashSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_sales', function (Blueprint $table) {
            $table->id();
            $table->string('memo_no')->unique()->index('memo_no');
            $table->string('customer_name')->nullable();
            $table->string('do_number');
            $table->unsignedBigInteger('account_id')->nullable();
            $table->foreign('account_id')->references('id')->on('chart_of_accounts');
            $table->float('item',8,0);
            $table->double('total_qty',12,0);
            $table->double('grand_total',12,0);
            $table->date('sale_date')->nullable();
            $table->date('delivery_date')->nullable();
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
        Schema::dropIfExists('cash_sales');
    }
}
