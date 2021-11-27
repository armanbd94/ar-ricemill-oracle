<?php

namespace Modules\HRM\Http\Requests;

use App\Http\Requests\FormRequest;

class SalaryGenerateRequestForm extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules['sd_date'] =     ['required'];

        //if (request()->update_id) {
            //$rules['machine_name'][2] = 'unique:maintenance_machines,machine_name,' . request()->update_id;
            //$rules['machine_code'][2] = 'unique:maintenance_machines,machine_code,' . request()->update_id;
        //}
        return $rules;
    }
    
    public function messages()
    {
        return [
            'sd_date.required' => 'The Date name field is required',
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
