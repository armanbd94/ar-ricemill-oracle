<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDailyClosingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_closings', function (Blueprint $table) {
            $table->id();
            $table->double('last_day_closing')->nullable();
            $table->double('cash_in')->nullable();
            $table->double('cash_out')->nullable();
            $table->double('amount')->nullable();
            $table->double('adjustment')->nullable();
            $table->date('date');
            $table->string('thousands')->default('0');
            $table->string('five_hundred')->default('0');
            $table->string('hundred')->default('0');
            $table->string('fifty')->default('0');
            $table->string('twenty')->default('0');
            $table->string('ten')->default('0');
            $table->string('five')->default('0');
            $table->string('two')->default('0');
            $table->string('one')->default('0');
            $table->string('created_by')->default('0');
            $table->string('modified_by')->default('0');
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
        Schema::dropIfExists('daily_closings');
    }
}
