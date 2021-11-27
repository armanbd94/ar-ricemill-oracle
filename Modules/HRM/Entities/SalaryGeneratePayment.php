<?php

namespace Modules\HRM\Entities;

use App\Models\BaseModel;
use Modules\HRM\Entities\SalaryGenerate;
use Modules\Account\Entities\Transaction;
use Modules\Account\Entities\ChartOfAccount;

class SalaryGeneratePayment extends BaseModel
{
    protected $fillable = [
        'salary_generated_id', 'account_id', 'transaction_id', 'employee_transaction_id ', 'voucher_no', 'voucher_date',
        'amount', 'payment_method', 'cheque_no', 'payment_note', 'created_by', 'modified_by'
    ];

    public function purchase()
    {
        return $this->belongsTo(SalaryGenerate::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id', 'id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function employee_debit_transaction()
    {
        return $this->belongsTo(Transaction::class, 'employee_transaction_id', 'id');
    }
}
