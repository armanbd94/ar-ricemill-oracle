<?php

namespace Modules\Vendor\Http\Requests;

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
        $rulse['name']             = ['required','string','max:100'];
        $rulse['trade_name']       = ['required','string','max:100'];
        $rulse['mobile']           = ['required','string','max:15','unique:vendors,mobile'];
        $rulse['email']            = ['nullable','email','string','max:100','unique:vendors,email'];
        $rulse['address']          = ['nullable','string'];
        $rulse['previous_balance'] = ['nullable','numeric'];

        if(request()->update_id){
            $rulse['mobile'][3]           = 'unique:vendors,mobile,'.request()->update_id;
            $rulse['email'][4]            = 'unique:vendors,email,'.request()->update_id;
        }
        return $rulse;
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
