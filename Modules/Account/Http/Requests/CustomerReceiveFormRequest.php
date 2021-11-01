<?php

namespace Modules\Account\Http\Requests;

use App\Http\Requests\FormRequest;

class CustomerReceiveFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'voucher_no'   => 'required',
            'voucher_date' => 'required',
            'warehouse_id'  => 'required',
            'district_id'  => 'required',
            'upazila_id'  => 'required',
            'route_id'  => 'required',
            'area_id'  => 'required',
            'customer_id'  => 'required',
            'payment_type' => 'required',
            'account_id'   => 'required',
            'amount'       => 'required|numeric|gt:0',
        ];
    }

    public function messages()
    {
        return [
            'warehouse_id.required' => 'The warehouse field is required',
            'district_id.required'  => 'The district field is required',
            'upazila_id.required'   => 'The upazila field is required',
            'route_id.required'     => 'The route field is required',
            'area_id.required'     => 'The route field is required',
            'customer_id.required'  => 'The customer field is required',
            'account_id.required'   => 'The account field is required'
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
