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
                    <form id="credit-voucher-form" method="post">
                        @csrf
                        <div class="row">
                                <div class="form-group col-md-3 required">
                                    <label for="voucher_no">Voucher No</label>
                                    <input type="text" class="form-control" name="voucher_no" id="voucher_no" value="{{ $voucher_no }}" readonly />
                                </div>
                                <div class="form-group col-md-3 required">
                                    <label for="voucher_date">Date</label>
                                    <input type="text" class="form-control date" name="voucher_date" id="voucher_date" value="{{ date('Y-m-d') }}" readonly />
                                </div>

                                <x-form.selectbox labelName="Receive From" name="debit_account_id" required="required"  col="col-md-3" class="selectpicker">
                                    {!! $coas !!}
                                </x-form.selectbox>
                                
                                <div class="col-md-12">
                                    <table class="table table-bordered" id="debit-voucher-table">
                                        <thead class="bg-primary">
                                            <th width="60%">Account Name</th>
                                            <th width="30%" class="text-right">Amount</th>
                                            <th width="10%"></th>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <select name="credit_account[1][id]" id="credit_account_1_id" class="form-control selectpicker">
                                                        <option value="">Select Please</option>
                                                        {!! $coas !!}
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control text-right amount" onkeyup="calculate_total()" name="credit_account[1][amount]" id="credit_account_1_amount" placeholder="0.00">
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td class="text-right">Total</td>
                                                <td><input type="text" class="form-control text-right bg-primary text-white" name="grand_total" id="grand_total" placeholder="0.00" readonly></td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-primary btn-sm" id="add_more_account"
                                                    data-toggle="tooltip" data-placement="top" data-original-title="Add More"><i class="fas fa-plus-square"></i></button>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <x-form.textarea labelName="Remarks" name="remarks" col="col-md-12"/>
                                <div class="form-group col-md-12 pt-5 text-center">
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
var count = 1;
function add_more_account_field(row){
    html = ` <tr>
                <td>
                    <select name="credit_account[`+row+`][id]" id="credit_account_`+row+`_id" class="form-control selectpicker">
                        <option value="">Select Please</option>
                        {!! $coas !!}
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control text-right amount" onkeyup="calculate_total()" name="credit_account[`+row+`][amount]" id="credit_account_`+row+`_amount" placeholder="0.00">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm remove" data-toggle="tooltip" 
                        data-placement="top" data-original-title="Remove">
                        <i class="fas fa-minus-square"></i>
                    </button>
                </td>
            </tr>`;
    $('#debit-voucher-table tbody').append(html);
    $('.selectpicker').selectpicker('refresh');
}

$(document).on('click','#add_more_account',function(){
    count++;
    add_more_account_field(count);
});
$(document).on('click','.remove',function(){
    count--;
    $(this).closest('tr').remove();
    calculate_total();
});

function calculate_total()
{
    var grand_total = 0;
    $('.amount').each(function() {
        if($(this).val() == '' || isNaN($(this).val())){
            grand_total += 0;
        }else{
            grand_total += parseFloat($(this).val());
        }
    });
    $('input[name="grand_total"]').val(parseFloat(grand_total).toFixed(2));
}

function store_data(){
    let form = document.getElementById('credit-voucher-form');
    let formData = new FormData(form);
    let url = "{{url('credit-voucher/store')}}";
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
            $('#credit-voucher-form').find('.is-invalid').removeClass('is-invalid');
            $('#credit-voucher-form').find('.error').remove();
            if (data.status == false) {
                $.each(data.errors, function (key, value) {
                    var key = key.split('.').join('_');
                    $('#credit-voucher-form input#' + key).addClass('is-invalid');
                    $('#credit-voucher-form textarea#' + key).addClass('is-invalid');
                    $('#credit-voucher-form select#' + key).parent().addClass('is-invalid');
                    $('#credit-voucher-form #' + key).parent().append(
                        '<small class="error text-danger">' + value + '</small>');
                });
            } else {
                notification(data.status, data.message);
                if (data.status == 'success') {
                    window.location.replace("{{ url('credit-voucher') }}");
                    
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