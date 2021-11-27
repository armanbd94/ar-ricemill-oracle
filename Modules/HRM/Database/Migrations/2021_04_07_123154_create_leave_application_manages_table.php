<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeaveApplicationManagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_application_manages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leave_id');
            $table->foreign('leave_id')->references('id')->on('leaves');
            $table->unsignedBigInteger('employee_id');
            $table->foreign('employee_id')->references('id')->on('employees');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('alternative_employee')->nullable();
            $table->integer('number_leave')->nullable();
            $table->integer('leave_type')->nullable();
            $table->string('employee_location')->nullable();
            $table->string('purpose')->nullable();
            $table->string('comments')->nullable();
            $table->enum('submission',['1','2'])->default('1')->comment = "1=Pre, 2=Post";
            $table->enum('leave_status',['1','2','3'])->default('1')->comment = "1=Pending, 2=Accepted, 3=Cancel";
            $table->enum('status',['1','2'])->default('1')->comment = "1=Active, 2=Inactive";
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
        Schema::dropIfExists('leave_application_manages');
    }
}
