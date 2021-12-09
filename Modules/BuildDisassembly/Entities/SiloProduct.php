<?php

namespace Modules\BuildDisassembly\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SiloProduct extends Model
{
    use HasFactory;

    protected $fillable = [];
    
    protected static function newFactory()
    {
        return \Modules\BuildDisassembly\Database\factories\SiloProductFactory::new();
    }
}
