<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaleOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_orders', function (Blueprint $table) {
            $table->id();
            $table->string('memo_no')->unique()->index('memo_no');
            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->unsignedBigInteger('via_customer_id')->nullable();
            $table->foreign('via_customer_id')->references('id')->on('via_customers');
            $table->string('so_no',100)->nullable();
            $table->float('item',8,0);
            $table->double('total_qty',12,0);
            $table->double('grand_total',12,0);
            $table->date('order_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->text('shipping_address')->nullable();
            $table->enum('order_status',['1','2'])->default(2)->comment("1=Delivered,2=Pending");
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
        Schema::dropIfExists('sale_orders');
    }
}
