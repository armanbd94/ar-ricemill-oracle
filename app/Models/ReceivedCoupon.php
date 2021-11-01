<?php

namespace App\Models;

use App\Models\BaseModel;

class ReceivedCoupon extends BaseModel
{
    protected $fillable = ['salesmen_id', 'customer_id', 'coupon_id'];

    
}
