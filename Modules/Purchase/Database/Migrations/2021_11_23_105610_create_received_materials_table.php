<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReceivedMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('received_materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->foreign('order_id')->references('id')->on('purchase_orders');
            $table->unsignedBigInteger('received_id');
            $table->foreign('received_id')->references('id')->on('order_received');
            $table->unsignedBigInteger('material_id');
            $table->foreign('material_id')->references('id')->on('materials');
            $table->unsignedBigInteger('item_class_id');
            $table->foreign('item_class_id')->references('id')->on('item_classes');
            $table->unsignedBigInteger('site_id');
            $table->foreign('site_id')->references('id')->on('sites');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->foreign('location_id')->references('id')->on('locations');
            $table->double('received_qty',12,0);
            $table->unsignedBigInteger('received_unit_id')->nullable();
            $table->foreign('received_unit_id')->references('id')->on('units');
            $table->double('net_unit_cost',12,0);
            $table->double('old_cost',12,0)->nullable();
            $table->double('total',12,0);
            $table->text('description')->nullable();
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
        Schema::dropIfExists('order_received_materials');
    }
}
