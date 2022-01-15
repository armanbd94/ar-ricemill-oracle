@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
@endpush

@section('content')
<div class="d-flex flex-column-fluid">
    <div class="container-fluid">
       <!--begin::Notice-->
       <div class="card card-custom custom-card">
            <div class="card-header flex-wrap p-0">
                <div class="card-toolbar m-0">
                    <!--begin::Button-->
                    <a href="{{ route('cash.adjustment') }}" class="btn btn-warning btn-sm font-weight-bolder custom-btn">
                        <i class="fas fa-arrow-left"></i> Back</a>
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom">
            <div class="card-body">
                <!--begin: Datatable-->
                <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <form id="cash-adjustment-form" method="post">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                
                                <div class="form-group col-md-6 required">
                                    <label for="voucher_no">Voucher No</label>
                                    <input type="text" class="form-control" name="voucher_no" id="voucher_no" value="{{ $voucher_data->voucher_no }}"  />
                                    <input type="hidden" class="form-control" name="update_id" id="update_id" value="{{ $voucher_data->id }}"  />
                                </div>
                                <div class="form-group col-md-6 required">
                                    <label for="voucher_date">Date</label>
                                    <input type="text" class="form-control date" name="voucher_date" value="{{ $voucher_data->voucher_date }}" id="voucher_date" value="{{ date('Y-m-d') }}" readonly />
                                </div>
                                <x-form.selectbox labelName="Account" name="account_id" required="required"  col="col-md-6" class="selectpicker">
                                    {!! $account_list !!}
                                </x-form.selectbox>
                                <x-form.selectbox labelName="Advance Type" name="type" required="required"  col="col-md-6" class="selectpicker">
                                    <option value="debit" {{ !empty($voucher_data->debit) ? 'selected' : '' }}>Debit</option>
                                    <option value="credit" {{ !empty($voucher_data->credit) ? 'selected' : '' }}>Credit</option>
                                </x-form.selectbox>
                                <x-form.textbox labelName="Amount" name="amount" value="{{ $voucher_data->debit ? $voucher_data->debit : $voucher_data->credit  }}" required="required" col="col-md-6" placeholder="0.00"/>
                                <x-form.textarea labelName="Remarks" name="remarks" value="{{ $voucher_data->description }}" col="col-md-6"/>
                                <div class="form-group col-md-6 pt-5">
                                    <button type="button" class="btn btn-danger btn-sm mr-3"><i class="fas fa-sync-alt"></i> Reset</button>
                                    <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn" onclick="store_data()"><i class="fas fa-save"></i> Update</button>
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
function store_data(){
    let form = document.getElementById('cash-adjustment-form');
    let formData = new FormData(form);
    let url = "{{url('cash-adjustment/update')}}";
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
            $('#cash-adjustment-form').find('.is-invalid').removeClass('is-invalid');
            $('#cash-adjustment-form').find('.error').remove();
            if (data.status == false) {
                $.each(data.errors, function (key, value) {
                    var key = key.split('.').join('_');
                    $('#cash-adjustment-form input#' + key).addClass('is-invalid');
                    $('#cash-adjustment-form textarea#' + key).addClass('is-invalid');
                    $('#cash-adjustment-form select#' + key).parent().addClass('is-invalid');
                    $('#cash-adjustment-form #' + key).parent().append(
                        '<small class="error text-danger">' + value + '</small>');
                });
            } else {
                notification(data.status, data.message);
                if (data.status == 'success') {
                    window.location.replace("{{ url('cash-adjustment') }}");
                    
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