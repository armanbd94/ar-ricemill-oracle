<?php

namespace Modules\Loan\Http\Requests;

use App\Http\Requests\FormRequest;

class OfficialLoanFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules['voucher_no']      = ['required','string'];
        $rules['employee_id']      = ['required'];
        $rules['adjusted_date']    = ['required','date','date_format:Y-m-d'];
        $rules['amount']      = ['required'];
        $rules['adjust_amount']      = ['string'];
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
