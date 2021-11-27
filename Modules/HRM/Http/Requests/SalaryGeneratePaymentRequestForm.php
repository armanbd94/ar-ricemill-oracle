<?php

namespace Modules\HRM\Http\Requests;

use App\Http\Requests\FormRequest;

class SalaryGeneratePaymentRequestForm extends FormRequest
{
    protected $rules = [];
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    
    public function rules()
    {

        $this->rules['amount'] = ['required','numeric','lte:'.request()->due_amount];
        $this->rules['payment_method'] = ['required'];
        $this->rules['account_id'] = ['required'];
        if(!empty(request()->payment_method) && request()->payment_method == 2){
            $this->rules['cheque_number'] = ['required'];
        }
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
