<?php

namespace Modules\BuildReProcess\Entities;

use Illuminate\Database\Eloquent\Model;
class BuildReProcessByProduct extends Model
{
    protected $fillable = ['process_id','product_id','ratio','qty'];
}
