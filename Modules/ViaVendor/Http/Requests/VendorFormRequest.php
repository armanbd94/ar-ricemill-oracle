<?php

namespace Modules\ViaVendor\Http\Requests;

use App\Http\Requests\FormRequest;

class VendorFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rulse['vendor_id']      = ['required'];
        $rulse['name']             = ['required','string','max:100'];
        $rulse['mobile']           = ['required','string','max:15','unique:via_vendors,mobile'];
        $rulse['email']            = ['nullable','email','string','max:100','unique:via_vendors,email'];
        $rulse['address']          = ['nullable','string'];

        if(request()->update_id){
            $rulse['mobile'][3]           = 'unique:via_vendors,mobile,'.request()->update_id;
            $rulse['email'][4]            = 'unique:via_vendors,email,'.request()->update_id;
        }
        return $rulse;
    }

    public function messages()
    {
        return [
            'vendor_id.required' => 'The vendor field is required'
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
