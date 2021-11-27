<?php

namespace Modules\HRM\Http\Requests;

use App\Http\Requests\FormRequest;

class LeaveTypeFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules['name']      = ['required','string','unique:holidays,name'];
        $rules['short_name']      = ['required','string','unique:holidays,short_name'];
        $rules['number']      = ['required'];
        $rules['leave_type']      = ['required'];
        $rules['deletable'] = ['required'];
        if(request()->update_id)
        {
            $rules['name'] = 'unique:leaves,name,'.request()->update_id;
            $rules['short_name'] = 'unique:leaves,short_name,'.request()->update_id;
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
