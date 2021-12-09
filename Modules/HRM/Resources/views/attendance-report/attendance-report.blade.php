@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link href="plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
<link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
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
                                    <option value="{{ $value->id }}" <?php if($value->id == $_GET['employee_id']){ echo "selected=selected";}?>>{{ $value->name.' - '.$value->employee_id  }}</option>
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
                                            <p style="font-weight: normal;margin:0;"><b>Date: </b>@if (!empty($start_date)) {{ date('d-F-Y', strtotime($start_date_current))}} to {{date('d-F-Y', strtotime($end_date))}} @endif</p>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-12">  
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
                                <?php
                                $start_date = strtotime($start_date);
                                $end_date = strtotime($end_date);
                                $start_date_current = strtotime($start_date_current);
                                $present = 0;
                                $absent = 0;
                                $leave = 0;
                                $late_count = 0;
                                $early_leave = 0;
                                $total_working_hour = 0;
                                $total_ot_hour = 0;
                                $total_ot_day = 0;
                                $early_leave_count = 0;
                                $total_weekend = 0;
                                
                                for ($i = $start_date_current; $i <= $end_date; $i += 86400) {    
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
                                        $leave_exists = Modules\HRM\Entities\AttendanceReport::getDatesFromRange($val->start_date, $val->end_date, $current_date);
                                        if ($leave_exists == true) {
                                            $leave_data = $val->leave_id;
                                        }
                                    }
                                    $holyday_data = array();
                                    foreach ($holyday_info as $valhol) {
                                        $holyday_exists = Modules\HRM\Entities\AttendanceReport::getDatesFromRange($valhol->start_date, $valhol->end_date, $current_date);
                                        if ($holyday_exists == true) {
                                            $holyday_data = $valhol->name;
                                        }
                                    }

                                    foreach ($shift_info as $shift_val) {
                                        $shift_exists = Modules\HRM\Entities\AttendanceReport::getDatesFromRange($shift_val->start_date, $shift_val->end_date, $current_date);
                                        if ($shift_exists == true) {
                                            $check_shift_id = $shift_val->shift_id;
                                            $check_shift_start_time = $shift_val->start_time;
                                            $check_shift_end_time = $shift_val->end_time;
                                            $check_shift_night_status = $shift_val->night_status;
                                        }
                                        $shift_exists_next_day = Modules\HRM\Entities\AttendanceReport::getDatesFromRange($shift_val->start_date, $shift_val->end_date, $next_day);
                                        if ($shift_exists_next_day == true) {
                                            $next_check_shift_id = $shift_val->shift_id;
                                            $next_check_shift_start_time = $shift_val->start_time;
                                            $next_check_shift_end_time = $shift_val->end_time;
                                            $next_check_shift_night_status = $shift_val->night_status;
                                        }
                                        $shift_exists_previous_day = Modules\HRM\Entities\AttendanceReport::getDatesFromRange($shift_val->start_date, $shift_val->end_date, $previous_day);
                                        if ($shift_exists_previous_day == true) {
                                            $previous_check_shift_id = $shift_val->shift_id;
                                            $previous_check_shift_start_time = $shift_val->start_time;
                                            $previous_check_shift_end_time = $shift_val->end_time;
                                            $previous_check_shift_night_status = $shift_val->night_status;
                                        }
                                    }
                                    //dd($holiday);
                                    if(count($holiday)){
                                        for($hl=1;$hl<=count($holiday);$hl++):
                                            if(date('N', $i)==$holiday[$hl]) {
                                                $weekends = date('N', $i);
                                                $weekend = 1;
                                            }
                                        endfor;
                                    }
                                    ?>
                                 <tr>
                                    <td class="text-nowrap"><?= date('Y-m-d', $i); ?></td>
                                    <td  class="text-nowrap" <?php if (date('N', $i) == $weekends){ ?>style="color: red;font-weight: bold"<?php } ?>>
                                            <?= date('l', $i); ?>
                                    </td>

                                    <td class="text-nowrap"><!-- In Time Time Count --->
                                            <?php
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
                                                        $get_current_day_second_in_time = $this->common->get_current_day_second_in_time('attendences', $em_code, $current_date, $current_date_check_shift_start_time);

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
                                            echo $in_time;
                                            ?>
                                        </td>
                                        <td class="text-nowrap"><!-- Out Time Count --->
                                            <?php
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
                                            echo $out_time;
                                            ?>
                                        </td>
                                        <td class="text-nowrap">                                            
                                        <?php
                                            if (!empty($check_shift_id)) {
                                                $check_shift_info = Modules\HRM\Entities\AttendanceReport::getAnyRowInfos('shifts', 'id', $check_shift_id);
                                                echo $check_shift_info->name . "(" . date('h:i:s a', strtotime($check_shift_info->start_time)) . "-" . date('h:i:s a', strtotime($check_shift_info->end_time)) . ")";
                                            } else {
                                                if (!empty($default_shift_name)) {
                                                    echo $default_shift_name->name . "(" . date('h:i:s a', strtotime($default_shift_name->start_time)) . "-" . date('h:i:s a', strtotime($default_shift_name->end_time)) . ")";
                                                }
                                            } ?>
                                        </td>

                                        <td class="text-nowrap"><!-- Early Leave Count --->
                                            <?php
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
                                                            echo '<span style="color:red;text-align:center;">Early Leave (' . $early_leave_hour . ":" . $early_leave_minute . ')</span>';
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
                                                        echo '<span style="color:red;text-align:center;">Early Leave (' . $early_leave_hour . ":" . $early_leave_minute . ')</span>';
                                                    } else {
                                                        echo '';
                                                    }
                                                }
                                            }
                                            ?>
                                        </td>
                                        <td class="text-nowrap"> <!-- Late Count --->
                                            <?php
                                            if ($present_flag != 0) {
                                                if (!empty($check_shift_id)) {
                                                    if (!empty($check_shift_start_time) && !empty($in_time_date_with_shift)) {
                                                        $shift_in_time = strtotime($current_date . " " . $check_shift_start_time);
                                                        $late = $shift_in_time - $in_time_date_with_shift;
                                                        if ($late < 0) {
                                                            $late_count++;
                                                            $late_hour = abs(ceil($late / (60 * 60)));
                                                            $late_minute = abs(floor(($late / 60) % 60));
                                                            echo '<span style="color:red;text-align:center;">Late (' . $late_hour . ":" . $late_minute . ')</span>';
                                                        }
                                                    }
                                                } elseif (!empty($shift_start_time) && !empty($in_time_with_date)) {
                                                    $shift_in_time = strtotime($current_date . " " . $shift_start_time);
                                                    $late = $shift_in_time - $in_time_with_date;
                                                    if ($late < 0) {
                                                        $late_count++;
                                                        $late_hour = abs(ceil($late / (60 * 60)));
                                                        $late_minute = abs(floor(($late / 60) % 60));
                                                        echo '<span style="color:red;text-align:center;">Late (' . $late_hour . ":" . $late_minute . ')</span>';
                                                    }
                                                }
                                            }

                                            ?>
                                        </td>
                                        <td class="text-nowrap"><!-- Working Count --->
                                            <?php
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
                                            echo $working_hour_min;
                                            ?>
                                        </td>
                                        <td class="text-nowrap"><!-- Out Count --->
                                            <?php
                                            
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
                                                        echo '<span style="color:blueviolet;text-align:center;font-weight: bold">' . $ot_hour . ":" . $ot_minute . ' (Hour)</span>';
                                                    }

                                                } else {
                                                    if (!empty($shift_end_time) && !empty($out_time_with_date)) {
                                                        $shift_out_time = strtotime($current_date . " " . $shift_end_time);
                                                        $ot_count = $out_time_with_date - $shift_out_time;
                                                        if ($ot_count > 0) {
                                                            $total_ot_hour += $ot_count;
                                                            $ot_hour = abs(floor($ot_count / (60 * 60)));
                                                            $ot_minute = abs(floor(($ot_count / 60) % 60));
                                                            echo '<span style="color:blueviolet;text-align:center;font-weight: bold">' . $ot_hour . ":" . $ot_minute . ' (Hour)</span>';
                                                        }
                                                    }
                                                }
                                            }else if($present_flag == 1 && $holy_present_flag == 1){
                                                        $total_ot_day += $total_holyday;
                                                        echo '<span style="color:blueviolet;text-align:center;font-weight: bold">' . $total_holyday . ":" . 00 . ' (Day)</span>';
                                            }
                                            ?>
                                        </td>
                                        <td><!-- Present Count --->
                                            <?php if (empty($in_time) && empty($out_time)) {
                                                $present_flag = 0;
                                            }  
                                            
                                                if ($present_flag == 1 && empty($holyday_data) && $weekend == 0) {
                                                    echo '<span style="color:green;text-align:center;font-weight: bold">Present</span>';
                                                    $present++;
                                                }elseif($present_flag == 0 && !empty($holyday_data)){
                                                    echo '<span style="color:blue;text-align:center;font-weight: bold">'.$holyday_data.'</span>';
                                                    $total_holyday++;
                                                }elseif($present_flag == 1 && !empty($holyday_data)){
                                                    echo '<span style="color:blue;text-align:center;font-weight: bold">'.$holyday_data.'</span> (<span style="color:green;text-align:center;font-weight: bold">Present</span>)';
                                                    $total_holyday++;
                                                    $present++;
                                                    $holy_present_flag = 1;
                                                } elseif ($present_flag == 1 && $weekend == 1){
                                                        echo '<span style="color:red;text-align:center;font-weight: bold">Weekly Holiday</span> (<span style="color:green;text-align:center;font-weight: bold">Present</span>)';
                                                        $total_weekend++;
                                                        $total_holyday++;
                                                        $present++;
                                                        $holy_present_flag = 1;
                                                } elseif ($present_flag == 0 && $weekend == 1){
                                                        echo '<span style="color:red;text-align:center;font-weight: bold">Weekly Holiday</span>';
                                                        $total_weekend++;
                                                } else {
                                                    if (!empty($leave_data)) {
                                                        if ($leave_data == $orleaves[$leave_data]):?>
                                                            <span style="color:blue;text-align:center;font-weight: bold">{{$orleavesName[$leave_data]}}</span>
                                                       <?php endif;
                                                        $leave++;
                                                    }else {
                                                        echo '<span style="color:red;text-align:center;font-weight: bold">Absent</span>';
                                                        $absent++;
                                                    }
                                            }
                                            ?>
                                        </td>
                                </tr>
                                <?php }?>                                
                                </tbody>
                                <tfoot style="font-wight:bold:color:green;">
                                <tr>
                                    <td colspan="7"></td>
                                    <td colspan="2" class="text-center">Total Present</td>
                                    <td class="text-center"><?= $present ?></td>
                                </tr>
                                <tr>
                                    <td colspan="7"></td>
                                    <td colspan="2"  class="text-center">Total Absent</td>
                                    <td class="text-center"><?= $absent ?></td>
                                </tr>
                                <tr>
                                    <td colspan="7"></td>
                                    <td colspan="2"  class="text-center">Total Late</td>
                                    <td class="text-center"><?= $late_count ?></td>
                                </tr>
                                <tr>
                                    <td colspan="7"></td>
                                    <td colspan="2"  class="text-center">Total Leave</td>
                                    <td class="text-center"><?= $leave ?></td>
                                </tr>
                                <tr>
                                    <td colspan="7"></td>
                                    <td colspan="2"  class="text-center">Total Early Leave</td>
                                    <td class="text-center"><?= $early_leave_count ?></td>
                                </tr>
                                <tr>
                                    <td colspan="7"></td>
                                    <td colspan="2"  class="text-center">Total Working Hour</td>
                                    <td class="text-center"><?php $w_hour = floor($total_working_hour / (60 * 60));
                                        $w_minute = floor(($total_working_hour / 60) % 60);
                                        echo $w_hour . ":" . $w_minute; ?></td>
                                </tr>
                                <tr>
                                    <td colspan="7"></td>
                                    <td colspan="2"  class="text-center">Total OT Hour</td>
                                    <td class="text-center"><?php $t_ot_hour = floor($total_ot_hour / (60 * 60));
                                        $t_ot_minute = floor(($total_ot_hour / 60) % 60);
                                        echo $t_ot_hour . ":" . $t_ot_minute;  ?></td>
                                </tr>
                                <tr>
                                    <td colspan="7"></td>
                                    <td colspan="2"  class="text-center">Total OT Day</td>
                                    <td class="text-center"><?=$total_ot_day?></td>
                                </tr>
                                <tr>
                                    <td colspan="7"></td>
                                    <td colspan="2"  class="text-center">Total Weekend</td>
                                    <td class="text-center"><?=$total_weekend?></td>
                                </tr>
                                </tfoot>
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
<script src="plugins/custom/datatables/datatables.bundle.js" type="text/javascript"></script>
<script src="js/moment.js"></script>
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
