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
                    <button type="button" class="btn btn-danger btn-sm mr-3 custom-btn"><i class="fas fa-sync-alt"></i> Reset</button>
                    <button type="button" class="btn btn-primary btn-sm mr-3 custom-btn" id="save-btn" onclick="store_data()"><i class="fas fa-save"></i> Save</button>
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
                        <div class="row">
                            <x-form.textbox labelName="Date" name="packing_date" value="{{ date('Y-m-d') }}" required="required" class="date" col="col-md-4"/>

                            

                            <x-form.selectbox labelName="To Site" name="to_site_id" required="required" onchange="getLocations(this.value,1)" class="selectpicker"  col="col-md-4">
                                @if(!$sites->isEmpty())  
                                    @foreach ($sites as $site)
                                        <option value="{{ $site->id }}">{{ $site->name }}</option>
                                    @endforeach
                                @endif
                            </x-form.selectbox>

                            <x-form.selectbox labelName="To Location" name="to_location_id" required="required" col="col-md-4" class="selectpicker" />

                            <x-form.selectbox labelName="Converted To" name="to_product_id" required="required"  col="col-md-4" class="selectpicker">
                                @if (!$products->isEmpty())
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                @endif
                            </x-form.selectbox>

                            <div class="form-group col-md-4 required">
                                <label for="chalan_no">Memo No.</label>
                                <input type="text" class="form-control" name="memo_no" id="memo_no" />
                            </div>
                            <x-form.textbox labelName="Number" name="packing_number" required="required"  col="col-md-4"/>

                           

                           
                            <div class="col-md-12 pt-5 material_section table-responsive" style="min-height: 350px;">
                                <table class="table table-bordered">
                                    <thead class="bg-primary">
                                        <th>From Site</th>
                                        <th>From Location</th>
                                        <th>Item</th>
                                        <th class="text-center">Available Qty</th>
                                        <th>Description</th>
                                        <th class="text-center">Converted Qty</th>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            
                                            <td  style="width: 300px;">
                                                <select name="from_site_id" id="from_site_id" class="form-control selectpicker" onchange="getLocations(this.value,2)" style="width: 300px;" data-live-search="true" data-live-search-placeholder="Search">
                                                    <option value="">Select Please</option>
                                                    @if(!$sites->isEmpty())  
                                                        @foreach ($sites as $site)
                                                            <option value="{{ $site->id }}">{{ $site->name }}</option>
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
                                                <select name="from_product_id" id="from_product_id" class="form-control selectpicker" style="width: 300px;" data-live-search="true" onchange="setData(1)" data-live-search-placeholder="Search">
                                                    <option value="">Select Please</option>
                                                </select>
                                            </td>
                                            <td class="text-center">
                                                <input type="text" name="product_stock_qty" id="product_stock_qty"  class="form-control text-right bg-secondary" readonly>
                                            </td>
                                            <td class="text-center">
                                                <input type="text" name="product_description" id="product_description"  class="form-control text-left">
                                            </td>
                                            <td class="text-center">
                                                <input type="text" name="product_qty" id="product_qty" class="form-control text-right">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td  style="width: 300px;">
                                                <select name="bag_site_id" id="bag_site_id" class="form-control selectpicker" onchange="getLocations(this.value,3)" style="width: 300px;" data-live-search="true" data-live-search-placeholder="Search">
                                                    <option value="">Select Please</option>
                                                    @if(!$sites->isEmpty())  
                                                        @foreach ($sites as $site)
                                                            <option value="{{ $site->id }}">{{ $site->name }}</option>
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
                                                <select  style="width: 300px;" name="bag_id" id="bag_id" class="form-control selectpicker" data-live-search="true" onchange="setData(2)" data-live-search-placeholder="Search">
                                                    <option value="">Select Please</option>
                                                
                                                </select>
                                            </td>
                                            <td class="text-center">
                                                <input type="text" name="bag_stock_qty" id="bag_stock_qty"  class="form-control text-right bg-secondary" readonly>
                                            </td>
                                            <td class="text-center">
                                                <input type="text" name="bag_description" id="bag_description"  class="form-control text-left">
                                            </td>
                                            <td class="text-center">
                                                <input type="text" name="bag_qty" id="bag_qty" class="form-control text-right">
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
    const category_id = 3; //Fetch Product List Except By Products
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


function getLocations(site_id,selector)
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
            }else if(selector == 2){
                $(`#from_location_id`).empty().append(html);
                $(`#from_location_id.selectpicker`).selectpicker('refresh');
            }else{
                $(`#bag_location_id`).empty().append(html);
                $(`#bag_location_id.selectpicker`).selectpicker('refresh');
            }
            
        },
    });
}
function store_data(){
    let form = document.getElementById('store_form');
    let formData = new FormData(form);
    let url = "{{route('bom.re.packing.store')}}";
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
                    window.location.replace("{{ url('bom/re-packing/view') }}/"+data.packing_id);
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