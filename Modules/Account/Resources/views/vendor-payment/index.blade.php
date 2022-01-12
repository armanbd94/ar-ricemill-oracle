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
                    <form id="vendor-payment-form" method="post">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group col-md-6 required">
                                    <label for="voucher_no">Memo No</label>
                                    <input type="text" class="form-control" name="voucher_no" id="voucher_no" value="{{ $voucher_no }}"  />
                                </div>
                                <div class="form-group col-md-6 required">
                                    <label for="voucher_date">Date</label>
                                    <input type="text" class="form-control date" name="voucher_date" id="voucher_date" value="{{ date('Y-m-d') }}" readonly />
                                </div>
                                <x-form.selectbox labelName="Vendor" name="vendor_id"  onchange="dueAmount(this.value)" required="required"  col="col-md-6" class="selectpicker">
                                    @if (!$vendors->isEmpty())
                                        @foreach ($vendors as $vendor)
                                            <option value="{{ $vendor->id }}">{{ $vendor->trade_name.' - '.$vendor->name }}</option>
                                        @endforeach
                                    @endif
                                </x-form.selectbox>
                                <div class="form-group col-md-6">
                                    <label for="due_amount">Due Amount</label>
                                    <input type="text" class="form-control"  id="due_amount" readonly />
                                </div>
                                <x-form.selectbox labelName="Payment Type" name="payment_type" required="required"  col="col-md-6" class="selectpicker">
                                    @foreach (PAYMENT_METHOD as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </x-form.selectbox>
                                <x-form.selectbox labelName="Account" name="account_id" required="required"  col="col-md-6" class="selectpicker"/>
                                <x-form.textbox labelName="Amount" name="amount" required="required" col="col-md-6" placeholder="0.00"/>
                                <x-form.textarea labelName="Remarks" name="remarks" col="col-md-6"/>
                                <div class="form-group col-md-6 pt-5">
                                    <button type="button" class="btn btn-danger btn-sm mr-3"><i class="fas fa-sync-alt"></i> Reset</button>
                                    <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn" onclick="store_data()"><i class="fas fa-save"></i> Save</button>
                                </div>
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
            $('#vendor-payment-form #account_id').html('');
            $('#vendor-payment-form #account_id').html(data);
            $('#vendor-payment-form #account_id.selectpicker').selectpicker('refresh');
        },
        error: function (xhr, ajaxOption, thrownError) {
            console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
        }
    });
});
function dueAmount(vendor_id)
{
    $.ajax({
        url: "{{url('vendor/due-amount')}}/"+vendor_id,
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
function store_data(){
    let form = document.getElementById('vendor-payment-form');
    let formData = new FormData(form);
    let url = "{{url('vendor-payment')}}";
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
            $('#vendor-payment-form').find('.is-invalid').removeClass('is-invalid');
            $('#vendor-payment-form').find('.error').remove();
            if (data.status == false) {
                $.each(data.errors, function (key, value) {
                    var key = key.split('.').join('_');
                    $('#vendor-payment-form input#' + key).addClass('is-invalid');
                    $('#vendor-payment-form textarea#' + key).addClass('is-invalid');
                    $('#vendor-payment-form select#' + key).parent().addClass('is-invalid');
                    $('#vendor-payment-form #' + key).parent().append(
                        '<small class="error text-danger">' + value + '</small>');
                });
            } else {
                notification(data.status, data.message);
                if (data.status == 'success' && data.vendor_transaction != '') {

                    window.location.replace("{{ url('vendor-payment') }}/"+data.vendor_transaction+'/'+$('#payment_type option:selected').val());
                    
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