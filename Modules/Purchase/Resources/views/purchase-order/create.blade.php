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
                    <button type="button" class="btn btn-danger btn-sm mr-3"><i class="fas fa-sync-alt"></i> Reset</button>
                    <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn" onclick="store_data()"><i class="fas fa-save"></i> Save</button>
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
                                <input type="text" class="form-control" name="memo_no" id="memo_no" />
                            </div>
                            <x-form.textbox labelName="Order Date" name="order_date" value="{{ date('Y-m-d') }}" required="required" class="date" col="col-md-3"/>
                            <x-form.textbox labelName="Delivery Date" name="delivery_date" value="{{ date('Y-m-d') }}" required="required" class="date" col="col-md-3"/>
                            <x-form.textbox labelName="PO No." name="po_no"  required="required"  col="col-md-3"/>
                            <x-form.textbox labelName="NOS Truck" name="nos_truck"  required="required"  col="col-md-3"/>
                            <x-form.selectbox labelName="Vendor" name="vendor_id" required="required" class="selectpicker" onchange="getViaVendorList(this.value)" col="col-md-3">
                                @if (!$vendors->isEmpty())
                                    @foreach ($vendors as $vendor)
                                        <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                    @endforeach
                                @endif
                            </x-form.selectbox>
                            <x-form.selectbox labelName="Via Vendor" name="via_vendor_id"  class="selectpicker" col="col-md-3"/>
                            <div class="col-md-12">
                                <table class="table table-bordered" id="material_table">
                                    <thead class="bg-primary">
                                        <th>Item</th>
                                        <th>Description</th>
                                        <th class="text-center">Class</th>
                                        <th class="text-center">Unit</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-right">Rate</th>
                                        <th class="text-right">Subtotal</th>
                                        <th class="text-center"><i class="fas fa-trash text-white"></i></th>
                                    </thead>
                                    <tbody>

                                        <tr>
                                            <td class="col-md-3">                                                  
                                                <select name="materials[1][id]" id="materials_1_id" class="fcs col-md-12 form-control selectpicker" onchange="setMaterialDetails(1)"  data-live-search="true" data-row="1">    
                                                    <option value="">Select Please</option>                                        
                                                    @if (!$materials->isEmpty())
                                                        @foreach ($materials as $material)
                                                            <option value="{{ $material->id }}" data-unitid={{ $material->unit_id }} data-unitname="{{ $material->unit->unit_name }}" data-category="{{ $material->category->name }}">{{ $material->material_name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </td>    
                                            <td><input type="text" class="form-control" name="materials[1][description]" id="materials_1_description" data-row="1"></td>                                    
                                            <td>
                                                <select name="materials[1][item_class_id]" id="materials_1_item_class_id" class="fcs col-md-12 form-control selectpicker" data-live-search="true" data-row="1">    
                                                    <option value="">Select Please</option>                                        
                                                    @if (!$classes->isEmpty())
                                                        @foreach ($classes as $class)
                                                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </td>
                                            <td class="unit_name_1 text-center"  id="unit_name_1"  data-row="1"></td>
                                            <td><input type="text" class="form-control qty text-center" onkeyup="calculateRowTotal(1)" name="materials[1][qty]" id="materials_1_qty" data-row="1"></td>
                                            <td><input type="text" class="text-right form-control net_unit_cost" onkeyup="calculateRowTotal(1)" name="materials[1][net_unit_cost]" id="materials_1_net_unit_cost" data-row="1"></td>
                                            <td class="subtotal_1 text-right" id="sub_total_1" data-row="1"></td>
                                            <td class="text-center" data-row="1"></td>
                                            <input type="hidden" id="materials_1_purchase_unit_id" name="materials[1][purchase_unit_id]" data-row="1">
                                            <input type="hidden" class="subtotal" id="materials_1_subtotal" name="materials[1][subtotal]" data-row="1">
                                        </tr>

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
                            
                            <div class="col-md-12">
                                <input type="hidden" name="item" id="item">
                                <input type="hidden" name="total_qty" id="total_qty">
                                <input type="hidden" name="grand_total" id="grand_total">
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
$(document).ready(function () {
    $('.date').datetimepicker({format: 'YYYY-MM-DD'});

    $('#material_table').on('click','.remove-material',function(){
        $(this).closest('tr').remove();
        calculateTotal();
    });

    var count = 1;
    $('#material_table').on('click','.add-material',function(){
        count++;
        material_row_add(count);
    }); 
       
    function material_row_add(count){
        var html = `<tr>
                        <td class="col-md-3">                                                  
                            <select name="materials[${count}][id]" id="materials_${count}_id" class="fcs col-md-12 form-control selectpicker" onchange="setMaterialDetails(${count})"  data-live-search="true" data-row="${count}">    
                                <option value="">Select Please</option>                                        
                                @if (!$materials->isEmpty())
                                    @foreach ($materials as $material)
                                        <option value="{{ $material->id }}" data-unitid={{ $material->unit_id }} data-unitname="{{ $material->unit->unit_name }}" data-category="{{ $material->category->name }}">{{ $material->material_name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </td>    
                        <td><input type="text" class="form-control" name="materials[${count}][description]" id="materials_${count}_description" data-row="${count}"></td>                                    
                        <td>
                            <select name="materials[${count}][item_class_id]" id="materials_${count}_item_class_id" class="fcs col-md-12 form-control selectpicker" data-live-search="true" data-row="${count}">    
                                <option value="">Select Please</option>                                        
                                @if (!$classes->isEmpty())
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </td>
                        <td class="unit_name_${count} text-center"  id="unit_name_${count}"  data-row="${count}"></td>
                        <td><input type="text" class="form-control qty text-center" onkeyup="calculateRowTotal(${count})" name="materials[${count}][qty]" id="materials_${count}_qty"  data-row="${count}"></td>
                        <td><input type="text" class="text-right form-control net_unit_cost" onkeyup="calculateRowTotal(${count})" name="materials[${count}][net_unit_cost]" id="materials_${count}_net_unit_cost" data-row="${count}"></td>
                        <td class="subtotal_${count} text-right" id="sub_total_${count}" data-row="${count}"></td>
                        <td class="text-center" data-row="${count}"><button type="button" class="btn btn-danger btn-sm remove-material"><i class="fas fa-trash"></i></button></td>
                        <input type="hidden" id="materials_${count}_purchase_unit_id" name="materials[${count}][purchase_unit_id]" data-row="${count}">
                        <input type="hidden" class="subtotal" id="materials_${count}_subtotal" name="materials[${count}][subtotal]" data-row="${count}">
                    </tr>`;
        $('#material_table tbody').append(html);
        $('#material_table .selectpicker').selectpicker();
    }
});
function setMaterialDetails(row){
    let unit_id       = $(`#materials_${row}_id option:selected`).data('unitid');
    let unit_name     = $(`#materials_${row}_id option:selected`).data('unitname');

    $(`.unit_name_${row}`).text(unit_name);
    $(`#materials_${row}_purchase_unit_id`).val(unit_id);
} 
function calculateRowTotal(row)
{
    let cost = $(`#materials_${row}_net_unit_cost`).val() ? parseFloat($(`#materials_${row}_net_unit_cost`).val()) : 0;
    let qty = $(`#materials_${row}_qty`).val() ? parseFloat($(`#materials_${row}_qty`).val()) : 0;
    if(qty < 0 || qty == ''){
        qty = 0;
        $(`#materials_${row}_qty`).val('');
    }
    if(cost < 0 || cost == ''){
        cost = 0;
        $(`#materials_${row}_net_unit_cost`).val('');
    }

    $(`.subtotal_${row}`).text(parseFloat(qty * cost));
    $(`#materials_${row}_subtotal`).val(parseFloat(qty * cost));
    
    calculateTotal();
}

function calculateTotal()
{
    //sum of qty
    var total_qty = 0;
    $('.qty').each(function() {
        if($(this).val() == ''){
            total_qty += 0;
        }else{
            total_qty += parseFloat($(this).val());
        }
    });
    $('#total-qty').text(total_qty);
    $('input[name="total_qty"]').val(total_qty);

    //sum of subtotal
    var total = 0;
    $('.subtotal').each(function() {
        total += parseFloat($(this).val());
    });
    $('#total').text(total);
    $('input[name="grand_total"]').val(total);

    var item = $('#material_table tbody tr:last').index()+1;
    $('input[name="item"]').val(item);
}

function getViaVendorList(vendor_id)
{
    $.ajax({
        url:"{{ url('vendor-wise-list') }}/"+vendor_id,
        type:"GET",
        dataType:"JSON",
        success:function(data){
            $('#via_vendor_id').empty();
            var html = '<option value="">Select Please</option>';
            $.each(data, function(key, value) {
                html += '<option value="'+ key +'">'+ value +'</option>';
            });
            $('#via_vendor_id').append(html);
            $('#via_vendor_id.selectpicker').selectpicker('refresh');
        },
    });
}

function store_data(){
    var rownumber = $('table#material_table tbody tr:last').index();
    if (rownumber < 0) {
        notification("error","Please insert material to order table!")
    }else{
        let form = document.getElementById('purchase_store_form');
        let formData = new FormData(form);
        let url = "{{route('purchase.order.store')}}";
        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            dataType: "JSON",
            contentType: false,
            processData: false,
            cache: false,
            beforeSend: function(){
                $('#save-btn').addClass('spinner spinner-white spinner-right');
            },
            complete: function(){
                $('#save-btn').removeClass('spinner spinner-white spinner-right');
            },
            success: function (data) {
                $('#purchase_store_form').find('.is-invalid').removeClass('is-invalid');
                $('#purchase_store_form').find('.error').remove();
                if (data.status == false) {
                    $.each(data.errors, function (key, value) {
                        var key = key.split('.').join('_');
                        $('#purchase_store_form input#' + key).addClass('is-invalid');
                        $('#purchase_store_form textarea#' + key).addClass('is-invalid');
                        $('#purchase_store_form select#' + key).parent().addClass('is-invalid');
                        $('#purchase_store_form #' + key).parent().append(
                            '<small class="error text-danger">' + value + '</small>');
                    });
                } else {
                    notification(data.status, data.message);
                    if (data.status == 'success') {
                        window.location.replace("{{ url('purchase/order/view') }}/"+data.purchase_id);
                        
                        
                    }
                }

            },
            error: function (xhr, ajaxOption, thrownError) {
                console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
            }
        });
    }
    
}
</script>
@endpush