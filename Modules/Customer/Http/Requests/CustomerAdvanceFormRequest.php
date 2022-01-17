<?php

namespace Modules\Customer\Http\Requests;

use App\Http\Requests\FormRequest;

class CustomerAdvanceFormRequest extends FormRequest
{
    protected $rules = [];
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->rules['customer_coa_id'] = ['required'];
        $this->rules['type'] = ['required'];
        $this->rules['amount'] = ['required','numeric','gt:0'];
        $this->rules['payment_method'] = ['required'];
        $this->rules['account_id'] = ['required'];
        return $this->rules;
    }

    public function messages()
    {
        return [
            'customer_coa_id.required' => 'The customer field is required'
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
