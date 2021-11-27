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
                    $weekholiday = DB::select(DB::raw("SELECT wholi.employee_id,wholi.weekly_holiday_id,holi.id,holi.name as hname,holi.short_name as same FROM `weekly_holiday_assigns` as wholi 
                    JOIN holidays as holi ON holi.id=wholi.weekly_holiday_id WHERE wholi.employee_id='" . $employee_id . "' 
                    group by wholi.weekly_holiday_id"));

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

                $attendance = DB::select(DB::raw("SELECT MIN(att.id),MAX(att.id),MIN(att.time_str_am_pm) as in_time_str,MAX(att.time_str_am_pm) as out_time_str,MIN(att.time) as in_time,MAX(att.time) as out_time,
                att.employee_id,att.date_time,att.date,att.time,att.am_pm,att.time_str,
                att.time_str_am_pm,reg.shift_id,shift.start_time as shift_start_time,shift.end_time as shift_end_time,shift.name as shift_name FROM `attendances` as att 
                LEFT JOIN employees as reg ON reg.id=att.employee_id JOIN shifts as shift ON shift.id=reg.shift_id WHERE att.date >='" . $data['start_date'] . "' 
                AND att.date <='" . $end_date . "' AND att.employee_id='" . $employee_id . "' 
                group by att.date,att.employee_id")); 
                
                $leave_data = DB::select(DB::raw("SELECT employee_id,start_date,end_date,leave_id,leave_status FROM `leave_application_manages`
                WHERE employee_id='" . $employee_id . "' AND start_date >='" . $data['start_date'] . "' 
                AND end_date <='" . $end_date . "'"));
                
                $shift_data = DB::select(DB::raw("SELECT change_shift.shift_id,shift.start_time,shift.end_time,shift.night_status,change_shift.start_date,change_shift.end_date FROM `shift_manages` as change_shift 
                JOIN `shifts` as shift on shift.id=change_shift.shift_id WHERE change_shift.employee_id='" . $employee_id . "' 
                and change_shift.start_date >='" . $data['start_date'] . "' AND change_shift.end_date <='" . $end_date . "'"));

                $holyday_data = DB::select(DB::raw("SELECT total_holiday.name,total_holiday.short_name,total_holiday.start_date,total_holiday.end_date,
                total_holiday.status FROM `holidays` as total_holiday 
                WHERE total_holiday.start_date>='" . $data['start_date'] . "' 
                and total_holiday.end_date <='" . $end_date . "'"));
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
