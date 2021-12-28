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
            $table->double('last_day_closing',12,8)->nullable();
            $table->double('cash_in',12,8)->nullable();
            $table->double('cash_out',12,8)->nullable();
            $table->double('balance',12,8)->nullable();
            $table->double('transfer',12,8)->nullable();
            $table->double('closing_amount',12,8)->nullable();
            $table->double('adjustment',12,8)->nullable();
            $table->date('closing_date');
            $table->string('thousands')->nullable();
            $table->string('five_hundred')->nullable();
            $table->string('hundred')->nullable();
            $table->string('fifty')->nullable();
            $table->string('twenty')->nullable();
            $table->string('ten')->nullable();
            $table->string('five')->nullable();
            $table->string('two')->nullable();
            $table->string('one')->nullable();
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
        Schema::dropIfExists('daily_closings');
    }
}
