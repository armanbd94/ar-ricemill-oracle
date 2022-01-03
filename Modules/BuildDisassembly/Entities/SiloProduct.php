<?php

namespace Modules\BuildDisassembly\Entities;

use App\Models\BaseModel;

class SiloProduct extends BaseModel
{
    protected $fillable = ['product_id','qty'];

    public function product()
    {
        return $this->belongsTo(Product::class,'product_id','id');
    }
}
