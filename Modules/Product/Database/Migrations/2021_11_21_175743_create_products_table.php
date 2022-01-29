<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->index('name');
            $table->string('code')->unique();
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->unsignedBigInteger('unit_id');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->decimal('cost',12)->nullable();
            $table->decimal('price',12)->nullable();
            $table->decimal('qty',12)->nullable();
            $table->decimal('alert_qty',12)->nullable();
            $table->unsignedBigInteger('tax_id')->nullable();
            $table->foreign('tax_id')->references('id')->on('taxes');
            $table->enum('tax_method',['1','2'])->default('2')->comment("1=Exclusive,2=Inclusive");
            $table->enum('status',['1','2'])->default('1')->comment = "1=Active, 2=Inactive";
            $table->enum('has_opening_stock',['1','2'])->default('2')->comment = "1=Yes, 2=No";
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
        Schema::dropIfExists('products');
    }
}
