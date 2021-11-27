<?php

namespace Modules\Purchase\Entities;

use App\Models\Unit;
use Modules\Setting\Entities\Site;
use Modules\Setting\Entities\Location;
use Illuminate\Database\Eloquent\Model;
use Modules\Material\Entities\Material;
use Modules\Purchase\Entities\CashPurchase;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CashPurchaseMaterial extends Model
{
    protected $table = 'cash_purchase_materials';
    protected $fillable = [
        'cash_id','material_id', 'site_id', 'location_id','qty','purchase_unit_id','net_unit_cost','old_cost','total','description',
    ];

    public function cash_purchase()
    {
        return $this->belongsTo(CashPurchase::class,'cash_id','id');
    }
    public function material()
    {
        return $this->belongsTo(Material::class,'material_id','id'); 
    }
    public function site()
    {
        return $this->belongsTo(Site::class,'site_id','id'); 
    }
    public function location()
    {
        return $this->belongsTo(Location::class,'location_id','id'); 
    }
    public function purchase_unit()
    {
        return $this->belongsTo(Unit::class,'purchase_unit_id','id'); 
    }
}
