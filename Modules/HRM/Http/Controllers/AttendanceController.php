<?php

namespace Modules\HRM\Http\Controllers;

use Illuminate\Http\Request;
use Modules\HRM\Entities\Shift;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\HRM\Entities\Division;
use Modules\HRM\Entities\Employee;
use Modules\HRM\Entities\Attendance;
use Modules\HRM\Entities\Department;
use Modules\HRM\Entities\Designation;
use Modules\HRM\Entities\EmployeeRoute;
use Modules\HRM\Entities\WeeklyHoliday;
use App\Http\Controllers\BaseController;
use Illuminate\Contracts\Support\Renderable;
use Modules\HRM\Entities\AllowanceDeduction;
use Modules\HRM\Http\Requests\AttendanceFormRequest;

class AttendanceController extends BaseController
{
    public function __construct(Attendance $model)
    {
        $this->model = $model;
    }
    
    public function index()
    {
        if (permission('attendance-access')) {

            $this->setPageData('Manage Employee Attendance', 'Manage Employee Attendance', 'fas fa-user-secret', [['name' => 'HRM', 'link' => 'javascript::void();'], ['name' => 'Manage Employee Attendance']]);
            $data = [
                'deletable' => self::DELETABLE,
                'employees'    => Employee::toBase()->where('status', 1)->get()
            ];
            return view('hrm::attendance.index', $data);
        } else {
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if (!empty($request->employee_id)) {
                $this->model->setEmployeeID($request->employee_id);
            }
            // if (!empty($request->start_date)) {
            //     $this->model->setStartDate($request->start_date);
            // }
            // if (!empty($request->end_date)) {
            //     $this->model->setEndDate($request->end_date);
            // }

            $this->set_datatable_default_properties($request);//set datatable default properties
            $list = $this->model->getDatatableList();//get table data
            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $action = '';
                if(permission('attendance-edit')){
                    $action .= ' <a class="dropdown-item edit_data" data-wallet-number="' . $value->wallet_number . '" data-id="' . $value->id . '">Save</a>';
                }

                $row = [];
                $row[] = $no;
                $row[] = $value->name.' - '.$value->phone;
                $row[] = $value->dname;
                $row[] = $value->dename;
                $row[] = ($value->in_time_str) ? date('h:i:s a', $value->in_time_str):'';
                $row[] = ($value->out_time_str) ? date('h:i:s a', $value->out_time_str):'';
                $row[] = action_button($action); //custom helper function for action button
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
            $this->model->count_filtered(), $data);
            
        }else{
            return response()->json($this->unauthorized());
        }
    }
    
    public function store_or_update_data(AttendanceFormRequest $request)
    {
        if($request->ajax()){
            if(permission('attendance-add')){
                //dd($request->all());
                $mDateTime = date('Y-m-d', strtotime($request->date)) . " " . $request->start_time;
                $ex = explode(" ", $request->start_time);
                $dataIn = array(
                    "employee_id" => $request->emp_id,
                    "wallet_number" => ($request->wallet_number) ? $request->wallet_number : '',
                    "date_time" => $mDateTime,
                    "date" => date('Y-m-d', strtotime($request->date)),
                    "time" => trim($ex[0] . ":" . "00"),
                    "time_str" => strtotime(date('Y-m-d', strtotime($request->date)) . " " . trim($ex[0] . ":" . "00")),
                    "time_str_am_pm" => strtotime(date('Y-m-d', strtotime($request->date)) . " " . trim($ex[0] . ":" . "00")." ".trim($ex[1])),
                    "am_pm" => trim($ex[1]),
                    'created_by' => auth()->user()->name
                );
                
                Attendance::insert($dataIn);
                $mDateTimeOut = date('Y-m-d', strtotime($request->date)) . " " . $request->end_time;
                $exOut = explode(" ", $request->end_time);
                $dataOut = array(
                    "employee_id" => $request->emp_id,
                    "wallet_number" => ($request->wallet_number) ? $request->wallet_number : '',
                    "date_time" => $mDateTimeOut,
                    "date" => date('Y-m-d', strtotime($request->date)),
                    "time" => trim($exOut[0] . ":" . "00"),
                    "time_str" => strtotime(date('Y-m-d', strtotime($request->date)) . " " . trim($exOut[0] . ":" . "00")),
                    "time_str_am_pm" => strtotime(date('Y-m-d', strtotime($request->date)) . " " . trim($exOut[0] . ":" . "00")." ".trim($exOut[1])),
                    "am_pm" => trim($exOut[1]),
                    'created_by' => auth()->user()->name
                );
                $result = Attendance::insert($dataOut);
                $output       = $this->store_message($result, $request->update_id);
                $this->model->flushCache();
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function delete(Request $request)
    {
        if ($request->ajax()) {
            if (permission('attendance-delete')) {
                $result = Attendance::where('employee_id', $request->id)->delete();
                $output   = $this->delete_message($result);
            } else {
                $output   = $this->unauthorized();
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function bulk_delete(Request $request)
    {
        if ($request->ajax()) {
            if (permission('attendance-bulk-delete')) {
                $result = Attendance::where('employee_id', $request->ids)->delete();
                $output   = $this->bulk_delete_message($result);
            } else {
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }
}
