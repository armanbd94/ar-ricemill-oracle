<?php

namespace Modules\Vendor\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VendorLedger extends Model
{
    use HasFactory;

    protected $fillable = [];
    
    protected static function newFactory()
    {
        return \Modules\Vendor\Database\factories\VendorLedgerFactory::new();
    }
}
