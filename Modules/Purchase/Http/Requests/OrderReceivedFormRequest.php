<?php

namespace Modules\Purchase\Http\Requests;

use App\Http\Requests\FormRequest;

class OrderReceivedFormRequest extends FormRequest
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
        $this->rules['memo_no']       = ['required'];
        $this->rules['challan_no']       = ['required','unique:order_received,challan_no'];
        $this->rules['received_date']    = ['required','date_format:Y-m-d'];
        $this->rules['transport_no']     = ['required'];
        if(request()->purchase_id)
        {
            $this->rules['challan_no'][1] = 'unique:order_received,challan_no,'.request()->purchase_id;
        }

        if(request()->has('materials'))
        {
            foreach (request()->materials as $key => $value) {
                $this->rules   ['materials.'.$key.'.qty']          = ['required','numeric','gt:0'];
                $this->messages['materials.'.$key.'.qty.required'] = 'This field is required';
                $this->messages['materials.'.$key.'.qty.numeric']  = 'The value must be numeric';
                $this->messages['materials.'.$key.'.qty.gt']       = 'The value must be greater than 0';

                $this->rules   ['materials.'.$key.'.net_unit_cost']          = ['required','numeric','gt:0'];
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
