<?php

namespace Modules\Account\Entities;

use App\Models\BaseModel;
use Modules\Account\Entities\ChartOfAccount;

class Transaction extends BaseModel
{

    protected $fillable   = ['chart_of_account_id','voucher_no', 'voucher_type', 'voucher_date', 'description', 'debit', 
    'credit', 'is_opening','posted', 'approve', 'created_by', 'modified_by'];

    public function coa()
    {
        return $this->belongsTo(ChartOfAccount::class,'chart_of_account_id','id');
    }


}
