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
                    @if (permission('coa-add'))
                    <a href="javascript:void(0);" onclick="showFormModal('Add New Account','Save')" class="btn btn-primary btn-sm font-weight-bolder"> 
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
                        <x-form.textbox labelName="Account Name" name="name" col="col-md-3" />
                        <div class="col-md-9">
                            <div style="margin-top:28px;">    
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
                                        <th>Code</th>
                                        <th>Parent Head</th>
                                        <th>Head Type</th>
                                        <th>Opening Balance</th>
                                        {{-- <th>Balance</th> --}}
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
@include('account::coa.modal')
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
            "url": "{{route('coa.datatable.data')}}",
            "type": "POST",
            "data": function (data) {
                data.name          = $("#form-filter #name").val();
                data._token        = _token;
            }
        },
        "columnDefs": [
            {

                "targets": [6],
                "orderable": false,
                "className": "text-center"
            },
            {

                "targets": [2,4],
                "className": "text-center"
            },
            {
                "targets": [5],
                "className": "text-right"
            }
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
                'className':'btn btn-secondary btn-sm text-white',
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
                'className':'btn btn-secondary btn-sm text-white',
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
                'className':'btn btn-secondary btn-sm text-white',
                "title": "{{ $page_title }} List",
                "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                "orientation": "landscape", //portrait
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
        table.ajax.reload();
    });

    
    $(document).on('click', '#save-btn', function () {
        let form = document.getElementById('store_or_update_form');
        let formData = new FormData(form);
        let url = "{{route('coa.store.or.update')}}";
        let id = $('#update_id').val();
        let method;
        if (id) {
            method = 'update';
        } else {
            method = 'add';
        }
        store_or_update_data(table, method, url, formData);
    });

    $(document).on('click', '.edit_data', function () {
        let id = $(this).data('id');
        $('#store_or_update_form')[0].reset();
        $('#store_or_update_form').find('.is-invalid').removeClass('is-invalid');
        $('#store_or_update_form').find('.error').remove();
        if (id) {
            $.ajax({
                url: "{{route('coa.edit')}}",
                type: "POST",
                data: { id: id,_token: _token},
                dataType: "JSON",
                success: function (data) {
                    if(data.status == 'error'){
                        notification(data.status,data.message)
                    }else{
                        console.log(data);
                        $('#store_or_update_form #update_id').val(data.id);
                        $('#store_or_update_form #parent_name').val(data.parent);
                        $('#store_or_update_form #name').val(data.name);
                        $('#store_or_update_form #code').val(data.code);
                        $('#store_or_update_form #level').val(data.level);
                        $('#store_or_update_form #type').val(data.type);
                        $('#store_or_update_form .selectpicker').selectpicker('refresh');
                        if(data.transaction == 1)
                        {
                            $('#transaction').prop('checked',true);
                            $('#transaction').val(1);
                        }else{
                            $('#transaction').prop('checked',false);
                            $('#transaction').val(2);
                        }
                        if(data.general_ledger == 1)
                        {
                            $('#general_ledger').prop('checked',true);
                            $('#general_ledger').val(1);
                        }else{
                            $('#general_ledger').prop('checked',false);
                            $('#general_ledger').val(2);
                        }
                        if(data.status == 1)
                        {
                            $('#status').prop('checked',true);
                            $('#status').val(1);
                        }else{
                            $('#status').prop('checked',false);
                            $('#status').val(2);
                        }
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
        let url   = "{{ route('coa.delete') }}";
        delete_data(id, url, table, row, name);
    });

});
function fetch_parent_data(coa_id)
{
    $.ajax({
        url:"{{ url('coa/parent-head') }}",
        type:"POST",
        data:{coa_id:coa_id,_token:_token},
        success:function(data){
            if(data)
            {
                
                $('#code').val(data.code);
                $('#level').val(data.level);
                $('#type').val(data.type);
                $('#type.selectpicker').selectpicker('refresh');
                if(data.transaction == 1)
                {
                    $('#transaction').prop('checked',true);
                    $('#transaction').val(1);
                }else{
                    $('#transaction').prop('checked',false);
                    $('#transaction').val(2);
                }
                if(data.general_ledger == 1)
                {
                    $('#general_ledger').prop('checked',true);
                    $('#general_ledger').val(1);
                }else{
                    $('#general_ledger').prop('checked',false);
                    $('#general_ledger').val(2);
                }
            }
        },
        error: function (xhr, ajaxOption, thrownError) {
            console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
        }
    });
}
function set_checkbox_value(id)
{
    $('#'+id).is(':checked') ? $('#'+id).val(1) : $('#'+id).val(2);
}
</script>
@endpush