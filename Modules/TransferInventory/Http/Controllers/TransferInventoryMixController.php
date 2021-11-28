<?php

namespace Modules\TransferInventory\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TransferInventoryMixController extends Controller
{
    public function create()
    {
        if(permission('transfer-inventory-mix-add')){
            $this->setPageData('Transfer Inventory Mix Form','Transfer Inventory Mix Form','fas fa-people-carry',[['name' => 'Transfer Inventory Mix Form']]);
            $data = [
                'batches' => Batch::allBatches(),
                'sites'     => Site::allSites(),
                'materials' => Material::with('category')->where([['status',1],['type',1]])->get(),
            ];
            
            return view('transferinventory::transfer-inventory-mix.create',$data);
        }else{
            return $this->access_blocked();
        }
        
    }
}
