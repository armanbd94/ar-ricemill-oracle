<?php

namespace Modules\ViaCustomer\Http\Requests;

use App\Http\Requests\FormRequest;

class ViaCustomerFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rulse['customer_id']      = ['required'];
        $rulse['name']             = ['required','string','max:100'];
        $rulse['trade_name']       = ['nullable','string','max:100'];
        $rulse['mobile']           = ['required','string','max:15','unique:via_customers,mobile'];
        $rulse['email']            = ['nullable','email','string','max:100','unique:via_customers,email'];
        $rulse['address']          = ['nullable','string'];

        if(request()->update_id){
            $rulse['mobile'][3]           = 'unique:via_customers,mobile,'.request()->update_id;
            $rulse['email'][4]            = 'unique:via_customers,email,'.request()->update_id;
        }
        return $rulse;
    }

    public function messages()
    {
        return [
            'customer_id.required' => 'The customer field is required'
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
