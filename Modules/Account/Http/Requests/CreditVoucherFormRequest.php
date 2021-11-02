<?php

namespace Modules\Account\Http\Requests;

use App\Http\Requests\FormRequest;

class CreditVoucherFormRequest extends FormRequest
{
    protected $rules = [];
    protected $messages = [];
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->rules['voucher_no']                = ['required'];
        $this->rules['voucher_date']              = ['required'];
        $this->rules['debit_account_id']          = ['required'];
        $this->rules['warehouse_id'] = ['required'];
        $this->messages['warehouse_id.required']     = 'The warehouse field is required';
        $this->messages['debit_account_id.required'] = 'The debit account head field is required';

        if(request()->has('credit_account')){
            foreach (request()->credit_account as $key => $value) {
                $this->rules    ['credit_account.'.$key.'.id']             = ['required'];
                $this->messages['credit_account.'.$key.'.id.required']     = 'The account field is required';

                $this->rules    ['credit_account.'.$key.'.amount']         = ['required','numeric'];
                $this->messages['credit_account.'.$key.'.amount.required'] = 'The amount field is required';
                $this->messages['credit_account.'.$key.'.amount.numeric']  = 'The amount value must be numeric';
            }
        }
        return $this->rules;
    }

    public function messages()
    {
        return $this->messages;
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
