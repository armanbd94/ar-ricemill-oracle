<?php

namespace Modules\Purchase\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Material\Entities\Material;
use Modules\Purchase\Entities\OrderReceived;
use Modules\Purchase\Entities\PurchaseOrder;

class OrderReceivedMaterial extends Model
{
    protected $table = 'received_materials';
    protected $fillable = [
        'order_id','received_id','material_id', 'site_id', 'location_id','received_qty','received_unit_id','net_unit_cost','total','description','old_cost'
    ];

    public function order()
    {
        return $this->belongsTo(PurchaseOrder::class,'order_id','id');
    }

    public function receuved()
    {
        return $this->belongsTo(OrderReceived::class,'received_id','id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class,'material_id','id');
    }
}
