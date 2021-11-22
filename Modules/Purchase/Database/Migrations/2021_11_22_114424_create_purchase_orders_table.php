<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('memo_no')->unique()->index('memo_no');
            $table->unsignedBigInteger('vendor_id');
            $table->foreign('vendor_id')->references('id')->on('vendors');
            $table->unsignedBigInteger('via_vendor_id')->nullable();
            $table->foreign('via_vendor_id')->references('id')->on('via_vendors');
            $table->float('item',8,0);
            $table->double('total_qty',12,0);
            $table->double('grand_total',12,0);
            $table->date('order_date');
            $table->date('delivery_date')->nullable();
            $table->string('po_no')->nullable();
            $table->string('nos_truck')->nullable();
            $table->enum('purchase_status',['1','2','3'])->comment="1=Received,2=Partial,3=Ordered";
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
        Schema::dropIfExists('purchase_orders');
    }
}
