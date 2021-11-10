<?php

namespace Modules\Expense\Http\Requests;

use App\Http\Requests\FormRequest;


class ExpenseFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'date'            => 'required|date|date_format:Y-m-d',
            'expense_item_id' => 'required',
            'payment_type'    => 'required',
            'account_id'      => 'required',
            'amount'          => 'required|numeric|gt:0',
            'remarks'         => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'expense_item_id.required' => 'The expense type field is required',
            'account_id.required'      => 'The account field is required'
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
