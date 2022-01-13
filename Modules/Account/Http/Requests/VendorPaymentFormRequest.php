<?php

namespace Modules\Account\Http\Requests;

use App\Http\Requests\FormRequest;

class VendorPaymentFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'voucher_no'    => 'required',
            'voucher_date'  => 'required',
            'vendor_id' => 'required',
            'payment_type'  => 'required',
            'account_id'    => 'required',
            'amount'        => 'required|numeric|gt:0',
        ];
    }

    public function messages()
    {
        return [
            'voucher_no.required'    => 'The memo field is required',
            'vendor_id.required' => 'The vendor field is required',
            'account_id.required'    => 'The account field is required'
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
