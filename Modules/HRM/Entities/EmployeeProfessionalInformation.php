<?php

namespace Modules\HRM\Entities;

use Illuminate\Database\Eloquent\Model;

class EmployeeProfessionalInformation extends Model
{
    protected $fillable = ['employee_id', 'designation', 'company', 'from_date', 'to_date', 'responsibility', 'created_by', 'modified_by'];
    
}
