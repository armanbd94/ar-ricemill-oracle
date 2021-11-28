<?php

namespace Modules\TransferInventory\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Setting\Entities\Site;
use Modules\Setting\Entities\Batch;
use Modules\Product\Entities\Product;
use App\Http\Controllers\BaseController;

class TransferInventoryMixController extends BaseController
{
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
