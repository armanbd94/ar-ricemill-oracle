<?php

namespace Modules\Account\Http\Requests;

use App\Http\Requests\FormRequest;

class CashAdjustmentFormRequest extends FormRequest
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
            'warehouse_id'  => 'required',
            'voucher_date' => 'required|date|date_format:Y-m-d',
            'type'         => 'required',
            'amount'       => 'required|numeric',
        ];
    }

    public function messages()
    {
        return [
            'warehouse_id.required' => 'The warehouse field is required',

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
