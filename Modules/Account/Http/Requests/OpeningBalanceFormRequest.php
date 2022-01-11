<?php

namespace Modules\Account\Http\Requests;

use App\Http\Requests\FormRequest;



class OpeningBalanceFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'voucher_no'          => 'required',
            'voucher_date'        => 'required',
            'chart_of_account_id' => 'required',
            'amount'              => 'required|numeric|gt:0',
        ];
    }

    public function messages()
    {
        return [
            'chart_of_account_id.required' => 'The account head field is required',
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
