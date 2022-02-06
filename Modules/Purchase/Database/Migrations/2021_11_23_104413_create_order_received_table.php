<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderReceivedTable extends Migration
{

    public function up()
    {
        Schema::create('order_received', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->foreign('order_id')->references('id')->on('purchase_orders');
            $table->string('challan_no');
            $table->string('transport_no')->nullable();
            $table->double('truck_fare',12,0);
            $table->float('item',8,0);
            $table->double('total_qty',12,0);
            $table->double('grand_total',12,0);
            $table->date('received_date');
            $table->string('created_by')->nullable();
            $table->string('modified_by')->nullable();
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('order_receiveds');
    }
}
