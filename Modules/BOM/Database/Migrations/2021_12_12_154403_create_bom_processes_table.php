<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBomProcessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bom_processes', function (Blueprint $table) {
            $table->id();
            $table->string('memo_no')->nullable();
            
            $table->unsignedBigInteger('batch_id');
            $table->foreign('batch_id')->references('id')->on('batches');
            
            $table->unsignedBigInteger('assemble_site_id');
            $table->foreign('assemble_site_id')->references('id')->on('sites');
            $table->unsignedBigInteger('assemble_location_id');
            $table->foreign('assemble_location_id')->references('id')->on('locations');

            

            $table->string('process_number')->nullable();
            
            $table->unsignedBigInteger('to_product_id');
            $table->foreign('to_product_id')->references('id')->on('products');

            $table->unsignedBigInteger('bag_site_id');
            $table->foreign('bag_site_id')->references('id')->on('sites');
            $table->unsignedBigInteger('bag_location_id');
            $table->foreign('bag_location_id')->references('id')->on('locations');

            $table->unsignedBigInteger('from_product_id');
            $table->foreign('from_product_id')->references('id')->on('products');
            $table->string('product_particular')->nullable();
            $table->float('product_per_unit_qty',8,0);
            $table->double('product_required_qty',12,0);
   

            $table->unsignedBigInteger('bag_id');
            $table->foreign('bag_id')->references('id')->on('materials');
            $table->string('bag_particular')->nullable();
            $table->float('bag_per_unit_qty',8,0);
            $table->double('bag_required_qty',12,0);
   
            
            $table->double('total_rice_qty',12,0);
            $table->float('total_bag_qty',8,0);
            $table->date('process_date');
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
        Schema::dropIfExists('bom_processes');
    }
}
