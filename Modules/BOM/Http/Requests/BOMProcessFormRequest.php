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
            'from_site_id'         => ['required'],
            'from_location_id'     => ['required'],
            'product_particular'   => ['required'],
            'product_per_unit_qty' => ['required','gt:0'],
            'product_required_qty' => ['required','gt:0','lte:'.request()->product_stock_qty],
            'bag_site_id'          => ['required'],
            'bag_location_id'      => ['required'],
            'bag_id'               => ['required'],
            'bag_particular'       => ['required'],
            'bag_per_unit_qty'     => ['required','gt:0'],
            'bag_required_qty'     => ['required','gt:0','lte:'.request()->bag_stock_qty],
            'total_rice_qty'       => ['required','gt:0'],
            'total_bag_qty'        => ['required','gt:0'],
            'process_date'         => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'batch_id.required'             => 'This field is required',
            'process_number.required'       => 'This field is required',
            'to_product_id.required'        => 'This field is required',
            'to_site_id.required'           => 'This field is required',
            'to_location_id.required'       => 'This field is required',
            'from_site_id.required'         => 'This field is required',
            'from_location_id.required'     => 'This field is required',
            'from_product_id.required'      => 'This field is required',
            'product_particular.required'   => 'This field is required',
            'product_per_unit_qty.required' => 'This field is required',
            'product_required_qty.required' => 'This field is required',
            'product_required_qty.gt'       => 'The value must be greater than 0',
            'product_required_qty.lte'      => 'The value must be less than or equal to available qty',
            'bag_site_id.required'          => 'This field is required',
            'bag_location_id.required'      => 'This field is required',
            'bag_id.required'               => 'This field is required',
            'bag_particular.required'       => 'This field is required',
            'bag_per_unit_qty.required'     => 'This field is required',
            'bag_required_qty.required'     => 'This field is required',
            'bag_required_qty.gt'           => 'The value must be greater than 0',
            'bag_required_qty.lte'          => 'The value must be less than or equal to available qty',
            'total_rice_qty.required'       => 'This field is required',
            'total_bag_qty.required'        => 'This field is required',
            'process_date.required'         => 'This field is required',
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
