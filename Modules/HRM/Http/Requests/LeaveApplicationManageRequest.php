<?php

namespace Modules\HRM\Http\Requests;

use App\Http\Requests\FormRequest;

class LeaveApplicationManageRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        
        $rules['leave_id']      = ['required','string'];
        $rules['employee_id']      = ['required','string'];
        $rules['start_date']      = ['required'];
        $rules['end_date']      = ['required'];
        $rules['alternative_employee']      = ['string'];
        $rules['number_leave']      = ['required'];
        $rules['employee_location']      = ['string'];
        $rules['leave_type']      = ['required'];
        $rules['purpose']      = ['required'];
        $rules['deletable'] = ['required'];
        
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
