<?php

namespace Modules\Account\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Setting\Entities\Warehouse;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\ChartOfAccount;
use Modules\Account\Entities\VoucherApproval;

class VoucherApprovalController extends BaseController
{
    public function __construct(VoucherApproval $model)
    {
        $this->model = $model;
    }


    public function index()
    {
        if(permission('voucher-access')){
            $this->setPageData('Voucher Approval','Voucher Approval','far fa-money-bill-alt',[['name'=>'Accounts'],['name'=>'Voucher Approval']]);
            return view('account::voucher-approval.index',compact('warehouses'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('voucher-access')){

                if (!empty($request->start_date)) {
                    $this->model->setStartDate($request->start_date);
                }
                if (!empty($request->end_date)) {
                    $this->model->setEndDate($request->end_date);
                }
                if (!empty($request->voucher_no)) {
                    $this->model->setVoucherNo($request->voucher_no);
                }


                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('voucher-edit')){
                        $action .= ' <a class="dropdown-item" href="'.route("voucher.update",$value->voucher_no).'">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if(permission('voucher-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->voucher_no . '" data-name="' . $value->voucher_no . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }

                    if($value->approve == 3 && permission('voucher-approve'))
                        {
                        $voucher_approve = '<span class="label label-success label-pill label-inline approve_voucher" data-id="' . $value->voucher_no . '" data-name="' . $value->voucher_no . '" data-status="1" style="min-width:70px !important;cursor:pointer;">Approve It</span>';
                    }else{
                        $voucher_approve = VOUCHER_APPROVE_STATUS[$value->approve];
                    }
                    
                    $row = [];
                    $row[] = $no;
                    $row[] = $value->voucher_no;
                    $row[] = date(config('settings.date_format'),strtotime($value->voucher_date));;
                    $row[] = $value->description;
                    $row[] = $value->voucher_type == 'CV' ? 0 : number_format($value->debit,2);
                    $row[] = $value->voucher_type == 'DV' ? 0 : number_format($value->credit,2);
                    $row[] = $voucher_approve;
                    $row[] = $value->created_by;
                    $row[] = action_button($action);//custom helper function for action button
                    $data[] = $row;
                }
                return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
                $this->model->count_filtered(), $data);
            }
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function edit(string $voucher_no)
    {
        if(permission('voucher-edit')){
            $voucher_data = $this->model->where('voucher_no',$voucher_no)->get();
            $data = [];
            if($voucher_data)
            {
                $data = [
                    'transactional_accounts' => ChartOfAccount::where(['status'=>1,'transaction'=>1])->orderBy('id','asc')->get(),
                    'accounts'               => ChartOfAccount::where(['status'=>1,'transaction'=>1])
                                                ->where('code','like','1020101')
                                                ->orWhere('code','like','10201020%')
                                                ->orWhere('code','like','10201030%')
                                                ->orderBy('id','asc')->get(),
                    'voucher'               => $voucher_data
                ];
                if($voucher_data[0]->voucher_type == 'DV')
                {
                    //Debit Voucher Update Info
                    $data['debit_voucher'] = $this->model->where([['voucher_no',$voucher_no],['credit','<',1]])->get();

                    // Debit Update Voucher Credit Info
                    $data['credit_voucher'] = $this->model->where([['voucher_no',$voucher_no],['debit','<',1]])->get();

                    $title = 'Edit Debit Voucher';
                    $view = 'account::debit-voucher.edit';
                }

                if($voucher_data[0]->voucher_type == 'CV')
                {
                    // Credit Voucher Info
                    $data['credit_voucher'] = $this->model->where([['voucher_no',$voucher_no],['debit','<',1]])->get();

                    //Credit  Voucher Update Debit Info
                    $data['debit_voucher'] = $this->model->where([['voucher_no',$voucher_no],['credit','<',1]])->get();

                    $title = 'Edit Credit Voucher';
                    $view = 'account::credit-voucher.edit';
                }

                if($voucher_data[0]->voucher_type == 'CONTRAV')
                {
                    $title = 'Edit Contra Voucher';
                    $view = 'account::contra-voucher.edit';
                }

                if($voucher_data[0]->voucher_type == 'JOURNALV')
                {
                    $title = 'Edit journal Voucher';
                    $view = 'account::journal-voucher.edit';
                }
                $this->setPageData($title,$title,'far fa-money-bill-alt',[['name'=>'Accounts'],['name'=>$title]]);
                $data['warehouses'] = Warehouse::where('status',1)->pluck('name','id');
                return view($view,$data);
            }else{
                return redirect()->back();
            }
        }else{
            return $this->access_blocked();
        }
    }

    public function delete(Request $request)
    {
        if($request->ajax()){
            $result  = $this->model->where('voucher_no',$request->id)->delete();
            $output   = $this->delete_message($result);
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function approve(Request $request)
    {
        if($request->ajax()){
            if(permission('voucher-approve')){
                $result   = $this->model->where('voucher_no',$request->id)->update(['approve' => $request->status]);
                $output   = $result ? ['status' => 'success','message' => 'Voucher Approved Successfully']
                : ['status' => 'error','message' => 'Failed To Approve Voucher'];
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

}
