<?php
namespace Modules\Report\Http\Controllers\PurchaseReport\Trait;

use Illuminate\Support\Facades\DB;

trait PurchaseReport
{
    protected function purchase_received_count($start_date,$end_date,$category_id,$material_id=null)
    {
        return DB::table('received_materials rm')
        ->leftJoin('materials as m','rm.material_id','=','m.id')
        ->leftJoin('order_received as ore','rm.order_id','=','ore.id')
        ->leftJoin('purchase_orders as por','ore.order_id','=','por.id')
        ->where('m.category_id',$category_id) //5=Packet Rice
        ->whereBetween('ore.received_date',[$start_date,$end_date])
        ->when($material_id,function($q) use ($material_id){
            $q->where('rm.material_id',$material_id);
        })
        ->count();
    }

    protected function cash_purchase_count($start_date,$end_date,$category_id,$material_id=null)
    {
        
        return DB::table('cash_purchase_materials cpm')
        ->join('materials as m','cpm.material_id','=','m.id')
        ->join('cash_purchases as cp','cpm.cash_id','=','cp.id')
        ->where('m.category_id',$category_id) //5=Packet Rice
        ->whereBetween('cp.receive_date',[$start_date,$end_date])
        ->when($material_id,function($q) use ($material_id){
            $q->where('cpm.material_id',$material_id);
        })
        ->count();
    }
}
