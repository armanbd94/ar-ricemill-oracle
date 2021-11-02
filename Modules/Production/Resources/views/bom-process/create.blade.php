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
                            <div class="form-group col-md-3 required">
                                <label for="chalan_no">Memo No.</label>
                                <input type="text" class="form-control" name="memo_no" id="memo_no" value="{{ $memo_no }}" readonly />
                            </div>

                            <div class="form-group col-md-3 required">
                                <label for="batch_no">Batch No.</label>
                                <input type="text" class="form-control" name="batch_no" id="batch_no" value=""  />
                            </div>

                            <x-form.textbox labelName="Date" name="transfer_date" value="{{ date('Y-m-d') }}" required="required" class="date" col="col-md-3"/>

                            <x-form.textbox labelName="Assemble From" name="number" required="required"  col="col-md-3"/>

                            <x-form.textbox labelName="Assemble To" name="number" required="required"  col="col-md-3"/>
                            <x-form.textbox labelName="Finish Goods" name="from_location" required="required"  col="col-md-3"/>
                            <x-form.selectbox labelName="Inventory Site" name="to_site_id" col="col-md-3" class="selectpicker">
                                <option value="">Select Please</option>
                                <option value="">Site One</option>
                                <option value="">Site Two</option>
                            </x-form.selectbox>

                            <x-form.selectbox labelName="Location" name="to_location_id" col="col-md-3" class="selectpicker">
                                <option value="">Select Please</option>
                                <option value="">Location One</option>
                                <option value="">Location Two</option>
                            </x-form.selectbox>

                            <div class="col-md-12 pt-5">
                                <div class="row" style="position: relative;border: 1px solid #E4E6EF;padding: 10px 0 0 0; margin: 0 0 40px 0;border-radius:5px;">
                                    <div style="width: 100px;background: #fa8c15;text-align: center;margin: 0 auto;color: white;padding: 5px 0;
                                        position: absolute;top:-16px;left:10px;box-shadow: 1px 2px 5px 0px rgba(0,0,0,0.5);"><img src="images/rice.png" style="width: 20px;margin-right: 5px;"/>Fine Rice</div>
                                    <div class="col-md-12 pt-5 material_section">
                                        <div class="row">
                                            
                                            <div class="form-group col-md-3 required">
                                                <label for="fine_rice_qty" class="form-control-label">Finish Goods Qunatity</label>
                                                <div class="input-group">
                                                    <input type="text" name="fine_rice_qty" id="fine_rice_qty" class="form-control">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text bg-primary text-white" style="border-top-right-radius: 0.42rem;border-bottom-right-radius: 0.42rem;border:0;">
                                                            KGs
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                                <!-- Start :: Raw Material -->
                                <div class="row" style="position: relative;border: 1px solid #E4E6EF;padding: 10px 0 0 0; margin: 0 0 40px 0;border-radius:5px;">
                                    <div style="width: 140px;background: #fa8c15;text-align: center;margin: 0 auto;color: white;padding: 5px 0;
                                        position: absolute;top:-16px;left:10px;box-shadow: 1px 2px 5px 0px rgba(0,0,0,0.5);"><img src="images/supply.png" style="width: 20px;margin-right: 5px;"/>Raw Materials</div>
                                    <div class="col-md-12 pt-5 material_section">
                                        <table class="table table-bordered">
                                            <thead class="bg-primary">
                                                <th>Raw Material</th>
                                                <th class="text-center">Unit</th>
                                                <th class="text-center">Stock Out Qty</th>
                                                <th class="text-center">Used Qty</th>
                                                <th class="text-center">Approx. Main Product Ratio</th>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Paddy One</td>
                                                    <td class="text-center">Kg</td>
                                                    <td class="text-center">100</td>
                                                    <td class="text-center">100</td>
                                                    <td class="text-center">90%</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- End :: Raw Material -->

                                <!-- Start :: Packaging Material -->
                                <div class="row" style="position: relative;border: 1px solid #E4E6EF;padding: 10px 0 0 0; margin: 0 0 20px 0;border-radius:5px;">
                                    <div style="width: 160px;background: #fa8c15;text-align: center;margin: 0 auto;color: white;padding: 5px 0;
                                        position: absolute;top:-16px;left:10px;box-shadow: 1px 2px 5px 0px rgba(0,0,0,0.5);"><img src="images/supply.png" style="width: 20px;margin-right: 5px;"/>Packaging Materials</div>
                                    <div class="col-md-12 pt-5 material_section">
                                        <table class="table table-bordered">
                                            <thead class="bg-primary">
                                                <th>Packaging Material</th>
                                                <th class="text-center">Unit</th>
                                                <th class="text-center">Quantity</th>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                            <select name="by_products[1][id]" id="by_products_1_id" required="required" class="form-control selectpicker" data-live-search="true" 
                                                            data-live-search-placeholder="Search">
                                                                <option value="">Select Please</option>
                                                                
                                                            </select>
                                                    </td>
                                                    <td class="text-center">Kg</td>
                                                    <td class="text-center">
                                                        <input type="text" name="rice_convertion_ratio" id="rice_convertion_ratio" class="form-control text-center">
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- End :: Packaging Material -->

                                <!-- Start :: Final Production -->
                                <div class="row pt-3"  style="position: relative;border: 1px solid #E4E6EF;padding: 10px 0 0 0; margin: 0 0 40px 0;border-radius:5px;">
                                    <div class="col-md-12" style="padding: 0 25px;">
                                        <table class="table table-borderless">
                                            <thead class="bg-primary">
                                                <tr>
                                                    <td width="70%" class="text-right font-weight-bolder text-white">Total Finished Goods Bag</td>
                                                    <td width="30%"><input type="text" name="fine_rice_qty" id="fine_rice_qty" class="form-control text-right"></td>
                                                </tr>
                                                <tr>
                                                    <td width="70%" class="text-right font-weight-bolder text-white">Per Unit Cost</td>
                                                    <td width="30%"><input type="text" name="fine_rice_qty" id="fine_rice_qty" class="form-control text-right"></td>
                                                </tr>
                                                <tr>
                                                    <td width="70%" class="text-right font-weight-bolder text-white">Per Bag Cost</td>
                                                    <td width="30%"><input type="text" name="fine_rice_qty" id="fine_rice_qty" class="form-control text-right"></td>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                                <!-- End :: Final Production -->

                                <!-- Start :: By Products -->
                                <div class="row mt-5" style="position: relative;border: 1px solid #E4E6EF;padding: 10px 0 0 0; margin: 0;border-radius:5px;">
                                    <div style="width: 120px;background: #fa8c15;text-align: center;margin: 0 auto;color: white;padding: 5px 0;
                                        position: absolute;top:-16px;left:10px;box-shadow: 1px 2px 5px 0px rgba(0,0,0,0.5);"><img src="images/grain-sack.png" style="width: 20px;margin-right: 5px;"/>By Products</div>
                                    <div class="col-md-12 pt-5 material_section">
                                        <table class="table table-bordered">
                                            <thead class="bg-primary">
                                                <th>By Product</th>
                                                <th class="text-center">Unit</th>
                                                <th class="text-center">Qty</th>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>By Product One</td>
                                                    <td class="text-center">Kg</td>
                                                    <td class="text-center">10</td>
                                                </tr>
                                                <tr>
                                                    <td>By Product Two</td>
                                                    <td class="text-center">Kg</td>
                                                    <td class="text-center">10</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- End :: By Products -->
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