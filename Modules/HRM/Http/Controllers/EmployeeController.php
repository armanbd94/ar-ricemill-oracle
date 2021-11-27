<?php

namespace Modules\HRM\Http\Controllers;

use Exception;
use App\Models\Warehouse;
use App\Traits\UploadAble;
use Illuminate\Http\Request;
use Modules\HRM\Entities\Shift;
use Illuminate\Support\Facades\DB;
use Modules\HRM\Entities\Division;
use Modules\HRM\Entities\Employee;
use Modules\HRM\Entities\Department;
use Modules\HRM\Entities\Designation;
use Modules\HRM\Entities\ShiftManage;
use Modules\HRM\Entities\WeeklyHoliday;
use App\Http\Controllers\BaseController;
use Modules\HRM\Entities\EmployeeEducation;
use Modules\Account\Entities\ChartOfAccount;
use Modules\HRM\Entities\AllowanceDeduction;
use Modules\HRM\Entities\WeeklyHolidayAssign;
use Modules\HRM\Http\Requests\EmployeeFormRequest;
use Modules\HRM\Entities\EmployeeProfessionalInformation;

class EmployeeController extends BaseController
{
    use UploadAble;
    public function __construct(Employee $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('employee-access')) {
            $this->setPageData('Manage Employee', 'Manage Employee', 'fas fa-user-secret', [['name' => 'HRM', 'link' => 'javascript::void();'], ['name' => 'Manage Employee']]);
            $data = [
                'departments'  => Department::activeDepartments(),
                'divisions'    => Division::activeDivisions(),
                'designations' => Designation::activeDesignations(),
                'shifts' =>     Shift::activeShifts(),
                'weeklyholiday' => WeeklyHoliday::toBase()->where('status', 1)->where('holiday_type', 1)->get(),
                'employees'    => $this->model->toBase()->where('status', 1)->get(),
                'allowances'  => AllowanceDeduction::toBase()->where('status', 1)->where('type', 1)->get(),
                'deducts'  => AllowanceDeduction::toBase()->where('status', 1)->where('type', 2)->get(),
            ];
            return view('hrm::employee.index', $data);
        } else {
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax()) {
            if (!empty($request->name)) {
                $this->model->setName($request->name);
            }

            if (!empty($request->phone)) {
                $this->model->setPhone($request->phone);
            }
            if (!empty($request->email)) {
                $this->model->setEmail($request->email);
            }
            if (!empty($request->employee_id)) {
                $this->model->setEmployeeID($request->employee_id);
            }
            if (!empty($request->department_id)) {
                $this->model->setDepartmentID($request->department_id);
            }
            if (!empty($request->division_id)) {
                $this->model->setDivisionID($request->division_id);
            }
            if (!empty($request->designation_id)) {
                $this->model->setDesignationID($request->designation_id);
            }
            if (!empty($request->supervisor_id)) {
                $this->model->setSupervisorID($request->supervisor_id);
            }
            if (!empty($request->job_status)) {
                $this->model->setJobStatus($request->job_status);
            }
            if (!empty($request->duty_type)) {
                $this->model->setDutyType($request->duty_type);
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
                if (permission('employee-edit')) {
                    $action .= ' <a class="dropdown-item edit_data" href="' . url('employee/edit', $value->id) . '">' . self::ACTION_BUTTON['Edit'] . '</a>';
                }
                if (permission('employee-view')) {
                    //$action .= ' <a class="dropdown-item view_data" href="' . url('employee/details', $value->id) . '">' . self::ACTION_BUTTON['View'] . '</a>';
                }
                if (permission('employee-delete')) {
                    $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->name . '">' . self::ACTION_BUTTON['Delete'] . '</a>';
                }


                $row = [];
                if (permission('employee-bulk-delete')) {
                    $row[] = row_checkbox($value->id); //custom helper function to show the table each row checkbox
                }
                $row[] = $no;
                $row[] = $this->table_image(EMPLOYEE_IMAGE_PATH, $value->photograph, $value->name, (int)$value->gender);
                $row[] = $value->employee_id;
                $row[] = $value->name;
                $row[] = $value->phone;
                $row[] = $value->email ? $value->email : '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">No Email</span>';
                $row[] = $value->nid_no;
                $row[] = $value->current_designation->name;
                $row[] = $value->department->name;
                $row[] = $value->division->name;
                $row[] = $value->supervisor->name;
                $row[] = number_format($value->rate, 2);
                $row[] = JOB_STATUS[$value->job_status];
                $row[] = DUTY_TYPE[$value->duty_type];
                $row[] = BLOOD_GROUP[$value->blood_group];
                $row[] = permission('employee-edit') ? change_status($value->id, $value->status, $value->name) : STATUS_LABEL[$value->status];
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

    public function create()
    {
        if (permission('employee-add')) {
            $this->setPageData('Add Employee', 'Add Employee', 'fas fa-user-secret', [['name' => 'HRM', 'link' => 'javascript::void();'], ['name' => 'Add Employee']]);
            $data = [
                'departments'  => Department::toBase()->where('status', 1)->get(),
                'designations' => Designation::toBase()->where('status', 1)->get(),
                'shifts' =>     Shift::activeShifts(),
                'weeklyholiday' => WeeklyHoliday::toBase()->where('status', 1)->where('holiday_type', 1)->get(),
                'employees'    => $this->model->toBase()->where('status', 1)->get(),
                'allowances'  => AllowanceDeduction::toBase()->where('status', 1)->where('type', 1)->get(),
                'deducts'  => AllowanceDeduction::toBase()->where('status', 1)->where('type', 2)->get(),
            ];
            return view('hrm::employee.form', $data);
        } else {
            return $this->access_blocked();
        }
    }

    public function store_or_update_data(EmployeeFormRequest $request)
    {
        if ($request->ajax()) {
            if (permission('employee-add') || permission('employee-edit')) {
                DB::beginTransaction();
                try {
                    $collection = collect($request->validated())->except(['nid_photo', 'photograph']);
                    // dd($request->all());
                    $nid_photo = !empty($request->old_nid_photo) ? $request->old_nid_photo : null;
                    if ($request->hasFile('nid_photo')) {
                        $nid_photo  = $this->upload_file($request->file('nid_photo'), EMPLOYEE_NID_PHOTO);
                        if (!empty($request->old_nid_photo)) {
                            $this->delete_file($request->old_nid_photo, EMPLOYEE_NID_PHOTO);
                        }
                    }
                    $photograph = !empty($request->old_photograph) ? $request->old_photograph : null;
                    if ($request->hasFile('photograph')) {
                        $photograph  = $this->upload_file($request->file('photograph'), EMPLOYEE_IMAGE_PATH);
                        if (!empty($request->old_photograph)) {
                            $this->delete_file($request->old_photograph, EMPLOYEE_IMAGE_PATH);
                        }
                    }
                    $collection = $collection->merge(compact('nid_photo', 'photograph'));
                    $collection = $this->track_data($collection, $request->update_id);
                    $employee   = $this->model->updateOrCreate(['id' => $request->update_id], $collection->all());
                    if (empty($employee)) {
                        if ($request->hasFile('nid_photo')) {
                            $this->delete_file($nid_photo, EMPLOYEE_NID_PHOTO);
                        }
                        if ($request->hasFile('photograph')) {
                            $this->delete_file($photograph, EMPLOYEE_IMAGE_PATH);
                        }
                    }

                    if ($employee) {
                        if ($request->has('holiday')) {

                            $holiday   = [];
                            foreach ($request->holiday as $key => $value) {
                                $holiday[$key] = [
                                    'employee_id'  => $employee->id,
                                    'weekly_holiday_id' => $value,
                                    'created_by' => auth()->user()->name,
                                    'created_at' => date('Y-m-d H:i:s')
                                ];
                            }
                            //dd($holiday);
                            if (count($holiday) > 0) {
                                WeeklyHolidayAssign::where('employee_id', $employee->id)->delete(); //remove previous holiday data
                                WeeklyHolidayAssign::insert($holiday); //insert new holiday data
                            }
                        }
                    }

                    if ($employee) {

                        //Employee Education Array Data
                        if ($request->has('education')) {
                            $education   = [];
                            foreach ($request->education as $key => $value) {
                                //Check has degree
                                if (!empty($value['degree'])) {
                                    $education[$key] = [
                                        'employee_id'  => $employee->id,
                                        'degree'       => $value['degree'] ? $value['degree'] : null,
                                        'major'        => $value['major'] ? $value['major'] : null,
                                        'institute'    => $value['institute'] ? $value['institute'] : null,
                                        'passing_year' => $value['passing_year'] ? $value['passing_year'] : null,
                                        'result'       => $value['result'] ? $value['result'] : null,
                                        'created_by' => auth()->user()->name,
                                        'created_at' => date('Y-m-d H:i:s')
                                    ];
                                }
                            }
                            //check education array empty or not
                            if (count($education) > 0) {
                                EmployeeEducation::where('employee_id', $employee->id)->delete(); //remove previous education data
                                EmployeeEducation::insert($education); //insert new education data
                            }
                        }

                        //Employee Experience Array Data
                        if ($request->has('experience')) {
                            $experience   = [];
                            foreach ($request->experience as $key => $value) {
                                //Check has designation
                                if (!empty($value['designation'])) {
                                    $experience[$key] = [
                                        'employee_id'    => $employee->id,
                                        'designation'    => $value['designation'] ? $value['designation'] : null,
                                        'company'        => $value['company'] ? $value['company'] : null,
                                        'from_date'      => $value['from_date'] ? $value['from_date'] : null,
                                        'to_date'        => $value['to_date'] ? $value['to_date'] : null,
                                        'responsibility' => $value['responsibility'] ? $value['responsibility'] : null,
                                        'created_by' => auth()->user()->name,
                                        'created_at' => date('Y-m-d H:i:s')
                                    ];
                                }
                            }
                            //check experience array empty or not
                            if (count($experience) > 0) {
                                EmployeeProfessionalInformation::where('employee_id', $employee->id)->delete(); //remove previous experience data
                                EmployeeProfessionalInformation::insert($experience); //insert new experience data
                            }
                        }

                        if (empty($request->update_id)) {
                            $coa_max_code      = ChartOfAccount::where('level', 3)->where('code', 'like', '50202%')->max('code');
                            $code              = $coa_max_code ? ($coa_max_code + 1) : 502020001;
                            //$head_name         = $employee->id . '-' . $employee->name . '-' . $employee->wallet_number;
                            $head_name         = $employee->id . '-' . $employee->name . '-E';
                            $employee_coa_data = $this->employee_coa($code, $head_name);
                            $employee_coa      = ChartOfAccount::create($employee_coa_data);
                        } else {
                            //$old_head_name = $request->update_id . '-' . $request->old_name . '-' . $request->old_wallet_number;
                            //$new_head_name = $request->update_id . '-' . $request->name . '-' . $request->wallet_number;
                            $old_head_name = $request->update_id . '-' . $request->old_name . '-E';
                            $new_head_name = $request->update_id . '-' . $request->name . '-E';
                            $employee_coa  = ChartOfAccount::where(['name' => $old_head_name])->first();
                            if ($employee_coa) {
                                $employee_coa->update(['name' => $new_head_name]);
                            }
                        }
                        $output = ['status' => 'success', 'message' => 'Data has been saved successfully'];
                    } else {
                        $output = ['status' => 'error', 'message' => 'Failed to save'];
                    }
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                    $output       = ['status' => 'error', 'message' => $e->getMessage()];
                }
            } else {
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    private function employee_coa(string $code, string $head_name)
    {
        return [
            'code'              => $code,
            'name'              => $head_name,
            'parent_name'       => 'Employee Ledger',
            'level'             => 3,
            'type'              => 'L',
            'transaction'       => 1,
            'general_ledger'    => 2,
            'budget'            => 2,
            'depreciation'      => 2,
            'depreciation_rate' => '0',
            'status'            => 1,
            'created_by'        => auth()->user()->name
        ];
    }

    public function edit(int $id)
    {

        if (permission('employee-edit')) {
            $this->setPageData('Edit Employee', 'Edit Employee', 'fas fa-user-secret', [['name' => 'HRM', 'link' => 'javascript::void();'], ['name' => 'Edit Employee']]);
            $employee = $this->model->with(['educations', 'professional_informations'])->findOrFail($id);
            $data = [
                'employee'     => $employee,
                'departments'  => Department::toBase()->where('status', 1)->get(),
                'divisions'     => $employee ? Division::where('department_id', $employee->department_id)->get() : '',
                'designations' => Designation::toBase()->where('status', 1)->get(),
                'employees'    => $this->model->toBase()->where('status', 1)->get(),
                'weeklyholiday' => WeeklyHoliday::toBase()->where('status', 1)->where('holiday_type', 1)->get(),
                'shifts' =>     Shift::activeShifts(),
                'allowances'  => AllowanceDeduction::toBase()->where('status', 1)->where('type', 1)->get(),
                'deducts'  => AllowanceDeduction::toBase()->where('status', 1)->where('type', 2)->get(),
            ];

            return view('hrm::employee.form', $data);
        } else {
            return $this->access_blocked();
        }
    }

    public function show(Request $request)
    {
        if ($request->ajax()) {
            if (permission('employee-view')) {
                $employee   = $this->model->with('benifits', 'educations', 'professional_informations', 'supervisor')->findOrFail($request->id);
                return view('salesmen::view-data', compact('employee'))->render();
            }
        }
    }

    public function delete(Request $request)
    {
        if ($request->ajax()) {
            if (permission('employee-delete')) {
                WeeklyHolidayAssign::where('employee_id', $request->id)->delete();
                EmployeeEducation::where('employee_id', $request->id)->delete();
                EmployeeProfessionalInformation::where('employee_id', $request->id)->delete();
                $result   = $this->model->find($request->id)->delete();
                $output   = $this->delete_message($result);
            } else {
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function bulk_delete(Request $request)
    {
        if ($request->ajax()) {
            if (permission('employee-bulk-delete')) {
                WeeklyHolidayAssign::where('employee_id', $request->ids)->delete();
                EmployeeEducation::where('employee_id', $request->ids)->delete();
                EmployeeProfessionalInformation::where('employee_id', $request->ids)->delete();
                $result   = $this->model->destroy($request->ids);
                $output   = $this->bulk_delete_message($result);
            } else {
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }
    public function change_shift(Request $request)
    {
        if ($request->ajax()) {
            if (permission('employee-shift-change')) {
                if ($request->has('shift_id')) {
                    $shiftchange   = [];
                    foreach ($request->ids as $key => $id) {
                        $shiftchange[$key] = [
                            'shift_id'  => $request->shift_id,
                            'employee_id'  => $id,
                            'start_date' => $request->start_date,
                            'end_date' => $request->end_date,
                            'presentstatus' => 1,
                            'created_by' => auth()->user()->name,
                            'created_at' => date('Y-m-d H:i:s')
                        ];
                    }
                    //dd($shiftchange);
                    if (count($shiftchange) > 0) {
                        ShiftManage::insert($shiftchange); //insert new holiday data
                    }
                }
                $output = ['status' => 'success', 'message' => 'Data has been saved successfully'];
            } else {
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }
    // public function setup_allowance_deduction(Request $request)
    // {
    //     if ($request->ajax()) {
    //         if (permission('employee-benifit-setup')) {
    //             $benifits   = [];
    //             $amount=0;
    //             foreach ($request->ids as $key => $id) {
    //                 $employees = $this->model->toBase()->where('id', $id)->where('status', 1)->first();
    //                 foreach ($request->benifit_ids as $key => $benifit_id) {
    //                     $allbenifits = AllowanceDeduction::toBase()->where('status', 1)->first();
    //                     $amount = ($employees->rate);

    //                     $benifits[$key] = [
    //                         'allownace_deduction_id'  => $request->shift_id,
    //                         'employee_id'  => $id,
    //                         'type' => $request->start_date,
    //                         'basic_salary' => $request->end_date,
    //                         'percentage' => 1,
    //                         'amount' => 1,
    //                         'created_by' => auth()->user()->name,
    //                         'created_at' => date('Y-m-d H:i:s')
    //                     ];
    //                 }
    //                 if (count($benifits) > 0) {
    //                     AllowanceDeduction::insert($benifits); //insert new holiday data
    //                 }
    //             }
    //             $output = ['status' => 'success', 'message' => 'Data has been saved successfully'];
    //         } else {
    //             $output       = $this->unauthorized();
    //         }
    //         return response()->json($output);
    //     } else {
    //         return response()->json($this->unauthorized());
    //     }
    // }

    public function change_status(Request $request)
    {
        if ($request->ajax()) {
            if (permission('employee-edit')) {
                $result   = $this->model->find($request->id)->update(['status' => $request->status]);
                $output   = $result ? ['status' => 'success', 'message' => 'Status Has Been Changed Successfully']
                    : ['status' => 'error', 'message' => 'Failed To Change Status'];
            } else {
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function employee_id_wise_employee_details(int $id)
    {
        $employee_details = $this->model->where('id',$id)->first();

        $empdata=[
            'id' => $employee_details->id, 
            'wallet_number' => $employee_details->wallet_number 
        ];

        return json_encode($empdata);
    }
}
