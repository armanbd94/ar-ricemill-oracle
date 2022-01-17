@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link href="plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
<link href="css/daterangepicker.min.css" rel="stylesheet" type="text/css" />
@endpush

@section('content')
<div class="d-flex flex-column-fluid">
    <div class="container-fluid">
        <!--begin::Notice-->
        <div class="card card-custom custom-card">
            <div class="card-header flex-wrap p-0">
                <div class="card-toolbar m-0">
                    <!--begin::Button-->
                    @if (permission('customer-advance-add'))
                    <a href="javascript:void(0);" onclick="showAdvanceFormModal('Add New Customer Advance','Save')" class="btn btn-primary btn-sm font-weight-bolder custom-btn"> 
                        <i class="fas fa-plus-circle"></i> Add New</a>
                    @endif
                    <!--end::Button-->
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom">
            <div class="card-header flex-wrap py-5">
                <form method="POST" id="form-filter" class="col-md-12 px-0">
                    <div class="row">
                        <div class="form-group col-md-3">
                            <label for="name">Choose Your Date</label>
                            <div class="input-group">
                                <input type="text" class="form-control daterangepicker-filed">
                                <input type="hidden" id="start_date" name="start_date" >
                                <input type="hidden" id="end_date" name="end_date" >
                            </div>
                        </div>

                        <x-form.selectbox labelName="Customer" name="customer_id" col="col-md-3" class="selectpicker">
                            @if (!$customers->isEmpty())
                            @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->trade_name.' ('.$customer->name.')' }}</option>
                            @endforeach
                            @endif
                        </x-form.selectbox>
                        

                        <x-form.selectbox labelName="Advance Type" name="type" col="col-md-3" class="selectpicker">
                            <option value="debit">Payment</option>
                            <option value="credit">Receive</option>
                        </x-form.selectbox>
                        <div class="col-md-3">
                            <div>    
                                <div>    
                                    <button id="btn-reset" class="btn btn-danger btn-sm btn-elevate btn-icon float-right custom-btn" type="button"
                                    data-toggle="tooltip" data-theme="dark" title="Reset">
                                    <i class="fas fa-undo-alt"></i></button>
    
                                    <button id="btn-filter" class="btn btn-primary btn-sm btn-elevate btn-icon mr-2 float-right custom-btn" type="button"
                                    data-toggle="tooltip" data-theme="dark" title="Search">
                                    <i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-body">
                <!--begin: Datatable-->
                <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <div class="row">
                        <div class="col-sm-12">
                            <table id="dataTable" class="table table-bordered table-hover">
                                <thead class="bg-primary">
                                    <tr>
                                        <th>Sl</th>
                                        <th>Name</th>
                                        <th>Trade Name</th>
                                        <th>Mobile No.</th>
                                        <th>Advance Type</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Payment Method</th>
                                        <th>Account Name</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!--end: Datatable-->
            </div>
        </div>
        <!--end::Card-->
    </div>
</div>
@include('customer::advance.modal')
@endsection

@push('scripts')
<script src="plugins/custom/datatables/datatables.bundle.js" type="text/javascript"></script>
<script src="js/moment.js"></script>
<script src="js/knockout-3.4.2.js"></script>
<script src="js/daterangepicker.min.js"></script>
<script>
$('.daterangepicker-filed').daterangepicker({
    callback: function(startDate, endDate, period){
        var start_date = startDate.format('YYYY-MM-DD');
        var end_date   = endDate.format('YYYY-MM-DD');
        var title = start_date + ' To ' + end_date;
        $(this).val(title);
        $('input[name="start_date"]').val(start_date);
        $('input[name="end_date"]').val(end_date);
    }
});
var table;
$(document).ready(function(){

    table = $('#dataTable').DataTable({
        "processing": true, //Feature control the processing indicator
        "serverSide": true, //Feature control DataTable server side processing mode
        "order": [], //Initial no order
        "responsive": true, //Make table responsive in mobile device
        "bInfo": true, //TO show the total number of data
        "bFilter": false, //For datatable default search box show/hide
        "lengthMenu": [
            [5, 10, 15, 25, 50, 100, 1000, 10000, -1],
            [5, 10, 15, 25, 50, 100, 1000, 10000, "All"]
        ],
        "pageLength": 25, //number of data show per page
        "language": { 
            processing: `<i class="fas fa-spinner fa-spin fa-3x fa-fw text-primary"></i> `,
            emptyTable: '<strong class="text-danger">No Data Found</strong>',
            infoEmpty: '',
            zeroRecords: '<strong class="text-danger">No Data Found</strong>'
        },
        "ajax": {
            "url": "{{route('customer.advance.datatable.data')}}",
            "type": "POST",
            "data": function (data) {
                data.customer_id = $("#form-filter #customer_id").val();
                data.type        = $("#form-filter #type").val();
                data.start_date  = $("#form-filter #start_date").val();
                data.end_date    = $("#form-filter #end_date").val();
                data._token      = _token;
            }
        },
        "columnDefs": [
            {
                "targets": [9],
                "className": "text-center",
                "orderable":false
            },
            {
                "targets": [0,3,4,6,7,8],
                "className": "text-center"
            },
            {
                "targets": [5],
                "className": "text-right"
            },
        ],
        "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6' <'float-right'B>>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'<'float-right'p>>>",

        "buttons": [
            {
                'extend':'colvis','className':'btn btn-secondary btn-sm text-white custom-btn','text':'Column','columns': ':gt(0)'
            },
            {
                "extend": 'print',
                'text':'Print',
                'className':'btn btn-secondary btn-sm text-white custom-btn',
                "title": "{{ $page_title }} List",
                "orientation": "portrait", //portrait
                "pageSize": "A4", //A3,A5,A6,legal,letter
                "exportOptions": {
                    columns: function (index, data, node) {
                        return table.column(index).visible();
                    }
                },
                customize: function (win) {
                    $(win.document.body).addClass('bg-white');
                    $(win.document.body).find('table thead').css({'background':'#034d97'});
                    $(win.document.body).find('table tfoot tr').css({'background-color':'#034d97'});
                    $(win.document.body).find('h1').css('text-align', 'center');
                    $(win.document.body).find('h1').css('font-size', '15px');
                    $(win.document.body).find('table').css( 'font-size', 'inherit' );
                },
            },
            {
                "extend": 'csv',
                'text':'CSV',
                'className':'btn btn-secondary btn-sm text-white custom-btn',
                "title": "{{ $page_title }} List",
                "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                "exportOptions": {
                    columns: function (index, data, node) {
                        return table.column(index).visible();
                    }
                }
            },
            {
                "extend": 'excel',
                'text':'Excel',
                'className':'btn btn-secondary btn-sm text-white custom-btn',
                "title": "{{ $page_title }} List",
                "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                "exportOptions": {
                    columns: function (index, data, node) {
                        return table.column(index).visible();
                    }
                }
            },
            {
                "extend": 'pdf',
                'text':'PDF',
                'className':'btn btn-secondary btn-sm text-white custom-btn',
                "title": "{{ $page_title }} List",
                "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                "orientation": "portrait", //portrait
                "pageSize": "A4", //A3,A5,A6,legal,letter
                "exportOptions": {
                    columns: function (index, data, node) {
                        return table.column(index).visible();
                    }
                },
                customize: function(doc) {
                doc.defaultStyle.fontSize = 7; //<-- set fontsize to 16 instead of 10 
                doc.styles.tableHeader.fontSize = 7;
                doc.pageMargins = [5,5,5,5];
            }  
            },
        ],
    });

    $('#btn-filter').click(function () {
        table.ajax.reload();
    });

    $('#btn-reset').click(function () {
        $('#form-filter')[0].reset();
        $('#form-filter .selectpicker').selectpicker('refresh');
        $('#form-filter #start_date').val('');
        $('#form-filter #end_date').val('');
        table.ajax.reload();
    });

    $(document).on('click', '#save-btn', function () {
        var customer_coa_id = $('#store_or_update_form #customer_coa_id option:selected').val();
        var type           = $('#store_or_update_form #type option:selected').val();
        var amount         = $('#store_or_update_form #amount').val();
        var payment_method = $('#store_or_update_form #payment_method option:selected').val();
        var account_id     = $('#store_or_update_form #account_id option:selected').val();
        var description    = $('#store_or_update_form #description').val();

        let url = "{{route('customer.advance.store.or.update')}}";
        let id = $('#update_id').val();
        let method;
        if (id) {
            method = 'update';
        } else {
            method = 'add';
        }

        $.ajax({
            url: url,
            type: "POST",
            data: {id:id,customer_coa_id:customer_coa_id,type:type,amount:amount,
                payment_method:payment_method,account_id:account_id,description:description,_token:_token},
            dataType: "JSON",
            beforeSend: function(){
                $('#save-btn').addClass('spinner spinner-white spinner-right');
            },
            complete: function(){
                $('#save-btn').removeClass('spinner spinner-white spinner-right');
            },
            success: function (data) {
                $('#store_or_update_form').find('.is-invalid').removeClass('is-invalid');
                $('#store_or_update_form').find('.error').remove();
                if (data.status == false) {
                    $.each(data.errors, function (key, value) {
                        $('#store_or_update_form input#' + key).addClass('is-invalid');
                        $('#store_or_update_form textarea#' + key).addClass('is-invalid');
                        $('#store_or_update_form select#' + key).parent().addClass('is-invalid');
                        $('#store_or_update_form #' + key).parent().append(
                        '<small class="error text-danger">' + value + '</small>');
                    });
                } else {
                    notification(data.status, data.message);
                    if (data.status == 'success') {
                        if (method == 'update') {
                            table.ajax.reload(null, false);
                        } else {
                            table.ajax.reload();
                        }
                        $('#store_or_update_modal').modal('hide');
                    }
                }
            },
            error: function (xhr, ajaxOption, thrownError) {
                console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
            }
        });
        
    });
    
    $(document).on('click', '.edit_data', function () {
        let id = $(this).data('id');
        $('#store_or_update_form')[0].reset();
        $('#store_or_update_form').find('.is-invalid').removeClass('is-invalid');
        $('#store_or_update_form').find('.error').remove();
        if (id) {
            $.ajax({
                url: "{{route('customer.advance.edit')}}",
                type: "POST",
                data: { id: id,_token: _token},
                dataType: "JSON",
                success: function (data) {
                    if(data.status == 'error'){
                        notification(data.status,data.message)
                    }else{
                        $('#store_or_update_form #update_id').val(data.id);
                        
                        $('#store_or_update_form #type').val(data.type);
                        $('#store_or_update_form #amount').val(data.amount);
                        $('#store_or_update_form #payment_method').val(data.payment_method);
                        $('#store_or_update_form #description').val(data.description);
                        $('#store_or_update_form #customer_coa_id').val(data.customer_coa_id);
                        account_list(data.payment_method,data.account_id);
                        $('#store_or_update_form select#customer').each(function(){
                            $('#store_or_update_form select#customer option').each(function() {
                                if(!this.selected) {
                                    $(this).attr('disabled', true);
                                }
                            });
                        });
                        $('#store_or_update_form .selectpicker').selectpicker('refresh');
                        $('#store_or_update_modal').modal({
                            keyboard: false,
                            backdrop: 'static',
                        });
                        $('#store_or_update_modal .modal-title').html(
                            '<i class="fas fa-edit text-white"></i> <span>Edit ' + data.name + '</span>');
                        $('#store_or_update_modal #save-btn').text('Update');
                    }
                },
                error: function (xhr, ajaxOption, thrownError) {
                    console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                }
            });
        }
    });

    $(document).on('click', '.delete_data', function () {
        let id    = $(this).data('id');
        let name  = $(this).data('name');
        let row   = table.row($(this).parent('tr'));
        let url   = "{{ route('customer.advance.delete') }}";
        delete_data(id, url, table, row, name);
    });

    $(document).on('change', '#payment_method', function () {
        account_list($('#payment_method option:selected').val());
    });

});
function account_list(payment_method,account_id='')
{
    $.ajax({
        url: "{{route('account.list')}}",
        type: "POST",
        data: { payment_method: payment_method,_token: _token},
        success: function (data) {
            $('#store_or_update_form #account_id').html('');
            $('#store_or_update_form #account_id').html(data);
            $('#store_or_update_form #account_id.selectpicker').selectpicker('refresh');
            if(account_id)
            {
                $('#store_or_update_form #account_id').val(account_id);
                $('#store_or_update_form #account_id.selectpicker').selectpicker('refresh');
            }
        },
        error: function (xhr, ajaxOption, thrownError) {
            console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
        }
    });
}


function showAdvanceFormModal(modal_title, btn_text) {
    $('#store_or_update_form')[0].reset();
    $('#store_or_update_form #update_id').val('');
    $('#store_or_update_form').find('.is-invalid').removeClass('is-invalid');
    $('#store_or_update_form').find('.error').remove();
    $('#store_or_update_form #account_id').empty();
    $('#store_or_update_form .selectpicker').selectpicker('refresh');
    $('#store_or_update_modal').modal({
        keyboard: false,
        backdrop: 'static',
    });
    $('#store_or_update_modal .modal-title').html('<i class="fas fa-plus-square text-white"></i> '+modal_title);
    $('#store_or_update_modal #save-btn').text(btn_text);
}
</script>
@endpush
