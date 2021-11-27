<?php

namespace Modules\HRM\Http\Requests;

use App\Http\Requests\FormRequest;

class AllowanceDeductionManageFormRequest extends FormRequest
{
    protected $rules;
    protected $messages;

    public function rules()
    {
        $this->rules['employee_id']                = ['required'];
        $this->rules['basic_salary']              = ['required'];

        // if(request()->has('benifits_insert')){
        //     foreach (request()->benifits_insert as $key => $value) {
        //         $this->rules    ['benifits_insert.'.$key.'.id']             = ['required'];
        //         $this->messages['benifits_insert.'.$key.'.id.required']     = 'The account field is required';                
        //     }
        // }
        return $this->rules;
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
