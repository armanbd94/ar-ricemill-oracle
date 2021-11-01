<?php

namespace Modules\Production\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;

class DisassemblyController extends BaseController
{
    public function create()
    {
        $this->setPageData('Build Disassembly Form','Build Disassembly Form','fas fa-pallet',[['name'=>'Build Disassembly Form']]);
        $data = [
            'memo_no' => date('ymdH').rand(111,999)
        ];
   
        return view('production::disassembly.create',$data);
    }
}
