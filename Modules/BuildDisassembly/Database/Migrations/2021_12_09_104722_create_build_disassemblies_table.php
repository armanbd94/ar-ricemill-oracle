<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBuildDisassembliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('build_disassemblies', function (Blueprint $table) {
            $table->id();
            $table->string('memo_no')->nullable();
            $table->unsignedBigInteger('batch_id');
            $table->foreign('batch_id')->references('id')->on('batches');
            $table->unsignedBigInteger('from_site_id');
            $table->foreign('from_site_id')->references('id')->on('sites');
            $table->unsignedBigInteger('from_location_id');
            $table->foreign('from_location_id')->references('id')->on('locations');
            $table->unsignedBigInteger('material_id');
            $table->foreign('material_id')->references('id')->on('materials');
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products');
            $table->float('build_ratio',8,0);
            $table->double('build_qty',12,0);
            $table->double('required_qty',12,0);
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->date('build_date');
            $table->float('convertion_ratio',8,0)->comment('Convertion Ration Of Fine Rice');
            $table->double('converted_qty',12,0)->comment('Total Converted Quantity Of Fine Rice');
            $table->double('total_milling_qty',8,0);
            $table->integer('total_milling_ratio');
            $table->unsignedBigInteger('bp_site_id')->comment('By Product Storage Site After Build Disassembly');
            $table->foreign('bp_site_id')->references('id')->on('sites');
            $table->unsignedBigInteger('bp_location_id')->comment('By Product Storage Site Location After Build Disassembly');
            $table->foreign('bp_location_id')->references('id')->on('locations');
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
        Schema::dropIfExists('build_disassemblies');
    }
}
