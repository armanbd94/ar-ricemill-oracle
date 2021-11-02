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
                            <x-form.textbox labelName="Order Date" name="order_date" value="{{ date('Y-m-d') }}" required="required" class="date" col="col-md-3"/>

                            <x-form.selectbox labelName="Vendor" name="vendor_id" required="required" col="col-md-3">
                                <option value="">Vendor One</option>
                                <option value="">Vendor Two</option>
                            </x-form.selectbox>

                            <x-form.textbox labelName="Delivery Date" name="delivery_date" value="{{ date('Y-m-d') }}" required="required" class="date" col="col-md-3"/>
                            <x-form.textbox labelName="PO No." name="po_no"  required="required"  col="col-md-3"/>
                            <x-form.textbox labelName="NOS Truck" name="nos_truck"  required="required"  col="col-md-3"/>
                            <x-form.selectbox labelName="Via Vendor" name="via_vendor_id" col="col-md-3">
                                <option value="">Vendor One</option>
                                <option value="">Vendor Two</option>
                            </x-form.selectbox>
                            <x-form.selectbox labelName="Payment Status" name="payment_status" required="required" col="col-md-3">
                                @foreach (PAYMENT_STATUS as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </x-form.selectbox>
                            <div class="col-md-12">
                                <table class="table table-bordered" id="material_table">
                                    <thead class="bg-primary">
                                        <th>Item</th>
                                        <th>Description</th>
                                        <th class="text-center">Category</th>
                                        <th class="text-center">Unit</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-right">Rate</th>
                                        <th class="text-right">Subtotal</th>
                                        <th class="text-center"><i class="fas fa-trash text-white"></i></th>
                                    </thead>
                                    <tbody>
                                        <td class="col-md-3">                                                  
                                            <select name="materials[1][material_id]" id="material_list_1" class="fcs col-md-12 material_name form-control" onchange="getMaterialDetails(this,1)"  data-live-search="true" data-row="1">                                            
                                                <option>Material One</option>
                                                <option>Material Two</option>
                                            </select>
                                        </td>    
                                        <td><input type="text" class="text-right form-control" name="materials[1][description]" id="description_1" data-row="1"></td>                                    
                                        <td class="category-name_1 text-center"  id="category_name_1"  data-row="1"></td>
                                        <td class="unit-name_1 text-center"  id="unit_name_1"  data-row="1"></td>
                                        <td><input type="text" class="form-control qty text-center" name="materials[1][qty]" id="materials_qty_1" value="1"  data-row="1"></td>
                                        <td><input type="text" class="text-right form-control net_unit_cost" name="materials[1][net_unit_cost]" id="materials_net_unit_cost_1" data-row="1"></td>
                                        <td class="sub-total text-right" data-row="1"></td>
                                        <td class="text-center" data-row="1"><button type="button" class="edit-material btn btn-sm small-btn btn-primary mr-2 small-btn d-none"  id="edit_modal_1" data-toggle="modal" data-target="#editModal"><i class="fas fa-edit"></i></button></td>
                                        <input type="hidden" class="material-id_1" id="material_id_1" name="materials[1][id]" data-row="1">
                                        <input type="hidden" class="material-category_1" id="material_category_1" name="materials[1][category]" data-row="1">
                                        <input type="hidden" class="material-unit_1" id="material_unit_1" name="materials[1][unit]" data-row="1">
                                        <input type="hidden" class="subtotal-value" id="subtotal_value_1" name="materials[1][subtotal]" data-row="1">
                                    </tbody>
                                    <tfoot class="bg-primary">
                                        <th colspan="4" class="font-weight-bolder">Total</th>
                                        <th id="total-qty" class="text-center font-weight-bolder">0</th>
                                        <th></th>
                                        <th id="total" class="text-right font-weight-bolder">0.00</th>
                                        <th class="text-center"><button type="button" data-toggle="tooltip" data-theme="dark" title="Add More" class="btn btn-success btn-sm add-material"><i class="fas fa-plus"></i></button></th>
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
                                        <th><strong>Items</strong><span class="float-right" id="item">0.00</span></th>
                                        <th><strong>Total</strong><span class="float-right" id="subtotal">0.00</span></th>
                                        <th><strong>Order Tax</strong><span class="float-right" id="order_total_tax">0.00</span></th>
                                        <th><strong>Order Discount</strong><span class="float-right" id="order_total_discount">0.00</span></th>
                                        <th><strong>Shipping Cost</strong><span class="float-right" id="shipping_total_cost">0.00</span></th>
                                        <th><strong>Grand Total</strong><span class="float-right" id="grand_total">0.00</span></th>
                                    </thead>
                                </table>
                            </div>
                            <div class="col-md-12">
                                <input type="hidden" name="total_qty">
                                <input type="hidden" name="total_discount">
                                <input type="hidden" name="total_tax">
                                <input type="hidden" name="total_labor_cost">
                                <input type="hidden" name="total_cost">
                                <input type="hidden" name="item">
                                <input type="hidden" name="order_tax">
                                <input type="hidden" name="grand_total">
                            </div>
                            <div class="payment col-md-12 d-none">
                                <div class="row">
                                    <div class="form-group col-md-3 required">
                                        <label for="paid_amount">Paid Amount</label>
                                        <input type="text" class="form-control" name="paid_amount" id="paid_amount">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="due_amount">Due Amount</label>
                                        <input type="text" class="form-control" id="due_amount" readonly>
                                    </div>
                                    <x-form.selectbox labelName="Payment Method" name="payment_method" required="required"  col="col-md-3">
                                        @foreach (PAYMENT_METHOD as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </x-form.selectbox>
                                    <x-form.selectbox labelName="Account" name="account_id" required="required"  col="col-md-3"/>
                                    <div class="form-group col-md-3 d-none cheque_number required">
                                        <label for="cheque_number">Cheque No.</label>
                                        <input type="text" class="form-control" name="cheque_number" id="cheque_number">
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
</script>
@endpush