<?php

namespace Modules\HRM\Entities;

use App\Models\BaseModel;

class ShiftManage extends BaseModel
{
    protected $fillable = ['shift_id ','employee_id','start_time','end_time','presentstatus','status', 'deletable', 'created_by', 'modified_by'];

}
