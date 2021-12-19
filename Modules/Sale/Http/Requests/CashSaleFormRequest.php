<?php

namespace Modules\Sale\Http\Requests;

use App\Http\Requests\FormRequest;

class CashSaleFormRequest extends FormRequest
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
        $this->rules['memo_no']       = ['required','unique:cash_sales,memo_no'];
        $this->rules['sale_date']     = ['required','date_format:Y-m-d'];
        $this->rules['delivery_date'] = ['required','date_format:Y-m-d','after_or_equal:sale_date'];
        $this->rules['customer_name'] = ['required'];
        $this->rules['do_number']     = ['required'];
        $this->rules['account_id']    = ['required'];

        if(request()->sale_id)
        {
            $this->rules['memo_no'][1] = 'unique:cash_sales,memo_no,'.request()->sale_id;
        }
        $this->messages['account_id.required'] = 'The deposit account field is required';

        if(request()->has('products'))
        {
            foreach (request()->products as $key => $value) {

                $this->rules ['products.'.$key.'.site_id']        = ['required'];
                $this->rules ['products.'.$key.'.location_id']    = ['required'];
                $this->rules ['products.'.$key.'.qty']            = ['required','numeric','gt:0','lte:'.$value['stock_qty']];
                $this->rules ['products.'.$key.'.net_unit_price'] = ['required','numeric','gt:0'];

                $this->messages['products.'.$key.'.site_id.required']        = 'This field is required';
                $this->messages['products.'.$key.'.location_id.required']    = 'This field is required';
                $this->messages['products.'.$key.'.qty.required']            = 'This field is required';
                $this->messages['products.'.$key.'.qty.numeric']             = 'The value must be numeric';
                $this->messages['products.'.$key.'.qty.gt']                  = 'The value must be greater than 0';
                $this->messages['products.'.$key.'.qty.lte']                 = 'The value must be less than or equal to available quantity';
                $this->messages['products.'.$key.'.net_unit_price.required'] = 'This field is required';
                $this->messages['products.'.$key.'.net_unit_price.numeric']  = 'The value must be numeric';
                $this->messages['products.'.$key.'.net_unit_price.gt']       = 'The value must be greater than 0';
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
