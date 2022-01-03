<?php

namespace Modules\Purchase\Entities;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderMaterial extends Model
{
    protected $table = 'purchase_order_materials';
    
    protected $fillable = ['order_id', 'material_id','item_class_id','qty', 'purchase_unit_id', 'net_unit_cost', 'total', 'description'];
}
