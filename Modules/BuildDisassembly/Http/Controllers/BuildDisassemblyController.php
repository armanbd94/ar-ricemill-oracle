<?php

namespace Modules\BuildDisassembly\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Setting\Entities\Site;
use Modules\Setting\Entities\Batch;
use Modules\Material\Entities\Material;
use App\Http\Controllers\BaseController;
use Modules\BuildDisassembly\Entities\BuildDisassembly;

class BuildDisassemblyController extends BaseController
{
    public function __construct(BuildDisassembly $model)
    {
        $this->model = $model;
    }
    
    public function index()
    {
        if(permission('build-disassembly-access')){
            $this->setPageData('Manage Build Disassembly','Manage Build Disassembly','fas fa-pallet',[['name' => 'Manage Build Disassembly']]);
            $batches = Batch::allBatches();
            return view('builddisassembly::index',compact('batches'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('build-disassembly-access')){

                if (!empty($request->memo_no)) {
                    $this->model->setMemoNo($request->memo_no);
                }
                if (!empty($request->batch_id)) {
                    $this->model->setBatchID($request->batch_id);
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
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('build-disassembly-edit')){
                        $action .= ' <a class="dropdown-item" href="'.route("build.disassembly.edit",$value->id).'">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if(permission('build-disassembly-view')){
                        $action .= ' <a class="dropdown-item view_data" href="'.route("build.disassembly.view",$value->id).'">'.self::ACTION_BUTTON['View'].'</a>';
                    }
                    if(permission('build-disassembly-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->memo_no . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }
                    
                    $row = [];
                    if(permission('build-disassembly-bulk-delete')){
                        $row[] = row_checkbox($value->id);//custom helper function to show the table each row checkbox
                    }
                    $row[] = $no;
                    $row[] = $value->memo_no;
                    $row[] = $value->batch_no;
                    $row[] = $value->material_name;
                    $row[] = $value->product_name;
                    $row[] = $value->from_site;
                    $row[] = $value->from_location;
                    $row[] = $value->convertion_ratio;
                    $row[] = $value->converted_qty;
                    $row[] = date(config('settings.date_format'),strtotime($value->build_date));
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

    public function create()
    {
        if(permission('build-disassembly-add')){
            $this->setPageData('Transfer Inventory Form','Transfer Inventory Form','fas fa-people-carry',[['name' => 'Transfer Inventory Form']]);
            $data = [
                'batches'   => Batch::allBatches(),
                'sites'     => Site::allSites(),
                'materials' => Material::with('category')->where([['status',1],['type',1]])->get(),
            ];
            
            return view('builddisassembly::create',$data);
        }else{
            return $this->access_blocked();
        }
    } 
}
