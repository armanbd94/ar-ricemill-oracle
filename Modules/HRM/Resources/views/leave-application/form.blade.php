@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
<link href="css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
<link href="css/pages/wizard/wizard-4.css" rel="stylesheet" type="text/css" />
<style>
    .wizard.wizard-4 .wizard-nav .wizard-steps {
        flex-direction: column;
    }

    .wizard.wizard-4 .wizard-nav .wizard-steps .wizard-step .wizard-wrapper {
        flex: 1;
        display: flex;
        align-items: center;
        flex-wrap: unset !important;
        color: #3F4254;
        padding: 2rem 2.5rem !important;
        height: 100px !important;
    }

    .wizard.wizard-4 .wizard-nav .wizard-steps .wizard-step {
        width: 100% !important;

    }
</style>
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
                    <a href="{{ route('leave.application') }}" class="btn btn-secondary btn-sm font-weight-bolder">
                        <i class="fas fa-arrow-circle-left"></i> Back
                    </a>
                    <!--end::Button-->
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom">
            <div class="card-body">
                <div class="wizard wizard-4" id="kt_wizard" data-wizard-state="first" data-wizard-clickable="true">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-custom card-shadowless rounded-top-0">
                                <div class="card-body p-0">
                                    <div class="row justify-content-center py-8 px-8 py-lg-15 px-lg-10">
                                        <div class="col-xl-12 col-xxl-12">
                                            <table id="dataTable" class="table table-bordered table-hover">
                                                <thead class="bg-primary">
                                                    <tr>
                                                        <th>Leave Name</th>
                                                        <th>Total</th>
                                                        <th>Used</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="show_all_leave_data">
                                                    @if (!$leaves->isEmpty())
                                                    @foreach ($leaves as $leave)
                                                    <tr>
                                                        <td>{{ $leave->name.'('.$leave->short_name.')' }}</td>
                                                        <td>{{ $leave->number }}</td>
                                                        <td id="userd_<?php echo $leave->id; ?>"></td>
                                                    </tr>
                                                    @endforeach
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-xl-12 col-xxl-12">
                                            <!--begin: Wizard Form-->
                                            <form class="form mt-0 mt-lg-10 fv-plugins-bootstrap fv-plugins-framework" id="store_or_update_form" method="POST">
                                                @csrf
                                                <input type="hidden" name="update_id" id="update_id" value="{{ isset($leaveapplication) ? $leaveapplication->id : '' }}">
                                                <input type="hidden" name="leave_type" id="leave_type" value="{{ isset($leaveapplication) ? $leaveapplication->leave_type : '' }}">
                                                <div class="row">
                                                    <x-form.selectbox labelName="Employee" name="employee_id" onchange="getTotalLeaveTakenList(this.value)" required="required" col="col-md-4" class="selectpicker">
                                                        @if (!$employees->isEmpty())
                                                        @foreach ($employees as $employee)
                                                        <option value="{{ $employee->id }}" @if(isset($leaveapplication)) {{ ($leaveapplication->employee_id == $employee->id) ? 'selected' : '' }} @endif>
                                                            {{ $employee->name }}
                                                        </option>
                                                        @endforeach
                                                        @endif
                                                    </x-form.selectbox>
                                                    <x-form.selectbox labelName="Alternative Employee" name="alternative_employee" col="col-md-4" class="selectpicker">
                                                        @if (!$employees->isEmpty())
                                                        @foreach ($employees as $employeee)
                                                        <option value="{{ $employeee->id }}" @if(isset($leaveapplication)) {{ ($leaveapplication->alternative_employee == $employeee->id) ? 'selected' : '' }} @endif>
                                                            {{ $employeee->name }}
                                                        </option>
                                                        @endforeach
                                                        @endif
                                                    </x-form.selectbox>
                                                    <x-form.selectbox labelName="Leave" name="leave_id" onchange="getLeaveType(this.value)" required="required" col="col-md-4" class="selectpicker">
                                                        @if (!$leaves->isEmpty())
                                                        @foreach ($leaves as $leave)
                                                        <option value="{{ $leave->id }}" @if(isset($leaveapplication)) {{ ($leaveapplication->leave_id == $leave->id) ? 'selected' : '' }} @endif>
                                                            {{ $leave->name }}
                                                        </option>
                                                        @endforeach
                                                        @endif
                                                    </x-form.selectbox>
                                                    <x-form.textbox labelName="Start Date" name="start_date" required="required" value="{{ isset($leaveapplication) ? $leaveapplication->start_date : '' }}" required="required" col="col-md-4" />
                                                    <x-form.textbox labelName="End Date" name="end_date" required="required" value="{{ isset($leaveapplication) ? $leaveapplication->end_date : '' }}" required="required" col="col-md-4" />
                                                    <x-form.textbox type="text" labelName="Leave Number" name="number_leave" value="{{ isset($leaveapplication) ? $leaveapplication->number_leave : '' }}" required="required" col="col-md-4" property="readonly" />
                                                    <x-form.textbox labelName="Employee Location" name="employee_location" value="{{ isset($leaveapplication) ? $leaveapplication->employee_location : '' }}" col="col-md-4" />

                                                    <x-form.selectbox labelName="Submission" name="submission" required="required" col="col-md-4" class="selectpicker">
                                                        @foreach ($submissions as $key => $items)
                                                        <option value="{{ $key }}" @if(isset($leaveapplication)) {{ ($leaveapplication->submission == $key) ? 'selected' : '' }} @endif>{{ $items }}</option>
                                                        @endforeach
                                                    </x-form.selectbox>

                                                    <x-form.selectbox labelName="Deletable" name="deletable" required="required" col="col-md-4" class="selectpicker">
                                                        @foreach ($deletable as $key => $item)
                                                        <option value="{{ $key }}" @if(isset($leaveapplication)) {{ ($leaveapplication->deletable == $key) ? 'selected' : '' }} @endif>{{ $item }}</option>
                                                        @endforeach

                                                    </x-form.selectbox>
                                                    <x-form.textbox labelName="Purpose" name="purpose" value="{{ isset($leaveapplication) ? $leaveapplication->purpose : '' }}" row="8" col="col-md-12" required="required" />
                                                </div>
                                        </div>
                                        <div class="d-flex justify-content-between border-top mt-5 pt-10">

                                            <div>
                                                <button type="button" class="btn btn-primary btn-sm font-weight-bolder text-uppercase" id="save-btn" onclick="store_data()">Submit</button>
                                            </div>
                                        </div>

                                        </form>
                                        <!--end: Wizard Form-->
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>


            </div>
        </div>
    </div>
    <!--end::Card-->
</div>
</div>
@endsection

@push('scripts')
<script src="js/bootstrap-datetimepicker.min.js"></script>
<script src="js/bootstrap-datepicker.min.js"></script>
<script>
    $(document).ready(function() {
        $('.date').datetimepicker({
            format: 'YYYY-MM-DD',
            ignoreReadonly: true
        });
    });

    function store_data() {
        let form = document.getElementById('store_or_update_form');
        let formData = new FormData(form);
        let url = "{{route('leave.application.store.or.update')}}";
        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            dataType: "JSON",
            contentType: false,
            processData: false,
            cache: false,
            beforeSend: function() {
                $('#save-btn').addClass('spinner spinner-white spinner-right');
            },
            complete: function() {
                $('#save-btn').removeClass('spinner spinner-white spinner-right');
            },
            success: function(data) {
                $('#store_or_update_form').find('.is-invalid').removeClass('is-invalid');
                $('#store_or_update_form').find('.error').remove();
                if (data.status == false) {
                    $.each(data.errors, function(key, value) {
                        var key = key.split('.').join('_');
                        $('#store_or_update_form input#' + key).addClass('is-invalid');
                        $('#store_or_update_form textarea#' + key).addClass('is-invalid');
                        $('#store_or_update_form select#' + key).parent().addClass('is-invalid');
                        $('#store_or_update_form #' + key).parent().append(
                            '<small class="error text-danger">' + value + '</small>');

                    });
                } else {
                    notification(data.status, data.message);
                    if (data.status == 'success') {
                        window.location.replace("{{ route('leave.application') }}");
                    }
                }

            },
            error: function(xhr, ajaxOption, thrownError) {
                console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
            }
        });
    }

    function loadFile(event, target_id) {
        var output = document.getElementById(target_id);
        output.src = URL.createObjectURL(event.target.files[0]);
    };

    function getTotalLeaveTakenList(employee_id) {
        $.ajax({
            url: "{{ url('employee-id-wise-leave-list') }}/" + employee_id,
            type: "GET",
            dataType: "JSON",
            success: function(data) {
                var stuff = "";
                var sl = 1;
                $.each(data.list, function(key, val) {

                    stuff = stuff + "<tr class='" + val.id + "tr'>" +
                        "<td>" + val.name + "</td>" +
                        "<td>" + val.lnumber + "</td>" +
                        "<td>" + val.sumleave + "</td>" +
                        "</tr>";
                });


                $("#show_all_leave_data").html(stuff);
            },
        });
    }

    function getLeaveType(leave_id) {
        $.ajax({
            url: "{{ url('leave-type-wise-leave') }}/" + leave_id,
            type: "GET",
            dataType: "JSON",
            success: function(data) {
                $('#leave_type').val(data);
            },
        });
    }

    $(function() {

        $('#start_date').datepicker({
            format: "yyyy-mm-dd",
            todayHighlight: 'TRUE',
            autoclose: true,
            minDate: 0,
            maxDate: '+1Y+6M'
        }).on('changeDate', function(ev) {
            $('#tdate').datepicker('setStartDate', $("#start_date").val());
        });

        $('#end_date').datepicker({
            format: "yyyy-mm-dd",
            todayHighlight: 'TRUE',
            autoclose: true,
            minDate: '0',
            maxDate: '+1Y+6M'
        }).on('changeDate', function(ev) {
            var start = $("#start_date").val();
            var startD = new Date(start);
            var end = $("#end_date").val();
            var endD = new Date(end);
            var diff = parseInt((endD.getTime() - startD.getTime()) / (24 * 3600 * 1000));
            $("#number_leave").val(diff);
            //alert(diff);
        });

    });

    /*****************************************
     * End :: Dynamic Experience Input Field *
     ******************************************/
</script>
@endpush