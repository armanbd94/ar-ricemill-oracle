<?php

namespace Modules\TransferInventory\Entities;

use Modules\Setting\Entities\Site;
use Modules\Setting\Entities\Location;
use Illuminate\Database\Eloquent\Model;
use Modules\Material\Entities\Material;

class TransferMixItem extends Model
{
    protected $table = 'transfer_mix_items';
    protected $fillable = [
        'transfer_id','from_site_id','from_location_id', 'material_id','item_class_id','qty','description'
    ];

    public function from_site()
    {
        return $this->belongsTo(Site::class,'from_site_id','id');
    }
    public function from_location()
    {
        return $this->belongsTo(Location::class,'from_location_id','id');
    }
    public function material()
    {
        return $this->belongsTo(Material::class,'material_id','id');
    }
}
