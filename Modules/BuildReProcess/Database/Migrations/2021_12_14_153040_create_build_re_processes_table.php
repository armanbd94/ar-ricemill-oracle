<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBuildReProcessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('build_re_processes', function (Blueprint $table) {
            $table->id();
            $table->string('memo_no')->nullable();
            $table->unsignedBigInteger('batch_id');
            $table->foreign('batch_id')->references('id')->on('batches');
            $table->unsignedBigInteger('from_site_id');
            $table->foreign('from_site_id')->references('id')->on('sites');
            $table->unsignedBigInteger('from_location_id');
            $table->foreign('from_location_id')->references('id')->on('locations');
            $table->unsignedBigInteger('to_site_id');
            $table->foreign('to_site_id')->references('id')->on('sites');
            $table->unsignedBigInteger('to_location_id');
            $table->foreign('to_location_id')->references('id')->on('locations');
            $table->unsignedBigInteger('from_product_id');
            $table->foreign('from_product_id')->references('id')->on('products');
            $table->unsignedBigInteger('to_product_id');
            $table->foreign('to_product_id')->references('id')->on('products');
            $table->float('build_ratio',8,0);
            $table->double('build_qty',12,0);
            $table->double('required_qty',12,0);
            $table->unsignedBigInteger('item_class_id');
            $table->foreign('item_class_id')->references('id')->on('item_classes');
            $table->date('build_date');
            $table->float('convertion_ratio',8,0)->comment('Convertion Ratio Of Fine Rice');
            $table->double('converted_qty',12,0)->comment('Total Converted Quantity Of Fine Rice');
            $table->double('total_milling_qty',8,0);
            $table->integer('total_milling_ratio');
            $table->unsignedBigInteger('bp_site_id')->comment('By Product Storage Site After Build Disassembly');
            $table->foreign('bp_site_id')->references('id')->on('sites');
            $table->unsignedBigInteger('bp_location_id')->comment('By Product Storage Site Location After Build Disassembly');
            $table->foreign('bp_location_id')->references('id')->on('locations');
            $table->enum('product_type',['1','2'])->comment('1=Packet Rice,2=By Product');
            $table->float('bp_rate',8,0)->nullable();
            $table->float('from_product_cost',8,0);
            $table->float('to_product_cost',8,0);
            $table->float('to_product_old_cost',8,0);
            $table->float('bag_cost',8,0);
            $table->float('per_unit_cost',8,0);
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
        Schema::dropIfExists('build_re_processes');
    }
}
