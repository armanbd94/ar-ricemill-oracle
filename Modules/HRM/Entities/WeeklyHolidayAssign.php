<?php

namespace Modules\HRM\Entities;

use App\Models\BaseModel;

class WeeklyHolidayAssign extends BaseModel
{
    protected $fillable = ['employee_id','weekly_holiday_id','status', 'deletable', 'created_by'];

}
