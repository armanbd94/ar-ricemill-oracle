@extends('layouts.app')

@section('title', $page_title)

@push('styles')
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
                    <button type="button" class="btn btn-danger btn-sm mr-3 custom-btn"><i class="fas fa-window-close"></i> Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm mr-3 custom-btn" id="save-btn" onclick="store_data()"><i class="fas fa-save"></i> Update</button>
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom">
            <div class="card-body">

                <!--begin: Datatable-->
                <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <form  id="store_form" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="packing_id" value="{{ $data->id }}"/>
                        <div class="row">
                            <x-form.textbox labelName="Date" name="packing_date" value="{{ date('Y-m-d') }}" required="required" class="date" col="col-md-4"/>

                            <x-form.selectbox labelName="To Site" name="to_site_id" required="required" onchange="getLocations(this.value,1)" class="selectpicker"  col="col-md-4">
                                @if(!$sites->isEmpty())  
                                    @foreach ($sites as $site)
                                        <option value="{{ $site->id }}" {{ $data->to_site_id == $site->id ? 'selected' : '' }}>{{ $site->name }}</option>
                                    @endforeach
                                @endif
                            </x-form.selectbox>

                            <x-form.selectbox labelName="To Location" name="to_location_id" required="required" col="col-md-4" class="selectpicker" />

                            <x-form.selectbox labelName="Converted To" name="to_product_id" required="required"  col="col-md-4" class="selectpicker">
                                @if (!$products->isEmpty())
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}"  {{ $data->to_product_id == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                                    @endforeach
                                @endif
                            </x-form.selectbox>

                            <div class="form-group col-md-4 required">
                                <label for="chalan_no">Memo No.</label>
                                <input type="text" class="form-control" name="memo_no" id="memo_no" value="{{ $data->memo_no }}" />
                            </div>
                            <x-form.textbox labelName="Number" name="packing_number" required="required" value="{{ $data->packing_number }}" col="col-md-4"/>

                            <div class="col-md-12 pt-5 material_section table-responsive" style="min-height: 550px;">
                                <table class="table table-bordered">
                                    <thead class="bg-primary">
                                        <th>From Site</th>
                                        <th>From Location</th>
                                        <th>Item</th>
                                        <th class="text-center">Available Qty</th>
                                        <th>Description</th>
                                        <th class="text-center">Converted Qty</th>
                                        <th class="text-center">Class</th>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            
                                            <td  style="width: 300px;">
                                                <select name="from_site_id" id="from_site_id" class="form-control selectpicker" onchange="getLocations(this.value,2)" style="width: 300px;" data-live-search="true" data-live-search-placeholder="Search">
                                                    <option value="">Select Please</option>
                                                    @if(!$sites->isEmpty())  
                                                        @foreach ($sites as $site)
                                                            <option value="{{ $site->id }}"  {{ $data->from_site_id == $site->id ? 'selected' : '' }}>{{ $site->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </td>
                                            <td  style="width: 300px;">
                                                <select name="from_location_id" id="from_location_id" class="form-control selectpicker" onchange="product_list()" style="width: 300px;" data-live-search="true" data-live-search-placeholder="Search">
                                                    <option value="">Select Please</option>
                                                </select>
                                            </td>
                                            <td  style="width: 300px;">
                                                @php
                                                    $product_stock = $data->product_qty;
                                                @endphp
                                                <select name="from_product_id" id="from_product_id" class="form-control selectpicker" style="width: 300px;" data-live-search="true" onchange="setData(1)" data-live-search-placeholder="Search">
                                                    <option value="">Select Please</option>
                                                    @if (!$site_products->isEmpty())
                                                        @foreach ($site_products as $product)
                                                            <?php 
                                                                if($data->from_product_id == $product->id)
                                                                {
                                                                    $product_stock += $product->qty ? $product->qty : 0;
                                                                }
                                                            ?>
                                                            <option value="{{ $product->id }}" {{ $data->from_product_id == $product->id ? 'selected' : '' }} data-stockqty="{{ $product->qty }}">{{ $product->product_name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </td>
                                            <td class="text-center">
                                                <input type="text" name="product_stock_qty" id="product_stock_qty" value="{{ $product_stock }}" class="form-control text-right bg-secondary" readonly>
                                            </td>
                                            <td class="text-center">
                                                <input type="text" name="product_description" id="product_description" value="{{ $data->product_description }}"  class="form-control text-left">
                                            </td>
                                            <td class="text-center">
                                                <input type="text" name="product_qty" id="product_qty" value="{{ $data->product_qty }}" class="form-control text-right">
                                            </td>
                                            <td style="width:250px;">
                                                <select name="item_class_id"  style="width:250px;" id="item_class_id" class="fcs col-md-12 form-control selectpicker" data-live-search="true" data-row="1">    
                                                    <option value="">Select Please</option>                                        
                                                    @if (!$classes->isEmpty())
                                                        @foreach ($classes as $class)
                                                            <option value="{{ $class->id }}" {{ $data->item_class_id == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td  style="width: 300px;">
                                                <select name="bag_site_id" id="bag_site_id" class="form-control selectpicker" onchange="getLocations(this.value,3)" style="width: 300px;" data-live-search="true" data-live-search-placeholder="Search">
                                                    <option value="">Select Please</option>
                                                    @if(!$sites->isEmpty())  
                                                        @foreach ($sites as $site)
                                                            <option value="{{ $site->id }}"  {{ $data->bag_site_id == $site->id ? 'selected' : '' }}>{{ $site->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </td>
                                            <td  style="width: 300px;">
                                                <select name="bag_location_id" id="bag_location_id" class="form-control selectpicker" onchange="bag_list()" style="width: 300px;" data-live-search="true" data-live-search-placeholder="Search">
                                                    <option value="">Select Please</option>
                                                </select>
                                            </td>
                                            <td  style="width: 300px;">
                                                @php
                                                    $bag_stock = $data->bag_qty;
                                                @endphp
                                                <select  style="width: 300px;" name="bag_id" id="bag_id" class="form-control selectpicker" data-live-search="true" onchange="setData(2)" data-live-search-placeholder="Search">
                                                    <option value="">Select Please</option>
                                                    @if (!$bags->isEmpty())
                                                    @foreach($bags as $value)
                                                    <?php 
                                                                if($data->bag_id == $value->id)
                                                                {
                                                                    $bag_stock += $value->qty ? $value->qty : 0;
                                                                }
                                                            ?>
                                                    <option value="{{ $value->id }}" {{ $data->bag_id == $value->id ? 'selected' : '' }} data-stockqty="{{ $value->qty }}" data-category="{{ $value->category_name }}" 
                                                        data-unitname="{{ $value->unit_name }}" data-unitcode="{{ $value->unit_code }}">{{ $value->material_name }}</option>
                                                    @endforeach
                                                    @endif
                                                </select>
                                            </td>
                                            <td class="text-center">
                                                <input type="text" name="bag_stock_qty" id="bag_stock_qty" value="{{ $bag_stock }}" class="form-control text-right bg-secondary" readonly>
                                            </td>
                                            <td class="text-center">
                                                <input type="text" name="bag_description" id="bag_description" value="{{ $data->bag_description }}"  class="form-control text-left">
                                            </td>
                                            <td class="text-center">
                                                <input type="text" name="bag_qty" id="bag_qty" value="{{ $data->bag_qty }}" class="form-control text-right">
                                            </td>
                                            <td style="width:250px;">
                                                <select name="bag_class_id"  style="width:250px;" id="bag_class_id" class="fcs col-md-12 form-control selectpicker" data-live-search="true" data-row="1">    
                                                    <option value="">Select Please</option>                                        
                                                    @if (!$classes->isEmpty())
                                                        @foreach ($classes as $class)
                                                            <option value="{{ $class->id }}" {{ $data->bag_class_id == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
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
});

function product_list()
{
    const site_id       = $(`#from_site_id option:selected`).val();
    const location_id   = $(`#from_location_id option:selected`).val();
    const category_id = 5; //Fetch Product List Except By Products
    if(site_id && location_id)
    {
        $.ajax({
            url:"{{ route('product.list') }}",
            type:"POST",
            data:{
                site_id:site_id,location_id:location_id,category_id:category_id,_token:_token
            },
            success:function(data){
                $(`#from_product_id`).empty().append(data);
                $(`#from_product_id.selectpicker`).selectpicker('refresh');
            },
        });
        $('#product_stock_qty').val('');
    }
}
function bag_list()
{
    const site_id       = $(`#bag_site_id option:selected`).val();
    const location_id   = $(`#bag_location_id option:selected`).val();
    if(site_id && location_id)
    {
        $.ajax({
            url:"{{ route('material.bag.list') }}",
            type:"POST",
            data:{
                site_id:site_id,location_id:location_id,_token:_token
            },
            success:function(data){
                $(`#bag_id`).empty().append(data);
                $(`#bag_id.selectpicker`).selectpicker('refresh');
            },
        });
        $('#bag_stock_qty').val('');
    }
}
function setData(row)
{
    if(row == 1)
    {
        const qty = $('#from_product_id option:selected').data('stockqty');
        $('#product_stock_qty').val(parseFloat(qty));
    }else{
        const qty = $('#bag_id option:selected').data('stockqty');
        $('#bag_stock_qty').val(parseFloat(qty));
    }
}

getLocations("{{ $data->to_site_id }}",1,"{{ $data->to_location_id }}");
getLocations("{{ $data->from_site_id }}",2,"{{ $data->from_location_id }}");
getLocations("{{ $data->bag_site_id }}",3,"{{ $data->bag_location_id }}");
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
                $(`#to_location_id`).empty().append(html);
                $(`#to_location_id.selectpicker`).selectpicker('refresh');
                if(location_id)
                {
                    $(`#to_location_id`).val(location_id);
                    $(`#to_location_id.selectpicker`).selectpicker('refresh');
                }
            }else if(selector == 2){
                $(`#from_location_id`).empty().append(html);
                $(`#from_location_id.selectpicker`).selectpicker('refresh');
                if(location_id)
                {
                    $(`#from_location_id`).val(location_id);
                    $(`#from_location_id.selectpicker`).selectpicker('refresh');
                }
            }else{
                $(`#bag_location_id`).empty().append(html);
                $(`#bag_location_id.selectpicker`).selectpicker('refresh');
                if(location_id)
                {
                    $(`#bag_location_id`).val(location_id);
                    $(`#bag_location_id.selectpicker`).selectpicker('refresh');
                }
            }
            
        },
    });
}
function store_data(){
    let form = document.getElementById('store_form');
    let formData = new FormData(form);
    let url = "{{route('bom.re.packing.update')}}";
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
            $('#store_form').find('.is-invalid').removeClass('is-invalid');
            $('#store_form').find('.error').remove();
            if (data.status == false) {
                $.each(data.errors, function (key, value) {
                    var key = key.split('.').join('_');
                    $('#store_form input#' + key).addClass('is-invalid');
                    $('#store_form textarea#' + key).addClass('is-invalid');
                    $('#store_form select#' + key).parent().addClass('is-invalid');
                    $('#store_form #' + key).parent().append(
                        '<small class="error text-danger">' + value + '</small>');

                });
            } else {
                notification(data.status, data.message);
                if (data.status == 'success') {
                    window.location.replace("{{ url('bom/re-packing') }}");
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