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
                    <a href="{{ route('purchase.received') }}" type="button" class="btn btn-danger btn-sm mr-3"><i class="fas fa-window-close"></i> Cancel</a>
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


                    <form action="" id="purchase_received_form" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-4 required">
                                <label for="chalan_no">Memo No.</label>
                                <input type="text" class="form-control" name="memo_no" id="memo_no" value="{{ $purchase->memo_no }}" readonly />
                                <input type="hidden" class="form-control" name="receive_id" id="receive_id" value="{{ $receive->id }}" />
                                <input type="hidden" class="form-control" name="order_id" id="order_id" value="{{ $purchase->id }}" />
                                <input type="hidden" class="form-control" name="order_total_qty" id="order_total_qty" value="{{ $purchase->total_qty }}" />
                                
                            </div>
                            <div class="form-group col-md-4 required">
                                <label>Vendor</label>
                                <input type="text" class="form-control" name="vendor_name" value="{{ $purchase->vendor->name }}" readonly />
                                <input type="hidden" class="form-control" name="vendor_coa_id" value="{{ $purchase->vendor->coa->id }}" />
                            </div>
                            <div class="form-group col-md-4">
                                <label>Via Vendor</label>
                                <input type="text" class="form-control" value="{{ $purchase->via_vendor->name }}" readonly />
                            </div>
                            <x-form.textbox labelName="Challan No." name="challan_no" value="{{ $receive->challan_no }}"  required="required"  col="col-md-4"/>
                            <x-form.textbox labelName="Receive Date" name="received_date" value="{{ $receive->received_date }}" required="required" class="date" col="col-md-4"/>
                            <x-form.textbox labelName="Transport No." name="transport_no" value="{{ $receive->transport_no }}" col="col-md-4"/>
                            <div class="col-md-12 table-responsive" style="min-height: 500px;">

                                <table class="table table-bordered" id="material_table">
                                    <thead class="bg-primary">
                                        <th>Item</th>
                                        <th>Description</th>
                                        <th class="text-center">Class</th>
                                        <th class="text-center">Site</th>
                                        <th class="text-center">Location</th>
                                        <th class="text-center">Unit</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-right">Rate</th>
                                        <th class="text-right">Subtotal</th>
                                        <th class="text-center"><i class="fas fa-trash text-white"></i></th>
                                    </thead>
                                    <tbody>
                                        @if (!$receive->received_materials->isEmpty())
                                            @foreach ($receive->received_materials as $key => $item)
                                            @php
                                                $received_unit = DB::table('units')->where('id',$item->pivot->received_unit_id)->value('unit_name');
                                                $locations = DB::table('locations')->where('site_id',$item->pivot->site_id)->get();
                                            @endphp
                                            <tr>
                                                <td>                     
                                                    <select name="materials[{{ $key + 1 }}][id]" id="materials_{{ $key + 1 }}_id" class="fcs col-md-12 form-control selectpicker" onchange="setMaterialDetails({{ $key + 1 }})"  data-live-search="true" data-row="{{ $key + 1 }}">    
                                                        <option value="">Select Please</option>   
                                                        @if(!$purchase->materials->isEmpty())  
                                                            @foreach ($purchase->materials as $material)
                                                            @php
                                                            $unit_name = DB::table('units')->where('id',$material->pivot->purchase_unit_id)->value('unit_name');
                                                            @endphp
                                                                <option {{ $material->id == $item->id ? 'selected' : '' }} value="{{ $material->id }}" data-unitid="{{ $material->pivot->purchase_unit_id }}" data-unitname="{{ $unit_name }}" data-category="{{ $material->category->name }}" data-rate={{ $material->pivot->net_unit_cost }}>{{ $material->material_name }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </td>    
                                                <td><input type="text" class="form-control" value="{{ $item->pivot->description }}" style="width: 150px;" name="materials[{{ $key + 1 }}][description]" id="materials_{{ $key + 1 }}_description" data-row="{{ $key + 1 }}"></td>                                    
                                                <td class="category_name_{{ $key + 1 }} text-center" style="min-width: 120px;" id="category_name_{{ $key + 1 }}"  data-row="{{ $key + 1 }}">{{ $item->category->name }}</td>
                                                <td>                                                  
                                                    <select name="materials[{{ $key + 1 }}][site_id]" id="materials_{{ $key + 1 }}_site_id" class="fcs col-md-12 site_id form-control selectpicker" onchange="getLocations(this.value,{{ $key + 1 }})"  data-live-search="true" data-row="{{ $key + 1 }}">                                            
                                                        <option value="">Select Please</option>  
                                                        @if(!$sites->isEmpty())  
                                                            @foreach ($sites as $site)
                                                                <option value="{{ $site->id }}" {{ $site->id == $item->pivot->site_id ? 'selected' : '' }}>{{ $site->name }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </td>  
                                                <td>                                    

                                                    <select name="materials[{{ $key + 1 }}][location_id]" id="materials_{{ $key + 1 }}_location_id" class="fcs col-md-12 location_id form-control selectpicker"  data-live-search="true" data-row="{{ $key + 1 }}">                                            
                                                        <option value="">Select Please</option>  
                                                        @if(!$locations->isEmpty())  
                                                            @foreach ($locations as $location)
                                                                <option value="{{ $location->id }}" {{ $location->id == $item->pivot->location_id ? 'selected' : '' }}>{{ $location->name }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </td>  
                                                <td class="unit_name_{{ $key + 1 }} text-center" style="min-width: 80px;" id="unit_name_{{ $key + 1 }}"  data-row="{{ $key + 1 }}">{{ $received_unit }}</td>
                                                <td><input type="text" class="form-control qty text-center" value="{{ $item->pivot->received_qty }}" style="width: 120px;" onkeyup="calculateRowTotal({{ $key + 1 }})" name="materials[{{ $key + 1 }}][qty]" id="materials_{{ $key + 1 }}_qty"  data-row="{{ $key + 1 }}"></td>
                                                <td><input type="text" style="width: 120px;" value="{{ $item->pivot->net_unit_cost }}" class="text-right form-control net_unit_cost" name="materials[{{ $key + 1 }}][net_unit_cost]" id="materials_{{ $key + 1 }}_net_unit_cost" data-row="{{ $key + 1 }}" readonly></td>
                                                <td class="subtotal_{{ $key + 1 }} text-right" id="sub_total_{{ $key + 1 }}" data-row="{{ $key + 1 }}">{{ number_format($item->pivot->total,2,'.',',') }}</td>
                                                <td class="text-center" data-row="{{ $key + 1 }}"></td>
                                                <input type="hidden" id="materials_{{ $key + 1 }}_received_unit_id" value="{{ $item->pivot->received_unit_id }}" name="materials[{{ $key + 1 }}][received_unit_id]" data-row="{{ $key + 1 }}">
                                                <input type="hidden" class="subtotal" value="{{ $item->pivot->total }}" id="materials_{{ $key + 1 }}_subtotal" name="materials[{{ $key + 1 }}][subtotal]" data-row="{{ $key + 1 }}">
                                            </tr>
                                            @endforeach
                                        @endif
                                        
                                    </tbody>
                                    <tfoot class="bg-primary">
                                        <th colspan="6" class="font-weight-bolder">Total</th>
                                        <th id="total-qty" class="text-center font-weight-bolder">{{ $receive->total_qty }}</th>
                                        <th></th>
                                        <th id="total" class="text-right font-weight-bolder">{{ number_format($receive->grand_total,2,'.',',') }}</th>
                                        <th class="text-center"><button type="button" data-toggle="tooltip" data-theme="dark" title="Add More" class="btn btn-success btn-sm add-material"><i class="fas fa-plus"></i></button></th>
                                    </tfoot>
                                </table>
                            </div>

            
                            <div class="col-md-12">
                                <input type="hidden" name="item" value="{{ $receive->item }}">
                                <input type="hidden" name="total_qty" value="{{ $receive->total_qty }}">
                                <input type="hidden" name="grand_total" value="{{ $receive->grand_total }}">
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
    @if (!$receive->received_materials->isEmpty())
    count = "{{ count($receive->received_materials) }}";
    @endif
    $('#material_table').on('click','.add-material',function(){
        count++;
        material_row_add(count);
    }); 

    function material_row_add(count){
        var html = `<tr>
                        <td>                     
                            <select name="materials[${count}][id]" id="materials_${count}_id" class="fcs col-md-12 form-control selectpicker" onchange="setMaterialDetails(${count})"  data-live-search="true" data-row="${count}">    
                                <option value="">Select Please</option>   
                                @if(!$purchase->materials->isEmpty())  
                                    @foreach ($purchase->materials as $material)
                                    @php
                                    $unit_name = DB::table('units')->where('id',$material->pivot->purchase_unit_id)->value('unit_name');
                                    @endphp
                                        <option value="{{ $material->id }}" data-unitid="{{ $material->pivot->purchase_unit_id }}" data-unitname="{{ $unit_name }}" data-category="{{ $material->category->name }}" data-rate={{ $material->pivot->net_unit_cost }}>{{ $material->material_name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </td>    
                        <td><input type="text" class="form-control" style="width: 150px;" name="materials[${count}][description]" id="materials_${count}_description" data-row="${count}"></td>                                    
                        <td class="category_name_${count} text-center" style="min-width: 120px;" id="category_name_${count}"  data-row="${count}"></td>
                        <td>                                                  
                            <select name="materials[${count}][site_id]" id="materials_${count}_site_id" class="fcs col-md-12 site_id form-control selectpicker" onchange="getLocations(this.value,${count})"  data-live-search="true" data-row="${count}">                                            
                                <option value="">Select Please</option>  
                                @if(!$sites->isEmpty())  
                                    @foreach ($sites as $site)
                                        <option value="{{ $site->id }}">{{ $site->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </td>  
                        <td>                                                  
                            <select name="materials[${count}][location_id]" id="materials_${count}_location_id" class="fcs col-md-12 location_id form-control selectpicker"  data-live-search="true" data-row="${count}">                                            
                                <option value="">Select Please</option>  
                            </select>
                        </td>  
                        <td class="unit_name_${count} text-center" style="min-width: 80px;" id="unit_name_${count}"  data-row="${count}"></td>
                        <td><input type="text" class="form-control qty text-center" style="width: 120px;" onkeyup="calculateRowTotal(${count})" name="materials[${count}][qty]" id="materials_${count}_qty"  data-row="${count}"></td>
                        <td><input type="text" style="width: 120px;" class="text-right form-control net_unit_cost" name="materials[${count}][net_unit_cost]" id="materials_${count}_net_unit_cost" data-row="${count}" readonly></td>
                        <td class="subtotal_${count} text-right" id="sub_total_${count}" data-row="${count}"></td>
                        <td class="text-center" data-row="${count}"><button type="button" class="btn btn-danger btn-sm remove-material"><i class="fas fa-trash"></i></button></td>
                        <input type="hidden" id="materials_${count}_received_unit_id" name="materials[${count}][received_unit_id]" data-row="${count}">
                        <input type="hidden" class="subtotal" id="materials_${count}_subtotal" name="materials[${count}][subtotal]" data-row="${count}">
                    </tr>`;
        $('#material_table tbody').append(html);
        $('#material_table .selectpicker').selectpicker();
    }
});
function setMaterialDetails(row){
    let unit_id       = $(`#materials_${row}_id option:selected`).data('unitid');
    let unit_name     = $(`#materials_${row}_id option:selected`).data('unitname');
    let category_name = $(`#materials_${row}_id option:selected`).data('category');
    let net_unit_cost = $(`#materials_${row}_id option:selected`).data('rate');

    $(`.unit_name_${row}`).text(unit_name);
    $(`.category_name_${row}`).text(category_name);
    $(`#materials_${row}_received_unit_id`).val(unit_id);
    $(`#materials_${row}_net_unit_cost`).val(parseFloat(net_unit_cost));
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
        let form = document.getElementById('purchase_received_form');
        let formData = new FormData(form);
        let url = "{{route('purchase.received.update')}}";
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
                $('#purchase_received_form').find('.is-invalid').removeClass('is-invalid');
                $('#purchase_received_form').find('.error').remove();
                if (data.status == false) {
                    $.each(data.errors, function (key, value) {
                        var key = key.split('.').join('_');
                        $('#purchase_received_form input#' + key).addClass('is-invalid');
                        $('#purchase_received_form textarea#' + key).addClass('is-invalid');
                        $('#purchase_received_form select#' + key).parent().addClass('is-invalid');
                        $('#purchase_received_form #' + key).parent().append(
                            '<small class="error text-danger">' + value + '</small>');
                    });
                } else {
                    notification(data.status, data.message);
                    if (data.status == 'success') {
                        window.location.replace("{{ url('purchase/received') }}");
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