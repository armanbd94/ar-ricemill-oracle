<?php

namespace Modules\Purchase\Http\Requests;

use App\Http\Requests\FormRequest;

class CashPurchaseFormRequest extends FormRequest
{
    protected $rules;
    protected $messages;
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->rules['memo_no']      = ['nullable'];
        $this->rules['challan_no']   = ['required','unique:cash_purchases,challan_no'];
        $this->rules['receive_date'] = ['required','date_format:Y-m-d'];
        $this->rules['vendor_name'] = ['required'];
        $this->rules['vendor_name'] = ['required'];
        if(request()->receive_id)
        {
            $this->rules['challan_no'][1] = 'unique:cash_purchases,challan_no,'.request()->receive_id;
        }

        if(request()->has('materials'))
        {
            foreach (request()->materials as $key => $value) {
                $this->rules ['materials.'.$key.'.site_id']       = ['required'];
                $this->rules ['materials.'.$key.'.location_id']   = ['required'];
                $this->rules ['materials.'.$key.'.qty']           = ['required','numeric','gt:0'];
                $this->rules ['materials.'.$key.'.net_unit_cost'] = ['required','numeric','gt:0'];

                $this->messages['materials.'.$key.'.site_id.required']       = 'This field is required';
                $this->messages['materials.'.$key.'.location_id.required']   = 'This field is required';
                $this->messages['materials.'.$key.'.qty.required']           = 'This field is required';
                $this->messages['materials.'.$key.'.qty.numeric']            = 'The value must be numeric';
                $this->messages['materials.'.$key.'.qty.gt']                 = 'The value must be greater than 0';
                $this->messages['materials.'.$key.'.net_unit_cost.required'] = 'This field is required';
                $this->messages['materials.'.$key.'.net_unit_cost.numeric']  = 'The value must be numeric';
                $this->messages['materials.'.$key.'.net_unit_cost.gt']       = 'The value must be greater than 0';
            }
        }

        return $this->rules;
    }

    public function messages()
    {
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
