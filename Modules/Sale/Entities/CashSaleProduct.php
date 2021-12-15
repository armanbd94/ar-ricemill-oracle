<?php

namespace Modules\Sale\Entities;

use Illuminate\Database\Eloquent\Model;

class CashSaleProduct extends Model
{
    protected $fillable = [
        'sale_id','site_id','location_id','product_id','qty','net_unit_price','total','description'
    ];

}
