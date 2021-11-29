<?php

namespace Modules\TransferInventory\Entities;

use App\Models\BaseModel;

class TransferMixInventory extends BaseModel
{
    protected $table = 'transfer_mix_inventories';
    protected $fillable = [
        'memo_no', 'batch_id','product_id','category_id','to_site_id','to_location_id',
        'item','total_qty', 'transfer_date', 'transfer_number','created_by','modified_by',
    ];
}
