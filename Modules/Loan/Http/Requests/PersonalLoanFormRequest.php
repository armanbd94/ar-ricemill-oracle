<?php

namespace Modules\Loan\Http\Requests;

use App\Http\Requests\FormRequest;

class PersonalLoanFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules['voucher_no']      = ['required','string'];
        $rules['person_id']      = ['required','string'];
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
