@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link rel="stylesheet" href="css/jquery-ui.css" />
<link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
<style>
    .dropdown.bootstrap-select{width: 200px;}
</style>
@endpush

@section('content')
<div class="d-flex flex-column-fluid">
    <div class="container-fluid">
        <!--begin::Notice-->
        <div class="card card-custom custom-card">
            <div class="card-header flex-wrap p-0">
                <div class="card-toolbar m-0">
                    <!--begin::Button-->
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


                    <form action="" id="cash_purchase_form" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <x-form.textbox labelName="Transfer Date" name="transfer_date" value="{{ date('Y-m-d') }}" required="required" class="date" col="col-md-3"/>

                            <x-form.selectbox labelName="WIP Batch" name="batch_id" required="required"  class="selectpicker" col="col-md-3">
                                @if (!$batches->isEmpty())
                                    @foreach ($batches as $batch)
                                        <option value="{{ $batch->id }}">{{ $batch->batch_no }}</option>
                                    @endforeach
                                @endif
                            </x-form.selectbox>

                            <div class="form-group col-md-3">
                                <label for="memo_no">Memo No.</label>
                                <input type="text" class="form-control" name="memo_no" id="memo_no"  />
                            </div>

                            <x-form.selectbox labelName="Mix Item" name="to_site_id" required="required" class="selectpicker" col="col-md-3">
                                
                            </x-form.selectbox>
                            <x-form.selectbox labelName="Transfer To" name="to_site_id" required="required" onchange="getLocations(this.value,2)"  class="selectpicker" col="col-md-3">
                                @if(!$sites->isEmpty())  
                                @foreach ($sites as $site)
                                    <option value="{{ $site->id }}">{{ $site->name }}</option>
                                @endforeach
                            @endif
                            </x-form.selectbox>
                            <x-form.selectbox labelName="To Location" name="to_location_id"  required="required" class="selectpicker" col="col-md-3"/>

                            <div class="col-md-12 table-responsive" style="min-height: 500px;">

                                <table class="table table-bordered" id="material_table">
                                    <thead class="bg-primary">
                                        <th class="text-center">Transfer From</th>
                                        <th class="text-center">Location</th>
                                        <th>Item</th>
                                        <th>Description</th>
                                        <th class="text-center">Class</th>
                                        <th class="text-center">Unit</th>
                                        <th class="text-center">Available Qty</th>
                                        <th class="text-center">Transfer Qty</th>
                                        <th class="text-center"><i class="fas fa-trash text-white"></i></th>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style="width: 300px;">                                                  
                                                <select  style="width: 300px;" name="materials[1][site_id]" id="materials_1_site_id" class="fcs col-md-12 site_id form-control selectpicker" onchange="getLocations(this.value,1)"  data-live-search="true" data-row="1">                                            
                                                    <option value="">Select Please</option>  
                                                    @if(!$sites->isEmpty())  
                                                        @foreach ($sites as $site)
                                                            <option value="{{ $site->id }}">{{ $site->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </td>  
                                            <td>                                                  
                                                <select name="materials[1][location_id]" id="materials_1_location_id" class="fcs col-md-12 location_id form-control selectpicker"  data-live-search="true" data-row="1">                                            
                                                    <option value="">Select Please</option>  
                                                </select>
                                            </td>  
                                            <td>                     
                                                <select name="materials[1][id]" id="materials_1_id" class="fcs col-md-12 form-control selectpicker" onchange="setMaterialDetails(1)"  data-live-search="true" data-row="1">    
                                                    <option value="">Select Please</option>                                        

                                                </select>
                                            </td>    
                                            <td style="width: 350px;"><input type="text" class="form-control" style="width: 350px;" name="materials[1][description]" id="materials_1_description" data-row="1"></td>                                    
                                            <td class="category_name_1 text-center" style="width: 120px;" id="category_name_1"  data-row="1"></td>
                                            <td class="unit_name_1 text-center" style="min-width: 80px;" id="unit_name_1"  data-row="1"></td>
                                            <td style="width: 120px;"><input type="text" class="form-control qty text-center" style="width: 120px;" name="materials[1][available_qty]" id="materials_1_qty" readonly  data-row="1"></td>
                                            <td style="width: 120px;"><input type="text" class="form-control qty text-center" style="width: 120px;" onkeyup="checkQty(1)" name="materials[1][transfer_qty]" id="materials_1_qty"  data-row="1"></td>
                                            <td class="text-center" data-row="1"></td>
                                            <input type="hidden" id="materials_1_transfer_unit_id" name="materials[1][transfer_unit_id]" data-row="1">
                                        </tr>
                                    </tbody>
                                    <tfoot class="bg-primary">
                                        <th colspan="7" class="font-weight-bolder">Total</th>
                                        <th id="total-qty" class="text-center font-weight-bolder">0</th>
                                        <th class="text-center"><button type="button" data-toggle="tooltip" data-theme="dark" title="Add More" class="btn btn-success btn-sm add-material"><i class="fas fa-plus"></i></button></th>
                                    </tfoot>
                                </table>
                            </div>

            
                            <div class="col-md-12">
                                <input type="hidden" name="item">
                                <input type="hidden" name="total_qty">
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
                                            <td style="width: 300px;">                                                  
                                                <select  style="width: 300px;" name="materials[1][site_id]" id="materials_1_site_id" class="fcs col-md-12 site_id form-control selectpicker" onchange="getLocations(this.value,1)"  data-live-search="true" data-row="1">                                            
                                                    <option value="">Select Please</option>  
                                                    @if(!$sites->isEmpty())  
                                                        @foreach ($sites as $site)
                                                            <option value="{{ $site->id }}">{{ $site->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </td>  
                                            <td>                                                  
                                                <select name="materials[1][location_id]" id="materials_1_location_id" class="fcs col-md-12 location_id form-control selectpicker"  data-live-search="true" data-row="1">                                            
                                                    <option value="">Select Please</option>  
                                                </select>
                                            </td>  
                                            <td>                     
                                                <select name="materials[1][id]" id="materials_1_id" class="fcs col-md-12 form-control selectpicker" onchange="setMaterialDetails(1)"  data-live-search="true" data-row="1">    
                                                    <option value="">Select Please</option>                                        

                                                </select>
                                            </td>    
                                            <td style="width: 350px;"><input type="text" class="form-control" style="width: 350px;" name="materials[1][description]" id="materials_1_description" data-row="1"></td>                                    
                                            <td class="category_name_1 text-center" style="width: 120px;" id="category_name_1"  data-row="1"></td>
                                            <td class="unit_name_1 text-center" style="min-width: 80px;" id="unit_name_1"  data-row="1"></td>
                                            <td style="width: 120px;"><input type="text" class="form-control qty text-center" style="width: 120px;" name="materials[1][available_qty]" id="materials_1_qty" readonly  data-row="1"></td>
                                            <td style="width: 120px;"><input type="text" class="form-control qty text-center" style="width: 120px;" onkeyup="checkQty(1)" name="materials[1][transfer_qty]" id="materials_1_qty"  data-row="1"></td>
                                            <td class="text-center" data-row="1"></td>
                                            <input type="hidden" id="materials_1_transfer_unit_id" name="materials[1][transfer_unit_id]" data-row="1">
                                        </tr>`;
        $('#material_table tbody').append(html);
        $('#material_table .selectpicker').selectpicker();
    }
});
function setMaterialDetails(row){
    let unit_id       = $(`#materials_${row}_id option:selected`).data('unitid');
    let unit_name     = $(`#materials_${row}_id option:selected`).data('unitname');
    let category_name = $(`#materials_${row}_id option:selected`).data('category');

    $(`.unit_name_${row}`).text(unit_name);
    $(`.category_name_${row}`).text(category_name);
    $(`#materials_${row}_purchase_unit_id`).val(unit_id);
} 
function calculateRowTotal(row)
{
    let qty = $(`#materials_${row}_qty`).val() ? parseFloat($(`#materials_${row}_qty`).val()) : 0;
    if(qty < 0 || qty == ''){
        qty = 0;
        $(`#materials_${row}_qty`).val('');
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

    var item = $('#material_table tbody tr:last').index()+1;
    $('input[name="item"]').val(item);
}
function getLocations(site_id,row)
{
    $.ajax({
        url:"{{ url('site-wise-location-list') }}/"+site_id,
        type:"GET",
        dataType:"JSON",
        success:function(data){
            $(`#materials_${row}_location_id`).empty();
            var html = '<option value="">Select Please</option>';
            $.each(data, function(key, value) {
                html += '<option value="'+ key +'">'+ value +'</option>';
            });
            $(`#materials_${row}_location_id`).append(html);
            $(`#materials_${row}_location_id.selectpicker`).selectpicker('refresh');
        },
    });
}

function store_data(){
    var rownumber = $('table#material_table tbody tr:last').index();
    if (rownumber < 0) {
        notification("error","Please insert material to order table!")
    }else{
        let form = document.getElementById('cash_purchase_form');
        let formData = new FormData(form);
        let url = "{{route('purchase.cash.store')}}";
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
                $('#cash_purchase_form').find('.is-invalid').removeClass('is-invalid');
                $('#cash_purchase_form').find('.error').remove();
                if (data.status == false) {
                    $.each(data.errors, function (key, value) {
                        var key = key.split('.').join('_');
                        $('#cash_purchase_form input#' + key).addClass('is-invalid');
                        $('#cash_purchase_form textarea#' + key).addClass('is-invalid');
                        $('#cash_purchase_form select#' + key).parent().addClass('is-invalid');
                        $('#cash_purchase_form #' + key).parent().append(
                            '<small class="error text-danger">' + value + '</small>');
                    });
                } else {
                    notification(data.status, data.message);
                    if (data.status == 'success') {
                        window.location.replace("{{ url('purchase/cash/view') }}/"+data.purchase_id);
                        
                        
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