<?php

namespace Modules\Production\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;

class BOMRePackingController extends BaseController
{
    public function create()
    {
        $this->setPageData('BOM Re Packing Form','BOM Re Packing Form','fas fa-retweet',[['name'=>'BOM Re Packing Form']]);
        $data = [
            'memo_no' => date('ymdH').rand(111,999)
        ];
   
        return view('production::bom-repacking.create',$data);
    }
}
