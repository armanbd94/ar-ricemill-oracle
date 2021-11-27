<?php

namespace Modules\HRM\Http\Requests;

use App\Http\Requests\FormRequest;

class HolidayFormRequest extends FormRequest
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
        $rules['holiday_type']      = ['required'];
        $rules['start_date']    = ['required', 'date', 'date_format:Y-m-d'];
        $rules['end_date']    = ['required', 'date', 'date_format:Y-m-d'];
        $rules['deletable'] = ['required'];
        if(request()->update_id)
        {
            $rules['name'] = 'unique:holidays,name,'.request()->update_id;
            $rules['short_name'] = 'unique:holidays,short_name,'.request()->update_id;
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
