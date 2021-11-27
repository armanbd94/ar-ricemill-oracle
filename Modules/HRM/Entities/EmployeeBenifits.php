<?php

namespace Modules\HRM\Entities;

use Illuminate\Database\Eloquent\Model;

class EmployeeBenifits extends Model
{

    protected $fillable = ['employee_id', 'benefit_class_code', 'benefit_description', 'benefit_accrual_date', 'benefit_status', 'created_by', 'modified_by'];
    
}
