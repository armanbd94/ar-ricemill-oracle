<?php

namespace Modules\Production\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;

class BuilReProcessController extends BaseController
{
    public function create()
    {
        $this->setPageData('Build Re Process Form','Build Re Process Form','fas fa-retweet',[['name'=>'Build Re Process Form']]);
        $data = [
            'memo_no' => date('ymdH').rand(111,999)
        ];
   
        return view('production::build-reprocess.create',$data);
    }
}
