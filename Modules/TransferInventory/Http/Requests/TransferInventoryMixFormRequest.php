<?php

namespace Modules\TransferInventory\Http\Requests;

use App\Http\Requests\FormRequest;

class TransferInventoryMixFormRequest extends FormRequest
{
    protected $rules;
    protected $messages;
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->rules['memo_no']        = ['nullable'];
        $this->rules['transfer_date']  = ['required','date_format:Y-m-d'];
        $this->rules['batch_id']       = ['required'];
        $this->rules['product_id']     = ['required'];
        $this->rules['category_id']    = ['required'];
        $this->rules['to_site_id']     = ['required'];
        $this->rules['to_location_id'] = ['required'];

        $this->messages['batch_id.required']       = 'This wip batch field is required';
        $this->messages['product_id.required']     = 'This mix item field is required';
        $this->messages['category_id.required']    = 'This class field is required';
        $this->messages['to_site_id.required']     = 'This transfer to field is required';
        $this->messages['to_location_id.required'] = 'This to location field is required';
        // if(request()->purchase_id)
        // {
        //     $this->rules['challan_no'][1] = 'unique:cash_purchases,challan_no,'                                                                                                                                                                                                                                                                                                                        .request()->purchase_id;
        // }

        if(request()->has('materials'))
        {
            foreach (request()->materials as $key => $value) {
                $this->rules ['materials.'.$key.'.from_site_id']     = ['required'];
                $this->rules ['materials.'.$key.'.from_location_id'] = ['required'];
                $this->rules ['materials.'.$key.'.id']               = ['required'];
                $this->rules ['materials.'.$key.'.qty']              = ['required','numeric','gt:0','lte:'.$value['available_qty']];

                $this->messages['materials.'.$key.'.from_site_id.required']     = 'This field is required';
                $this->messages['materials.'.$key.'.from_location_id.required'] = 'This field is required';
                $this->messages['materials.'.$key.'.id.required']               = 'This field is required';
                $this->messages['materials.'.$key.'.qty.required']              = 'This field is required';
                $this->messages['materials.'.$key.'.qty.numeric']               = 'The value must be numeric';
                $this->messages['materials.'.$key.'.qty.gt']                    = 'The value must be greater than 0';
                $this->messages['materials.'.$key.'.qty.lte']                   = 'The value must be less than or equal to available qty';
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
