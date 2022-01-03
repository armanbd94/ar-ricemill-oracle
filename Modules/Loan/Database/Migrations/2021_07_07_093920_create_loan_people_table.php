<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoanPeopleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_people', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->enum('status',['1','2'])->default('1')->comment("1=Active,2=InActive");
            $table->enum('loan_term_type',['1','2'])->comment("1=Short Term,2=Long Term");
            $table->enum('deletable',['1','2'])->default('2')->comment = "1=No, 2=Yes";
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
        Schema::dropIfExists('loan_people');
    }
}
