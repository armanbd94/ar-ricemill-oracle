<?php

namespace Modules\Product\Http\Controllers;

use Keygen\Keygen;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use App\Http\Controllers\BaseController;
use Modules\Product\Entities\SiteProduct;
use Modules\Product\Http\Requests\ProductFormRequest;


class ProductController extends BaseController
{
    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('product-access')){
            $this->setPageData('Manage Product','Manage Product','fas fa-toolbox',[['name' => 'Manage Product']]);
            $data = [
                'units'      => Unit::where('status',1)->get(),
                'taxes'      => Tax::activeTaxes(),
                'categories' => Category::allProductCategories(),
            ];
            return view('product::index',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('product-access')){

                if (!empty($request->name)) {
                    $this->model->setName($request->name);
                }
                if (!empty($request->code)) {
                    $this->model->setCode($request->code);
                }
                if (!empty($request->status)) {
                    $this->model->setStatus($request->status);
                }
                if (!empty($request->category_id)) {
                    $this->model->setCategoryID($request->category_id);
                }

                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('product-edit')){
                        $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if(permission('product-view')){
                        $action .= ' <a class="dropdown-item view_data" data-id="' . $value->id . '" data-name="' . $value->name . '">'.self::ACTION_BUTTON['View'].'</a>';
                    }
                    if(permission('product-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->name . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }

                    $row = [];
                    if(permission('product-bulk-delete')){
                        $row[] = row_checkbox($value->id);//custom helper function to show the table each row checkbox
                    }
                    $row[] = $no;
                    $row[] = $value->name;
                    $row[] = $value->code;
                    $row[] = $value->category->name;
                    $row[] = $value->price ? number_format($value->price,2,'.',',') : 0;
                    $row[] = $value->unit->unit_name;
                    $row[] = $value->alert_qty ? $value->alert_qty : 0;
                    $row[] = permission('product-edit') ? change_status($value->id,$value->status, $value->name) : STATUS_LABEL[$value->status];
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

    public function store_or_update_data(ProductFormRequest $request)
    {
        if($request->ajax()){
            if(permission('product-add') || permission('product-edit')){
                DB::beginTransaction();
                try {
                    $collection = collect($request->validated())->except('alert_qty','tax_id');
                    $alert_qty  = $request->alert_qty ? $request->alert_qty : 0;
                    $tax_id     = ($request->tax_id != 0) ? $request->tax_id : null;
                    $collection = $collection->merge(compact('alert_qty','tax_id'));
                    $collection = $this->track_data($collection,$request->update_id);
                    $result     = $this->model->updateOrCreate(['id'=>$request->update_id],$collection->all());
                    $output     = $this->store_message($result, $request->update_id);
                    DB::commit();
                }catch (\Throwable $th) {
                   DB::rollback();
                   $output = ['status' => 'error','message' => $th->getMessage()];
                }
            }else{
                $output     = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function show(Request $request)
    {
        if($request->ajax()){
            if(permission('product-view')){
                $product = $this->model->with('unit')->findOrFail($request->id);
                return view('product::view-modal-data',compact('product'))->render();
            }
        }
    }

    public function edit(Request $request)
    {
        if($request->ajax()){
            if(permission('product-edit')){
                $data   = $this->model->findOrFail($request->id);
                $output = $this->data_message($data); //if data found then it will return data otherwise return error message
            }else{
                $output = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function delete(Request $request)
    {
        if($request->ajax()){
            if(permission('product-delete')){
                SiteProduct::where('product_id',$request->id)->delete();
                $material  = $this->model->find($request->id)->delete();
                $output   = $this->delete_message($material);
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
            if(permission('product-bulk-delete')){
                SiteProduct::whereIn('product_id',$request->ids)->delete();
                $material  = $this->model->destroy($request->ids);
                $output   = $this->bulk_delete_message($material);
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
            if(permission('product-edit')){
                $result   = $this->model->find($request->id)->update(['status' => $request->status]);
                $output   = $result ? ['status' => 'success','message' => 'Status Has Been Changed Successfully']
                : ['status' => 'error','message' => 'Failed To Change Status'];
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    //Generate Material Code
    public function generateProductCode()
    {
        $code = Keygen::numeric(8)->generate();
        //Check Material Code ALready Exist or Not
        if(DB::table('products')->where('code',$code)->exists())
        {
            $this->generateMaterialCode();
        }else{
            return response()->json($code);
        }
    }

    public function product_list(Request $request)
    {
        $category_id = $request->category_id;
        $products = DB::table('site_product as sp')
        ->select('p.id','p.name as product_name','c.name as category_name','u.unit_name','u.unit_code','sp.qty')
        ->leftJoin('products as p','sp.product_id','=','p.id')
        ->leftJoin('categories as c','p.category_id','=','c.id')
        ->leftJoin('units as u','p.unit_id','=','u.id')
        ->where([
            'sp.site_id'     => $request->site_id,
            'sp.location_id' => $request->location_id,
        ])
        ->when( $category_id,function($q) use ($category_id){
            $q->where('p.category_id','!=',$category_id);
        })
        ->orderBy('p.category_id','desc')
        ->orderBy('p.id','asc')
        ->get();

        $output = '<option value="">Select Please</option>';
        if(!$products->isEmpty())
        {
            foreach ($products as $value) {
                $output .= '<option value="'.$value->id.'" data-stockqty="'.$value->qty.'" data-category="'.$value->category_name.'" data-unitname="'.$value->unit_name.'" data-unitcode="'.$value->unit_code.'">'.$value->product_name.'</option>';
            }
        }
        return $output;
    }

    public function stock_qty(Request $request)
    {
        $stock_qty = DB::table('site_product')->where([
            'site_id'     => $request->site_id,
            'location_id' => $request->location_id,
            'product_id' => $request->product_id,
        ])->value('qty');

        return response()->json($stock_qty ?? 0);
    }
}
