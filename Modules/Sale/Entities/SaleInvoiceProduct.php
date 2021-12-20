<?php

namespace Modules\Sale\Entities;

use Modules\Setting\Entities\Site;
use Modules\Product\Entities\Product;
use Modules\Sale\Entities\SaleInvoice;
use Modules\Setting\Entities\Location;
use Illuminate\Database\Eloquent\Model;

class SaleInvoiceProduct extends Model
{
    protected $fillable = [
        'sale_id','site_id','location_id','product_id','qty','net_unit_price','total','description'
    ];

    public function sale_invoice()
    {
        return $this->belongsTo(SaleInvoice::class,'sale_id','id');
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
}
