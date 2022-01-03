<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBuildDisassemblyByProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('build_disassembly_by_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('disassembly_id');
            $table->foreign('disassembly_id')->references('id')->on('build_disassemblies');
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products');
            $table->float('ratio',8,0);
            $table->double('qty',12,0);
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
        Schema::dropIfExists('build_disassembly_by_products');
    }
}
