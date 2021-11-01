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
                    <form id="journal-voucher-form" method="post">
                        @csrf
                        <div class="row">
                                <div class="form-group col-md-4 required">
                                    <label for="voucher_no">Voucher No</label>
                                    <input type="text" class="form-control" name="voucher_no" id="voucher_no" value="{{ $voucher_no }}" readonly />
                                </div>
                                <div class="form-group col-md-4 required">
                                    <label for="voucher_date">Date</label>
                                    <input type="text" class="form-control date" name="voucher_date" id="voucher_date" value="{{ date('Y-m-d') }}" readonly />
                                </div>

                                <x-form.selectbox labelName="Warehouse" name="warehouse_id" required="required"  col="col-md-4" class="selectpicker">
                                    @if (!$warehouses->isEmpty())
                                    @foreach ($warehouses as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                    @endif
                                </x-form.selectbox>
                                
                                <div class="col-md-12">
                                    <table class="table table-bordered" id="debit-voucher-table">
                                        <thead class="bg-primary">
                                            <th width="40%">Account Name</th>
                                            <th width="25%" class="text-right">Debit</th>
                                            <th width="25%" class="text-right">Credit</th>
                                            <th width="10%"></th>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <select name="journal_account[1][id]" id="journal_account_1_id" class="form-control selectpicker">
                                                        <option value="">Select Please</option>
                                                        @if (!$transactional_accounts->isEmpty())
                                                        @foreach ($transactional_accounts as $coa)
                                                            <option value="{{ $coa->id }}">{{ $coa->name }} @if($coa->supplier_id) {{ '(Supplier)' }}  @endif @if($coa->customer_id) {{ '(Customer)' }}  @endif</option>
                                                        @endforeach
                                                        @endif
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control text-right debit_amount" onkeyup="calculate_total(1)" name="journal_account[1][debit_amount]" id="journal_account_1_debit_amount" placeholder="0.00">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control text-right credit_amount" onkeyup="calculate_total(2)" name="journal_account[1][credit_amount]" id="journal_account_1_credit_amount" placeholder="0.00">
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td class="text-right">Total</td>
                                                <td><input type="text" class="form-control text-right bg-primary text-white" name="debit_grand_total" id="debit_grand_total" placeholder="0.00" readonly></td>
                                                <td><input type="text" class="form-control text-right bg-primary text-white" name="credit_grand_total" id="credit_grand_total" placeholder="0.00" readonly></td>
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
                    <select name="journal_account[`+row+`][id]" id="journal_account_`+row+`_id" class="form-control selectpicker">
                        <option value="">Select Please</option>
                        @if (!$transactional_accounts->isEmpty())
                        @foreach ($transactional_accounts as $coa)
                            <option value="{{ $coa->id }}">{{ $coa->name }} @if($coa->supplier_id) {{ '(Supplier)' }}  @endif @if($coa->customer_id) {{ '(Customer)' }}  @endif</option>
                        @endforeach
                        @endif
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control text-right debit_amount" onkeyup="calculate_total(1)" name="journal_account[`+row+`][debit_amount]" id="journal_account_`+row+`_debit_amount" placeholder="0.00">
                </td>
                <td>
                    <input type="text" class="form-control text-right credit_amount" onkeyup="calculate_total(2)" name="journal_account[`+row+`][credit_amount]" id="journal_account_`+row+`_credit_amount" placeholder="0.00">
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

function calculate_total(type)
{
    if(type == 1)
    {
        var debit_grand_total = 0;
        $('.debit_amount').each(function() {
            if($(this).val() == '' || isNaN($(this).val())){
                debit_grand_total += 0;
            }else{
                debit_grand_total += parseFloat($(this).val());
            }
        });
        $('input[name="debit_grand_total"]').val(parseFloat(debit_grand_total).toFixed(2));
    }else{
        var credit_grand_total = 0;
        $('.credit_amount').each(function() {
            if($(this).val() == '' || isNaN($(this).val())){
                credit_grand_total += 0;
            }else{
                credit_grand_total += parseFloat($(this).val());
            }
        });
        $('input[name="credit_grand_total"]').val(parseFloat(credit_grand_total).toFixed(2));
    }
}

function store_data(){
    let form = document.getElementById('journal-voucher-form');
    let formData = new FormData(form);
    let url = "{{url('journal-voucher/store')}}";
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
            $('#journal-voucher-form').find('.is-invalid').removeClass('is-invalid');
            $('#journal-voucher-form').find('.error').remove();
            if (data.status == false) {
                $.each(data.errors, function (key, value) {
                    var key = key.split('.').join('_');
                    $('#journal-voucher-form input#' + key).addClass('is-invalid');
                    $('#journal-voucher-form textarea#' + key).addClass('is-invalid');
                    $('#journal-voucher-form select#' + key).parent().addClass('is-invalid');
                    $('#journal-voucher-form #' + key).parent().append(
                        '<small class="error text-danger">' + value + '</small>');
                });
            } else {
                notification(data.status, data.message);
                if (data.status == 'success') {
                    window.location.replace("{{ url('journal-voucher') }}");
                    
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