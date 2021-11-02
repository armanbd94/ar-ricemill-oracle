<?php

namespace Modules\Setting\Http\Requests;

use App\Http\Requests\FormRequest;

class JobTypeFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [];
        $rules['job_type'] = ['required','string','unique:job_types,job_type'];
        if(request()->update_id)
        {
            $rules['job_type'][2] = 'unique:job_types,job_type,'.request()->update_id;
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
