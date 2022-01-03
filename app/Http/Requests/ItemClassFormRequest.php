<?php

namespace App\Http\Requests;

use App\Http\Requests\FormRequest;


class ItemClassFormRequest extends FormRequest
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
        $rules['name'] = ['required','unique:item_classes,name'];
        if(request()->update_id)
        {
            $rules['name'][1] = 'unique:item_classes,name,'.request()->update_id;
        }
        return $rules;
    }
}
