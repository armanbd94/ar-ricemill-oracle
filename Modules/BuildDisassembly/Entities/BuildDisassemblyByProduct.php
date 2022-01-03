<?php

namespace Modules\BuildDisassembly\Entities;

use Illuminate\Database\Eloquent\Model;
class BuildDisassemblyByProduct extends Model
{
    protected $fillable = ['disassembly_id','product_id','ratio','qty'];
    
}
