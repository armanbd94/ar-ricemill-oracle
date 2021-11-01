<?php

namespace Modules\Account\Http\Requests;

use App\Http\Requests\FormRequest;

class SupplierPaymentFormRequest extends FormRequest
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
            'supplier_id'  => 'required',
            'payment_type' => 'required',
            'account_id'   => 'required',
            'amount'       => 'required|numeric|gt:0',
        ];
    }

    public function messages()
    {
        return [
            'supplier_id.required'        => 'The supplier field is required',
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
