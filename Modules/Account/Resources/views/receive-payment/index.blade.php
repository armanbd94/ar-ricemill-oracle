@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
@endpush

@section('content')
<div class="d-flex flex-column-fluid">
    <div class="container-fluid">
        <!--begin::Notice-->
        <div class="card card-custom gutter-b">
            <div class="card-header flex-wrap py-5">
                <div class="card-title">
                    <h3 class="card-label"><i class="{{ $page_icon }} text-primary"></i> {{ $sub_title }}</h3>
                </div>
                
            </div>

        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom">
            <div class="card-body">
                <!--begin: Datatable-->
                <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <form id="customer-receive-form" method="post">
                        @csrf
                        <div class="row">
                            <x-form.selectbox labelName="Warehouse" name="warehouse_id" col="col-md-4" required="required" class="selectpicker">
                                @if (!$warehouses->isEmpty())
                                @foreach ($warehouses as $id => $name)
                                    <option value="{{ $id }}" data-name="{{ $name }}">{{ $name }}</option>
                                @endforeach
                                @endif
                            </x-form.selectbox>
                            <div class="form-group col-md-4 required">
                                <label for="voucher_no">Voucher No</label>
                                <input type="text" class="form-control" name="voucher_no" id="voucher_no" value="{{ $voucher_no }}" readonly />
                            </div>
                            <div class="form-group col-md-4 required">
                                <label for="voucher_date">Date</label>
                                <input type="text" class="form-control date" name="voucher_date" id="voucher_date" value="{{ date('Y-m-d') }}" readonly />
                            </div>
                            
                            <x-form.selectbox labelName="District" name="district_id" col="col-md-4" class="selectpicker"
                                onchange="getUpazilaList(this.value,2)">
                                @if (!$districts->isEmpty())
                                @foreach ($districts as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                                @endif
                            </x-form.selectbox>

                            <x-form.selectbox labelName="Upazila" name="upazila_id" col="col-md-4" class="selectpicker" onchange="getRouteList(this.value,2)"/>
    
                            <x-form.selectbox labelName="Route" name="route_id" col="col-md-4" class="selectpicker" onchange="getAreaList(this.value,2)"/>
    
                            <x-form.selectbox labelName="Area" name="area_id" col="col-md-4" class="selectpicker" onchange="customer_list(2)"/>

                            <x-form.selectbox labelName="Customer" name="customer_id" onchange="dueAmount(this.value)" required="required" col="col-md-4" class="selectpicker" />

                            <div class="form-group col-md-4">
                                <label for="due_amount">Due Amount</label>
                                <input type="text" class="form-control"  id="due_amount" readonly />
                            </div>
                             <x-form.selectbox labelName="Payment Type" name="payment_type" required="required"  col="col-md-4" class="selectpicker">
                                @foreach (SALE_PAYMENT_METHOD as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </x-form.selectbox>
                            
                            <x-form.selectbox labelName="Account" name="account_id" required="required"  col="col-md-4" class="selectpicker"/>
                            <x-form.textbox labelName="Amount" name="amount" required="required" col="col-md-4" placeholder="0.00"/>
                            <x-form.textarea labelName="Remarks" name="remarks" col="col-md-12"/>
                            <div class="form-group col-md-12 pt-5">
                                <button type="button" class="btn btn-danger btn-sm mr-3"><i class="fas fa-sync-alt"></i> Reset</button>
                                <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn" onclick="store_data()"><i class="fas fa-save"></i> Save</button>
                            </div>
                        </div>
                    </form>
                </div>
                <!--end: Datatable-->
            </div>
        </div>
        <!--end::Card-->
    </div>
</div>

@endsection

@push('scripts')
<script src="js/moment.js"></script>
<script src="js/bootstrap-datetimepicker.min.js"></script>
<script>
$('.date').datetimepicker({format: 'YYYY-MM-DD',ignoreReadonly: true});
$(document).on('change', '#payment_type', function () {
    $.ajax({
        url: "{{route('account.list')}}",
        type: "POST",
        data: { payment_method: $('#payment_type option:selected').val(),_token: _token},
        success: function (data) {
            $('#customer-receive-form #account_id').html('');
            $('#customer-receive-form #account_id').html(data);
            $('#customer-receive-form #account_id.selectpicker').selectpicker('refresh');
        },
        error: function (xhr, ajaxOption, thrownError) {
            console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
        }
    });
});
function dueAmount(customer_id)
{
    $.ajax({
        url: "{{url('customer/previous-balance')}}/"+customer_id,
        type: "GET",
        dataType: "JSON",
        success: function (data) {
            data ? $('#due_amount').val(parseFloat(data).toFixed(2)) : $('#due_amount').val('0.00');
        },
        error: function (xhr, ajaxOption, thrownError) {
            console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
        }
    });
}
function customer_list()
{
    let district_id = document.getElementById('district_id').value;
    let upazila_id = document.getElementById('upazila_id').value;
    let route_id = document.getElementById('route_id').value;
    let area_id = document.getElementById('area_id').value;
    $.ajax({
        url:"{{ url('customer-list') }}",
        type:"POST",
        data:{district_id:district_id,upazila_id:upazila_id,route_id:route_id,area_id:area_id,_token:_token},
        dataType:"JSON",
        success:function(data){
            html = `<option value="">Select Please</option>`;
            $.each(data, function(key, value) {
                html += `<option value="${value.id}">${value.name} - ${value.mobile} (${value.shop_name})</option>`;
            });

            $('#customer-receive-form #customer_id').empty().append(html);
            $('#customer-receive-form #customer_id.selectpicker').selectpicker('refresh');
            
        },
    });
}
function getUpazilaList(district_id){
    $.ajax({
        url:"{{ url('district-id-wise-upazila-list') }}/"+district_id,
        type:"GET",
        dataType:"JSON",
        success:function(data){
            html = `<option value="">Select Please</option>`;
            $.each(data, function(key, value) {
                html += '<option value="'+ key +'">'+ value +'</option>';
            });

            $('#customer-receive-form #upazila_id').empty().append(html);
            $('.selectpicker').selectpicker('refresh');
      
        },
    });
}
function getRouteList(upazila_id){
    $.ajax({
        url:"{{ url('upazila-id-wise-route-list') }}/"+upazila_id,
        type:"GET",
        dataType:"JSON",
        success:function(data){
            html = `<option value="">Select Please</option>`;
            $.each(data, function(key, value) {
                html += '<option value="'+ key +'">'+ value +'</option>';
            });

            $('#customer-receive-form #route_id').empty().append(html);
            $('.selectpicker').selectpicker('refresh');
            
        },
    });
}

function getAreaList(route_id){
    $.ajax({
        url:"{{ url('route-id-wise-area-list') }}/"+route_id,
        type:"GET",
        dataType:"JSON",
        success:function(data){
            html = `<option value="">Select Please</option>`;
            $.each(data, function(key, value) {
                html += '<option value="'+ key +'">'+ value +'</option>';
            });

            $('#customer-receive-form #area_id').empty().append(html);
            $('.selectpicker').selectpicker('refresh');
      
        },
    });
}
function store_data(){
    let form = document.getElementById('customer-receive-form');
    let formData = new FormData(form);
    let url = "{{url('customer-receive')}}";
    $.ajax({
        url: url,
        type: "POST",
        data: formData,
        dataType: "JSON",
        contentType: false,
        processData: false,
        cache: false,
        beforeSend: function(){
            $('#save-btn').addClass('spinner spinner-white spinner-right');
        },
        complete: function(){
            $('#save-btn').removeClass('spinner spinner-white spinner-right');
        },
        success: function (data) {
            $('#customer-receive-form').find('.is-invalid').removeClass('is-invalid');
            $('#customer-receive-form').find('.error').remove();
            if (data.status == false) {
                $.each(data.errors, function (key, value) {
                    var key = key.split('.').join('_');
                    $('#customer-receive-form input#' + key).addClass('is-invalid');
                    $('#customer-receive-form textarea#' + key).addClass('is-invalid');
                    $('#customer-receive-form select#' + key).parent().addClass('is-invalid');
                    $('#customer-receive-form #' + key).parent().append(
                        '<small class="error text-danger">' + value + '</small>');
                });
            } else {
                notification(data.status, data.message);
                if (data.status == 'success' && data.supplier_transaction != '') {

                    window.location.replace("{{ url('customer-receive') }}/"+data.customer_transaction+'/'+$('#payment_type option:selected').val());
                    
                }
            }

        },
        error: function (xhr, ajaxOption, thrownError) {
            console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
        }
    });
}

</script>
@endpush