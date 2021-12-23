<?php

namespace Modules\Sale\Entities;

use App\Models\ItemClass;
use Modules\Setting\Entities\Site;
use Modules\Sale\Entities\CashSale;
use Modules\Product\Entities\Product;
use Modules\Setting\Entities\Location;
use Illuminate\Database\Eloquent\Model;

class CashSaleProduct extends Model
{
    protected $fillable = [
        'sale_id','site_id','location_id','product_id','item_class_id','qty','net_unit_price','total','description'
    ];

    public function cash_sale()
    {
        return $this->belongsTo(CashSale::class,'sale_id','id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class,'product_id','id'); 
    }
    public function site()
    {
        return $this->belongsTo(Site::class,'site_id','id'); 
    }
    public function location()
    {
        return $this->belongsTo(Location::class,'location_id','id'); 
    }
    public function class()
    {
        return $this->belongsTo(ItemClass::class,'item_class_id','id')->withDefault(['name'=>'']); 
    }

}
