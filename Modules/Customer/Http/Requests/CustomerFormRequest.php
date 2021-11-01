<?php

namespace Modules\Customer\Http\Requests;

use App\Http\Requests\FormRequest;

class CustomerFormRequest extends FormRequest
{
    protected $rules = [];
    protected $messages = [];
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->rules['name']              = ['required','string','max:100'];
        $this->rules['shop_name']         = ['required','string','max:100'];
        $this->rules['mobile']            = ['required','string','max:15','unique:customers,mobile'];
        $this->rules['email']             = ['nullable','email','string','max:100','unique:customers,email'];
        $this->rules['area_id']           = ['required'];
        $this->rules['customer_group_id'] = ['required'];
        $this->rules['district_id']       = ['required'];
        $this->rules['upazila_id']        = ['required'];
        $this->rules['route_id']          = ['required'];
        $this->rules['address']           = ['required','string'];
        $this->rules['previous_balance']  = ['nullable','numeric','gt:0'];
        $this->rules['avatar']                = ['nullable','image', 'mimes:png,jpg,jpeg,svg'];
        if(request()->update_id){
            $this->rules['mobile'][3] = 'unique:customers,mobile,'.request()->update_id;
            $this->rules['email'][4]  = 'unique:customers,email,'.request()->update_id;
        }
        return $this->rules;
    }

    public function messages()
    {
        $this->messages['area_id.required']      = 'This area field is required';
        $this->messages['customer_group_id.required'] = 'This customer group field is required';
        $this->messages['district_id.required']       = 'This district field is required';
        $this->messages['upazila_id.required']        = 'This upazila field is required';
        $this->messages['route_id.required']          = 'This route field is required';
        return $this->messages;
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
