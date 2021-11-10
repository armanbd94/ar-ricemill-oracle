<?php

namespace Modules\Bank\Http\Requests;

use App\Http\Requests\FormRequest;

class BankTransactionFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'voucher_date'        => 'required|date',
            'account_type'        => 'required|string',
            'bank_name'           => 'required|string',
            'voucher_no'          => 'required|string',
            'amount'              => 'required|numeric|gt:0',
            'description'         => 'nullable|string'
        ];
    }

    public function messages()
    {
        return [
            'voucher_date.required'        => 'The date field is required',
            'voucher_date.date'            => 'The date field value must be date',
            'voucher_no.required'          => 'The withdraw/deposit id field is required',
            'voucher_no.string'            => 'The withdraw/deposit id field value must be string',
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
