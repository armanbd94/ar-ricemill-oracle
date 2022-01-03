<?php

namespace Modules\Loan\Http\Controllers\Report;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Loan\Entities\LoanReport;
use App\Http\Controllers\BaseController;
use Illuminate\Contracts\Support\Renderable;
use Modules\Account\Entities\ChartOfAccount;

class LoanReportController extends BaseController
{
    
    public function __construct(LoanReport $model)
    {
        $this->model = $model;
    }
    
    public function index()
    {
        if(permission('loan-report-access')){
            $this->setPageData('Loan Report List','Loan Report List','far fa-money-bill-alt',[['name'=>'Distributor'],['name'=>'Loan Report List']]);
            $data = [
                'employee_list' => ChartOfAccount::where(['status'=>1,'is_transaction'=>1,'parent_name'=>'Employee Ledger'])->orderBy('id','asc')->get(),
                ];
            return view('loan::report-list.report',$data);
        }else{
            return $this->access_blocked();
        }
    }    

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('loan-report-access')){

                if (!empty($request->start_date)) {
                    $this->model->setStartDate($request->start_date);
                }
                if (!empty($request->end_date)) {
                    $this->model->setEndDate($request->end_date);
                }
                if (!empty($request->employee_id)) {
                    $this->model->setEmployee($request->employee_id);
                    //dd($request->chart_of_account_id);
                }
                if (!empty($request->person_id)) {
                    $this->model->setPerson($request->person_id);
                    //dd($request->chart_of_account_id);
                }

                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';                                      
                    $row = [];
                    $row[] = $no;
                    $row[] = $value->voucher_no;
                    $row[] = date(config('settings.date_format'),strtotime($value->voucher_date));
                    $row[] = $value->cname;
                    $row[] = $value->description;
                    $row[] = $value->voucher_type == 'PLI' || $value->voucher_type == 'EMPSALOLI' ? 0 : number_format($value->debit,2);
                    $row[] = $value->voucher_type == 'PL' || $value->voucher_type == 'OL' ? 0 : number_format($value->credit,2);
                    $row[] = $value->created_by;
                    $data[] = $row;
                }
                return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
                $this->model->count_filtered(), $data);
            }
        }else{
            return response()->json($this->unauthorized());
        }
    }
}
