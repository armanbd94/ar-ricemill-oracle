<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBomRePackingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bom_re_packings', function (Blueprint $table) {
            $table->id();
            $table->string('memo_no')->nullable();
            $table->string('packing_number')->nullable();
            $table->unsignedBigInteger('from_site_id');
            $table->foreign('from_site_id')->references('id')->on('sites');
            $table->unsignedBigInteger('from_location_id');
            $table->foreign('from_location_id')->references('id')->on('locations');
            $table->unsignedBigInteger('from_product_id');
            $table->foreign('from_product_id')->references('id')->on('products');
            $table->unsignedBigInteger('to_site_id');
            $table->foreign('to_site_id')->references('id')->on('sites');
            $table->unsignedBigInteger('to_location_id');
            $table->foreign('to_location_id')->references('id')->on('locations');
            $table->unsignedBigInteger('to_product_id');
            $table->foreign('to_product_id')->references('id')->on('products');
            $table->unsignedBigInteger('bag_site_id');
            $table->foreign('bag_site_id')->references('id')->on('sites');
            $table->unsignedBigInteger('bag_location_id');
            $table->foreign('bag_location_id')->references('id')->on('locations');
            $table->unsignedBigInteger('bag_id');
            $table->foreign('bag_id')->references('id')->on('materials');
            $table->string('product_description')->nullable();
            $table->string('bag_description')->nullable();
            $table->double('product_qty',12,8);
            $table->double('bag_qty',12,8);
            $table->date('packing_date');
            $table->unsignedBigInteger('item_class_id');
            $table->foreign('item_class_id')->references('id')->on('item_classes');
            $table->unsignedBigInteger('bag_class_id');
            $table->foreign('bag_class_id')->references('id')->on('item_classes');
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
        Schema::dropIfExists('bom_re_packings');
    }
}
