<?php

namespace Modules\Vendor\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Vendor\Entities\Vendor;
use App\Http\Controllers\BaseController;
use Modules\Vendor\Entities\VendorLedger;

class VendorLedgerController extends BaseController
{
    public function __construct(VendorLedger $model)
    {
        $this->model = $model;
    }


    public function index()
    {
        if(permission('vendor-ledger-access')){
            $this->setPageData('Vendor Ledger','Vendor Ledger','fas fa-file-invoice-dollar',[['name'=>'Vendor','link'=>route('vendor')],['name'=>'Vendor Ledger']]);
            $vendors = Vendor::with('coa')->where(['status'=>1])->orderBy('name','asc')->get();
            return view('vendor::ledger.index',compact('vendors'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('vendor-ledger-access')){

                if (!empty($request->vendor_id)) {
                    $this->model->setVendorID($request->vendor_id);
                }
                if (!empty($request->from_date)) {
                    $this->model->setFromDate($request->from_date);
                }
                if (!empty($request->to_date)) {
                    $this->model->setToDate($request->to_date);
                }

                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $debit = $credit = $balance = 0;
                foreach ($list as $value) {
                    $debit += $value->debit;
                    $credit += $value->credit;
                    $balance = $debit - $credit;
                    $row = [];
                    $row[] = $value->voucher_date;
                    $row[] = $value->description;
                    $row[] = $value->voucher_no;
                    $row[] = $value->debit ? number_format($value->debit,2, '.', ',') :  number_format(0,2, '.', ',');
                    $row[] = $value->credit ? number_format($value->credit,2, '.', ',') :  number_format(0,2, '.', ',');
                    $row[] = number_format(($balance),2, '.', ',');
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
