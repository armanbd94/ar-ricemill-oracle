<?php

namespace Modules\Purchase\Http\Requests;

use App\Http\Requests\FormRequest;

class PurchaseOrderFormRequest extends FormRequest
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
        $this->rules['memo_no']       = ['required','unique:purchase_orders,memo_no'];
        $this->rules['order_date']    = ['required','date_format:Y-m-d'];
        $this->rules['delivery_date'] = ['required','date_format:Y-m-d','after_or_equal:'.request()->order_date];
        $this->rules['vendor_id']     = ['required'];
        $this->rules['po_no']         = ['required'];
        $this->rules['nos_truck']     = ['required'];
        if(request()->purchase_id)
        {
            $this->rules['memo_no'][1] = 'unique:purchase_orders,memo_no,'.request()->purchase_id;
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
