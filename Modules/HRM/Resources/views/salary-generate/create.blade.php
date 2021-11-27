@php
    use Modules\HRM\Entities\SalaryGenerate;
@endphp
@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link href="css/daterangepicker.min.css" rel="stylesheet" type="text/css" />
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
            <div class="card-header flex-wrap py-5">
             <form method="GET" action="{{ route('salary.generate.report.data') }}" id="form-filter" class="col-md-12 px-0">
                    <div class="row justify-content-center">
                        
                    <div class="form-group col-md-3">
                            <label for="name">Choose Your Date</label>
                            <div class="input-group">
                                <input type="text" class="form-control daterangepicker-filed" value="{{ date('Y-m-d') }} To {{ date('Y-m-d') }}">
                                <input type="hidden" id="start_date" name="start_date" value="{{ date('Y-m-d') }}">
                                <input type="hidden" id="end_date" name="end_date" value="{{ date('Y-m-d')}}">
                            </div>
                        </div>
                        <x-form.selectbox labelName="Employee" name="employee_id" col="col-md-3" class="selectpicker">
                            @if (!$employees->isEmpty())
                                @foreach ($employees as $value)
                                    <option value="{{ $value->id }}">{{ $value->name.' - '.$value->employee_id  }}</option>
                                @endforeach
                            @endif
                        </x-form.selectbox>
                        <x-form.selectbox labelName="Department" name="department_id" col="col-md-3" onchange="getDivisionList(this.value)" class="selectpicker">
                            @if (!$departments->isEmpty())
                            @foreach ($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                            @endif
                        </x-form.selectbox>
                        <x-form.selectbox labelName="Designation" name="designation_id" col="col-md-3" class="selectpicker">
                            @if (!$designations->isEmpty())
                                @foreach ($designations as $value)
                                    <option value="{{ $value->id }}">{{ $value->name }}</option>
                                @endforeach
                            @endif
                        </x-form.selectbox>                        
                        <div class="col-md-1">
                            <div style="margin-top:28px;">    
                                <div style="margin-top:28px;">        
                                    <button id="btn-filter" class="btn btn-primary btn-sm btn-elevate btn-icon mr-2 float-right" type="submit"
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
                            <div class="table-responsive">
                            <table id="dataTable" class="table table-bordered table-hover">
                                <thead class="bg-primary">
                                <tr>
                                            <th class="vertically_middle text-center" style="vertical-align: middle"
                                                rowspan="2">SL
                                            </th>
                                            <th class="vertically_middle text-center" style="vertical-align: middle"
                                                rowspan="2">Name
                                            </th>
                                            <th class="vertically_middle text-center" style="vertical-align: middle"
                                                rowspan="2">Designation
                                            </th>
                                            <th class="vertically_middle text-center" style="vertical-align: middle"
                                                rowspan="2">Basic
                                            </th>
                                            <th class="vertically_middle text-center" style="vertical-align: middle"
                                                rowspan="2">Gross Salary
                                            </th>
                                            <th class="text-center" colspan="3">Absent</th>
                                            <th class="text-center" colspan="<?php echo (count($leaves)+1);?>">Leave</th>
                                            <th class="text-center" colspan="3">OT</th>
                                            <!-- <th class="vertically_middle text-center" style="vertical-align: middle"
                                                rowspan="2">Add/ Deduct
                                            </th> -->
                                            <th class="text-center">Adjustment</th>
                                            <th class="vertically_middle text-center" style="vertical-align: middle"
                                                rowspan="2">Total Salary
                                            </th>
                                            <!-- <th class="text-center" colspan="3">Adjustment</th> -->
                                            <!-- <th class="vertically_middle text-center" style="vertical-align: middle"
                                                rowspan="2">Net Payable
                                            </th>
                                            <th class="vertically_middle text-center" style="vertical-align: middle"
                                                rowspan="2">Signiture
                                            </th> -->
                                        </tr>
                                        <tr>
                                            <th class="text-center">ABS</th>
                                            <th class="text-center">LATE</th>
                                            <th class="text-center">TK</th>
                                            @if (!$leaves->isEmpty())
                                                @foreach ($leaves as $value)
                                                <th class="text-center">{{ $value->short_name }}</th>
                                                @endforeach
                                            @endif
                                            <th class="text-center">TK</th>
                                            <th class="text-center">HOUR</th>
                                            <th class="text-center">DAY</th>
                                            <th class="text-center">TK</th>
                                            <th class="text-center">Loan</th>
                                            <!-- <th class="text-center">Adv</th>
                                            <th class="text-center">Loan</th>
                                            <th class="text-center">PF</th> -->
                                        </tr>
                                </thead>
                                <tbody>                           
                                </tbody>
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
@include('hrm::attendance.modal')
@endsection

@push('scripts')
<script src="js/knockout-3.4.2.js"></script>
<script src="js/daterangepicker.min.js"></script>
<script src="js/bootstrap-datetimepicker.min.js"></script>
<script src="js/bootstrap-timepicker.min.js"></script>
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

    $('body').on('focus', ".timepicker", function () {
        var v = $(this);
        $(this).timepicker().on(
        'show.timepicker', function (e) {
            v.timepicker('setTime', e.time.value);
        });
        $(this).timepicker().on('hide.timepicker', function (e) {
            v.val(e.time.value);
        });

    });

    $('.date').datetimepicker({
        format: 'YYYY-MM-DD',
        ignoreReadonly: true
    });
    // $(".timepicker").timepicker({
    //     format : 'HH:mm',
    //     showInputs: false,
    //     showMeridian: false
    // });
    var table;
    $(document).ready(function(){       
    
    
    });
    </script>
@endpush
