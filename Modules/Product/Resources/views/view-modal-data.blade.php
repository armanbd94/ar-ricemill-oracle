<div class="col-md-12">
    <div class="table-responsive">
        <table class="table table-borderless">
            <tr>
                <td><b>Product Name</b></td>
                <td><b>:</b></td>
                <td>{{ $product->name }}</td>

                <td><b>Product Code</b></td>
                <td><b>:</b></td>
                <td>{{ $product->code }}</td>
            </tr>
            <tr>
                <td><b>Group</b></td>
                <td><b>:</b></td>
                <td>{{ $product->item_group->name }}</td>

                <td><b>Category</b></td>
                <td><b>:</b></td>
                <td>{{ $product->category->name }}</td>
            </tr>
            <tr>
                <td><b>Price</b></td>
                <td><b>:</b></td>
                <td>{{ $product->price ? number_format($product->price,2) : 0 }}</td>

                <td><b>Unit</b></td>
                <td><b>:</b></td>
                <td>{{ $product->unit->unit_name }}</td>
            </tr>
            <tr>
                <td><b>Stock Quantity</b></td>
                <td><b>:</b></td>
                <td>{{ $product->qty ?? 0 }}</td>

                <td><b>Stock Alert Quantity</b></td>
                <td><b>:</b></td>
                <td>{{$product->alert_qty ?? 0 }}
                </td>
            </tr>
            <tr>
                <td><b>Created By</b></td>
                <td><b>:</b></td>
                <td>{{  $product->created_by  }}</td>

                <td><b>Modified By</b></td>
                <td><b>:</b></td>
                <td>{{  $product->modified_by ? $product->modified_by : ''  }}</td>
            </tr>
            <tr>
                <td><b>Create Date</b></td>
                <td><b>:</b></td>
                <td>{{  $product->created_at ? date(config('settings.date_format'),strtotime($product->created_at)) : ''  }}
                </td>

                <td><b>Modified Date</b></td>
                <td><b>:</b></td>
                <td>{{  $product->modified_by ? date(config('settings.date_format'),strtotime($product->updated_at)) : ''  }}
                </td>
            </tr>
        </table>
    </div>
</div>

