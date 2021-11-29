<?php

namespace Modules\TransferInventory\Entities;

use Illuminate\Database\Eloquent\Model;

class TransferInventoryItem extends Model
{
    protected $table = 'transfer_inventory_items';
    protected $fillable = [
        'transfer_id','material_id','qty','description'
    ];
}
