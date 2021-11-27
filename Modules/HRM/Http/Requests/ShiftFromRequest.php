<?php

namespace Modules\HRM\Http\Requests;

use App\Http\Requests\FormRequest;

class ShiftFromRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules['name']      = ['required','string','unique:shifts,name'];
        $rules['start_time']      = ['required'];
        $rules['end_time']      = ['required'];
        $rules['night_status']      = ['required'];
        $rules['deletable'] = ['required'];
        if(request()->update_id)
        {
            $rules['name'] = 'unique:leaves,name,'.request()->update_id;
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
