<?php

namespace Modules\Production\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;

class ProductionController extends BaseController
{
    public function create()
    {
        $this->setPageData('Production Form','Production Form','fas fa-dolly-flatbed',[['name'=>'Production Form']]);
        $data = [
            'memo_no' => date('ymdH').rand(111,999)
        ];
        
        return view('production::create',$data);
    }
}
