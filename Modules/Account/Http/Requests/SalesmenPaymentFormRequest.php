<?php

namespace Modules\Account\Http\Requests;

use App\Http\Requests\FormRequest;

class SalesmenPaymentFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    
    public function rules()
    {
        return [
            'voucher_no'   => 'required',
            'voucher_date' => 'required',
            'salesmen_id'  => 'required',
            'payment_type' => 'required',
            'account_id'   => 'required',
            'amount'       => 'required|numeric|gt:0',
        ];
    }

    public function messages()
    {
        return [
            'salesmen_id.required'        => 'The salesmen field is required',
            'account_id.required' => 'The account field is required'
        ];
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
