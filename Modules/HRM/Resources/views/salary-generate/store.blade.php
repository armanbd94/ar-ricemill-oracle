@php
    use Modules\HRM\Entities\SalaryGenerate;
    use Illuminate\Support\Facades\DB;
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
                    <div class="row" id="report">
                        <style>
                            @media print {

                                body,
                                html {
                                    background: #fff !important;
                                    -webkit-print-color-adjust: exact !important;
                                    font-family: sans-serif;
                                }
                            }
                        </style>
                        <div class="col-md-12 pb-5">
                            <div class="row">
                                <table width="100%" style="margin:0;padding:0;">
                                    <tr>
                                        <td width="100%" class="text-center">
                                            <h3 style="margin:0;">{{ config('settings.title') ? config('settings.title') : env('APP_NAME') }}</h3>
                                            @if(config('settings.contact_no'))<p style="font-weight: normal;margin:0;"><b>Contact: </b>{{ config('settings.contact_no') }}, @if(config('settings.email'))<b>Email: </b>{{ config('settings.email') }}@endif</p>@endif
                                            @if(config('settings.address'))<p style="font-weight: normal;margin:0;">{{ config('settings.address') }}</p>@endif
                                            <p style="font-weight: normal;margin:0;"><b>Salary Month: </b>@if (!empty($start_date)) {{ $salary_month = date('F-Y', strtotime($start_date))}} @endif</p>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-12">                
                        <form id="salary-generate-form" method="POST">
                                @csrf
                                <input type="hidden" id="sd_date" name="sd_date" value="{{$start_date}}"/>
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
                                                            <!-- <th class="vertically_middle text-center" style="vertical-align: middle"
                                                                rowspan="2">Net Payable
                                                            </th> 
                                                            <th class="vertically_middle text-center" style="vertical-align: middle"
                                                                rowspan="2">Signiture
                                                            </th>-->
                                                        </tr>
                                                        <tr>
                                                            <th class="text-center">ABS</th>
                                                            <th class="text-center">LATE</th>
                                                            <th class="text-center">TK</th>
                                                            <?php $orleaves=array();?>
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
                                                            <th class="text-center">PF</th> -->
                                                        </tr>
                                                </thead>
                                                <tbody>      
                                                <?php
                                                $sl = 1;
                                                $total_net_payable_amount = 0;
                                                $noDays = SalaryGenerate::getNumberOfActiveDays();
                                                if (!empty($all_employee)):
                                                    $sd = date('Y-m-d', strtotime('-1 day', strtotime($start_date)));
                                                    $ed = $end_date;
                                                    $total_amount = 0;
                                                    $total_late_amount = 0;
                                                    $loan_count = 0;
                                                    $total_leave_amount = 0;
                                                    $total_ot_amount = 0;
                                                    $grand_total_salary = 0;
                                                    $total_net_payable_amount = 0;
                                                    $in_time_with_date = 0;
                                                    $out_time_with_date = 0;
                                                    $adjust_amount = 0;
                                                    $loan_adjust_amount = 0;
                                                    $total_salary = 0;
                                                    $allowanceAmount=0;
                                                    $deductionAmount=0;
                                                    $otherAmount=0;
                                                    $allowancesss = array();
                                                    $advance_start_month = date('F-Y', strtotime($start_date));
                                                    $loan_start_month = date('F-Y', strtotime($start_date));
                                                    $salary_month = date('F-Y', strtotime($start_date));
                                                    foreach ($all_employee as $emp_val):
                                                    
                                                        $daily_attendance = DB::select(DB::raw("SELECT MIN(att.id),MAX(att.id),MIN(att.time_str_am_pm) as in_time_str,MAX(att.time_str_am_pm) as out_time_str,MIN(att.time) as in_time,MAX(att.time) as out_time,
                                                        att.employee_id,att.date_time,att.date,att.time,att.am_pm,att.time_str,
                                                        att.time_str_am_pm,reg.shift_id,shift.start_time as shift_start_time,shift.end_time as shift_end_time,shift.name as shift_name FROM `attendances` as att 
                                                        LEFT JOIN employees as reg ON reg.id=att.employee_id JOIN shifts as shift ON shift.id=reg.shift_id WHERE att.date >='" . $sd . "' 
                                                        AND att.date <='" . $ed . "' AND att.employee_id='" . $emp_val->id . "' 
                                                        group by att.date,att.employee_id")); 

                                                        $leave_info = DB::select(DB::raw("SELECT employee_id,start_date,end_date,leave_id,leave_status FROM `leave_application_manages`
                                                        WHERE employee_id='" . $emp_val->id . "' AND start_date >='" . $sd . "' 
                                                        AND end_date <='" . $ed . "'"));

                                                        $shift_info = DB::select(DB::raw("SELECT change_shift.shift_id,shift.start_time,shift.end_time,shift.night_status,change_shift.start_date,change_shift.end_date FROM `shift_manages` as change_shift 
                                                        JOIN `shifts` as shift on shift.id=change_shift.shift_id WHERE change_shift.employee_id='" . $emp_val->id . "' 
                                                        and change_shift.start_date >='" . $sd . "' AND change_shift.end_date <='" . $ed . "'"));

                                                        $allallowance = DB::select(DB::raw("SELECT SUM(allowAmount.amount) as amount,allowAmount.employee_id,allow.id,allow.name as aname,allow.short_name as asname,allow.type as atype FROM `allowance_deduction_manages` as allowAmount 
                                                        JOIN `allowance_deductions` as allow ON allow.id=allowAmount.allowance_deduction_id WHERE allowAmount.employee_id='" . $emp_val->id . "' 
                                                        group by allowAmount.employee_id,atype"));
                                                        $allowanceAmount=0;
                                                        $deductionAmount=0;
                                                        $otherAmount=0;
                                                        foreach($allallowance as $allowance):
                                                            if($allowance->atype == 1){                
                                                                $allowanceAmount = $allowance->amount;                                                   
                                                            }else if($allowance->atype == 2){
                                                                $deductionAmount = $allowance->amount; 
                                                            }else{
                                                                $otherAmount = $allowance->amount;
                                                            }
                                                        endforeach;
                                                        $holiday=array();
                                                        $holi=0;
                                                        $weekholiday = DB::select(DB::raw("SELECT wholi.employee_id,wholi.weekly_holiday_id,holi.id,holi.name as hname,holi.short_name as same FROM `weekly_holiday_assigns` as wholi 
                                                            JOIN holidays as holi ON holi.id=wholi.weekly_holiday_id WHERE wholi.employee_id='" . $emp_val->id . "' 
                                                            group by wholi.weekly_holiday_id"));
                                                        foreach($weekholiday as $holidays):
                                                            $holi++;
                                                            $holiday[$holi]=$holidays->weekly_holiday_id;
                                                        endforeach;
                                                        foreach ($leaves as $ls):
                                                            $orleaves[$ls->id]=$ls->id;
                                                            ${'totalShow-' . $ls->id}=0;
                                                            ${'totalAmount-' . $ls->id}=0;
                                                        endforeach;

                                                        $holyday_info = DB::select(DB::raw("SELECT total_holiday.id,total_holiday.name,total_holiday.short_name,total_holiday.start_date,total_holiday.end_date,
                                                        total_holiday.status FROM `holidays` as total_holiday 
                                                        WHERE total_holiday.start_date>='" . $sd . "' 
                                                        and total_holiday.end_date <='" . $ed . "'"));

                                                        $adjust_amount = 0;

                                                        $loan_info = DB::select(DB::raw("SELECT t_ln.* FROM `loans` as t_ln WHERE t_ln.employee_id='" . $emp_val->id . "' 
                                                        AND t_ln.loan_status='2'  and (t_ln.month_year ='" . $loan_start_month . "') "));
                                                        $total_loan_adjust_amount = 0;
                                                        if (!empty($loan_info)) {
                                                            $loan_adjust_info = array();
                                                            for ($k = 0; $k < count($loan_info); $k++) {
                                                                
                                                                //dd($loan_info[$k]->month_year);
                                                                if(strtotime(date('Y-m-01', strtotime($loan_info[$k]->month_year)))<=strtotime(date('Y-m-01', strtotime($salary_month)))) {
                                                                    //dd($loan_info[$k]['adjust_amount']);
                                                                    $total_loan_adjust_amount += $loan_info[$k]->adjust_amount;
                                                                    $loan_adjust_info['adjust_amount'][$k] = $loan_info[$k]->adjust_amount;
                                                                    $loan_adjust_info['loan_id'][$k] = $loan_info[$k]->id;
                                                                }else{
                                                                    $loan_info[$k]['adjust_amount'] = 0;
                                                                    $total_loan_adjust_amount += $loan_info[$k]->adjust_amount;
                                                                }
                                                            }
                                                        } else {
                                                            $total_loan_adjust_amount = 0;
                                                        }            

                                                        $start_date = strtotime(date('Y-m-d', strtotime('+1 day', strtotime($sd))));
                                                        $end_date = strtotime($ed);
                                                        $present = 0;
                                                        $absent = 0;
                                                        $leave = 0;
                                                        $t_ot_hour = 0;
                                                        $casual_leave = 0;                                       
                                                        $late_count = 0;
                                                        $early_leave = 0;
                                                        $total_working_hour = 0;
                                                        $total_ot_hour = 0;
                                                        $total_ot_day = 0;
                                                        $early_leave_count = 0;
                                                        $total_weekend = 0;                                        
                                                        if (!empty($daily_attendance)):
                                                            for ($i = $start_date; $i <= $end_date; $i += 86400):
                                                                $current_date = date('Y-m-d', $i);
                                                                $next_day = date('Y-m-d', strtotime('+1 day', strtotime($current_date)));
                                                                $previous_day = date('Y-m-d', strtotime('-1 day', strtotime($current_date)));
                                                                $in_time_with_date = '';
                                                                $out_time_with_date = '';
                                                                $present_flag = 0;
                                                                $holy_present_flag = 0;
                                                                $check_shift_night_status = 0;
                                                                $previous_check_shift_night_status = 0;
                                                                $next_check_shift_night_status = 0;
                                                                $check_shift_id = 0;
                                                                $out_time = '';
                                                                $in_time = '';
                                                                $weekend = 0;
                                                                $weekends = 0;
                                                                $total_holyday = 0;
                                                                $gross_salary = 0;
                                                                foreach ($daily_attendance as $rowp) {
                                                                    if ($rowp->date == $current_date) {
                                                                        $in_time_with_date = $rowp->in_time_str;
                                                                        $out_time_with_date = $rowp->out_time_str;
                                                                        $in_time_str = $rowp->in_time;
                                                                        $out_time_str = $rowp->out_time;
                                                                        $shift_start_time = $rowp->shift_start_time;
                                                                        $shift_end_time = $rowp->shift_end_time;
                                                                        $shift_name = $rowp->shift_name;
                                                                        $present_flag = 1;
                                                                    }
                                                                }
                                                                foreach ($daily_attendance as $rown) {
                                                                    if ($rown->date == $next_day) {
                                                                        $out_time_with_next_date = $rown->in_time_str;// because next date 1st min is the previous day out time
                                                                        $in_time_with_next_date = $rown->out_time_str;// because next date max time is the current day in time
                                                                        $next_day_in_time_str = $rown->in_time;
                                                                        $next_day_out_time_str = $rown->out_time;
                                                                        $next_day_shift_start_time = $rown->shift_start_time;
                                                                        $next_day_shift_end_time = $rown->shift_end_time;
                                                                        $next_day_shift_name = $rown->shift_name;
                                                                        //$present_flag = 1;
                                                                    }
                                                                }
                                                                foreach ($daily_attendance as $rowpre) {
                                                                    if ($rowpre->date == $previous_day) {
                                                                        $out_time_with_previous_date = $rowpre->in_time_str;// because next date 1st min is the previous day out time
                                                                        $in_time_with_previous_date = $rowpre->out_time_str;// because next date max time is the current day in time
                                                                        $previous_day_in_time_str = $rowpre->in_time;
                                                                        $previous_day_out_time_str = $rowpre->out_time;
                                                                        $previous_day_shift_start_time = $rowpre->shift_start_time;
                                                                        $previous_day_shift_end_time = $rowpre->shift_end_time;
                                                                        $previous_day_shift_name = $rowpre->shift_name;
                                                                        //$present_flag = 1;
                                                                    }
                                                                }
                                                                $leave_data = array();
                                                                foreach ($leave_info as $val) {
                                                                    $leave_exists = SalaryGenerate::getDatesFromRange($val->start_date, $val->end_date, $current_date);
                                                                    if ($leave_exists == true) {
                                                                        $leave_data = $val->leave_id;
                                                                    }
                                                                }

                                                                $holyday_data = array();
                                                                foreach ($holyday_info as $valhol) {
                                                                    $holyday_exists = SalaryGenerate::getDatesFromRange($valhol->start_date, $valhol->end_date, $current_date);
                                                                    if ($holyday_exists == true) {
                                                                        $holyday_data = $valhol->id;
                                                                    }
                                                                }
                                                                foreach ($shift_info as $shift_val) {
                                                                    $shift_exists = SalaryGenerate::getDatesFromRange($shift_val->start_date, $shift_val->end_date, $current_date);
                                                                    if ($shift_exists == true) {
                                                                        $check_shift_id = $shift_val->shift_id;
                                                                        $check_shift_start_time = $shift_val->start_time;
                                                                        $check_shift_end_time = $shift_val->end_time;
                                                                        $check_shift_night_status = $shift_val->night_status;
                                                                    }
                                                                    $shift_exists_next_day = SalaryGenerate::getDatesFromRange($shift_val->start_date, $shift_val->end_date, $next_day);
                                                                    if ($shift_exists_next_day == true) {
                                                                        $next_check_shift_id = $shift_val->shift_id;
                                                                        $next_check_shift_start_time = $shift_val->start_time;
                                                                        $next_check_shift_end_time = $shift_val->end_time;
                                                                        $next_check_shift_night_status = $shift_val->night_status;
                                                                    }
                                                                    $shift_exists_previous_day = SalaryGenerate::getDatesFromRange($shift_val->start_date, $shift_val->end_date, $previous_day);
                                                                    if ($shift_exists_previous_day == true) {
                                                                        $previous_check_shift_id = $shift_val->shift_id;
                                                                        $previous_check_shift_start_time = $shift_val->start_time;
                                                                        $previous_check_shift_end_time = $shift_val->end_time;
                                                                        $previous_check_shift_night_status = $shift_val->night_status;
                                                                    }
                                                                }
                                                                
                                                                if(count($holiday)){
                                                                    for($hl=1;$hl<=count($holiday);$hl++):
                                                                        if(date('N', $i)==$holiday[$hl]) {
                                                                            $weekends = date('N', $i);
                                                                            $weekend = 1;
                                                                        }
                                                                    endfor;
                                                                }
                                                                // if(date('N', $i)==$holiday[1] || date('N', $i) == $holiday[2]) {
                                                                //     $weekends = date('N', $i);
                                                                //     $weekend = 1;
                                                                // }

                                                                if (!empty($check_shift_id)) {
                                                                    if ($check_shift_night_status == 1) {
                                                                        // find current Out time
                                                                        $current_date_check_shift_start_time = strtotime($current_date . " " . date('H:i:s', strtotime($check_shift_start_time) - (60 * 60)));
                                                                        if (!empty($previous_check_shift_night_status) && $previous_check_shift_night_status == 1) {
                                                                            if ($out_time_with_date > $current_date_check_shift_start_time) { // if previous day shift is night shift then current date out time(max time) is the current day in time
                                                                                $in_time_date_with_shift = $out_time_with_date;
                                                                                $in_time = date('h:i:s a', $in_time_date_with_shift);
                                                                            } else {
                                                                                $in_time = '';
                                                                            }
                                                                        } else {
                                                                            if ($in_time_with_date > $current_date_check_shift_start_time) {
                                                                                $in_time_date_with_shift = $in_time_with_date;
                                                                                $in_time = date('h:i:s a', $in_time_date_with_shift);
                                                                            } else {
                                                                                $in_time = '';
                                                                            }
                                                                        }
                                                                    } else {
                                                                        $current_date_check_shift_start_time = strtotime($current_date . " " . date('H:i:s', strtotime($check_shift_start_time) - (60 * 60)));
                                                                        if (!empty($previous_check_shift_night_status) && $previous_check_shift_night_status == 1) {
                                                                            $get_current_day_second_in_time = SalaryGenerate::get_current_day_second_in_time('attendences', $emp_val->id, $current_date, $current_date_check_shift_start_time);

                                                                            if (!empty($get_current_day_second_in_time)) { // if previous day shift is night shift then current date out time(max time) is the current day in time
                                                                                $in_time_date_with_shift = $get_current_day_second_in_time;
                                                                                $in_time = date('h:i:s a', $in_time_date_with_shift);
                                                                            } else {
                                                                                $in_time = '';
                                                                            }
                                                                        } else {
                                                                            if (!empty($in_time_with_date)) { // if previous day shift is night shift then current date out time(max time) is the current day in time
                                                                                $in_time_date_with_shift = $in_time_with_date;
                                                                                $in_time = date('h:i:s a', $in_time_date_with_shift);
                                                                            } else {
                                                                                $in_time = '';
                                                                            }
                                                                        }
                                                                    }
                                                                } else {
                                                                    if (!empty($in_time_with_date))
                                                                        $in_time = date('h:i:s a', $in_time_with_date);
                                                                    else
                                                                        $in_time = '';
                                                                }

                                                                if (!empty($check_shift_id)) {
                                                                    if ($check_shift_night_status == 1) {
                                                                        ///find next day out time
                                                                        $current_date_check_shift_start_time = strtotime($current_date . " " . date('H:i:s', strtotime($check_shift_start_time) - (60 * 60)));
                                                                        $next_shift_start_time = strtotime($next_day . " " . date('H:i:s', strtotime($next_check_shift_start_time) - (60 * 60)));
                                                                        $current_date_time_before_mid_time = strtotime($next_day . " " . '12:00:00 am');
                    
                                                                        if (!empty($in_time) && ($in_time_with_date < $current_date_time_before_mid_time) && ($in_time_with_date > $current_date_check_shift_start_time)) {
                                                                        // **** $out_time_date_with_shift = $in_time_with_date; // if previous day shift is night shift then current date in time(min time) is the current day out time
                                                                            $out_time_date_with_shift = $out_time_with_next_date;
                                                                            $out_time = date('h:i:s a', $out_time_date_with_shift);
                                                                        } elseif (!empty($in_time) && !empty($out_time_with_next_date) && ($out_time_with_next_date > $current_date_time_before_mid_time) /*&& ($out_time_with_next_date <= $next_shift_start_time)*/) {
                                                                            $out_time_date_with_shift = $out_time_with_next_date;
                                                                            $out_time = date('h:i:s a', $out_time_date_with_shift);
                                                                        } else {
                                                                            $out_time = '';
                                                                        }
                                                                    } else {
                                                                        $current_date_check_shift_start_time = strtotime($current_date . " " . date('H:i:s', strtotime($check_shift_start_time) - (60 * 60)));
                                                                        //  if (!empty($previous_check_shift_night_status) && $previous_check_shift_night_status == 1) {
                                                                        if (!empty($in_time) && !empty($out_time_with_date)) { // if previous day shift is night shift then current date out time(max time) is the current day in time
                                                                            $out_time_date_with_shift = $out_time_with_date;
                                                                            $out_time = date('h:i:s a', $out_time_date_with_shift);
                                                                        } else {
                                                                            $out_time = '';
                                                                        }
                                                                        // }
                                                                    }
                                                                } else {
                                                                    if (!empty($in_time) && !empty($out_time_with_date)) {
                                                                        $out_time = date('h:i:s a', $out_time_with_date);
                                                                    } else {
                                                                        $out_time = '';
                                                                    }
                                                                }

                                                                //present Count
                                                                if (empty($in_time) && empty($out_time)) {
                                                                    $present_flag = 0;
                                                                }
                                                                if ($present_flag == 1 && empty($holyday_data) && $weekend == 0) {
                                                                    $present++;
                                                                }elseif($present_flag == 1 && !empty($holyday_data)){
                                                                        $total_holyday++;
                                                                        $present++;
                                                                        $holy_present_flag = 1;
                                                                }elseif($present_flag == 0 && !empty($holyday_data)){
                                                                        $total_holyday++;
                                                                } elseif ($present_flag == 0 && $weekend == 1) {
                                                                        $total_weekend++;
                                                                } elseif ($present_flag == 1 && $weekend == 1) {
                                                                        $total_holyday++;
                                                                        $present++;
                                                                        $holy_present_flag = 1;
                                                                } else {
                                                                    if (!empty($leave_data)) {
                                                                        if ($leave_data == $orleaves[$leave_data]) {
                                                                            ${'totalShow-' . $orleaves[$leave_data]}++;
                                                                        }
                                                                        $leave++;
                                                                    } else {
                                                                        $absent++;
                                                                    }
                                                                }

                                                                //late count
                                                                if ($present_flag != 0) {
                                                                    if (!empty($check_shift_id)) {
                                                                        if (!empty($check_shift_start_time) && !empty($in_time_date_with_shift)) {
                                                                            $shift_in_time = strtotime($current_date . " " . $check_shift_start_time);
                                                                            $late = $shift_in_time - $in_time_date_with_shift;
                                                                            if ($late < 0) {
                                                                                $late_count++;
                                                                                $late_hour = abs(ceil($late / (60 * 60)));
                                                                                $late_minute = abs(floor(($late / 60) % 60));
                                                                            }
                                                                        }
                                                                    } elseif (!empty($shift_start_time) && !empty($in_time_with_date)) {
                                                                        $shift_in_time = strtotime($current_date . " " . $shift_start_time);
                                                                        $late = $shift_in_time - $in_time_with_date;
                                                                        if ($late < 0) {
                                                                            $late_count++;
                                                                            $late_hour = abs(ceil($late / (60 * 60)));
                                                                            $late_minute = abs(floor(($late / 60) % 60));

                                                                        }
                                                                    }
                                                                }

                                                                
                                                                    //early leave count
                                                                    if ($present_flag == 1) {
                                                                        if (!empty($check_shift_id)) {
                                                                            if ($check_shift_night_status == 1) {
                                                                                $shift_out_time = strtotime($next_day . " " . $check_shift_end_time);
                                                                            } else {
                                                                                $shift_out_time = strtotime($current_date . " " . $check_shift_end_time);
                                                                            }
                                                                            if (!empty($check_shift_end_time) && !empty($out_time_date_with_shift)) {
                                                                                $early_leave = $shift_out_time - $out_time_date_with_shift;
                                                                                if ($early_leave > 0) {
                                                                                    $early_leave_count++;
                                                                                    $early_leave_hour = abs(floor($early_leave / (60 * 60)));
                                                                                    $early_leave_minute = abs(floor(($early_leave / 60) % 60));

                                                                                }
                                                                            } else {
                                                                                echo '';
                                                                            }
                                                                        } elseif (!empty($shift_end_time) && !empty($out_time_with_date)) {
                                                                            $shift_out_time = strtotime($current_date . " " . $shift_end_time);
                                                                            $early_leave = $shift_out_time - $out_time_with_date;
                                                                            if ($early_leave > 0) {
                                                                                $early_leave_count++;
                                                                                $early_leave_hour = abs(floor($early_leave / (60 * 60)));
                                                                                $early_leave_minute = abs(floor(($early_leave / 60) % 60));
                                                                            }
                                                                        }
                                                                    }

                                                                    //working count
                                                                    if ($present_flag == 1) {
                                                                        if (!empty($check_shift_id)) {
                                                                            $time_difference = $out_time_date_with_shift - $in_time_date_with_shift;
                                                                            $working_hour = floor($time_difference / (60 * 60));
                                                                            $working_minute = floor(($time_difference / 60) % 60);
                                                                            $working_hour_min = $working_hour . ":" . $working_minute;
                                                                            $total_working_hour += $time_difference;
                                                                        } else {
                                                                            $time_difference = $out_time_with_date - $in_time_with_date;
                                                                            $working_hour = floor($time_difference / (60 * 60));
                                                                            $working_minute = floor(($time_difference / 60) % 60);
                                                                            $working_hour_min = $working_hour . ":" . $working_minute;
                                                                            $total_working_hour += $time_difference;
                                                                        }

                                                                    } else {
                                                                        $working_hour_min = '';
                                                                    }

                                                                    //out count
                                                                    if ($present_flag == 1 && $holy_present_flag == 0) {
                                                                        if (!empty($check_shift_id)) {
                                                                            if ($check_shift_night_status == 1) {
                                                                                $shift_out_time = strtotime($next_day . " " . $check_shift_end_time);
                                                                            } else {
                                                                                $shift_out_time = strtotime($current_date . " " . $check_shift_end_time);
                                                                            }
                                                                            $ot_count = $out_time_date_with_shift - $shift_out_time;
                                                                            if ($ot_count > 0) {
                                                                                $total_ot_hour += $ot_count;
                                                                                $ot_hour = abs(floor($ot_count / (60 * 60)));
                                                                                $ot_minute = abs(floor(($ot_count / 60) % 60));
                                                                            }

                                                                        } else {
                                                                            if (!empty($shift_end_time) && !empty($out_time_with_date)) {
                                                                                $shift_out_time = strtotime($current_date . " " . $shift_end_time);
                                                                                $ot_count = $out_time_with_date - $shift_out_time;
                                                                                if ($ot_count > 0) {
                                                                                    $total_ot_hour += $ot_count;
                                                                                    $ot_hour = abs(floor($ot_count / (60 * 60)));
                                                                                    $ot_minute = abs(floor(($ot_count / 60) % 60));
                                                                                }
                                                                            }
                                                                        }
                                                                    }else if($present_flag == 1 && $holy_present_flag == 1){
                                                                        $total_ot_day += $total_holyday;
                                                                    }

                                                                endfor;
                                                            endif;
                                                    ?> 
                                                    <tr class="text-center">
                                                            <td><?php echo $sl++; ?></td>
                                                            <td>
                                                                <?php echo $emp_val->name . "-" . $emp_val->employee_id ?>
                                                                <input type="hidden" name="employee_code[]"
                                                                        value="<?php echo $emp_val->id; ?>"/>
                                                            </td>
                                                            <td>
                                                                <?php 
                                                                $designation = SalaryGenerate::getAnyRowInfos('designations','id',$emp_val->current_designation_id);
                                                                echo $designation->name ?>
                                                                <input type="hidden" name="designation[]"
                                                                        value="<?php echo $emp_val->current_designation_id; ?>"/>
                                                                <input type="hidden" name="department[]"
                                                                        value="<?php echo $emp_val->department_id; ?>"/>
                                                                <input type="hidden" name="division[]"
                                                                        value="<?php echo $emp_val->division_id; ?>"/>
                                                            </td>
                                                            <td>
                                                                <?php echo $emp_val->rate ?>
                                                                <input type="hidden" name="rate[]"
                                                                        value="<?php echo $emp_val->rate; ?>"/>
                                                            </td>
                                                            <td>

                                                                
                                                            <input type="hidden" name="allowance_amount[]"
                                                                        value="<?php if(!empty($allowanceAmount)){echo $allowanceAmount;}else{echo 0;} ?>">
                                                                <?php echo $gross_salary = ($emp_val->rate+$allowanceAmount); ?>
                                                                <input type="hidden" name="grossrate[]"
                                                                        value="<?php echo $gross_salary; ?>"/>
                                                            </td>
                                                            <td>
                                                                    <?php
                                                                    // if ($emp_val['attendance_type'] == 2) {
                                                                    //     if (!empty($attendance_info)) {
                                                                    //         echo $absent = $attendance_info->absent;
                                                                    //     }
                                                                    // } else {
                                                                    //     echo $absent;
                                                                    // }
                                                                    echo $absent;
                                                                    ?>
                                                                    <input type="hidden" name="absent[]" value="<?php echo $absent;
                                                                    $absent_amount = round(((($emp_val->rate) / $noDays) * $absent) * $company_condition['absent_condition']); ?>">
                                                                </td>
                                                                <td><?php echo $late_count; ?>
                                                                    <input type="hidden" name="late_count[]"
                                                                        value="<?php echo $late_count;
                                                                        $actual_late_count = ($late_count / 3);
                                                                        $actual_late_count_with_reminder = ($late_count % 3);
                                                                        //if ($actual_late_count_with_reminder == 0) {
                                                                        if ($actual_late_count >= 1) {
                                                                            $late_amount = round(((($emp_val->rate) / $noDays) * (int)$actual_late_count) * $company_condition['late_condition']);
                                                                        } else {
                                                                            $late_amount = 0;
                                                                        }
                                                                        ?>">
                                                                </td>
                                                                <td style="text-align: right;font-weight: bold"><?php
                                                                    //echo $actual_late_count_with_reminder.'<br>';
                                                                    echo $absent_late_amount = $absent_amount + $late_amount;
                                                                    //echo $absent_late_amount = $late_amount;
                                                                    $total_late_amount += $absent_late_amount;
                                                                    ?>
                                                                    <input type="hidden" name="total_absent_amount[]"
                                                                        value="<?= $absent_late_amount ?>"/>
                                                                </td>

                                                                <?php if (!$leaves->isEmpty()):
                                                                    foreach ($leaves as $value):?>
                                                                        <td>
                                                                        <?php 
                                                                        echo ${'totalShow-' . $value->id};
                                                                        if(!empty(${'totalShow-' . $value->id}))
                                                                            ${'totalAmount-' . $value->id} = round(((($emp_val->rate) / $noDays) * ${'totalShow-' . $value->id}) * $company_condition['casual_leave_condition']);
                                                                        $total_leave_amount += ${'totalAmount-' . $value->id}; 
                                                                                                                            
                                                                        ?>
                                                                        <input type="hidden" name="total_leave[]"
                                                                        value="<?= ${'totalShow-' . $value->id} ?>"/>
                                                                            
                                                                        </td>
                                                                    <?php endforeach;
                                                                            endif;
                                                                ?>
                                                                <td style="text-align: right;font-weight: bold"><?php echo ${'totalAmount-' . $value->id} ?>
                                                                    <input type="hidden" name="total_leave_amount[]"
                                                                        value="<?= ${'totalAmount-' . $value->id} ?>"/>
                                                                </td>
                                                                <td>
                                                                    <?php
                                                                    // if ($emp_val['attendance_type'] == 2) {
                                                                    //     if (!empty($attendance_info)) {
                                                                    //         echo $t_ot_hour = $attendance_info->ot_hour;
                                                                    //     }
                                                                    // } else {
                                                                        $t_ot_hour = floor($total_ot_hour / (60 * 60));
                                                                        $t_ot_minute = floor(($total_ot_hour / 60) % 60);
                                                                        echo $t_ot_hour;
                                                                    //}
                                                                    ?>

                                                                    <input type="hidden" name="total_ot_hour[]"
                                                                        value="<?php echo $t_ot_hour; ?>">
                                                                </td>
                                                                <td>
                                                                    <?php
                                                                    // if ($emp_val['attendance_type'] == 2) {
                                                                    //     if (!empty($attendance_info)) {
                                                                    //         echo $total_ot_day = $attendance_info->ot_day;
                                                                    //     }
                                                                    // } else {
                                                                        //$t_ot_hour = floor($total_ot_hour / (60 * 60));
                                                                        //$t_ot_minute = floor(($total_ot_hour / 60) % 60);
                                                                        echo $total_ot_day;
                                                                    //}
                                                                    ?>

                                                                    <input type="hidden" name="total_ot_day[]"
                                                                        value="<?php echo $total_ot_day; ?>">
                                                                </td>
                                                                <td style="text-align: right;font-weight: bold">
                                                                    <?php 
                                                                    
                                                                    $ot_amount = round((($emp_val->rate) / 208) * $t_ot_hour) + round((($emp_val->rate) / 208) * $total_ot_day);
                                                                    $total_ot_amount += $ot_amount;
                                                                    echo $ot_amount;
                                                                    ?>
                                                                    <input type="hidden" name="total_ot_amount[]"
                                                                        value="<?= $ot_amount ?>"/>
                                                                </td>

                                                                
                                                                <td><?= $total_loan_adjust_amount ?>
                                                                    <input type="hidden" name="adjusted_loan_amount[]"
                                                                        value="<?= $total_loan_adjust_amount ?>"/>
                                                                    <?php if ($total_loan_adjust_amount > 0) {
                                                                        $loan_count = count($loan_adjust_info['loan_id']);
                                                                        for ($j = 0; $j < $loan_count; $j++) {
                                                                            ?>
                                                                            <input type="hidden" name="loan_id_adjust_amount[]"
                                                                                value="<?php echo $loan_adjust_info['loan_id'][$j] . "-" . $loan_adjust_info['adjust_amount'][$j] ?>">
                                                                        <?php }
                                                                    } ?>
                                                                </td>
                                                                
                                                                <td style="text-align: right;font-weight: bold">                                                                
                                                                    <input type="hidden" name="deduction_amount[]"
                                                                        value="<?php if(!empty($deductionAmount)){echo $deductionAmount;}else{echo 0;} ?>">
                                                                <?php //$total_salary = $emp_val['gross_salary'] - ($absent_late_amount + $casual_leave_amount) + $ot_amount + $ad_amount;
                                                                    $total_salary = $gross_salary - ($absent_late_amount + $total_leave_amount + $deductionAmount + $total_loan_adjust_amount);
                                                                    ?>
                                                                    <?php if ($total_salary < 0)
                                                                        echo $total_salary = 0;
                                                                    else echo $total_salary ?>
                                                                    <input type="hidden" name="total_salary[]"
                                                                        value="<?php echo $total_salary;
                                                                        $grand_total_salary += $total_salary ?>">
                                                                </td>
                                                    </tr>                                   

                                                    <?php 
                                                        endforeach;
                                                        endif;
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-12 pt-5 text-center">
                                        <button type="button" class="btn btn-danger btn-sm mr-3"><i class="fas fa-sync-alt"></i>
                                            Reset</button>
                                        <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn" onclick="store_data()"><i class="fas fa-save"></i> Save</button>
                                    </div>
                                </div>
                        </form>
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
    
    function store_data() {
        let form = document.getElementById('salary-generate-form');
        let formData = new FormData(form);
        //alert(formData);
        let url = "{{route('salary.generate.store')}}";
        
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
                $('#salary-generate-form').find('.is-invalid').removeClass('is-invalid');
                $('#salary-generate-form').find('.error').remove();
                if (data.status == false) {
                    
                } else {
                    notification(data.status, data.message);
                    if (data.status == 'success') {
                        window.location.replace("{{ route('salary.generate') }}");

                    }
                }
            },
            error: function(xhr, ajaxOption, thrownError) {
                console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
            }
        });
    }
    </script>
@endpush
