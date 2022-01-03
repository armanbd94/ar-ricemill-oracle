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
                    <a href="{{ route('build.disassembly') }}" type="button" class="btn btn-danger btn-sm mr-3"><i class="fas fa-window-close"></i> Cancel</a>
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


                    <form action="" id="update_form" method="post" enctype="multipart/form-data" style="margin-bottom: 150px;">
                        @csrf
                        <input type="hidden" name="build_id" value="{{ $data->id }}">
                        <div class="row">
                            <x-form.textbox labelName="Date" name="build_date" value="{{ $data->build_date }}" required="required" class="date" col="col-md-3"/>

                            <x-form.selectbox labelName="WIP Batch" name="batch_id" required="required"  class="selectpicker" col="col-md-3">
                                @if (!$batches->isEmpty())
                                    @foreach ($batches as $batch)
                                        <option value="{{ $batch->id }}" {{ $data->batch_id == $batch->id ? 'selected' : '' }}>{{ $batch->batch_no }}</option>
                                    @endforeach
                                @endif
                            </x-form.selectbox>

                            <div class="form-group col-md-3">
                                <label for="memo_no">Memo No.</label>
                                <input type="text" class="form-control" name="memo_no" id="memo_no"  value="{{ $data->memo_no }}" />
                            </div>

                            <x-form.selectbox labelName="Transfer From" name="from_site_id" required="required" onchange="getLocations(this.value,1)"  class="selectpicker" col="col-md-3">
                                @if(!$sites->isEmpty())  
                                    @foreach ($sites as $site)
                                        <option value="{{ $site->id }}"  {{ $data->from_site_id == $site->id ? 'selected' : '' }}>{{ $site->name }}</option>
                                    @endforeach
                                @endif
                            </x-form.selectbox>

                            <x-form.selectbox labelName="From Location" name="from_location_id" required="required" onchange="material_list()" class="selectpicker" col="col-md-3"/>
                            
                            <x-form.textbox labelName="To Location (Storage)" name="to_location" value="Silo" property="readonly" required="required"  col="col-md-3"/>

                            <x-form.selectbox labelName="Raw Material Item" required="required" name="material_id" onchange="setMaterialData()" col="col-md-3" class="selectpicker"/>

                            
                            <div class="form-group col-md-3">
                                <label for="memo_no">Availbale Qty <span class="material_unit"></span></label>
                                <input type="text" class="form-control" name="available_qty" id="available_qty" readonly />
                            </div>
                            <x-form.selectbox labelName="Converted Item" name="product_id" onchange="setCategory()" col="col-md-3" class="selectpicker" required="required">
                                @if (!$products->isEmpty())
                                    @foreach ($products as $product)
                                        @if ($product->category_id != 3)
                                        <option value="{{ $product->id }}"  {{ $data->product_id == $product->id ? 'selected' : '' }} data-category="{{ $product->category_id }}">{{ $product->name }}</option>
                                        @endif
                                    @endforeach
                                @endif
                            </x-form.selectbox>
                            <x-form.textbox labelName="Approx. Main Product Ratio" name="build_ratio" value="{{ $data->build_ratio }}" onkeyup="calculateMaterialNeededQty()" required="required" required="required" col="col-md-3"/>
                            <x-form.textbox labelName="Quantity To Build (KGs)" name="build_qty" value="{{ $data->build_qty }}" onkeyup="calculateMaterialNeededQty()" required="required" required="required" col="col-md-3"/>
                            <div class="form-group col-md-3 required">
                                <label for="memo_no">RM Needed <span class="material_unit"></span></label>
                                <input type="text" class="form-control" name="required_qty" id="required_qty"  value="{{ $data->required_qty }}" readonly />
                            </div>
                            <x-form.selectbox labelName="Class" name="category_id" required="required" class="selectpicker" col="col-md-3">
                                @if(!$categories->isEmpty())  
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" {{ $data->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                @endif
                            </x-form.selectbox>
                            <div class="col-md-12 pt-5">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="row" style="position: relative;border: 1px solid #E4E6EF;padding: 10px 0 0 0; margin: 0 0 40px 0;border-radius:5px;">
                                            <div style="width: 100px;background: #fa8c15;text-align: center;margin: 0 auto;color: white;padding: 5px 0;
                                                position: absolute;top:-16px;left:10px;box-shadow: 1px 2px 5px 0px rgba(0,0,0,0.5);"><img src="images/rice.png" style="width: 20px;margin-right: 5px;"/>Fine Rice</div>
                                            <div class="col-md-12 pt-5 material_section">
                                                <div class="row">
                                                    <div class="form-group col-md-6 required">
                                                        <label for="rice_convertion_ratio" class="form-control-label">Convertion Ratio(%)</label>
                                                        <input type="text" name="rice_convertion_ratio" id="rice_convertion_ratio" value="{{ $data->convertion_ratio }}" class="form-control ratio" onkeyup="calculateProductQty(1,1)">
                                                    </div>
                                                    <div class="form-group col-md-6 required">
                                                        <label for="fine_rice_qty" class="form-control-label">Converted Qunatity</label>
                                                        <input type="text" name="fine_rice_qty" id="fine_rice_qty" value="{{ $data->converted_qty }}" class="form-control qty" readonly>
                                                    </div>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row" style="position: relative;border: 1px solid #E4E6EF;padding: 10px 0 0 0; margin: 0 0 40px 0;border-radius:5px;">
                                            <div style="width: 100px;background: #fa8c15;text-align: center;margin: 0 auto;color: white;padding: 5px 0;
                                                position: absolute;top:-16px;left:10px;box-shadow: 1px 2px 5px 0px rgba(0,0,0,0.5);"><img src="images/mill.png" style="width: 20px;margin-right: 5px;"/>Total Milling</div>
                                            <div class="col-md-12 pt-5 material_section">
                                                <div class="row">
                                                    <div class="form-group col-md-6 required">
                                                        <label for="rice_convertion_ratio" class="form-control-label">Convertion Ratio(%)</label>
                                                        <input type="text" name="milling_ratio" id="milling_ratio" value="{{ $data->total_milling_ratio }}" class="form-control " readonly>
                                                    </div>
                                                    <div class="form-group col-md-6 required">
                                                        <label for="rice_convertion_ratio" class="form-control-label">Convertion Quantity</label>
                                                        <input type="text" name="milling_qty" id="milling_qty" value="{{ $data->total_milling_qty }}" class="form-control " readonly>
                                                    </div>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="row" style="position: relative;border: 1px solid #E4E6EF;padding: 10px 0 0 0; margin: 0;border-radius:5px;">
                                            <div style="width: 120px;background: #fa8c15;text-align: center;margin: 0 auto;color: white;padding: 5px 0;
                                                position: absolute;top:-16px;left:10px;box-shadow: 1px 2px 5px 0px rgba(0,0,0,0.5);"><img src="images/grain-sack.png" style="width: 20px;margin-right: 5px;"/>By Products</div>
                                            <div class="col-md-12 pt-5">
                                                <div class="row">
                                                    <x-form.selectbox labelName="By Product Inventory Site" name="bp_site_id" required="required" onchange="getLocations(this.value,2)"  class="selectpicker" col="col-md-3">
                                                        @if(!$sites->isEmpty())  
                                                            @foreach ($sites as $site)
                                                                <option value="{{ $site->id }}" {{ $data->bp_site_id == $site->id ? 'selected' : '' }}>{{ $site->name }}</option>
                                                            @endforeach
                                                        @endif
                                                    </x-form.selectbox>
                        
                                                    <x-form.selectbox labelName="By Product Inventory Location" name="bp_location_id" required="required"  class="selectpicker" col="col-md-3"/>
                                                </div>
                                            </div>
                                            <div class="col-md-12 by_product_section">
                                                @if (!$data->by_products->isEmpty())
                                                    @foreach ($data->by_products as $key => $item)
                                                    <div class="row {{ $key != 0 ? 'remove_row' : '' }}">
                                                        <div class="form-group col-md-3 required">
                                                            @if($key == 0)<label for="by_products_{{ $key+1 }}_id" class="form-control-label">By Product Name</label>@endif
                                                            <select name="by_products[{{ $key+1 }}][id]" id="by_products_{{ $key+1 }}_id" required="required" class="form-control selectpicker" data-live-search="true" 
                                                            data-live-search-placeholder="Search">
                                                                <option value="">Select Please</option>
                                                                @if (!$products->isEmpty())
                                                                    @foreach ($products as $product)
                                                                        @if ($product->category_id == 3)
                                                                        <option value="{{ $product->id }}" {{ $item->id == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                                                                        @endif
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                        </div>
                                                        <div class="form-group col-md-3 required">
                                                            @if($key == 0)<label for="by_products[{{ $key+1 }}][ratio]" class="form-control-label">Convertion Ratio(%)</label>@endif
                                                            <input type="text" name="by_products[{{ $key+1 }}][ratio]" id="by_products_{{ $key+1 }}_ratio" value="{{ $item->pivot->ratio }}" onkeyup="calculateProductQty(2,{{ $key+1 }})" class="form-control ratio">
                                                        </div>
                                                        <div class="form-group col-md-3 required">
                                                            @if($key == 0)<label for="by_products[{{ $key+1 }}][qty]" class="form-control-label">Converted Qunatity</label>@endif
                                                            <input type="text" name="by_products[{{ $key+1 }}][qty]" id="by_products_{{ $key+1 }}_qty" value="{{ $item->pivot->qty }}" class="form-control qty" readonly>
                                                        </div>
                                                        @if($key != 0)
                                                        <div class="form-group col-md-3" style=""padding-top:28px;>
                                                            <button type="button" class="btn btn-danger btn-md remove-product" data-toggle="tooltip" 
                                                                data-placement="top" data-original-title="Remove">
                                                                <i class="fas fa-minus-square"></i>
                                                                </button>
                                                        </div>
                                                        @endif
                                                    </div>
                                                    @endforeach
                                                @endif
                                                
                                            </div>
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="form-group col-md-12">
                                                        <button type="button" id="add-by-product" class="btn btn-success btn-md" data-toggle="tooltip" 
                                                            data-placement="top" data-original-title="Add More">
                                                            <i class="fas fa-plus-square"></i>
                                                            </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                        
                                        
                                   
                                </div>
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

    $('.by_product_section').on('click','.remove-product',function(){
        $(this).closest('.remove_row').remove();
        calculateTotal();
    });

    var count = 1;
    @if (!$data->by_products->isEmpty())
    count = "{{ count($data->by_products) }}";
    @endif
    $(document).on('click','#add-by-product',function(){
        count++;
        by_product_row_add(count);
    }); 

    function by_product_row_add(count){
        var html = `<div class="row remove_row">
                        <div class="form-group col-md-3 required">
                            <select name="by_products[${count}][id]" id="by_products_${count}_id" required="required" class="form-control selectpicker" data-live-search="true" 
                            data-live-search-placeholder="Search">
                                <option value="">Select Please</option>
                                @if (!$products->isEmpty())
                                    @foreach ($products as $product)
                                        @if ($product->category_id == 3)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="form-group col-md-3 required">
                            <input type="text" name="by_products[${count}][ratio]" id="by_products_${count}_ratio" onkeyup="calculateProductQty(2,${count})"  class="form-control ratio">
                        </div>
                        <div class="form-group col-md-3 required">
                            <input type="text" name="by_products[${count}][qty]" id="by_products_${count}_qty" class="form-control qty">
                        </div>
                        <div class="form-group col-md-3" style=""padding-top:28px;>
                            <button type="button" class="btn btn-danger btn-md remove-product" data-toggle="tooltip" 
                                data-placement="top" data-original-title="Remove">
                                <i class="fas fa-minus-square"></i>
                                </button>
                        </div>
                    </div>`;
        $('.by_product_section').append(html);
        $('.by_product_section .selectpicker').selectpicker();
    }
});
getLocations("{{ $data->from_site_id }}",1,"{{ $data->from_location_id }}");
getLocations("{{ $data->bp_site_id }}",2,"{{ $data->bp_location_id }}");
setTimeout(() => {
    material_list("{{ $data->material_id }}");
}, 2000);
setTimeout(() => {
    setMaterialData();
}, 3500);


function material_list(material_id='')
{
    const site_id       = $(`#from_site_id option:selected`).val();
    const location_id   = $(`#from_location_id option:selected`).val();
    if(site_id && location_id)
    {
        $.ajax({
            url:"{{ route('material.list') }}",
            type:"POST",
            data:{
                site_id:site_id,location_id:location_id,_token:_token
            },
            success:function(data){
                $(`#material_id`).empty().append(data);
                $(`#material_id.selectpicker`).selectpicker('refresh');
                if(material_id)
                {
                    $(`#material_id`).val(material_id);
                    $(`#material_id.selectpicker`).selectpicker('refresh');
                }
            },
        });
        $('#available_qty').val('');
    }
}
function setMaterialData()
{
    
    const unitname = $('#material_id option:selected').data('unitcode');
    const qty = $('#material_id option:selected').data('stockqty');
    $('#available_qty').val(parseFloat(qty));
    $('.material_unit').text(`(${unitname})`);

}
function calculateMaterialNeededQty()
{
    const available_qty = $('#available_qty').val() ? parseFloat($('#available_qty').val()) : 0;
    if(available_qty > 0)
    {
        const build_ratio = $('#build_ratio').val() ? parseFloat($('#build_ratio').val()) : 0;
        const build_qty = $('#build_qty').val() ? parseFloat($('#build_qty').val()) : 0;
        const needed_qty = build_ratio * build_qty;
        if(needed_qty > available_qty)
        {
            notification('error','Raw Material Needed Quantity Can\'t Be Greater Than Available Quantity!');
        }else{
            $('#required_qty').val(parseFloat(needed_qty));
        }
    }else{
        notification('error','Raw Material Quantity Is Empty!');
    }
    
}
function setCategory(){
    $('#category_id').val($(`#product_id option:selected`).data('category'));
    $('#category_id.selectpicker').selectpicker('refresh');
}
function calculateProductQty(type,row)
{
    const required_qty = $('#required_qty').val() ? parseFloat($('#required_qty').val()) : 0;
    if(required_qty)
    {
        if(type == 1)
        {
            const ratio = $('#rice_convertion_ratio').val() ? parseFloat($('#rice_convertion_ratio').val()) : 0;
            const qty   = required_qty * (ratio/100);
            $('#fine_rice_qty').val(parseFloat(qty));
        }else{
            const ratio = $(`#by_products_${row}_ratio`).val() ? parseFloat($(`#by_products_${row}_ratio`).val()) : 0;
            const qty   = required_qty * (ratio/100);
            $(`#by_products_${row}_qty`).val(parseFloat(qty));
        }
        calculateTotal();
    }else{
        notification('error','RM Needed Quantity Is Required!');
        if(type == 1)
        {
            $('#fine_rice_qty').val('');
        }else{
            $(`#by_products_${row}_qty`).val('');
        }
    }
}
function calculateTotal()
{
    var total_qty = 0;
    $('.qty').each(function() {
        if($(this).val() == ''){
            total_qty += 0;
        }else{
            total_qty += parseFloat($(this).val());
        }
    });
    $('#milling_qty').val(total_qty);
    var total_ratio = 0;
    $('.ratio').each(function() {
        if($(this).val() == ''){
            total_ratio += 0;
        }else{
            total_ratio += parseFloat($(this).val());
        }
    });
    $('#milling_ratio').val(total_ratio);
}
function getLocations(site_id,selector,location_id='')
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
                $(`#from_location_id`).empty().append(html);
                $(`#from_location_id.selectpicker`).selectpicker('refresh');
                if(location_id)
                {
                    $(`#from_location_id`).val(location_id);
                    $(`#from_location_id.selectpicker`).selectpicker('refresh');
                }
            }else{
                $(`#bp_location_id`).empty().append(html);
                $(`#bp_location_id.selectpicker`).selectpicker('refresh');
                if(location_id)
                {
                    $(`#bp_location_id`).val(location_id);
                    $(`#bp_location_id.selectpicker`).selectpicker('refresh');
                }
            }
            
        },
    });
}
function store_data(){
    let form = document.getElementById('update_form');
    let formData = new FormData(form);
    let url = "{{route('build.disassembly.update')}}";
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
            $('#update_form').find('.is-invalid').removeClass('is-invalid');
            $('#update_form').find('.error').remove();
            if (data.status == false) {
                $.each(data.errors, function (key, value) {
                    var key = key.split('.').join('_');
                    $('#update_form input#' + key).addClass('is-invalid');
                    $('#update_form textarea#' + key).addClass('is-invalid');
                    $('#update_form select#' + key).parent().addClass('is-invalid');
                    $('#update_form #' + key).parent().append(
                        '<small class="error text-danger">' + value + '</small>');

                });
            } else {
                notification(data.status, data.message);
                if (data.status == 'success') {
                    window.location.replace("{{ url('build-disassembly') }}");
                }
            }

        },
        error: function (xhr, ajaxOption, thrownError) {
            console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
        }
    });
}
</script>
@endpush