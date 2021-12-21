<?php

namespace Modules\Sale\Http\Requests;

use App\Http\Requests\FormRequest;


class SaleInvoiceFormRequest extends FormRequest
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
        $this->rules['memo_no']      = ['required'];
        $this->rules['challan_no']   = ['required','unique:sale_invoices,challan_no'];
        $this->rules['invoice_date'] = ['required','date_format:Y-m-d'];
        $this->rules['customer_id']  = ['required'];
        $this->rules['truck_fare']   = ['nullable','gte:0'];

        if(request()->invoice_id)
        {
            $this->rules['challan_no'][1] = 'unique:sale_invoices,challan_no,'.request()->invoice_id;
        }

        if(!empty(request()->truck_fare) && request()->truck_fare > 0)
        {
            $this->rules['terms']  = ['required'];
        }

        if(request()->has('products'))
        {
            foreach (request()->products as $key => $value) {

                $this->rules ['products.'.$key.'.id']             = ['required'];
                $this->rules ['products.'.$key.'.site_id']        = ['required'];
                $this->rules ['products.'.$key.'.location_id']    = ['required'];
                $this->rules ['products.'.$key.'.qty']            = ['required','numeric','gt:0','lte:'.$value['stock_qty']];
                $this->rules ['products.'.$key.'.net_unit_price'] = ['required','numeric','gt:0'];

                $this->messages['products.'.$key.'.id.required']             = 'This field is required';
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
