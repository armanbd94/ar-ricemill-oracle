@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link rel="stylesheet" href="css/jquery-ui.css" />
<link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
<style>
    
    #material_table tr td:first-child,
    #material_table tr td:nth-child(2){width:185px !important;}

    #material_table tr td:nth-child(3){width:200px !important;}

    #material_table tr td:first-child .dropdown.bootstrap-select,
    #material_table tr td:nth-child(2) .dropdown.bootstrap-select
    {width:185px !important;}
    #material_table tr td:nth-child(3) .dropdown.bootstrap-select
    {width:200px !important;}

    #material_table .qty_column,
    #material_table .qty
    {width:100px !important;}
</style>
@endpush

@section('content')
<div class="d-flex flex-column-fluid">
    <div class="container-fluid">
        <!--begin::Notice-->
        <div class="card card-custom custom-card">
            <div class="card-header flex-wrap p-0">
                <div class="card-toolbar m-0">
                    {{-- <div class="btn-group">
                        <button type="button" class="btn btn-secondary btn-sm font-weight-bolder dropdown-toggle mr-3" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          Right-aligned menu
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <form class="col-md-12">
                               <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="exampleDropdownFormEmail1">Email address</label>
                                    <input type="email" class="form-control" id="exampleDropdownFormEmail1"
                                        placeholder="email@example.com">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="exampleDropdownFormPassword1">Password</label>
                                    <input type="password" class="form-control" id="exampleDropdownFormPassword1"
                                        placeholder="Password">
                                </div>
                                <button type="submit" class="btn btn-primary">Sign in</button>
                               </div>
                            </form>
                        </div>
                    </div> --}}
                    <!--begin::Button-->
                    <a href="{{ route('purchase.order') }}" class="btn btn-warning btn-sm font-weight-bolder"> 
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

                            <x-form.selectbox labelName="Finish Goods" name="via_vendor_id" col="col-md-3" class="selectpicker">
                                <option value="">Select Please</option>
                                <option value="">Product One</option>
                                <option value="">Product Two</option>
                            </x-form.selectbox>
                            <x-form.selectbox labelName="Transfer To" name="to_site_id" col="col-md-3" class="selectpicker">
                                <option value="">Select Please</option>
                                <option value="">Site One</option>
                                <option value="">Site Two</option>
                            </x-form.selectbox>
                            <x-form.selectbox labelName="To Location" name="to_location_id" col="col-md-3" class="selectpicker">
                                <option value="">Select Please</option>
                                <option value="">Location One</option>
                                <option value="">Location Two</option>
                            </x-form.selectbox>
                            <x-form.selectbox labelName="Has Mixed Item?" name="has_mixed_item" onchange="hasMixedItem(this.value)" col="col-md-3" class="selectpicker">
                                <option value="1">Yes</option>
                                <option value="2" selected>No</option>
                            </x-form.selectbox>

                            <div class="col-md-12">
                                {{-- <div class="col-md-12 text-center">
                                    <h3 class="py-3 bg-warning text-white" style="margin: 10px auto 10px auto;width:300px;">Dumping Materials</h3>
                                </div> --}}
                                <table class="table table-bordered" id="material_table">
                                    <thead class="bg-primary">
                                        <th>Transfer From</th>
                                        <th>From Location</th>
                                        <th>Material</th>
                                        <th class="text-center">Category</th>
                                        <th class="text-center">Unit</th>
                                        <th class="text-center">Available Qty.</th>
                                        <th class="text-center">Required Qty.</th>
                                        <th class="text-right">Rate</th>
                                        <th class="text-right">Subtotal</th>
                                        <th class="text-center"><i class="fas fa-trash text-white"></i></th>
                                    </thead>
                                    <tbody>
                                        <td class="col-md-3">                                                  
                                            <select name="materials[1][site_id]" id="materials_1_site_id" class="fcs col-md-12 site_id form-control selectpicker" onchange="getLocations(this.value,1)"  data-live-search="true" data-row="1">                                            
                                                <option value="">Select Please</option>
                                                <option>Site One</option>
                                                <option>Site Two</option>
                                            </select>
                                        </td>  
                                        <td class="col-md-3">                                                  
                                            <select name="materials[1][location_id]" id="materials_1_location_id" class="fcs col-md-12 location_id form-control selectpicker"  data-live-search="true" data-row="1">                                            
                                                <option value="">Select Please</option>
                                                <option>Location One</option>
                                                <option>Location Two</option>
                                            </select>
                                        </td>  
                                        <td class="col-md-3">                                                  
                                            <select name="materials[1][id]" id="materials_1_id" class="fcs col-md-12 material form-control selectpicker" onchange="getMaterialDetails(this.value,1)"  data-live-search="true" data-row="1">                                            
                                                <option value="">Select Please</option>
                                                <option>Material One</option>
                                                <option>Material Two</option>
                                            </select>
                                        </td>    
                                        <td class="category-name_1 text-center"  id="category_name_1"  data-row="1"></td>
                                        <td class="unit-name_1 text-center"  id="unit_name_1"  data-row="1"></td>
                                        <td class="unit-name_1 text-center"  id="stock_qty_1"  data-row="1"></td>
                                        <td class="qty_column"><input type="text" class="form-control qty text-center" name="materials[1][qty]" id="materials_1_qty" value="1"  data-row="1"></td>
                                        <td class="net-unit_cost_1 text-center"  id="net_unit_cost_1"  data-row="1"></td>
                                        <td class="sub-total text-right" data-row="1"></td>
                                       
                                        <td class="text-center" data-row="1"><button type="button" class="edit-material btn btn-sm small-btn btn-primary mr-2 small-btn d-none"  id="edit_modal_1" data-toggle="modal" data-target="#editModal"><i class="fas fa-edit"></i></button></td>
                                        <input type="hidden" class="material-id_1" id="material_id_1" name="materials[1][id]" data-row="1">
                                        <input type="hidden" class="material-category_1" id="material_category_1" name="materials[1][category]" data-row="1">
                                        <input type="hidden" class="material-unit_1" id="material_unit_1" name="materials[1][unit]" data-row="1">
                                        <input type="hidden" class="text-right form-control net_unit_cost" name="materials[1][net_unit_cost]" id="materials_1_net_unit_cost" data-row="1">
                                        <input type="hidden" class="subtotal-value" id="subtotal_value_1" name="materials[1][subtotal]" data-row="1">
                                    </tbody>
                                    <tfoot class="bg-primary">
                                        <th colspan="6" class="font-weight-bolder">Total</th>
                                        <th id="total-qty" class="text-center font-weight-bolder">0</th>
                                        <th></th>
                                        <th id="total" class="text-right font-weight-bolder">0.00</th>
                                        <th class="text-center"><button type="button" data-toggle="tooltip" data-theme="dark" title="Add More" class="btn btn-success btn-sm add-material d-none"><i class="fas fa-plus"></i></button></th>
                                    </tfoot>
                                </table>
                            </div>
                            
                            <div class="form-group col-md-12">
                                <label for="shipping_cost">Note</label>
                                <textarea  class="form-control" name="note" id="note" cols="30" rows="3"></textarea>
                            </div>
                            <div class="col-md-12">
                                <table class="table table-bordered">
                                    <thead class="bg-primary">
                                        <th width="30%"><strong>Items</strong><span class="float-right" id="item">0.00</span></th>
                                        <th width="40%"></th>
                                        <th width="30%"><strong>Grand Total</strong><span class="float-right" id="grand_total">0.00</span></th>
                                    </thead>
                                </table>
                            </div>
                            <div class="col-md-12">
                                <input type="hidden" name="total_qty">
                                <input type="hidden" name="total_cost">
                                <input type="hidden" name="item">
                                <input type="hidden" name="grand_total">
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