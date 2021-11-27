@php
    use Modules\HRM\Entities\AttendanceReport;
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
             <form method="GET" action="{{ route('attendance.report.datatable.data') }}" id="form-filter" class="col-md-12 px-0">
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

                        
                        <div class="col-md-1">
                            <div style="margin-top:28px;">    
                                <div style="margin-top:28px;">    
                                    <button id="btn-reset" class="btn btn-danger btn-sm btn-elevate btn-icon float-right" type="button"
                                    data-toggle="tooltip" data-theme="dark" title="Reset">
                                    <i class="fas fa-undo-alt"></i></button>
    
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
                            <table id="dataTable" class="table table-bordered table-hover">
                                <thead class="bg-primary">
                                    <tr>                                        
                                        <th>Date</th>
                                        <th>Day Name</th>
                                        <th>In Time</th>
                                        <th>Out Time</th>
                                        <th>Shift Name</th>
                                        <th>Early Leave</th>
                                        <th>Late</th>
                                        <th>Working Hour</th>
                                        <th>Over Time</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>                           
                                </tbody>
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
