<?php

namespace Modules\BOM\Http\Requests;

use App\Http\Requests\FormRequest;

class BOMRePackingFormRequest extends FormRequest
{
    public function rules()
    {
        return [
            'memo_no'             => ['required'],
            'packing_number'      => ['required'],
            'to_product_id'       => ['required'],
            'to_site_id'          => ['required'],
            'to_location_id'      => ['required'],
            'from_site_id'        => ['required'],
            'from_location_id'    => ['required'],
            'from_product_id'     => ['required'],
            'product_description' => ['nullable'],
            'product_qty'         => ['required','gt:0','lte:'.request()->product_stock_qty],
            'bag_site_id'         => ['required'],
            'bag_location_id'     => ['required'],
            'bag_id'              => ['required'],
            'bag_description'     => ['nullable'],
            'bag_qty'             => ['required','gt:0','lte:'.request()->bag_stock_qty],
            'packing_date'        => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'memo_no.required'          => 'This field is required',
            'packing_number.required'   => 'This field is required',
            'to_product_id.required'    => 'This field is required',
            'to_site_id.required'       => 'This field is required',
            'to_location_id.required'   => 'This field is required',
            'from_site_id.required'     => 'This field is required',
            'from_location_id.required' => 'This field is required',
            'from_product_id.required'  => 'This field is required',
            'product_qty.required'      => 'This field is required',
            'product_qty.gt'            => 'This field value must be greater than 0',
            'product_qty.lte'           => 'This field value must be less than or equal to current stock qty',
            'bag_site_id.required'      => 'This field is required',
            'bag_location_id.required'  => 'This field is required',
            'bag_id.required'           => 'This field is required',
            'bag_qty.required'          => 'This field is required',
            'bag_qty.gt'                => 'This field value must be greater than 0',
            'bag_qty.lte'               => 'This field value must be less than or equal to current stock qty',
            'packing_date.required'     => 'This field is required',
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
