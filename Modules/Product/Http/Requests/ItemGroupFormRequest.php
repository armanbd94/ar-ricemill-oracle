<?php

namespace Modules\Product\Http\Requests;

use App\Http\Requests\FormRequest;

class ItemGroupFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules['name'] = ['required','unique:item_groups,name'];
        if(request()->update_id)
        {
            $rules['name'][1] = 'unique:item_groups,name,'.request()->update_id;
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
