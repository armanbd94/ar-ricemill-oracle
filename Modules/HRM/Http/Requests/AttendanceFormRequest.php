<?php

namespace Modules\HRM\Http\Requests;

use App\Http\Requests\FormRequest;

class AttendanceFormRequest extends FormRequest
{
    protected $rules = [];
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->rules['date']      = ['required','string'];
        $this->rules['emp_id'] = ['required'];
        $this->rules['start_time'] = ['required'];
        $this->rules['end_time'] = ['required'];
        //$rules['deletable'] = ['required'];
        if(request()->update_id)
        {
            //$rules['date'] = 'unique:departments,name,'.request()->update_id;
        }
        return $this->rules;
    }

    public function messages()
    {
        return [
            'date.required' => 'The date field is required',
            'emp_id.required' => 'The employee id field is required',
            'start_time.required' => 'The start time field is required',
            'end_time.required' => 'The end time field is required'
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
