<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name',100);
            $table->string('email',100)->nullable();
            $table->string('phone',15)->unique();
            $table->string('photograph')->nullable();
            $table->string('alternative_phone',15)->nullable();
            $table->string('city',100)->nullable();
            $table->string('zipcode',10)->nullable();
            $table->string('holiday',100)->nullable();
            $table->integer('attendance_type')->nullable();
            $table->text('address');
            $table->string('employee_id')->unique();
            $table->string('finger_id')->unique();
            $table->string('wallet_number')->unique()->nullable();
            $table->unsignedBigInteger('shift_id');
            $table->foreign('shift_id')->references('id')->on('shifts');
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')->references('id')->on('departments');
            $table->unsignedBigInteger('division_id');
            $table->foreign('division_id')->references('id')->on('divisions');
            $table->enum('job_status',['1','2','3','4'])->comment = "1=Permanent,2=Probation,3=Resigned,4=Suspended";
            $table->enum('duty_type',['1','2','3','4'])->comment = "1=Full Time,2=Part Time,3=Contractual,4=Other";
            $table->unsignedBigInteger('joining_designation_id');
            $table->foreign('joining_designation_id')->references('id')->on('designations');
            $table->unsignedBigInteger('current_designation_id');
            $table->foreign('current_designation_id')->references('id')->on('designations');
            $table->date('joining_date')->nullable();
            $table->date('probation_start')->nullable();
            $table->date('probation_end')->nullable();
            $table->date('contract_start')->nullable();
            $table->date('contract_end')->nullable();
            $table->date('confirmation_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->text('termination_reason')->nullable();
            $table->enum('rate_type',['1','2'])->comment = "1=Hourly,2=Salary";
            $table->double('rate',12,0);
            $table->double('joining_rate',12,0)->nullable();
            $table->enum('overtime',['1','2'])->nullable()->comment = "1=Allowed,2=Not Allowed";
            $table->enum('pay_freequency',['1','2','3','4'])->comment = "1=Weekly,2=Biweekly,3=Monthly,4=Annual";
            $table->string('bank_name')->nullable();
            $table->string('account_no')->nullable();
            $table->unsignedInteger('supervisor_id')->nullable();
            $table->enum('is_supervisor',['1','2'])->nullable()->comment = "1=Yes,2=No";
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->date('dob')->nullable();
            $table->enum('gender',['1','2','3'])->comment = "1=Male,2=Female,3=Other";
            $table->enum('marital_status',['1','2','3','4','5'])->comment = "1=Single,2=Married,3=Divorced,4=Widowed,5=Other";
            $table->enum('blood_group',['1','2','3','4','5','6','7','8'])->comment = "1=A+,2=B+,3=A-,4=B-,5=AB+,6=AB-,7=O+,8=O-";
            $table->string('religion')->nullable();
            $table->string('nid_no')->nullable();
            $table->string('nid_photo')->nullable();
            $table->enum('residential_status',['1','2'])->nullable()->comment = "1=Resident,2=Non-Resident";
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relation')->nullable();
            $table->string('emergency_contact_address')->nullable();
            $table->string('alternative_emergency_contact_name')->nullable();
            $table->string('alternative_emergency_contact_phone')->nullable();
            $table->string('alternative_emergency_contact_relation')->nullable();
            $table->string('alternative_emergency_contact_address')->nullable();
            $table->enum('status',['1','2'])->default('1')->comment = "1=Active, 2=Inactive";
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
        Schema::dropIfExists('employees');
    }
}
