<?php

namespace Modules\TransferInventory\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Setting\Entities\Site;
use Modules\Setting\Entities\Batch;
use Modules\Product\Entities\Product;
use App\Http\Controllers\BaseController;
use Modules\TransferInventory\Entities\TransferMixInventory;

class TransferInventoryMixController extends BaseController
{
    public function __construct(TransferMixInventory $model)
    {
        $this->model = $model;
    }
    
    public function index()
    {
        if(permission('transfer-inventory-mix-access')){
            $this->setPageData('Manage Transfer Inventory Mix','Manage Transfer Inventory Mix','fas fa-people-carry',[['name' => 'Manage Transfer Inventory Mix']]);
            return view('transferinventory::transfer-inventory-mix.index');
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('transfer-inventory-mix-access')){

                if (!empty($request->memo_no)) {
                    $this->model->setMemoNo($request->memo_no);
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
                    if(permission('transfer-inventory-mix-edit')){
                        $action .= ' <a class="dropdown-item" href="'.route("transfer.inventory.mix.edit",$value->id).'">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if(permission('transfer-inventory-mix-view')){
                        $action .= ' <a class="dropdown-item view_data" href="'.route("transfer.inventory.mix.view",$value->id).'">'.self::ACTION_BUTTON['View'].'</a>';
                    }
                    if(permission('transfer-inventory-mix-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->memo_no . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }
                    
                    $row = [];
                    if(permission('transfer-inventory-mix-bulk-delete')){
                        $row[] = row_checkbox($value->id);//custom helper function to show the table each row checkbox
                    }
                    $row[] = $no;
                    $row[] = $value->memo_no;
                    $row[] = $value->batch_no;
                    $row[] = $value->product_name;
                    $row[] = $value->category_name;
                    $row[] = $value->to_site;
                    $row[] = $value->to_location;
                    $row[] = $value->item;
                    $row[] = $value->total_qty;
                    $row[] = date(config('settings.date_format'),strtotime($value->transfer_date));
                    $row[] = $value->created_by;
                    $row[] = $value->transfer_number;
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
        if(permission('transfer-inventory-mix-add')){
            $this->setPageData('Transfer Inventory Mix Form','Transfer Inventory Mix Form','fas fa-people-carry',[['name' => 'Transfer Inventory Mix Form']]);
            $data = [
                'batches' => Batch::allBatches(),
                'sites'     => Site::allSites(),
                'products' => Product::with('category')->where('status',1)->get(),
            ];
            
            return view('transferinventory::transfer-inventory-mix.create',$data);
        }else{
            return $this->access_blocked();
        }
        
    }
}
