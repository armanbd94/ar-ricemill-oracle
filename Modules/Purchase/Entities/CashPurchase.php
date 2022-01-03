<?php

namespace Modules\Purchase\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Modules\Material\Entities\Material;
use Modules\Setting\Entities\JobType;

class CashPurchase extends BaseModel
{
    protected $table = 'cash_purchases';
    protected $fillable = [
        'challan_no','memo_no','vendor_name','job_type_id','name','account_id', 'item','total_qty','grand_total','receive_date','created_by','modified_by',
    ];

    public function materials()
    {
        return $this->belongsToMany(Material::class,'cash_purchase_materials','cash_id','material_id','id','id')
        ->withPivot('id','item_class_id', 'site_id', 'location_id','qty','purchase_unit_id','net_unit_cost','old_cost','total','description')
        ->withTimeStamps(); 
    }

    public function jobType()
    {
        return $this->belongsTo(JobType::class,'job_type_id','id')->withDefault(['job_type'=>'']);
    }

     /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    protected $order = ['cp.id' => 'desc'];
    //custom search column property
    protected $_challan_no; 
    protected $_from_date; 
    protected $_to_date; 

    //methods to set custom search property value
    public function setChallanNo($challan_no)
    {
        $this->_challan_no = $challan_no;
    }

    public function setFromDate($from_date)
    {
        $this->_from_date = $from_date;
    }

    public function setToDate($to_date)
    {
        $this->_to_date = $to_date;
    }

    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        if (permission('cash-purchase-bulk-delete')){
            $this->column_order = ['cp.id', 'cp.id', 'cp.challan_no','cp.memo_no','cp.vendor_name','cp.job_type_id','cp.name','cp.account_id', 'cp.item','cp.total_qty','cp.grand_total','cp.receive_date','cp.created_by', null];
        }else{
            $this->column_order = ['cp.id', 'cp.challan_no','cp.memo_no','cp.vendor_name','cp.job_type_id','cp.name','cp.account_id', 'cp.item','cp.total_qty','cp.grand_total','cp.receive_date','cp.created_by', null];
        }
        
        $query = DB::table('cash_purchases as cp')
        ->leftJoin('job_types as jt','cp.job_type_id','=','jt.id')
        ->leftJoin('chart_of_accounts as coa','cp.account_id','=','coa.id')
        ->select('cp.id', 'cp.challan_no','cp.memo_no','cp.vendor_name','cp.job_type_id','jt.job_type','cp.name',
        'cp.account_id', 'coa.name as account_name','cp.item','cp.total_qty','cp.grand_total','cp.receive_date','cp.created_by');


        if (!empty($this->_challan_no)) {
            $query->where('cp.challan_no', 'like', '%' . $this->_challan_no . '%');
        }

        if (!empty($this->_from_date)) {
            $query->where('cp.receive_date', '>=',$this->_from_date);
        }

        if (!empty($this->_to_date)) {
            $query->where('cp.receive_date', '<=',$this->_to_date);
        }


        //order by data fetching code
        if (isset($this->orderValue) && isset($this->dirValue)) { //orderValue is the index number of table header and dirValue is asc or desc
            $query->orderBy($this->column_order[$this->orderValue], $this->dirValue); //fetch data order by matching column
        } else if (isset($this->order)) {
            $query->orderBy(key($this->order), $this->order[key($this->order)]);
        }
        return $query;
    }

    public function getDatatableList()
    {
        $query = $this->get_datatable_query();
        if ($this->lengthVlaue != -1) {
            $query->offset($this->startVlaue)->limit($this->lengthVlaue);
        }
        return $query->get();
    }

    public function count_filtered()
    {
        $query = $this->get_datatable_query();
        return $query->get()->count();
    }

    public function count_all()
    {
        return DB::table('cash_purchases')->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/

    public function transaction_data(array $data) : array
    {

        //Inventory Debit
        $inventory = array(
            'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', '10101')->value('id'),
            'voucher_no'          => $data['challan_no'],
            'voucher_type'        => 'Purchase',
            'voucher_date'        => $data['receive_date'],
            'description'         => 'Inventory debit '.$data['grand_total'].'Tk for material purchase from vendor '.$data['vendor_name'],
            'debit'               => $data['grand_total'],
            'credit'              => 0,
            'posted'              => 1,
            'approve'             => 1,
            'created_by'          => auth()->user()->name,
            'created_at'          => date('Y-m-d H:i:s')
        ); 

         // Expense for company
        $expense = array(
            'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', '402')->value('id'),
            'voucher_no'          => $data['challan_no'],
            'voucher_type'        => 'Purchase',
            'voucher_date'        => $data['receive_date'],
            'description'         => 'Company expense '.$data['grand_total'].'Tk for material purchase from vendor '.$data['vendor_name'],
            'debit'               => $data['grand_total'],
            'credit'              => 0,
            'posted'              => 1,
            'approve'             => 1,
            'created_by'          => auth()->user()->name,
            'created_at'          => date('Y-m-d H:i:s')
        ); 

        $payment = array(
            'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', '1020101')->value('id'),
            'voucher_no'          => $data['challan_no'],
            'voucher_type'        => 'Purchase',
            'voucher_date'        => $data['receive_date'],
            'description'         => 'Cash '.$data['grand_total'].'Tk given for material purchase',
            'debit'               => 0,
            'credit'              => $data['grand_total'],
            'posted'              => 1,
            'approve'             => 1,
            'created_by'          => auth()->user()->name,
            'created_at'          => date('Y-m-d H:i:s')
            
        );

        return [$inventory,$expense,$payment];
    } 
}
