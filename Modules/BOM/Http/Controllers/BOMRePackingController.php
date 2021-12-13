<?php

namespace Modules\BOM\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Setting\Entities\Site;
use Modules\Product\Entities\Product;
use Modules\BOM\Entities\BomRePacking;
use App\Http\Controllers\BaseController;

class BOMRePackingController extends BaseController
{
    public function __construct(BomRePacking $model)
    {
        $this->model = $model;
    }
    
    public function index()
    {
        if(permission('bom-re-packing-access')){
            $this->setPageData('Manage BOM Re Packing','Manage BOM Re Packing','fas fa-box',[['name' => 'Manage BOM Re Packing']]);
            return view('bom::bom-re-packing.index');
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('bom-re-packing-access')){

                if (!empty($request->process_type)) {
                    $this->model->setProcessType($request->process_type);
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
                    if(permission('bom-re-packing-edit')){
                        $action .= ' <a class="dropdown-item" href="'.route("bom.re.packing.edit",$value->id).'">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if(permission('bom-re-packing-view')){
                        $action .= ' <a class="dropdown-item view_data" href="'.route("bom.re.packing.view",$value->id).'">'.self::ACTION_BUTTON['View'].'</a>';
                    }
                    if(permission('bom-re-packing-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->memo_no . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }
                    
                    $row = [];
                    if(permission('bom-re-packing-bulk-delete')){
                        $row[] = row_checkbox($value->id);//custom helper function to show the table each row checkbox
                    }
                    $row[] = $no;
                    $row[] = $value->memo_no;
                    $row[] = $value->from_product;
                    $row[] = $value->from_site;
                    $row[] = $value->from_location;
                    $row[] = $value->to_product;
                    $row[] = $value->to_site;
                    $row[] = $value->to_location;
                    $row[] = $value->product_qty;
                    $row[] = $value->bag_name;
                    $row[] = $value->bag_qty;
                    $row[] = date(config('settings.date_format'),strtotime($value->packing_date));
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
        if(permission('bom-re-packing-add')){
            $this->setPageData('BOM Re Packing Form','BOM Re Packing Form','fas fa-box',[['name' => 'BOM Re Packing Form']]);
            $data = [
                'sites'      => Site::allSites(),
                'products'   => Product::where([['status',1],['category_id','!=',3]])->get(),
            ];
            return view('bom::bom-re-packing.create',$data);
        }else{
            return $this->access_blocked();
        }
    } 
}
