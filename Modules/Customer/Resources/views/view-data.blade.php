<div class="col-md-12">
    <div class="row">
        <div class="table-responsive col-9">
            <table class="table table-borderless">
                <tr>
                    <td><b>Shop Name</b></td><td><b>:</b></td><td>{{ $customer->shop_name }}</td>
                    <td><b>Name</b></td><td><b>:</b></td><td>{{ $customer->name }}</td>
                </tr>
                <tr>
                    <td><b>Mobile No.</b></td><td><b>:</b></td><td>{{ $customer->mobile }}</td>
                    <td><b>Email</b></td><td><b>:</b></td><td>{!! $customer->email ? $customer->email : '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">No Email</span>' !!}</td>
                </tr>
                
                <tr>
                    <td><b>Customer Group</b></td><td><b>:</b></td><td>{{  $customer->customer_group->group_name  }}</td>
                    <td><b>District</b></td><td><b>:</b></td><td>{{  $customer->district->name  }}</td>
                </tr>
                <tr>
                    <td><b>Upazila</b></td><td><b>:</b></td><td>{{  $customer->upazila->name  }}</td>
                    <td><b>Route</b></td><td><b>:</b></td><td>{{  $customer->route->name  }}</td>
                </tr>
                <tr>
                    <td><b>Area</b></td><td><b>:</b></td><td>{{  $customer->area->name  }}</td>
                    <td><b>Address</b></td><td><b>:</b></td><td>{{  $customer->address  }}</td>
                </tr>
                <tr>
                    <td><b>Status</b></td><td><b>:</b></td><td>{!! STATUS_LABEL[$customer->status] !!}</td>
                    <td><b>Created By</b></td><td><b>:</b></td><td>{{  $customer->created_by  }}</td>
                </tr>
                <tr>
                    <td><b>Modified By</b></td><td><b>:</b></td><td>{{  $customer->modified_by  }}</td>
                    <td><b>Create Date</b></td><td><b>:</b></td><td>{{  $customer->created_at ? date(config('settings.date_format'),strtotime($customer->created_at)) : ''  }}</td>
                </tr>
                <tr>
                    <td><b>Modified Date</b></td><td><b>:</b></td>
                    <td>
                        @if ($customer->modified_by)
                        {{  $customer->updated_at ? date(config('settings.date_format'),strtotime($customer->updated_at)) : ''  }}
                        @endif
                    </td>
                </tr>
            </table>
        </div>
        <div class="col-md-3 text-center">
            @if($customer->avatar)
                <img src='{{ ASM_BASE_PATH."storage/".CUSTOMER_AVATAR_PATH.$customer->avatar }}' alt='{{ $customer->name }}' style='width:150px;'/>
            @else
                <img src='images/male.svg' alt='Default Image' style='width:150px;'/>
            @endif
        </div>
    </div>
</div>
