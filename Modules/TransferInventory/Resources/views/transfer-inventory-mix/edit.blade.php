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
                    <a href="{{ route('transfer.inventory.mix') }}" type="button" class="btn btn-danger btn-sm mr-3"><i class="fas fa-window-close"></i> Cancel</a>
                    <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn" onclick="store_data()"><i class="fas fa-save"></i> Update</button>
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom">
            <div class="card-body">

                <!--begin: Datatable-->
                <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">


                    <form id="transfer_inventory_form" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="transfer_id" value="{{ $transfer->id }}">
                        <div class="row">
                            <x-form.textbox labelName="Transfer Date" name="transfer_date" value="{{ $transfer->transfer_date }}" required="required" class="date" col="col-md-3"/>

                            <x-form.selectbox labelName="WIP Batch" name="batch_id" required="required"  class="selectpicker" col="col-md-3">
                                @if (!$batches->isEmpty())
                                    @foreach ($batches as $batch)
                                        <option value="{{ $batch->id }}" {{ $transfer->batch_id == $batch->id ? 'selected' : '' }}>{{ $batch->batch_no }}</option>
                                    @endforeach
                                @endif
                            </x-form.selectbox>
                            <x-form.selectbox labelName="Mix Item" name="product_id" onchange="setCategory()" required="required" class="selectpicker" col="col-md-3">
                                @if(!$products->isEmpty())  
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}" {{ $transfer->product_id == $product->id ? 'selected' : '' }} data-category="{{ $product->category_id }}">{{ $product->name }}</option>
                                    @endforeach
                                @endif
                            </x-form.selectbox>
                            <x-form.selectbox labelName="Mix Class" name="category_id" required="required" class="selectpicker" col="col-md-3">
                                @if(!$categories->isEmpty())  
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"  {{ $transfer->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                @endif
                            </x-form.selectbox>
                            <x-form.selectbox labelName="Transfer To" name="to_site_id" required="required" onchange="getLocations(this.value,2)"  class="selectpicker" col="col-md-3">
                                @if(!$sites->isEmpty())  
                                @foreach ($sites as $site)
                                    <option value="{{ $site->id }}" {{ $transfer->to_site_id == $site->id ? 'selected' : '' }}>{{ $site->name }}</option>
                                @endforeach
                            @endif
                            </x-form.selectbox>
                            @php 
                                $locations = DB::table('locations')->where('site_id',$transfer->to_site_id)->get();
                                @endphp
                            <x-form.selectbox labelName="To Location" name="to_location_id"  required="required" class="selectpicker" col="col-md-3">
                                
                                @if(!$locations->isEmpty())  
                                    @foreach ($locations as $location)
                                        <option value="{{ $location->id }}" {{ $location->id == $transfer->to_location_id ? 'selected' : '' }}>{{ $location->name }}</option>
                                    @endforeach
                                @endif
                            </x-form.selectbox>
                            <div class="form-group col-md-3">
                                <label for="memo_no">Memo No.</label>
                                <input type="text" class="form-control" name="memo_no" id="memo_no" value="{{ $transfer->memo_no }}" />
                            </div>

                            <div class="form-group col-md-3">
                                <label for="number">Number</label>
                                <input type="text" class="form-control" name="number" id="number"  value="{{ $transfer->number }}" />
                            </div>

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
                                        @if(!$transfer->materials->isEmpty())
                                            @foreach ($transfer->materials as $key => $item)
                                                @php
                                                    $locations = DB::table('locations')->where('site_id',$item->pivot->from_site_id)->get();
                                                    $materials = DB::table('site_material as sm')
                                                                ->select('m.id','m.material_name','c.name as category_name','u.unit_name','sm.qty')
                                                                ->leftJoin('materials as m','sm.material_id','=','m.id')
                                                                ->leftJoin('categories as c','m.category_id','=','c.id')
                                                                ->leftJoin('units as u','m.unit_id','=','u.id')
                                                                ->where([
                                                                    'sm.site_id'     => $item->pivot->from_site_id,
                                                                    'sm.location_id' => $item->pivot->from_location_id
                                                                ])->get();
                                                    $stock_qty = 0;
                                                @endphp 
                                                <tr>
                                                    <td style="width: 300px;">                                                  
                                                        <select  style="width: 300px;" name="materials[{{ $key+1 }}][from_site_id]" id="materials_{{ $key+1 }}_from_site_id" class="fcs col-md-12 from_site_id form-control selectpicker" onchange="getLocations(this.value,1,'{{ $key+1 }}')"  data-live-search="true" data-row="{{ $key+1 }}">                                            
                                                            <option value="">Select Please</option>  
                                                            @if(!$sites->isEmpty())  
                                                                @foreach ($sites as $site)
                                                                    <option value="{{ $site->id }}" {{ $site->id == $item->pivot->from_site_id ? 'selected' : '' }}>{{ $site->name }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </td>  
                                                    <td>                                                  
                                                        <select name="materials[{{ $key+1 }}][from_location_id]" id="materials_{{ $key+1 }}_from_location_id" onchange="material_list('{{ $key+1 }}')" class="fcs col-md-12 from_location_id form-control selectpicker"  data-live-search="true" data-row="{{ $key+1 }}">                                            
                                                            <option value="">Select Please</option>  
                                                            @if(!$locations->isEmpty())  
                                                                @foreach ($locations as $location)
                                                                    <option value="{{ $location->id }}" {{ $location->id == $item->pivot->from_location_id ? 'selected' : '' }}>{{ $location->name }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </td>  
                                                    <td>                     
                                                        <select name="materials[{{ $key+1 }}][id]" id="materials_{{ $key+1 }}_id" class="fcs col-md-12 form-control selectpicker" onchange="setMaterialDetails('{{ $key+1 }}')"  data-live-search="true" data-row="{{ $key+1 }}">    
                                                            <option value="">Select Please</option>                                        
                                                            @if(!$materials->isEmpty())
                                                                @foreach ($materials as $value)
                                                                @php
                                                                    
                                                                    if($value->id == $item->id)
                                                                    {
                                                                        $stock_qty = $value->qty ?? 0;
                                                                    }
                                                                @endphp
                                                                <option value="{{ $value->id }}" {{ $value->id == $item->id ? 'selected' : '' }} data-stockqty="{{ $value->id == $item->id ? ($value->qty + $item->pivot->qty) : $value->qty }}" data-category="{{ $value->category_name }}" data-unitname="{{ $value->unit_name }}">{{ $value->material_name }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </td>    
                                                    <td style="width: 350px;"><input type="text" value="{{ $item->pivot->description }}" class="form-control" style="width: 350px;" name="materials[{{ $key+1 }}][description]" id="materials_{{ $key+1 }}_description" data-row="{{ $key+1 }}"></td>                                    
                                                    <td class="category_name_{{ $key+1 }} text-center" style="width: 120px;" id="category_name_{{ $key+1 }}"  data-row="{{ $key+1 }}">{{ $item->category->name }}</td>
                                                    <td class="unit_name_{{ $key+1 }} text-center" style="min-width: 80px;" id="unit_name_{{ $key+1 }}"  data-row="{{ $key+1 }}">{{ $item->unit->unit_name }}</td>
                                                    <td style="width: 120px;"><input type="text" value="{{ $stock_qty + $item->pivot->qty }}" class="form-control text-center" style="width: 120px;" name="materials[{{ $key+1 }}][available_qty]" id="materials_{{ $key+1 }}_available_qty" readonly  data-row="{{ $key+1 }}"></td>
                                                    <td style="width: 120px;"><input type="text" value="{{ $item->pivot->qty }}" class="form-control qty text-center" style="width: 120px;" onkeyup="checkQty({{ $key+1 }})" name="materials[{{ $key+1 }}][qty]" id="materials_{{ $key+1 }}_qty"  data-row="{{ $key+1 }}"></td>
                                                    <td class="text-center" data-row="{{ $key+1 }}">
                                                        @if($key != 0)
                                                        <button type="button" class="btn btn-danger btn-sm remove-material"><i class="fas fa-trash"></i></button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                    <tfoot class="bg-primary">
                                        <th colspan="7" class="font-weight-bolder">Total</th>
                                        <th id="total-qty" class="text-center font-weight-bolder">0</th>
                                        <th class="text-center"><button type="button" data-toggle="tooltip" data-theme="dark" title="Add More" class="btn btn-success btn-sm add-material"><i class="fas fa-plus"></i></button></th>
                                    </tfoot>
                                </table>
                            </div>

            
                            <div class="col-md-12">
                                <input type="hidden" name="item" value="{{ $transfer->item }}">
                                <input type="hidden" name="total_qty" value="{{ $transfer->total_qty }}">
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
    @if(!$transfer->materials->isEmpty())
    count = "{{ count($transfer->materials) }}";
    @endif
    $('#material_table').on('click','.add-material',function(){
        count++;
        material_row_add(count);
    }); 

    function material_row_add(count){
        var html = `<tr>
                        <td style="width: 300px;">                                                  
                            <select  style="width: 300px;" name="materials[${count}][from_site_id]" id="materials_${count}_from_site_id" class="fcs col-md-12 from_site_id form-control selectpicker" onchange="getLocations(this.value,1,${count})"  data-live-search="true" data-row="${count}">                                            
                                <option value="">Select Please</option>  
                                @if(!$sites->isEmpty())  
                                    @foreach ($sites as $site)
                                        <option value="{{ $site->id }}">{{ $site->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </td>  
                        <td>                                                  
                            <select name="materials[${count}][from_location_id]" id="materials_${count}_from_location_id" onchange="material_list(${count})" class="fcs col-md-12 from_location_id form-control selectpicker"  data-live-search="true" data-row="${count}">                                            
                                <option value="">Select Please</option>  
                            </select>
                        </td>  
                        <td>                     
                            <select name="materials[${count}][id]" id="materials_${count}_id" class="fcs col-md-12 form-control selectpicker" onchange="setMaterialDetails(${count})"  data-live-search="true" data-row="${count}">    
                                <option value="">Select Please</option>                                        

                            </select>
                        </td>    
                        <td style="width: 350px;"><input type="text" class="form-control" style="width: 350px;" name="materials[${count}][description]" id="materials_${count}_description" data-row="${count}"></td>                                    
                        <td class="category_name_${count} text-center" style="width: 120px;" id="category_name_${count}"  data-row="${count}"></td>
                        <td class="unit_name_${count} text-center" style="min-width: 80px;" id="unit_name_${count}"  data-row="${count}"></td>
                        <td style="width: 120px;"><input type="text" class="form-control text-center" style="width: 120px;" name="materials[${count}][available_qty]" id="materials_${count}_available_qty" readonly  data-row="${count}"></td>
                        <td style="width: 120px;"><input type="text" class="form-control qty text-center" style="width: 120px;" onkeyup="checkQty(${count})" name="materials[${count}][qty]" id="materials_${count}_qty"  data-row="${count}"></td>
                        <td class="text-center" data-row="${count}"><button type="button" class="btn btn-danger btn-sm remove-material"><i class="fas fa-trash"></i></button></td>
                    </tr>`;
        $('#material_table tbody').append(html);
        $('#material_table .selectpicker').selectpicker();
    }
});
function setCategory(){
    $('#category_id').val($(`#product_id option:selected`).data('category'));
    $('#category_id.selectpicker').selectpicker('refresh');
}
function setMaterialDetails(row){
    let stock_qty     = $(`#materials_${row}_id option:selected`).data('stockqty');
    let unit_name     = $(`#materials_${row}_id option:selected`).data('unitname');
    let category_name = $(`#materials_${row}_id option:selected`).data('category');

    $(`.unit_name_${row}`).text(unit_name);
    $(`.category_name_${row}`).text(category_name);
    $(`#materials_${row}_available_qty`).val(stock_qty);
} 
function checkQty(row)
{
    let available_qty = $(`#materials_${row}_available_qty`).val() ? parseFloat($(`#materials_${row}_available_qty`).val()) : 0;
    let qty           = $(`#materials_${row}_qty`).val() ? parseFloat($(`#materials_${row}_qty`).val()) : 0;
    if(qty < 0 || qty == ''){
        qty = 0;
        $(`#materials_${row}_qty`).val('');
    }else if(qty > available_qty)
    {
        qty = available_qty;
        $(`#materials_${row}_qty`).val(available_qty);
        notification('error','Transfer quantity must be less or equal than available stock quantity!');
    }
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
function getLocations(site_id,selector,row='')
{
    $.ajax({
        url:"{{ url('site-wise-location-list') }}/"+site_id,
        type:"GET",
        dataType:"JSON",
        success:function(data){
            var html = '<option value="">Select Please</option>';
            $.each(data, function(key, value) {
                html += '<option value="'+ key +'">'+ value +'</option>';
            });
            if(selector == 1)
            {
                $(`#materials_${row}_from_location_id`).empty().append(html);
                $(`#materials_${row}_from_location_id.selectpicker`).selectpicker('refresh');
            }else{
                $(`#to_location_id`).empty().append(html);
                $(`#to_location_id.selectpicker`).selectpicker('refresh');
            }
        },
    });
}

function material_list(row)
{
    const site_id       = $(`#materials_${row}_from_site_id option:selected`).val();
    const location_id   = $(`#materials_${row}_from_location_id option:selected`).val();
    if(site_id && location_id)
    {
        $.ajax({
            url:"{{ route('material.list') }}",
            type:"POST",
            data:{
                site_id:site_id,location_id:location_id,_token:_token
            },
            success:function(data){
                $(`#materials_${row}_id`).empty().append(data);
                $(`#materials_${row}_id.selectpicker`).selectpicker('refresh');
            },
        });
    }
}

function store_data(){
    var rownumber = $('table#material_table tbody tr:last').index();
    if (rownumber < 0) {
        notification("error","Please insert material to order table!")
    }else{
        let form = document.getElementById('transfer_inventory_form');
        let formData = new FormData(form);
        let url = "{{route('transfer.inventory.mix.update')}}";
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
                $('#transfer_inventory_form').find('.is-invalid').removeClass('is-invalid');
                $('#transfer_inventory_form').find('.error').remove();
                if (data.status == false) {
                    $.each(data.errors, function (key, value) {
                        var key = key.split('.').join('_');
                        $('#transfer_inventory_form input#' + key).addClass('is-invalid');
                        $('#transfer_inventory_form textarea#' + key).addClass('is-invalid');
                        $('#transfer_inventory_form select#' + key).parent().addClass('is-invalid');
                        $('#transfer_inventory_form #' + key).parent().append(
                            '<small class="error text-danger">' + value + '</small>');
                    });
                } else {
                    notification(data.status, data.message);
                    if (data.status == 'success') {
                        window.location.replace("{{ url('transfer-inventory/mix') }}");
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