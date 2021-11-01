@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link rel="stylesheet" href="css/jquery-ui.css" />
<link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
@endpush

@section('content')
<div class="d-flex flex-column-fluid">
    <div class="container-fluid">
        <!--begin::Notice-->
        <div class="card card-custom custom-card">
            <div class="card-header flex-wrap p-0">
                <div class="card-toolbar m-0">
                    <!--begin::Button-->
                    <a href="{{ route('build.reprocess') }}" class="btn btn-warning btn-sm font-weight-bolder"> 
                        <i class="fas fa-arrow-left"></i> Back</a>
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom">
            <div class="card-body">

                <!--begin: Datatable-->
                <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">


                    <form action="" id="purchase_store_form" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-3 required">
                                <label for="chalan_no">Memo No.</label>
                                <input type="text" class="form-control" name="memo_no" id="memo_no" value="{{ $memo_no }}" readonly />
                            </div>
                            <div class="form-group col-md-3 required">
                                <label for="batch_no">Batch No.</label>
                                <input type="text" class="form-control" name="batch_no" id="batch_no" value=""  />
                            </div>
                            <x-form.textbox labelName="Date" name="transfer_date" value="{{ date('Y-m-d') }}" required="required" class="date" col="col-md-3"/>

                            <x-form.textbox labelName="From Location" name="from_location" required="required"  col="col-md-3"/>
                            <x-form.textbox labelName="To Location (Storage)" name="to_location" required="required"  col="col-md-3"/>

                            <x-form.selectbox labelName="Product Type" name="via_vendor_id" col="col-md-3" class="selectpicker">
                                <option value="">Select Please</option>
                                <option value="">Product Type One</option>
                                <option value="">Product Type Two</option>
                            </x-form.selectbox>
                            <x-form.selectbox labelName="Packet Item" name="via_vendor_id" col="col-md-3" class="selectpicker">
                                <option value="">Select Please</option>
                                <option value="">Packet Item One</option>
                                <option value="">Packet Item Two</option>
                            </x-form.selectbox>
                            <x-form.selectbox labelName="Converted Item" name="via_vendor_id" col="col-md-3" class="selectpicker">
                                <option value="">Select Please</option>
                                <option value="">Product One</option>
                                <option value="">Product Two</option>
                            </x-form.selectbox>
                            <x-form.textbox labelName="Availbale Qty (KGs)" name="available_qty" required="required"  col="col-md-3"/>
                            <x-form.textbox labelName="Approx. Main Product Ratio" name="main_product_ratio" required="required"  col="col-md-3"/>
                            <x-form.textbox labelName="Quantity To Build (KGs)" name="build_qty" required="required"  col="col-md-3"/>
                            <x-form.textbox labelName="RM Needed (KGs)" name="needed_qty" required="required"  col="col-md-3"/>
                            <div class="col-md-12 pt-5">
                                <div class="row" style="position: relative;border: 1px solid #E4E6EF;padding: 10px 0 0 0; margin: 0 0 40px 0;border-radius:5px;">
                                    <div style="width: 100px;background: #fa8c15;text-align: center;margin: 0 auto;color: white;padding: 5px 0;
                                        position: absolute;top:-16px;left:10px;box-shadow: 1px 2px 5px 0px rgba(0,0,0,0.5);"><img src="images/rice.png" style="width: 20px;margin-right: 5px;"/>Fine Rice</div>
                                    <div class="col-md-12 pt-5 material_section">
                                        <div class="row">
                                            <div class="form-group col-md-3 required">
                                                <label for="fine_rice_qty" class="form-control-label">Converted Qunatity</label>
                                                <input type="text" name="fine_rice_qty" id="fine_rice_qty" class="form-control">
                                            </div>
                                            <div class="form-group col-md-3 required">
                                                <label for="rice_convertion_ratio" class="form-control-label">Convertion Ratio(%)</label>
                                                <input type="text" name="rice_convertion_ratio" id="rice_convertion_ratio" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-5" style="position: relative;border: 1px solid #E4E6EF;padding: 10px 0 0 0; margin: 0;border-radius:5px;">
                                    <div style="width: 120px;background: #fa8c15;text-align: center;margin: 0 auto;color: white;padding: 5px 0;
                                        position: absolute;top:-16px;left:10px;box-shadow: 1px 2px 5px 0px rgba(0,0,0,0.5);"><img src="images/grain-sack.png" style="width: 20px;margin-right: 5px;"/>By Products</div>
                                    <div class="col-md-12 pt-5 material_section">
                                        <div class="row">
                                            <div class="form-group col-md-3 required">
                                                <label for="by_products_1_id" class="form-control-label">By Product Name</label>
                                                <select name="by_products[1][id]" id="by_products_1_id" required="required" class="form-control selectpicker" data-live-search="true" 
                                                data-live-search-placeholder="Search">
                                                    <option value="">Select Please</option>
                                                    
                                                </select>
                                            </div>
                                            <div class="form-group col-md-3 required">
                                                <label for="by_products[1][qty]" class="form-control-label">Converted Qunatity</label>
                                                <input type="text" name="by_products[1][qty]" id="by_products[1][qty]" class="form-control">
                                            </div>
                                            <div class="form-group col-md-3 required">
                                                <label for="by_products[1][ratio]" class="form-control-label">Convertion Ratio(%)</label>
                                                <input type="text" name="by_products[1][ratio]" id="by_products[1][ratio]" class="form-control">
                                            </div>
                                            <div class="form-group col-md-3" style="padding-top: 28px;">
                                                <button type="button" id="add-by-product" class="btn btn-success btn-sm" data-toggle="tooltip" 
                                                    data-placement="top" data-original-title="Add More">
                                                    <i class="fas fa-plus-square"></i>
                                                    </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group col-md-12 pt-5">
                                <label for="shipping_cost">Note</label>
                                <textarea  class="form-control" name="note" id="note" cols="30" rows="3"></textarea>
                            </div>
                            <div class="form-grou col-md-12 text-center pt-5">
                                <button type="button" class="btn btn-danger btn-sm mr-3"><i class="fas fa-sync-alt"></i> Reset</button>
                                <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn" onclick="store_data()"><i class="fas fa-save"></i> Save</button>
                            </div>
                        </div>
                    </form>
                </div>
                <!--end: Datatable-->
            </div>
        </div>
        <!--end::Card-->
    </div>
</div>

@endsection

@push('scripts')
<script src="js/jquery-ui.js"></script>
<script src="js/moment.js"></script>
<script src="js/bootstrap-datetimepicker.min.js"></script>
<script>
    $("input,select,textarea").bind("keydown", function (e) {
        var keyCode = e.keyCode || e.which;
        if(keyCode == 13) {
            e.preventDefault();
            $('input, select, textarea')
            [$('input,select,textarea').index(this)+1].focus();
        }
    });
    
//array data depend on warehouse
var material_array = [];
var material_code  = [];
var material_name  = [];
var material_qty   = [];

// array data with selection
var material_cost        = [];
var material_labor_cost  = [];
var material_discount    = [];
var tax_rate             = [];
var tax_name             = [];
var tax_method           = [];
var unit_name            = [];
var unit_operator        = [];
var unit_operation_value = [];

//temporary array
var temp_unit_name            = [];
var temp_unit_operator        = [];
var temp_unit_operation_value = [];

var rowindex;
var row_material_cost=0;

$(document).ready(function () {
    $('.date').datetimepicker({format: 'YYYY-MM-DD'});

    $('#payment_status').on('change',function(){
        if($(this).val() != 3){
            $('.payment').removeClass('d-none');
        }else{
            $('#paid_amount').val(0);
        }
    });
});

function hasMixedItem(value)
{
    value == 1 ? $('.add-material').removeClass('d-none') : $('.add-material').addClass('d-none');
}
</script>
@endpush