<?php

namespace Modules\ViaCustomer\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ViaCustomer extends Model
{
    use HasFactory;

    protected $fillable = [];
    
    protected static function newFactory()
    {
        return \Modules\ViaCustomer\Database\factories\ViaCustomerFactory::new();
    }
}
