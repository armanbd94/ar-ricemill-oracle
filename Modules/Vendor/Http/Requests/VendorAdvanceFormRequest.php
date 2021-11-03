<?php

namespace Modules\Vendor\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VendorAdvanceFormRequest extends FormRequest
{
    protected $rules = [];

    public function rules()
    {
        $this->rules['vendor']         = ['required'];
        $this->rules['type']           = ['required'];
        $this->rules['amount']         = ['required','numeric','gt:0'];
        $this->rules['payment_method'] = ['required'];
        $this->rules['account_id']     = ['required'];
        if(request()->payment_method == 2){
            $this->rules['cheque_number'] = ['required'];
        }
        return $this->rules;
    }

    public function messages()
    {
        return [
            'vendor.required' => 'Vendor field is required'
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
