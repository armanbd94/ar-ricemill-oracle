<?php

namespace Modules\Sale\Entities;

use App\Models\ItemClass;
use Modules\Sale\Entities\SaleOrder;
use Modules\Product\Entities\Product;
use Illuminate\Database\Eloquent\Model;

class SaleOrderProduct extends Model
{
    protected $fillable = [
        'sale_id','product_id','item_class_id','qty','net_unit_price','total','description'
    ];

    public function sale_order()
    {
        return $this->belongsTo(SaleOrder::class,'sale_id','id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class,'product_id','id'); 
    }
    public function class()
    {
        return $this->belongsTo(ItemClass::class,'item_class_id','id')->withDefault(['name'=>'']); 
    }
}
