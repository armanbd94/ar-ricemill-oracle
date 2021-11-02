<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('material_name');
            $table->string('material_code')->unique();
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->unsignedBigInteger('unit_id');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->unsignedBigInteger('purchase_unit_id');
            $table->foreign('purchase_unit_id')->references('id')->on('units');
            $table->double('cost')->nullable();
            $table->double('old_cost')->nullable();
            $table->double('qty')->nullable();
            $table->double('alert_qty')->nullable();
            $table->unsignedBigInteger('tax_id')->nullable();
            $table->foreign('tax_id')->references('id')->on('taxes');
            $table->enum('tax_method',['1','2'])->comment = "1=Exclusive,2=Inclusive";
            $table->enum('type',['1','2'])->comment = "1=Raw, 2=Packaging";
            $table->enum('status',['1','2'])->default('1')->comment = "1=Active, 2=Inactive";
            $table->enum('has_opening_stock',['1','2'])->default('2')->comment = "1=Yes, 2=No";
            $table->double('opening_stock_qty')->nullable();
            $table->double('opening_cost')->nullable();
            $table->unsignedBigInteger('opening_warehouse_id')->nullable();
            $table->foreign('opening_warehouse_id')->references('id')->on('warehouses');
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
        Schema::dropIfExists('materials');
    }
}
