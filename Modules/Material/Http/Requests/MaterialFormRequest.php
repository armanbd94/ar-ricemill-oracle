<?php

namespace Modules\Material\Http\Requests;

use App\Http\Requests\FormRequest;

class MaterialFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules['material_name']        = ['required','string','unique:materials,material_name'];
        $rules['material_code']        = ['required','string','unique:materials,material_code'];
        $rules['type']                 = ['required'];
        $rules['category_id']          = ['required'];
        $rules['unit_id']              = ['required'];
        $rules['alert_qty']            = ['nullable','numeric','gte:0'];
        $rules['tax_id']               = ['nullable','numeric'];
        $rules['tax_method']           = ['required','numeric'];


        if(request()->update_id){
            $rules['material_name'][2] = 'unique:materials,material_name,'.request()->update_id;
            $rules['material_code'][2] = 'unique:materials,material_code,'.request()->update_id;
        }
        return $rules;
    }

    public function messages()
    {
        return [
            'category_id.required'      => 'The category name field is required',
            'unit_id.required'          => 'The unit name field is required',
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
