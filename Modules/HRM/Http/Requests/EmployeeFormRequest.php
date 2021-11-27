<?php

namespace Modules\HRM\Http\Requests;

use App\Http\Requests\FormRequest;

class EmployeeFormRequest extends FormRequest
{
    protected $rules = [];
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->rules['name']              = ['required'];
        $this->rules['email']             = ['nullable','unique:employees,email'];
        $this->rules['phone']             = ['required','unique:employees,phone'];
        $this->rules['alternative_phone'] = ['nullable','unique:employees,phone'];
        if(!empty(request()->update_id))
        {
            $this->rules['email'][1]             = 'unique:employees,email,'.request()->update_id;
            $this->rules['phone'][1]             = 'unique:employees,phone,'.request()->update_id;
            $this->rules['alternative_phone'][1] = 'unique:employees,phone,'.request()->update_id;
        }
        $this->rules['city']            = ['required'];
        //$this->rules['upazila_id']             = ['required'];
        $this->rules['zipcode']                = ['nullable'];
        $this->rules['address']                = ['required'];
        $this->rules['department_id']          = ['required'];
        $this->rules['division_id']            = ['required'];
        $this->rules['employee_id']            = ['required'];
        //$this->rules['wallet_number']            = ['required'];
        $this->rules['finger_id']            = ['required'];
        $this->rules['shift_id']               = ['required'];
        $this->rules['current_designation_id'] = ['required'];
        $this->rules['joining_designation_id'] = ['nullable'];
        $this->rules['job_status']             = ['required'];
        $this->rules['joining_date']           = ['nullable','date','date_format:Y-m-d'];
        $this->rules['probation_start']        = ['nullable','date','date_format:Y-m-d'];
        $this->rules['probation_end']          = ['nullable','date','date_format:Y-m-d'];
        $this->rules['confirmation_date']      = ['nullable','date','date_format:Y-m-d'];
        $this->rules['duty_type']              = ['required'];
        $this->rules['contract_start']         = ['nullable','date','date_format:Y-m-d'];
        $this->rules['contract_end']           = ['nullable','date','date_format:Y-m-d'];
        $this->rules['rate_type']              = ['required'];
        $this->rules['rate']                   = ['required'];
        $this->rules['joining_rate']           = ['nullable'];
        $this->rules['overtime']               = ['required'];
        $this->rules['pay_freequency']         = ['required'];
        $this->rules['bank_name']              = ['nullable'];
        $this->rules['account_no']             = ['nullable'];
        $this->rules['termination_date']       = ['nullable','date','date_format:Y-m-d'];
        $this->rules['termination_reason']     = ['nullable'];
        $this->rules['supervisor_id']     = ['required'];
        $this->rules['is_supervisor']     = ['required'];
        $this->rules['father_name']     = ['required'];
        $this->rules['mother_name']     = ['required'];
        $this->rules['dob']     = ['required'];
        $this->rules['gender']     = ['required'];
        $this->rules['marital_status']     = ['required'];
        $this->rules['blood_group']    = ['required'];
        $this->rules['residential_status']     = ['required'];
        $this->rules['religion']     = ['required'];
        $this->rules['nid_no']     = ['required'];
        $this->rules['holiday']     = ['required'];
        $this->rules['nid_photo']     = ['nullable','image','mimes:jpg,jpeg,png,svg'];
        $this->rules['photograph']     = ['nullable','image','mimes:jpg,jpeg,png,svg'];

        $this->rules['emergency_contact_name']     = ['required'];
        $this->rules['emergency_contact_phone']     = ['required'];
        $this->rules['emergency_contact_relation']     = ['required'];
        $this->rules['emergency_contact_address']     = ['required'];

        $this->rules['alternative_emergency_contact_name']     = ['nullable'];
        $this->rules['alternative_emergency_contact_phone']     = ['nullable'];
        $this->rules['alternative_emergency_contact_relation']     = ['nullable'];
        $this->rules['alternative_emergency_contact_address']     = ['nullable'];

        return $this->rules;
    }

    public function messages()
    {
        return [
            'warehouse_id.required' => 'The branch field is required',
            'dob.required' => 'The date of birth field is required'
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
