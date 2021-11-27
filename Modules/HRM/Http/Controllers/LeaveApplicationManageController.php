<?php

namespace Modules\HRM\Http\Controllers;

use Illuminate\Http\Request;
use Modules\HRM\Entities\Leave;
use Illuminate\Support\Facades\DB;
use Modules\HRM\Entities\Employee;
use App\Http\Controllers\BaseController;
use Modules\HRM\Entities\LeaveApplicationManage;
use Modules\HRM\Http\Requests\LeaveApplicationManageRequest;

class LeaveApplicationManageController extends BaseController
{
    protected const SUBMISSIONS = ['1' => 'Pre', '2' => 'post'];
    public function __construct(LeaveApplicationManage $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('leave-application-access')) {
            $this->setPageData('Manage Leave Application', 'Manage Leave Application', 'fas fa-user-secret', [['name' => 'HRM', 'link' => 'javascript::void();'], ['name' => 'Manage Leave Application']]);

            $leaves = Leave::toBase()->where('status', 1)->get();
            $employees  = Employee::toBase()->where('status', 1)->get();
            $deletable = self::DELETABLE;
            $submissions = self::SUBMISSIONS;
            // $data = [
            //     'leaves'  => Leave::toBase()->where('status',1)->get(),
            //     'employees'  => Employee::toBase()->where('status',1)->get(),
            //     'deletable' => self::DELETABLE,
            //     'submissions' => self::SUBMISSION,
            // ];
            //return view('hrm::leave-application.index',$data);
            return view('hrm::leave-application.index', compact('leaves', 'employees', 'deletable', 'submissions'));
        } else {
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax()) {

            if (!empty($request->employee_id)) {
                $this->model->setEmployeeID($request->employee_id);
            }
            if (!empty($request->leave_id)) {
                $this->model->setLeaveID($request->leave_id);
            }
            if (!empty($request->submission)) {
                $this->model->setSubmissionID($request->submission);
            }
            if (!empty($request->status)) {
                $this->model->setStatus($request->status);
            }

            $this->set_datatable_default_properties($request); //set datatable default properties
            $list = $this->model->getDatatableList(); //get table data
            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $action = '';
                if (permission('leave-application-edit')) {
                    $action .= ' <a class="dropdown-item edit_data" href="' . url('leave.application/edit', $value->id) . '">' . self::ACTION_BUTTON['Edit'] . '</a>';
                }
                if (permission('leave-application-view')) {
                    $action .= ' <a class="dropdown-item view_data" href="' . url('leave.application/details', $value->id) . '">' . self::ACTION_BUTTON['View'] . '</a>';
                }
                if (permission('leave-application-delete')) {
                    $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->name . '">' . self::ACTION_BUTTON['Delete'] . '</a>';
                }


                $row = [];
                if (permission('leave-application-bulk-delete')) {
                    $row[] = row_checkbox($value->id); //custom helper function to show the table each row checkbox
                }
                $row[] = $no;
                $row[] = $value->employee->name;
                $row[] = $value->leaves->name;
                $row[] = $value->start_date;
                $row[] = $value->end_date;
                $row[] = $value->number_leave;
                $row[] = $value->purpose;
                $row[] = PRE_POST_LABEL[$value->submission];
                $row[] = PAID_UNPAID_LABEL[$value->leave_type];
                $row[] = permission('leave-application-edit') ? change_status($value->id, $value->status, $value->leaves->name) : STATUS_LABEL[$value->status];
                $row[] = action_button($action); //custom helper function for action button
                $data[] = $row;
            }
            return $this->datatable_draw(
                $request->input('draw'),
                $this->model->count_all(),
                $this->model->count_filtered(),
                $data
            );
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function store_or_update_data(LeaveApplicationManageRequest $request)
    {
        if ($request->ajax()) {
            if (permission('leave-application-add')) {
                $collection   = collect($request->validated());
                $collection   = $this->track_data($collection, $request->update_id);
                $result       = $this->model->updateOrCreate(['id' => $request->update_id], $collection->all());
                $output       = $this->store_message($result, $request->update_id);
                $this->model->flushCache();
            } else {
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }


    public function create()
    {
        if (permission('leave-application-add')) {
            $this->setPageData('Apply Leave Application', 'Apply Leave Application', 'fas fa-user-secret', [['name' => 'HRM', 'link' => 'javascript::void();'], ['name' => 'Apply Leave Application']]);
            $data = [
                'leaves'  => Leave::toBase()->where('status', 1)->get(),
                'employees'  => Employee::toBase()->where('status', 1)->get(),
                'deletable' => self::DELETABLE,
                'submissions' => self::SUBMISSIONS,
            ];
            return view('hrm::leave-application.form', $data);
        } else {
            return $this->access_blocked();
        }
    }



    public function edit(int $id)
    {

        if (permission('leave-application-edit')) {
            $this->setPageData('Edit Leave Application', 'Edit Leave Application', 'fas fa-user-secret', [['name' => 'HRM', 'link' => 'javascript::void();'], ['name' => 'Edit Leave Application']]);
            $leaveapplication = $this->model->findOrFail($id);
            $data = [
                'leaveapplication'     => $leaveapplication,
                'leaves'  => Leave::toBase()->where('status', 1)->get(),
                'employees'  => Employee::toBase()->where('status', 1)->get(),
                'deletable' => self::DELETABLE,
                'submissions' => self::SUBMISSIONS,
            ];

            return view('hrm::leave-application.form', $data);
        } else {
            return $this->access_blocked();
        }
    }

    public function delete(Request $request)
    {
        if($request->ajax()){
            if(permission('leave-application-delete')){
                $result   = $this->model->find($request->id)->delete();
                $output   = $this->delete_message($result);
                $this->model->flushCache();
            }else{
                $output   = $this->unauthorized();

            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }
    

    public function bulk_delete(Request $request)
    {
        if($request->ajax()){
            if(permission('leave-application-bulk-delete')){
                $result   = $this->model->destroy($request->ids);
                $output   = $this->bulk_delete_message($result);
                $this->model->flushCache();
            }else{
                $output   = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function change_status(Request $request)
    {
        if($request->ajax()){
            if(permission('leave-application-edit')){
                $result   = $this->model->find($request->id)->update(['status' => $request->status]);
                $output   = $result ? ['status' => 'success','message' => 'Status Has Been Changed Successfully']
                : ['status' => 'error','message' => 'Failed To Change Status'];
                $this->model->flushCache();
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }
    public function employee_id_wise_leave_list(int $id)
    {
        $originalstartDate = date("Y");
        $start = $originalstartDate . "-" . "01" . "-" . "01";
        $end = $originalstartDate . "-" . "12" . "-" . "31";
        //dd($id);
        $leavealist = DB::table('leave_application_manages as leavem')
            ->selectRaw('leavem.*,sum(leavem.number_leave) as sumleave, leave.name as name, leave.short_name as sname, leave.number as lnumber')
            ->join('leaves as leave', 'leavem.leave_id', '=', 'leave.id')
            ->where('employee_id', '=', $id)
            ->where('leavem.status', '=', '1')
            ->where('start_date', '>=', $start)
            ->where('end_date', '<=', $end)
            ->groupBy('leavem.leave_id')
            ->get();

        $res["list"] = array();
        foreach ($leavealist as $val) {
            $post = array();
            $post["name"] = $val->name . '(' . $val->sname . ')';
            $post["lnumber"] = $val->lnumber;
            $post["sumleave"] = $val->sumleave;
            array_push($res["list"], $post);
        }
        return json_encode($res);
    }

    public function leave_type_wise_leave(int $id)
    {
        $leaveType = Leave::selectRaw('leave_type')
            ->where('id', '=', $id)
            ->first();
        return json_encode($leaveType->leave_type);
    }
}
