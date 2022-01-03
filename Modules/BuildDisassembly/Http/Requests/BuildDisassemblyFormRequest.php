<?php

namespace Modules\BuildDisassembly\Http\Requests;

use App\Http\Requests\FormRequest;

class BuildDisassemblyFormRequest extends FormRequest
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
        $this->rules['memo_no']               = ['nullable'];
        $this->rules['build_date']            = ['required','date_format:Y-m-d'];
        $this->rules['batch_id']              = ['required'];
        $this->rules['from_site_id']          = ['required'];
        $this->rules['from_location_id']      = ['required'];
        $this->rules['material_id']           = ['required'];
        $this->rules['product_id']            = ['required'];
        $this->rules['build_ratio']           = ['required'];
        $this->rules['build_qty']             = ['required'];
        $this->rules['required_qty']          = ['required'];
        $this->rules['category_id']           = ['required'];
        $this->rules['rice_convertion_ratio'] = ['required'];
        $this->rules['fine_rice_qty']         = ['required'];
        $this->rules['milling_qty']           = ['required'];
        $this->rules['milling_ratio']         = ['required','in:100'];
        $this->rules['bp_site_id']            = ['required'];
        $this->rules['bp_location_id']        = ['required'];

        $this->messages['build_date.required']            = 'This date field is required';
        $this->messages['batch_id.required']              = 'This wip batch field is required';
        $this->messages['from_site_id.required']          = 'This transfer from field is required';
        $this->messages['from_location_id.required']      = 'This from location field is required';
        $this->messages['material_id.required']           = 'This material field is required';
        $this->messages['product_id.required']            = 'This converted item field is required';
        $this->messages['build_ratio.required']           = 'This product ratio field is required';
        $this->messages['build_qty.required']             = 'This build quantity field is required';
        $this->messages['required_qty.required']          = 'This raw material needed field is required';
        $this->messages['category_id.required']           = 'This class field is required';
        $this->messages['rice_convertion_ratio.required'] = 'This convertion ratio field is required';
        $this->messages['fine_rice_qty.required']         = 'This converted quantity field is required';
        $this->messages['milling_qty.required']           = 'This field is required';
        $this->messages['milling_ratio.required']         = 'This field is required';
        $this->messages['milling_ratio.in']               = 'This ratio must be 100';
        $this->messages['tp_site_id.required']            = 'This field is required';
        $this->messages['tp_location_id.required']        = 'This field is required';


        if(request()->has('by_products'))
        {
            foreach (request()->by_products as $key => $value) {
                $this->rules ['by_products.'.$key.'.id']   = ['required'];
                $this->rules ['by_products.'.$key.'.qty']   = ['required','numeric','gt:0'];
                $this->rules ['by_products.'.$key.'.ratio'] = ['required','numeric','gt:0'];

                $this->messages['by_products.'.$key.'.id.required']   = 'This field is required';
                $this->messages['by_products.'.$key.'.qty.required']   = 'This field is required';
                $this->messages['by_products.'.$key.'.qty.numeric']    = 'The value must be numeric';
                $this->messages['by_products.'.$key.'.qty.gt']         = 'The value must be greater than 0';
                $this->messages['by_products.'.$key.'.ratio.required'] = 'This field is required';
                $this->messages['by_products.'.$key.'.ratio.numeric']  = 'The value must be numeric';
                $this->messages['by_products.'.$key.'.ratio.gt']       = 'The value must be greater than 0';
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
