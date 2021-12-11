<?php

namespace Modules\BOM\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Modules\Setting\Entities\Site;
use Modules\Setting\Entities\Batch;
use Modules\Product\Entities\Product;
use App\Http\Controllers\BaseController;

class BOMController extends BaseController
{
    public function create()
    {
        if(permission('bom-process-add')){
            $this->setPageData('BOM Process Form','BOM Process Form','fas fa-box',[['name' => 'BOM Process Form']]);
            $data = [
                'batches'    => Batch::allBatches(),
                'sites'      => Site::allSites(),
                'products'   => Product::where([['status',1],['category_id','!=',3]])->get(),
                'categories' => Category::allProductCategories(),
            ];
            return view('bom::bom-process.create',$data);
        }else{
            return $this->access_blocked();
        }
    } 
}
