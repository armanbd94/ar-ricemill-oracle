<?php

namespace Modules\TransferInventory\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Setting\Entities\Site;
use Modules\Setting\Entities\Batch;
use Modules\Material\Entities\Material;
use App\Http\Controllers\BaseController;

class TransferInventoryController extends BaseController
{
    // public function __construct(CashPurchase $model)
    // {
    //     $this->model = $model;
    // }
    
    // public function index()
    // {
    //     if(permission('cash-purchase-access')){
    //         $this->setPageData('Manage Cash Purchase','Manage Cash Purchase','fas fa-cart-arrow-down',[['name' => 'Manage Cash Purchase']]);
    //         return view('purchase::cash-purchase.index');
    //     }else{
    //         return $this->access_blocked();
    //     }
    // }

    public function create()
    {
        if(permission('transfer-inventory-add')){
            $this->setPageData('Transfer Inventory Form','Transfer Inventory Form','fas fa-people-carry',[['name' => 'Transfer Inventory Form']]);
            $data = [
                'batches' => Batch::allBatches(),
                'sites'     => Site::allSites(),
                'materials' => Material::with('category')->where([['status',1],['type',1]])->get(),
            ];
            
            return view('transferinventory::transfer-inventory.create',$data);
        }else{
            return $this->access_blocked();
        }
        
    }

}
