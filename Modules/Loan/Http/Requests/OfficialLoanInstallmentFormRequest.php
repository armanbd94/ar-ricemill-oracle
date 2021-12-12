<?php

namespace Modules\Loan\Http\Requests;

use App\Http\Requests\FormRequest;

class OfficialLoanInstallmentFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {        
        $rules['voucher_no']      = ['required','string'];
        $rules['employee_id']      = ['required','string'];
        $rules['loan_id']      = ['required'];
        $rules['installment_date']    = ['required','date','date_format:Y-m-d'];
        $rules['installment_amount']      = ['required'];
        $rules['payment_method']      = ['required'];
        $rules['account_id']      = ['required'];
        $rules['purpose']      = ['required'];        
        return $rules;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
