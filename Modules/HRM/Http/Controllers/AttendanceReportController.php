<?php

namespace Modules\HRM\Http\Controllers;

use Illuminate\Http\Request;
use Modules\HRM\Entities\Leave;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\HRM\Entities\Employee;
use Modules\HRM\Entities\EmployeeRoute;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Validator;
use Modules\HRM\Entities\AttendanceReport;
use Illuminate\Contracts\Support\Renderable;

class AttendanceReportController extends BaseController
{
    public function __construct(AttendanceReport $model)
    {
        $this->model = $model;
    }
    
    public function index()
    {
        if (permission('attendance-report-access')) {

            if(empty($_GET['start_date']) && empty($_GET['end_date'])) {
                $start_date = date('Y-m-01');
                $data['start_date_current'] = $start_date;
                $data['start_date'] = date('Y-m-01', strtotime('-1 day', strtotime($start_date)));
                $data['end_date'] = date('Y-m-31');
             }

            $this->setPageData('Manage Employee Attendance Rerport', 'Manage Employee Attendance Report', 'fas fa-user-secret', [['name' => 'HRM', 'link' => 'javascript::void();'], ['name' => 'Manage Employee Attendance Report']]);
            $data = [
                'deletable' => self::DELETABLE,
                'employees'    => Employee::toBase()->where('status', 1)->get()
            ];
            return view('hrm::attendance-report.index', $data);
        } else {
            return $this->access_blocked();
        }
    }

    public function attendance_report(Request $request)
    {
        if (permission('attendance-report-access')) {
            $v = Validator::make($request->all(), [
                'start_date' => 'required',
                'end_date' => 'required',
                'employee_id' => 'required',
            ]);
            $this->setPageData('Manage Employee Attendance Rerport', 'Manage Employee Attendance Report', 'fas fa-user-secret', [['name' => 'HRM', 'link' => 'javascript::void();'], ['name' => 'Manage Employee Attendance Report']]);
            $data = [
                'deletable' => self::DELETABLE,
                'employees'    => Employee::toBase()->where('status', 1)->get()
            ];
            if ($v->fails()) {
                $data['start_date'] = date('Y-m-01');
                $data['end_date'] = date('Y-m-30');
                return view('hrm::attendance-report.index', $data);
            } else {
                $employee_id = $request->employee_id;
                $start_date = $request->start_date;
                $end_date = $request->end_date;
                $holiday=array();
                if (!empty($_GET['employee_id'])) {
                    $data['employee_id'] = $employee_id;
                    $registration_data =  AttendanceReport::getAnyRowInfos('employees','id',$employee_id);

                    $weekholiday = DB::table('weekly_holiday_assigns as wholi')
                    ->selectRaw("wholi.employee_id,wholi.weekly_holiday_id,holi.id,holi.name as hname,holi.short_name as same")
                    ->join('holidays as holi','weekly_holiday_id','=','holi.id')
                    ->where('wholi.employee_id',$employee_id)
                    ->groupBy('wholi.weekly_holiday_id','wholi.employee_id','holi.id','holi.name','holi.short_name')
                    ->get();

                    //dd($weekholiday);

                    // $weekholiday = DB::raw("SELECT wholi.employee_id,wholi.weekly_holiday_id,holi.id,holi.name as hname,holi.short_name as same FROM `weekly_holiday_assigns` as wholi 
                    // JOIN holidays as holi ON holi.id=wholi.weekly_holiday_id WHERE wholi.employee_id='" . $employee_id . "' 
                    // GROUP BY wholi.weekly_holiday_id");

                    $holi=0;
                    foreach($weekholiday as $holidays):
                        $holi++;
                        $holiday[$holi]=$holidays->weekly_holiday_id;
                    endforeach;

                 }

                if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
                    $start_date = $_GET['start_date'];
                    $data['start_date_current'] = $start_date;
                    $data['start_date'] = date('Y-m-d', strtotime('-1 day', strtotime($start_date)));

                    $end_date = $_GET['end_date'];
                    $data['end_date'] = $end_date;
                 }else {
                    $start_date = date('Y-m-01');
                    $data['start_date_current'] = $start_date;
                    $data['start_date'] = date('Y-m-d', strtotime('-1 day', strtotime($start_date)));
                    $data['end_date'] = date('Y-m-31');
                 }


                $latestAttendance = DB::table('attendances')
                    ->select('employee_id',DB::raw("MIN(id) as min_id,MAX(id) as max_id,MIN(time_str_am_pm) as in_time_str,MAX(time_str_am_pm) as out_time_str,
                    MIN(time) as in_time,MAX(time) as out_time"),'date')
                    ->where('date','>=',$data['start_date'])
                    ->where('date','<=',$end_date)
                    ->groupBy('employee_id','date');
                    
                $attendance = DB::table('employees')
                ->selectRaw("employees.id,employees.name as employee_name,employees.phone,attendance.*,shift.start_time as shift_start_time,shift.end_time as shift_end_time,shift.name as shift_name")
                ->join('shifts as shift','employees.shift_id','=','shift.id')
                ->leftjoinSub($latestAttendance, 'attendance', function ($join) {
                    $join->on('employees.id', '=', 'attendance.employee_id');
                })
                ->where('employees.id',$employee_id)
                ->get();

                $leave_data = DB::table('leave_application_manages')
                ->selectRaw("employee_id,start_date,end_date,leave_id,leave_status")
                ->where('employee_id',$employee_id)
                ->where('start_date','>=',$data['start_date'])
                ->where('end_date','<=',$end_date)
                ->get();

                $shift_data = DB::table('shift_manages as change_shift')
                ->selectRaw("change_shift.shift_id,shift.start_time,shift.end_time,shift.night_status,change_shift.start_date,change_shift.end_date")
                ->join('shifts as shift','change_shift.shift_id','=','shift.id')
                ->where('change_shift.employee_id',$employee_id)
                ->where('change_shift.start_date','>=',$data['start_date'])
                ->where('change_shift.end_date','<=',$end_date)
                ->get();

                $holyday_data = DB::table('holidays as total_holiday')
                ->selectRaw("total_holiday.name,total_holiday.short_name,total_holiday.start_date,total_holiday.end_date,total_holiday.status")
                ->where('total_holiday.start_date','>=',$data['start_date'])
                ->where('total_holiday.end_date','<=',$end_date)
                ->get();

                $orleaves=array();
                $orleavesName=array();
                $leaves = Leave::activeLeaves();
                foreach($leaves as $l):
                    $orleaves[$l->id]=$l->id;
                    $orleavesName[$l->id]=$l->name;
                endforeach;

                $data['daily_attendance'] = $attendance;
                $data['orleaves'] = $orleaves;
                $data['orleavesName'] = $orleavesName;
                $data['leave_info'] = $leave_data;
                $data['shift_info'] = $shift_data;
                $data['holyday_info'] = $holyday_data;
                $data['holiday'] = $holiday;
                $data['default_shift_name'] = AttendanceReport::getAnyRowInfos('shifts', 'id', $registration_data->shift_id);
                return view('hrm::attendance-report.attendance-report', $data);
            }
        } else {
            return $this->access_blocked();
        }
    }


}
