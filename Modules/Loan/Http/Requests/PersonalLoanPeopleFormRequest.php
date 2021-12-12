<?php

namespace Modules\Loan\Http\Requests;

use App\Http\Requests\FormRequest;

class PersonalLoanPeopleFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules['name']      = ['required','string'];
        $rules['phone']      = ['required'];
        $rules['address']      = ['required'];
        $rules['loan_term_type']      = ['required'];
        
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
