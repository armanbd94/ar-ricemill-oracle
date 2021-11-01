<?php

namespace App\Http\Requests;

use App\Http\Requests\FormRequest;


class TaxFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules['name']      = ['required','string','unique:taxes,name'];
        $rules['rate']     = ['required','numeric','gt:0'];

        if(request()->update_id)
        {
            $rules['name'] = 'unique:taxes,name,'.request()->update_id;
        }
        return $rules;
    }
}
