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
                                    <form class="form mt-0 mt-lg-10 fv-plugins-bootstrap fv-plugins-framework" id="store_or_update_form" method="POST">
                                        @csrf
                                        <div class="row justify-content-center py-12 px-12 py-lg-15 px-lg-10">
                                            <div class="col-md-offset-4 col-xl-8 col-xxl-8">
                                                <x-form.selectbox labelName="Employee" name="employee_id" onchange="getTotalBasicList(this.value)" required="required" col="col-md-12" class="selectpicker">
                                                    @if (!$employees->isEmpty())
                                                    @foreach ($employees as $employee)
                                                    <option value="{{ $employee->id }}" @if(isset($salarysetup)) {{ ($salarysetup->employee_id == $employee->id) ? 'selected' : '' }} @endif>
                                                        {{ $employee->name }}
                                                    </option>
                                                    @endforeach
                                                    @endif
                                                </x-form.selectbox>
                                                <x-form.textbox type="text" labelName="Basic" name="basic_salary" value="{{ isset($salarysetup) ? $salarysetup->basic_salary : '' }}" required="required" col="col-md-12" property="readonly" />

                                            </div>
                                            <div class="col-xl-12 col-xxl-12">
                                                <div class="row">
                                                    <div class="col-xl-12 col-xxl-12">
                                                        <div class="row">
                                                            @if (!$allowancededucts->isEmpty())
                                                            @foreach ($allowancededucts as $key => $allowance)
                                                            @if ($allowance->type == 1)
                                                            <div class="col-xl-6 col-xxl-6">
                                                                <div class="form-group col-md-12">
                                                                    <label for="voucher_no">{{ $allowance->name }}%</label>
                                                                    <input type="hidden" class="custom-control-input" name="allowance[{{$key+1}}][id]" value="{{ $allowance->id }}" id="allowance_{{$key+1}}_id">
                                                                    <input type="text" class="form-control text-left allowance_percent" name="allowance[{{$key+1}}][percent]" onkeyup="calculate_total({{$allowance->type}})" id="allowance_{{$key+1}}_percent" placeholder="{{ $allowance->name }}">
                                                                </div>
                                                            </div>
                                                            @endif
                                                            @if ($allowance->type == 2)
                                                            <div class="col-xl-6 col-xxl-6">
                                                                <div class="form-group col-md-12">
                                                                    <label for="voucher_no">{{ $allowance->name }}%</label>
                                                                    <input type="hidden" class="custom-control-input" name="allowance[{{$key+1}}][id]" value="{{ $allowance->id }}" id="allowance_{{$key+1}}_id">
                                                                    <input type="text" class="form-control text-left allowance_percent" name="allowance[{{$key+1}}][percent]" onkeyup="calculate_total({{$allowance->type}})" id="allowance_{{$key+1}}_percent" placeholder="{{ $allowance->name }}">
                                                                </div>
                                                            </div>
                                                            @endif
                                                            @endforeach
                                                            @endif
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                            <div class="col-md-offset-4 col-xl-8 col-xxl-8">
                                                <x-form.textbox type="text" labelName="Basic" name="net_basic" value="{{ isset($salarysetup) ? $salarysetup->basic_salary : '' }}" required="required" col="col-md-12" property="readonly" />

                                            </div>
                                            <div class="d-flex justify-content-between border-top mt-5 pt-10">

                                                <div>
                                                    <button type="button" class="btn btn-primary btn-sm font-weight-bolder text-uppercase" id="save-btn" onclick="store_data()">Submit</button>
                                                </div>
                                            </div>

                                            <!--end: Wizard Form-->
                                        </div>

                                    </form>
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
    function calculate_total(type) {
        var basic = 0;
        var net_salary_after_allowance = 0.00;
        var net_salary_after_deduct = 0.00;
        var net_salary = 0.00;
        basic = parseFloat($('input[name="basic_salary"]').val());
        if (type == 1) {
            var allowance_grand_total = 0;
            $('.allowance_percent').each(function() {
                if ($(this).val() == '' || isNaN($(this).val())) {
                    allowance_grand_total += 0;
                } else {
                    allowance_grand_total += parseFloat($(this).val());
                }
            });
            net_salary_after_allowance = parseFloat(((basic * allowance_grand_total) / 100));
        } else {
            var deduct_grand_total = 0;
            $('.deduct_percent').each(function() {
                if ($(this).val() == '' || isNaN($(this).val())) {
                    deduct_grand_total += 0;
                } else {
                    deduct_grand_total += parseFloat($(this).val());
                }
            });
            //$('input[name="credit_grand_total"]').val(parseFloat(credit_grand_total).toFixed(2));
            net_salary_after_deduct = parseFloat(((basic * deduct_grand_total) / 100));
        }

        net_salary = ((parseFloat(basic) + parseFloat(net_salary_after_allowance)) - (parseFloat(basic) + parseFloat(net_salary_after_deduct)));
        console.log(net_salary);
        if (!net_salary) {
            net_salary = 0.00;
        }
        //alert(net_profit);
        $('input[name="net_basic"]').val(parseFloat(net_salary).toFixed(2));
    }

    $(document).ready(function() {
        $('.date').datetimepicker({
            format: 'YYYY-MM-DD',
            ignoreReadonly: true
        });
    });

    function store_data() {
        let form = document.getElementById('store_or_update_form');
        let formData = new FormData(form);
        let url = "{{route('salary.setup.store.or.update')}}";
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
                        window.location.replace("{{ route('salary.setup') }}");
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

    function getTotalBasicList(employee_id) {
        $.ajax({
            url: "{{ url('employee-id-wise-salary-list') }}/" + employee_id,
            type: "GET",
            dataType: "JSON",
            success: function(data) {
                $("#basic_salary").val(data.rate);
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