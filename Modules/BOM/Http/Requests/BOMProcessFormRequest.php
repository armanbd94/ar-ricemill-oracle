<?php

namespace Modules\BOM\Http\Requests;

use App\Http\Requests\FormRequest;

class BOMProcessFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'memo_no'              => ['required'],
            'batch_id'             => ['required'],
            'process_number'       => ['required'],
            'to_product_id'        => ['required'],
            'to_site_id'           => ['required'],
            'to_location_id'       => ['required'],
            'from_product_id'      => ['required'],
            'product_particular'   => ['required'],
            'product_per_unit_qty' => ['required','gt:0'],
            'product_required_qty' => ['required','gt:0'],
            'bag_site_id'          => ['required'],
            'bag_location_id'      => ['required'],
            'bag_id'               => ['required'],
            'bag_particular'       => ['required'],
            'bag_per_unit_qty'     => ['required','gt:0'],
            'bag_required_qty'     => ['required','gt:0'],
            'total_rice_qty'       => ['required','gt:0'],
            'total_bag_qty'        => ['required','gt:0'],
            'process_date'         => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'batch_id.required'       => 'This field is required',
            'process_number.required' => 'This field is required',
            'to_product_id.required'  => 'This field is required',
            'to_site_id'              => 'This field is required',
            'to_location_id'          => 'This field is required',
            'from_product_id'         => 'This field is required',
            'product_particular'      => 'This field is required',
            'product_per_unit_qty'    => 'This field is required',
            'product_required_qty'    => 'This field is required',
            'bag_site_id'             => 'This field is required',
            'bag_location_id'         => 'This field is required',
            'bag_id'                  => 'This field is required',
            'bag_particular'          => 'This field is required',
            'bag_per_unit_qty'        => 'This field is required',
            'bag_required_qty'        => 'This field is required',
            'total_rice_qty'          => 'This field is required',
            'total_bag_qty'           => 'This field is required',
            'process_date'            => 'This field is required',
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
