<?php

namespace Modules\Account\Http\Requests;

use App\Http\Requests\FormRequest;

class DebitVoucherFormRequest extends FormRequest
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
        $this->rules['voucher_no']        = ['required'];
        $this->rules['voucher_date']      = ['required'];
        $this->rules['warehouse_id'] = ['required'];
        $this->rules['credit_account_id'] = ['required'];
        $this->messages['warehouse_id.required'] = 'The warehouse field is required';
        $this->messages['credit_account_id.required'] = 'The credit account head field is required';
        if(request()->has('debit_account')){
            foreach (request()->debit_account as $key => $value) {
                $this->rules   ['debit_account.'.$key.'.id']           = ['required'];
                $this->messages['debit_account.'.$key.'.id.required']  = 'The account field is required';

                $this->rules   ['debit_account.'.$key.'.amount']           = ['required','numeric'];
                $this->messages['debit_account.'.$key.'.amount.required']  = 'The amount field is required';
                $this->messages['debit_account.'.$key.'.amount.numeric']  = 'The amount value must be numeric';
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
