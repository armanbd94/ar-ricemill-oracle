<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransferInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfer_inventories', function (Blueprint $table) {
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
            $table->float('item',8,0);
            $table->double('total_qty',12,0);
            $table->date('transfer_date');
            $table->string('transfer_number')->nullable();
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
        Schema::dropIfExists('transfer_inventories');
    }
}
