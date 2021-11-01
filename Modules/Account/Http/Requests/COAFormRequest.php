<?php

namespace Modules\Account\Http\Requests;

use App\Http\Requests\FormRequest;

class COAFormRequest extends FormRequest
{
    protected $rules = [];

    public function rules()
    {
        $this->rules['name']           = ['required','string','unique:chart_of_accounts,name'];
        $this->rules['code']           = ['required','string','unique:chart_of_accounts,code'];
        $this->rules['parent_name']    = ['required'];
        $this->rules['level']          = ['required'];
        $this->rules['type']           = ['required','string'];
        $this->rules['transaction']    = ['nullable'];
        $this->rules['general_ledger'] = ['nullable'];
        $this->rules['status']         = ['nullable'];

        if(request()->update_id){
            $this->rules['name'][2] = 'unique:chart_of_accounts,name,'.request()->update_id;
            $this->rules['code'][2] = 'unique:chart_of_accounts,code,'.request()->update_id;
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
