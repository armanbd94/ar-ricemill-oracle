<?php

namespace Modules\TransferInventory\Entities;

use Illuminate\Database\Eloquent\Model;

class TransferMixItem extends Model
{
    protected $table = 'transfer_mix_items';
    protected $fillable = [
        'transfer_id','from_site_id','from_location_id', 'material_id','qty','description'
    ];
    
}
