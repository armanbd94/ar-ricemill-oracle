<?php

namespace Modules\Setting\Http\Requests;

use App\Http\Requests\FormRequest;

class LocationFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [];
        $rules['name'] = ['required','string','unique:sites,name'];
        $rules['site_id'] = ['required'];
        if(request()->update_id)
        {
            $rules['name'][2] = 'unique:sites,name,'.request()->update_id;
        }
        return $rules;
    }

    public function messages()
    {
        return ['site_id.required' => 'The site field is required'];
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
