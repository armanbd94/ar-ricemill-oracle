<?php

namespace Modules\HRM\Http\Requests;

use App\Http\Requests\FormRequest;

class RouteFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules['name']      = ['required','string','unique:employee_routes,name'];
        $rules['short_name']      = ['required','string','unique:employee_routes,short_name'];
        $rules['deletable'] = ['required'];
        if(request()->update_id)
        {
            $rules['name'] = 'unique:employee_routes,name,'.request()->update_id;
            $rules['short_name'] = 'unique:employee_routes,short_name,'.request()->update_id;
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
