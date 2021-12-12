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
                    <a href="{{ route('bom.process') }}" class="btn btn-warning btn-sm font-weight-bolder"> 
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
                            <x-form.textbox labelName="Date" name="process_date" value="{{ date('Y-m-d') }}" required="required" class="date" col="col-md-3"/>

                            <div class="form-group col-md-3 required">
                                <label for="batch_no">Batch No.</label>
                                <input type="text" class="form-control" name="batch_no" id="batch_no" value=""  />
                            </div>

                            <div class="form-group col-md-6 required">
                                <label for="chalan_no">Memo No.</label>
                                <input type="text" class="form-control" name="memo_no" id="memo_no"  readonly />
                            </div>

                            <x-form.textbox labelName="Assemble From" name="assemble_from" required="required"  col="col-md-3"/>

                            <x-form.textbox labelName="Assemble To" name="assemble_to" required="required"  col="col-md-3"/>
                            <x-form.textbox labelName="Number" name="process_number" required="required"  col="col-md-3"/>
                            <x-form.textbox labelName="Converted To" name="to_product_id" required="required"  col="col-md-3"/>
                            <x-form.selectbox labelName="Inventory Site" name="to_site_id" onchange="getLocations(this.value)" col="col-md-3" class="selectpicker">
                                @if(!$sites->isEmpty())  
                                    @foreach ($sites as $site)
                                        <option value="{{ $site->id }}">{{ $site->name }}</option>
                                    @endforeach
                                @endif
                            </x-form.selectbox>

                            <x-form.selectbox labelName="Location" name="to_location_id" col="col-md-3" class="selectpicker" onchange="bag_list()" />

                            <div class="col-md-12 pt-5">
                                <div class="row" style="position: relative;border: 1px solid #E4E6EF;padding: 10px 0 0 0; margin: 0 0 40px 0;border-radius:5px;">
                                    <div style="width: 120px;background: #fa8c15;text-align: center;margin: 0 auto;color: white;padding: 5px 0;
                                        position: absolute;top:-16px;left:10px;box-shadow: 1px 2px 5px 0px rgba(0,0,0,0.5);"><img src="images/rice.png" style="width: 20px;margin-right: 5px;"/>Raw Material</div>
                                    <div class="col-md-12 pt-5 material_section">
                                        <table class="table table-bordered">
                                            <thead class="bg-primary">
                                                <th>Raw Material</th>
                                                <th class="text-center">Available Qty</th>
                                                <th class="text-center">Particular</th>
                                                <th class="text-center">Per Unit Qty</th>
                                                <th class="text-center">Qty Needed</th>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <select name="from_product_id" id="from_product_id" class="form-control selectpicker" data-live-search="true" data-live-search-placeholder="Search">
                                                            <option value="">Select Please</option>
                                                            @if (!$products->isEmpty())
                                                                @foreach ($products as $product)
                                                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="text" name="product_stock_qty" id="product_stock_qty"  class="form-control">
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="text" name="product_particular" id="product_particular"  class="form-control">
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="text" name="product_per_unit_qty" id="product_per_unit_qty"  class="form-control">
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="text" name="product_required_qty" id="product_required_qty"  class="form-control">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <select name="bag_id" id="bag_id" class="form-control selectpicker" data-live-search="true" data-live-search-placeholder="Search">
                                                            <option value="">Select Please</option>
                                                        
                                                        </select>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="text" name="bag_stock_qty" id="bag_stock_qty"  class="form-control">
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="text" name="bag_particular" id="bag_particular"  class="form-control">
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="text" name="bag_per_unit_qty" id="bag_per_unit_qty"  class="form-control">
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="text" name="bag_required_qty" id="bag_required_qty"  class="form-control">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" class="text-right font-weight-bolder">Fine Rice Quantity to Build</td>
                                                    <td><input type="text" name="total_qty" id="total_qty"  class="form-control"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" class="text-right font-weight-bolder">Total Bag Used Quantity</td>
                                                    <td><input type="text" name="total_bag" id="total_bag"  class="form-control"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

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