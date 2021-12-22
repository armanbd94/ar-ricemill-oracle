<?php

namespace Modules\Setting\Http\Requests;

use App\Http\Requests\FormRequest;

class BatchFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [];
        $rules['batch_start_date'] = ['required'];
        $rules['batch_no'] = ['required','string','unique:batches,batch_no'];
        if(request()->update_id)
        {
            $rules['batch_no'][2] = 'unique:batches,batch_no,'.request()->update_id;
        }
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
