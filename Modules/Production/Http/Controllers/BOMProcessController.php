<?php

namespace Modules\Production\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;

class BOMProcessController extends BaseController
{
    public function create()
    {
        $this->setPageData('BOM Process Form','BOM Process Form','fas fa-retweet',[['name'=>'BOM Process Form']]);
        $data = [
            'memo_no' => date('ymdH').rand(111,999)
        ];
   
        return view('production::bom-process.create',$data);
    }
}
