<div class="col-md-12">
    <div class="table-responsive">
        <table class="table table-borderless">
            <tr>
                <td><b>Material Name</b></td>
                <td><b>:</b></td>
                <td>{{ $material->material_name }}</td>

                <td><b>Material Code</b></td>
                <td><b>:</b></td>
                <td>{{ $material->material_code }}</td>
            </tr>
            <tr>
                <td><b>Cost</b></td>
                <td><b>:</b></td>
                <td>{{ $material->cost ? number_format($material->cost,2) : 0 }}</td>

                <td><b>Unit</b></td>
                <td><b>:</b></td>
                <td>{{ $material->unit->unit_name }}</td>
            </tr>
            <tr>
                <td><b>Stock Quantity</b></td>
                <td><b>:</b></td>
                <td>{{ $material->qty ?? 0 }}</td>

                <td><b>Stock Alert Quantity</b></td>
                <td><b>:</b></td>
                <td>{{$material->alert_qty ?? 0 }}
                </td>
            </tr>
            <tr>
                <td><b>Created By</b></td>
                <td><b>:</b></td>
                <td>{{  $material->created_by  }}</td>

                <td><b>Modified By</b></td>
                <td><b>:</b></td>
                <td>{{  $material->modified_by ? $material->modified_by : ''  }}</td>
            </tr>
            <tr>
                <td><b>Create Date</b></td>
                <td><b>:</b></td>
                <td>{{  $material->created_at ? date(config('settings.date_format'),strtotime($material->created_at)) : ''  }}
                </td>

                <td><b>Modified Date</b></td>
                <td><b>:</b></td>
                <td>{{  $material->modified_by ? date(config('settings.date_format'),strtotime($material->updated_at)) : ''  }}
                </td>
            </tr>
        </table>
    </div>
</div>

