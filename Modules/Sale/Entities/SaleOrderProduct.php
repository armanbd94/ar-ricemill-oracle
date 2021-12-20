<?php

namespace Modules\Sale\Entities;

use Modules\Sale\Entities\SaleOrder;
use Modules\Product\Entities\Product;
use Illuminate\Database\Eloquent\Model;

class SaleOrderProduct extends Model
{
    protected $fillable = [
        'sale_id','product_id','qty','net_unit_price','total','description'
    ];

    public function sale_order()
    {
        return $this->belongsTo(SaleOrder::class,'sale_id','id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class,'product_id','id'); 
    }

}
