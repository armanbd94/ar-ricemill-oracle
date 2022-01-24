<?php

namespace Modules\Product\Http\Requests;

use App\Http\Requests\FormRequest;

class ProductFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules['name']          = ['required','string','unique:products,name'];
        $rules['code']          = ['required','string','unique:products,code'];
        $rules['item_group_id'] = ['required'];
        $rules['category_id']   = ['required'];
        $rules['unit_id']       = ['required'];
        $rules['alert_qty']     = ['nullable','numeric','gte:0'];
        $rules['price']         = ['nullable','numeric','gte:0'];
        $rules['tax_id']        = ['nullable','numeric'];
        $rules['tax_method']    = ['required','numeric'];

        if(request()->update_id){
            $rules['name'][2] = 'unique:products,name,'.request()->update_id;
            $rules['code'][2] = 'unique:products,code,'.request()->update_id;
        }
        return $rules;
    }

    public function messages()
    {
        return [
            'category_id.required'   => 'The category name field is required',
            'item_group_id.required' => 'The group name field is required',
            'unit_id.required'       => 'The unit name field is required',
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
