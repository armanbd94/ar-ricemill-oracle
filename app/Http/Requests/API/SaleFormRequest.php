<?php

namespace App\Http\Requests\API;

use App\Http\Requests\API\FormRequest;

class SaleFormRequest extends FormRequest
{

    protected $rules = [];
    protected $messages = [];

    public function rules()
    {
        $this->rules['memo_no']         = ['required','unique:sales,memo_no'];
        $this->rules['sale_date']       = ['required','date','date_format:Y-m-d'];
        $this->rules['customer_id']     = ['required'];
        $this->rules['order_discount']  = ['nullable','numeric','gte:0'];
        $this->rules['shipping_cost']   = ['nullable','numeric','gte:0'];
        $this->rules['labor_cost']      = ['nullable','numeric','gte:0'];
        $this->rules['total_commission']      = ['nullable','numeric','gte:0'];

        if(request()->has('products'))
        {
            foreach (request()->products as $key => $value) {
                $this->rules['products.'.$key.'.qty']             = ['required','numeric','gt:0','lte:'.$value['stock_qty']];
                $this->messages['products.'.$key.'.qty.required'] = 'This field is required';
                $this->messages['products.'.$key.'.qty.numeric']  = 'The value must be numeric';
                $this->messages['products.'.$key.'.qty.gt']       = 'The value must be greater than 0';
                $this->messages['products.'.$key.'.qty.lte']      = 'The value must be less than or equal to stock quantity';
            }
        }


        $this->rules['payment_status'] = ['required'];
        if(!empty(request()->payment_status) && request()->payment_status != 3)
        {
            $this->rules['paid_amount'] = ['required','numeric','gt:0','lt:'.request()->net_total];
            if(request()->payment_status == 1)
            {
                $this->rules['paid_amount'][2] = 'min:'.request()->net_total;
                $this->rules['paid_amount'][3] = 'max:'.request()->net_total;
            }
            $this->rules['payment_method'] = ['required'];
            $this->rules['account_id'] = ['required'];
            if(request()->payment_method != 1)
            {
                $this->rules['reference_no'] = ['required'];
            }
        }
        return $this->rules;
    }

    public function messages()
    {
        return $this->messages;
    }

    public function authorize()
    {
        return true;
    }
}
