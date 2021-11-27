<?php

namespace Modules\HRM\Entities;

use Illuminate\Database\Eloquent\Model;

class EmployeeEducation extends Model
{

    protected $fillable = ['employee_id', 'degree', 'major', 'institute', 'passing_year', 'result', 'created_by', 'modified_by'];
    
}
