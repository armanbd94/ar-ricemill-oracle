@extends('layouts.app')

@section('title', $page_title)

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
                    @if (permission('employee-add'))
                    <a href="{{ route('employee.add') }}" class="btn btn-primary btn-sm font-weight-bolder">
                        <i class="fas fa-plus-circle"></i> Add New
                    </a>
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
                        <x-form.textbox labelName="Employee ID" name="employee_id" col="col-md-3" placeholder="Enter employee id" />
                        <x-form.textbox labelName="Name" name="name" col="col-md-3" placeholder="Enter name" />
                        <x-form.textbox labelName="Phone No." name="phone" col="col-md-3" placeholder="Enter phone number" />
                        <x-form.textbox labelName="Email" name="email" col="col-md-3" placeholder="Enter email" />
                        <x-form.selectbox labelName="Department" name="department_id" col="col-md-3" onchange="getDivisionList(this.value)" class="selectpicker">
                            @if (!$departments->isEmpty())
                            @foreach ($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                            @endif
                        </x-form.selectbox>
                        <x-form.selectbox labelName="Division" name="division_id" required="required" col="col-md-3" class="selectpicker">
                            @if (!$divisions->isEmpty())
                            @foreach ($divisions as $division)
                            <option value="{{ $division->id }}" @if(isset($employee)) {{ ($employee->division_id == $division->id) ? 'selected' : '' }} @endif>{{ $division->name }}</option>
                            @endforeach
                            @endif
                        </x-form.selectbox>
                        <x-form.selectbox labelName="Designation" name="designation_id" col="col-md-3" class="selectpicker">
                            @if (!$designations->isEmpty())
                            @foreach ($designations as $designation)
                            <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                            @endforeach
                            @endif
                        </x-form.selectbox>
                        <x-form.selectbox labelName="Supervisor Name" name="supervisor_id" required="required" col="col-md-3" class="selectpicker">
                            <option value="0">Self</option>
                            @if (!$employees->isEmpty())
                            @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name.' - '.$employee->employee_id }}</option>
                            @endforeach
                            @endif
                        </x-form.selectbox>
                        <x-form.selectbox labelName="Job Status" name="job_status" required="required" col="col-md-3" class="selectpicker">
                            @foreach (JOB_STATUS as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </x-form.selectbox>
                        <x-form.selectbox labelName="Duty Type" name="duty_type" required="required" col="col-md-3" class="selectpicker">
                            @foreach (DUTY_TYPE as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </x-form.selectbox>
                        <x-form.selectbox labelName="Status" name="status" col="col-md-3" class="selectpicker">
                            <option value="1">Active</option>
                            <option value="2">Inactive</option>
                        </x-form.selectbox>
                        <div class="col-md-12">
                            <div style="margin-top:28px;">
                                <div style="margin-top:28px;">
                                    <button id="btn-reset" class="btn btn-danger btn-sm btn-elevate btn-icon float-right" type="button" data-toggle="tooltip" data-theme="dark" title="Reset">
                                        <i class="fas fa-undo-alt"></i></button>

                                    <button id="btn-filter" class="btn btn-primary btn-sm btn-elevate btn-icon mr-2 float-right" type="button" data-toggle="tooltip" data-theme="dark" title="Search">
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
                            <div class="table-responsive">
                                <table id="dataTable" class="table table-bordered table-hover">
                                    <thead class="bg-primary">
                                        <tr>
                                            @if (permission('employee-bulk-delete'))
                                            <th>
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="select_all" onchange="select_all()">
                                                    <label class="custom-control-label" for="select_all"></label>
                                                </div>
                                            </th>
                                            @endif
                                            <th>Sl</th>
                                            <th>Photo</th>
                                            <th>Employee ID</th>
                                            <th>Name</th>
                                            <th>Phone</th>
                                            <th>Email</th>
                                            <th>NID NO</th>
                                            <th>Desgination</th>
                                            <th>Department</th>
                                            <th>Division</th>
                                            <th>Supervisor</th>
                                            <th>Salary</th>
                                            <th>Job Status</th>
                                            <th>Duty Type</th>
                                            <th>Blood Group</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end: Datatable-->
            </div>
        </div>
        <!--end::Card-->
    </div>
</div>

@include('hrm::employee.modal')
@include('hrm::employee.benifitsmodal')
@endsection

@push('scripts')
<script src="js/bootstrap-datetimepicker.min.js"></script>
<script>
    var table;
    $(document).ready(function() {
        $('.date').datetimepicker({
            format: 'YYYY-MM-DD',
            ignoreReadonly: true
        });
        table = $('#dataTable').DataTable({
            "processing": true, //Feature control the processing indicator
            "serverSide": true, //Feature control DataTable server side processing mode
            "order": [], //Initial no order
            "responsive": false, //Make table responsive in mobile device
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
                "url": "{{route('employee.datatable.data')}}",
                "type": "POST",
                "data": function(data) {
                    data.employee_id = $("#form-filter #employee_id").val();
                    data.name = $("#form-filter #name").val();
                    data.phone = $("#form-filter #phone").val();
                    data.email = $("#form-filter #email").val();
                    data.warehouse_id = $("#form-filter #warehouse_id option:selected").val();
                    data.department_id = $("#form-filter #department_id option:selected").val();
                    data.division_id = $("#form-filter #division_id option:selected").val();
                    data.designation_id = $("#form-filter #designation_id option:selected").val();
                    data.supervisor_id = $("#form-filter #supervisor_id option:selected").val();
                    data.status = $("#form-filter #status").val();
                    data._token = _token;
                }
            },
            "columnDefs": [{
                    @if(permission('employee-bulk-delete'))
                    "targets": [0, 17],
                    @else "targets": [16],
                    @endif

                    "orderable": false,
                    "className": "text-center"
                },
                {
                    @if(permission('employee-bulk-delete'))
                    "targets": [1, 2, 3, 5, 7, 8, 9, 10, 11, 13, 14, 15, 16],
                    @else "targets": [0, 1, 2, 4, 6, 7, 8, 9, 10, 12, 13, 14, 15],
                    @endif "className": "text-center"
                },
                {
                    @if(permission('employee-bulk-delete'))
                    "targets": [12],
                    @else "targets": [11],
                    @endif "className": "text-right"
                }
            ],
            "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6' <'float-right'B>>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'<'float-right'p>>>",

            "buttons": [
                @if(permission('employee-report')) {
                    'extend': 'colvis',
                    'className': 'btn btn-secondary btn-sm text-white',
                    'text': 'Column',
                    'columns': ':gt(0)'
                },
                {
                    "extend": 'print',
                    'text': 'Print',
                    'className': 'btn btn-secondary btn-sm text-white',
                    "title": "{{ $page_title }} List",
                    "orientation": "landscape", //portrait
                    "pageSize": "A4", //A3,A5,A6,legal,letter
                    "exportOptions": {
                        @if(permission('employee-bulk-delete'))
                        columns: ':visible:not(:eq(0),:eq(17))'
                        @else
                        columns: ':visible:not(:eq(16))'
                        @endif
                    },
                    customize: function(win) {
                        $(win.document.body).addClass('bg-white');
                        $(win.document.body).find('table thead').css({
                            'background': '#034d97'
                        });
                        $(win.document.body).find('table tfoot tr').css({
                            'background-color': '#034d97'
                        });
                        $(win.document.body).find('h1').css('text-align', 'center');
                        $(win.document.body).find('h1').css('font-size', '15px');
                        $(win.document.body).find('table').css('font-size', 'inherit');
                    },
                },
                {
                    "extend": 'csv',
                    'text': 'CSV',
                    'className': 'btn btn-secondary btn-sm text-white',
                    "title": "{{ $page_title }} List",
                    "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                    "exportOptions": {
                        @if(permission('employee-bulk-delete'))
                        columns: ':visible:not(:eq(0),:eq(17))'
                        @else
                        columns: ':visible:not(:eq(16))'
                        @endif
                    },
                },
                {
                    "extend": 'excel',
                    'text': 'Excel',
                    'className': 'btn btn-secondary btn-sm text-white',
                    "title": "{{ $page_title }} List",
                    "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                    "exportOptions": {
                        @if(permission('employee-bulk-delete'))
                        columns: ':visible:not(:eq(0),:eq(17))'
                        @else
                        columns: ':visible:not(:eq(16))'
                        @endif
                    },
                },
                {
                    "extend": 'pdf',
                    'text': 'PDF',
                    'className': 'btn btn-secondary btn-sm text-white',
                    "title": "{{ $page_title }} List",
                    "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                    "orientation": "landscape", //portrait
                    "pageSize": "A4", //A3,A5,A6,legal,letter
                    "exportOptions": {
                        @if(permission('employee-bulk-delete'))
                        columns: ':visible:not(:eq(0),:eq(17))'
                        @else
                        columns: ':visible:not(:eq(16))'
                        @endif
                    },
                    customize: function(doc) {
                        doc.defaultStyle.fontSize = 7; //<-- set fontsize to 16 instead of 10 
                        doc.styles.tableHeader.fontSize = 7;
                        doc.pageMargins = [5, 5, 5, 5];
                    }
                },
                @endif
                @if(permission('employee-bulk-delete')) {
                    'className': 'btn btn-danger btn-sm delete_btn d-none text-white',
                    'text': 'Delete',
                    action: function(e, dt, node, config) {
                        multi_delete();
                    }
                },
                @endif

                // @if(permission('employee-benifit-setup')) {
                //     'className': 'btn btn-success btn-sm delete_btn d-none text-white',
                //     'text': 'Employee Benifit Set',
                //     action: function(e, dt, node, config) {
                //         //showing_shift_model();
                //         showFormModalForBenifits('Employee Benifit', 'Save');
                //     }
                // },
                // @endif

                @if(permission('employee-shift-change')) {
                    'className': 'btn btn-info btn-sm delete_btn d-none text-white',
                    'text': 'Shift Change',
                    action: function(e, dt, node, config) {
                        //showing_shift_model();
                        showFormModal('Employee Shift Changing', 'Save');
                    }
                }
                @endif
            ],
        });

        $('#btn-filter').click(function() {
            table.ajax.reload();
        });

        $('#btn-reset').click(function() {
            $('#form-filter')[0].reset();
            $('#form-filter .selectpicker').selectpicker('refresh');
            table.ajax.reload();
        });

        $(document).on('click', '.delete_data', function() {
            let id = $(this).data('id');
            let name = $(this).data('name');
            let row = table.row($(this).parent('tr'));
            let url = "{{ route('employee.delete') }}";
            delete_data(id, url, table, row, name);
        });

        function multi_delete() {
            let ids = [];
            let rows;
            $('.select_data:checked').each(function() {
                ids.push($(this).val());
                rows = table.rows($('.select_data:checked').parents('tr'));
            });
            if (ids.length == 0) {
                Swal.fire({
                    type: 'error',
                    title: 'Error',
                    text: 'Please checked at least one row of table!',
                    icon: 'warning',
                });
            } else {
                let url = "{{route('employee.bulk.delete')}}";
                bulk_delete(ids, url, table, rows);
            }
        }

        $(document).on('click', '#save-btn', function() {
            let ids = [];
            let rows;
            let start_date = $('#start_date').val();
            let end_date = $('#start_date').val();
            let shift_id = $('#shift_id').val();
            $('.select_data:checked').each(function() {
                ids.push($(this).val());
                rows = table.rows($('.select_data:checked').parents('tr'));
            });
            if (ids.length == 0) {
                Swal.fire({
                    type: 'error',
                    title: 'Error',
                    text: 'Please checked at least one row of table!',
                    icon: 'warning',
                });
            } else {
                let url = "{{route('employee.change.shift')}}";
                $.ajax({
                    url: url,
                    type: "POST",
                    data: {
                        ids: ids,
                        start_date: start_date,
                        end_date: end_date,
                        shift_id: shift_id,
                        _token: _token
                    },
                    dataType: "JSON",
                }).done(function(response) {

                    if (response.status == "success") {
                        Swal.fire("Saved", response.message, "success").then(function() {

                            table.rows(rows).remove().draw(false);
                            $('#select_all').prop('checked', false);
                            $('.delete_btn').addClass('d-none');
                            $('#store_or_update_modal').modal('hide');
                        });
                    }
                    if (response.status == "error") {
                        Swal.fire('Oops...', response.message, "error");
                    }
                });
            }
        });
        $(document).on('click', '#save-btn-benifit', function() {
            let ids = [];
            let benifit_ids = [];
            let rows;
            $('.select_data:checked').each(function() {
                ids.push($(this).val());
                rows = table.rows($('.select_data:checked').parents('tr'));
            });
            $('.benifits_checked:checked').each(function() {
                benifit_ids.push($(this).val());
            });
            if (ids.length == 0) {
                Swal.fire({
                    type: 'error',
                    title: 'Error',
                    text: 'Please checked at least one row of table!',
                    icon: 'warning',
                });
            } else {
                let url = "{{route('employee.setup.allowance.deduction')}}";
                $.ajax({
                    url: url,
                    type: "POST",
                    data: {
                        ids: ids,
                        benifit_ids: benifit_ids,
                        _token: _token
                    },
                    dataType: "JSON",
                }).done(function(response) {

                    if (response.status == "success") {
                        Swal.fire("Saved", response.message, "success").then(function() {

                            table.rows(rows).remove().draw(false);
                            $('#select_all').prop('checked', false);
                            $('.delete_btn').addClass('d-none');
                            $('#store_or_update_modal_benifit').modal('hide');
                        });
                    }
                    if (response.status == "error") {
                        Swal.fire('Oops...', response.message, "error");
                    }
                });
            }
        });

        $(document).on('click', '.change_status', function() {
            let id = $(this).data('id');
            let name = $(this).data('name');
            let status = $(this).data('status');
            let row = table.row($(this).parent('tr'));
            let url = "{{ route('employee.change.status') }}";
            change_status(id, url, table, row, name, status);
        });

        function showing_shift_model() {
            $('#store_or_update_modal').show();
        }


    });

    function showFormModalForBenifits(modal_title, btn_text) {
        //$('#store_or_update_modal_benifit')[0].reset();
        $('#store_or_update_modal_benifit #update_id').val('');
        $('#store_or_update_modal_benifit').find('.is-invalid').removeClass('is-invalid');
        $('#store_or_update_modal_benifit').find('.error').remove();
        $('#store_or_update_modal_benifit .selectpicker').selectpicker('refresh');
        $('#store_or_update_modal_benifit table tbody').find("tr:gt(0)").remove();

        $('#store_or_update_modal_benifit').modal({
            keyboard: false,
            backdrop: 'static',
        });
        $('#store_or_update_modal .modal-title').html('<i class="fas fa-plus-square text-white"></i> ' + modal_title);
        $('#store_or_update_modal #save-btn').text(btn_text);
    }

    function getDivisionList(department_id) {
        $.ajax({
            url: "{{ url('department-id-wise-division-list') }}/" + department_id,
            type: "GET",
            dataType: "JSON",
            success: function(data) {
                html = `<option value="">Select Please</option>`;
                $.each(data, function(key, value) {
                    html += '<option value="' + key + '">' + value + '</option>';
                });
                $('#division_id').empty();
                $('#division_id').append(html);
                $('.selectpicker').selectpicker('refresh');
            },
        });
    }
</script>
@endpush