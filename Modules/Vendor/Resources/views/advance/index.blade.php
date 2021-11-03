@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link href="plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
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
                <div class="card-toolbar">
                    <!--begin::Button-->
                    @if (permission('vendor-advance-add'))
                    <a href="javascript:void(0);" onclick="showAdvanceFormModal('Add New Vendor Advance','Save')" class="btn btn-primary btn-sm font-weight-bolder"> 
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
                        <x-form.selectbox labelName="Vendor Name" name="supplier_id" col="col-md-3" class="selectpicker">
                            @if (!$suppliers->isEmpty())
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" data-coaid="{{ $supplier->coa->id }}" data-name="{{ $supplier->name }}">{{ $supplier->name.' - '.$supplier->mobile }}</option>
                            @endforeach
                            @endif
                        </x-form.selectbox>
                        <x-form.selectbox labelName="Advance Type" name="type" col="col-md-3" class="selectpicker">
                            <option value="debit">Payment</option>
                            <option value="credit">Receive</option>
                        </x-form.selectbox>
                        <div class="col-md-6">
                            <div style="margin-top:28px;">       
                                <button id="btn-reset" class="btn btn-danger btn-sm btn-elevate btn-icon float-right" type="button"
                                data-toggle="tooltip" data-theme="dark" title="Reset">
                                <i class="fas fa-undo-alt"></i></button>

                                <button id="btn-filter" class="btn btn-primary btn-sm btn-elevate btn-icon mr-2 float-right" type="button"
                                data-toggle="tooltip" data-theme="dark" title="Search">
                                <i class="fas fa-search"></i></button>
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
                                        @if (permission('vendor-advance-bulk-delete'))
                                        <th>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="select_all" onchange="select_all()">
                                                <label class="custom-control-label" for="select_all"></label>
                                            </div>
                                        </th>
                                        @endif
                                        <th>Sl</th>
                                        <th>Name</th>
                                        <th>Advance Type</th>
                                        <th>Amount</th>
                                        <th>Status</th>
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
@include('supplier::advance.modal')
@include('supplier::advance.status-modal')
@endsection

@push('scripts')
<script src="plugins/custom/datatables/datatables.bundle.js" type="text/javascript"></script>
<script>
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
                "url": "{{route('vendor.advance.datatable.data')}}",
                "type": "POST",
                "data": function (data) {
                    data.supplier_id = $("#form-filter #supplier_id option:selected").val();
                    data.type        = $("#form-filter #type").val();
                    data._token      = _token;
                }
            },
            "columnDefs": [{

                    @if (permission('vendor-advance-bulk-delete'))
                    "targets": [0,8],
                    @else
                    "targets": [7],
                    @endif
                    "orderable": false,
                    "className": "text-center"
                },
                {
                    @if (permission('vendor-advance-bulk-delete'))
                    "targets": [1,3,5,6],
                    @else
                    "targets": [0,2,4,5],
                    @endif
                    "className": "text-center"
                },
                {
                    @if (permission('vendor-advance-bulk-delete'))
                    "targets": [4],
                    @else
                    "targets": [3],
                    @endif
                    "className": "text-right"
                },
            ],
            "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6' <'float-right'B>>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'<'float-right'p>>>",
    
            "buttons": [
                {
                    'extend':'colvis','className':'btn btn-secondary btn-sm text-white','text':'Column','columns': ':gt(0)'
                },
                {
                    "extend": 'print',
                    'text':'Print',
                    'className':'btn btn-secondary btn-sm text-white',
                    "title": "{{ $page_title }} List",
                    "orientation": "landscape", //portrait
                    "pageSize": "A4", //A3,A5,A6,legal,letter
                    "exportOptions": {
                        @if (permission('vendor-advance-bulk-delete'))
                        columns: ':visible:not(:eq(0),:eq(8))' 
                        @else
                        columns: ':visible:not(:eq(7))' 
                        @endif
                    },
                    customize: function (win) {
                        $(win.document.body).addClass('bg-white');
                    },
                },
                {
                    "extend": 'csv',
                    'text':'CSV',
                    'className':'btn btn-secondary btn-sm text-white',
                    "title": "{{ $page_title }} List",
                    "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                    "exportOptions": {
                        @if (permission('vendor-advance-bulk-delete'))
                        columns: ':visible:not(:eq(0),:eq(8))' 
                        @else
                        columns: ':visible:not(:eq(7))' 
                        @endif
                    }
                },
                {
                    "extend": 'excel',
                    'text':'Excel',
                    'className':'btn btn-secondary btn-sm text-white',
                    "title": "{{ $page_title }} List",
                    "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                    "exportOptions": {
                        @if (permission('vendor-advance-bulk-delete'))
                        columns: ':visible:not(:eq(0),:eq(8))' 
                        @else
                        columns: ':visible:not(:eq(7))' 
                        @endif
                    }
                },
                {
                    "extend": 'pdf',
                    'text':'PDF',
                    'className':'btn btn-secondary btn-sm text-white',
                    "title": "{{ $page_title }} List",
                    "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                    "orientation": "landscape", //portrait
                    "pageSize": "A4", //A3,A5,A6,legal,letter
                    "exportOptions": {
                        @if (permission('vendor-advance-bulk-delete'))
                        columns: ':visible:not(:eq(0),:eq(8))' 
                        @else
                        columns: ':visible:not(:eq(7))' 
                        @endif
                    },
                    customize: function(doc) {
                        doc.defaultStyle.fontSize = 7; //<-- set fontsize to 16 instead of 10 
                        doc.styles.tableHeader.fontSize = 7;
                        doc.pageMargins = [5,5,5,5];
                    }  
                },
                @if (permission('vendor-advance-bulk-delete'))
                {
                    'className':'btn btn-danger btn-sm delete_btn d-none text-white',
                    'text':'Delete',
                    action:function(e,dt,node,config){
                        multi_delete();
                    }
                }
                @endif
            ],
        });
    
        $('#btn-filter').click(function () {
            table.ajax.reload();
        });
    
        $('#btn-reset').click(function () {
            $('#form-filter')[0].reset();
            $('#form-filter .selectpicker').selectpicker('refresh');
            table.ajax.reload();
        });
    
        $(document).on('click', '#save-btn', function () {
            var supplier       = $('#store_or_update_form #supplier option:selected').val();
            var supplier_coaid = $('#store_or_update_form #supplier option:selected').data('coaid');
            var supplier_name  = $('#store_or_update_form #supplier option:selected').data('name');
            var type           = $('#store_or_update_form #type option:selected').val();
            var amount         = $('#store_or_update_form #amount').val();
            var payment_method = $('#store_or_update_form #payment_method option:selected').val();
            var account_id    = $('#store_or_update_form #account_id option:selected').val();
            var cheque_number = '';
            if(payment_method == 2){
                cheque_number = $('#store_or_update_form #cheque_number').val();
            }
            
            let url = "{{route('vendor.advance.store.or.update')}}";
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
                data: {id:id,supplier:supplier,supplier_coaid:supplier_coaid,supplier_name:supplier_name,type:type,amount:amount,
                    payment_method:payment_method,account_id:account_id,cheque_number:cheque_number,_token:_token},
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
                    url: "{{route('vendor.advance.edit')}}",
                    type: "POST",
                    data: { id: id,_token: _token},
                    dataType: "JSON",
                    success: function (data) {
                        
                        if(data.status == 'error'){
                            notification(data.status,data.message)
                        }else{
                            $('#store_or_update_form #update_id').val(data.id);
                            $('#store_or_update_form #supplier').val(data.supplier_id);
                            $('#store_or_update_form #type').val(data.type);
                            $('#store_or_update_form #amount').val(data.amount);
                            $('#store_or_update_form #payment_method').val(data.payment_method);
                            if(data.payment_method == 2){
                                console.log(data.cheque_no);
                                $('.cheque_number').removeClass('d-none');
                                $('#store_or_update_form #cheque_number').val(data.cheque_no);
                            }else{
                                $('.cheque_number').addClass('d-none');
                                $('#store_or_update_form #cheque_number').val('');
                            }
                            
                            account_list(data.payment_method,data.account_id)
                           
                            $('#store_or_update_form select#supplier').each(function(){
                                $('#store_or_update_form select#supplier option').each(function() {
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
            let url   = "{{ route('vendor.advance.delete') }}";
            delete_data(id, url, table, row, name);
        });
    
        function multi_delete(){
            let ids = [];
            let rows;
            $('.select_data:checked').each(function(){
                ids.push($(this).val());
                rows = table.rows($('.select_data:checked').parents('tr'));
            });
            if(ids.length == 0){
                Swal.fire({
                    type:'error',
                    title:'Error',
                    text:'Please checked at least one row of table!',
                    icon: 'warning',
                });
            }else{
                let url = "{{route('vendor.advance.bulk.delete')}}";
                bulk_delete(ids,url,table,rows);
            }
        }

        //Show Status Change Modal
        $(document).on('click','.change_status',function(){
            $('#approve_status_form #approve_id').val($(this).data('id'));
            $('#approve_status_form #approval_status').val($(this).data('status'));
            $('#approve_status_form #approval_status.selectpicker').selectpicker('refresh');
            $('#approve_status_modal').modal({
                keyboard: false,
                backdrop: 'static',
            });
            $('#approve_status_modal .modal-title').html('<span>Change Sale Status</span>');
            $('#approve_status_modal #status-btn').text('Change Status');
                    
        });

        $(document).on('click','#status-btn',function(){
            var approve_id     = $('#approve_status_form #approve_id').val();
            var approval_status =  $('#approve_status_form #approval_status option:selected').val();
            if(approve_id && approval_status)
            {
                $.ajax({
                    url: "{{route('vendor.advance.change.approval.status')}}",
                    type: "POST",
                    data: {approve_id:approve_id,approval_status:approval_status,_token:_token},
                    dataType: "JSON",
                    beforeSend: function(){
                        $('#status-btn').addClass('spinner spinner-white spinner-right');
                    },
                    complete: function(){
                        $('#status-btn').removeClass('spinner spinner-white spinner-right');
                    },
                    success: function (data) {
                        notification(data.status, data.message);
                        if (data.status == 'success') {
                            $('#approve_status_modal').modal('hide');
                            table.ajax.reload(null, false);
                        }
                    },
                    error: function (xhr, ajaxOption, thrownError) {
                        console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                    }
                });
            }
        });

        $(document).on('change', '#payment_method', function () {
            if($('#payment_method option:selected').val() == 2)
            {
                $('.cheque_number').removeClass('d-none');
            }else{
                $('.cheque_number').addClass('d-none');
            }
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
        $('#store_or_update_form select#supplier').each(function(){
            $('#store_or_update_form select#supplier option').each(function() {
                $(this).attr('disabled', false);
            });
        });
        $('#store_or_update_form #account_id').html('');
        $('#store_or_update_form select#supplier').val('');
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
