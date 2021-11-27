<?php

namespace Modules\HRM\Http\Requests;

use App\Http\Requests\FormRequest;

class DivisionFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules['name']      = ['required','string','unique:divisions,name'];
        $rules['department_id']      = ['required'];
        $rules['deletable'] = ['required'];
        if(request()->update_id)
        {
            $rules['name'] = 'unique:divisions,name,'.request()->update_id;
        }
        return $rules;
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
