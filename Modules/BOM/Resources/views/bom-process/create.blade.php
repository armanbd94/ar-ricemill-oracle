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
                            <x-form.textbox labelName="Date" name="process_date" value="{{ date('Y-m-d') }}" required="required" class="date" col="col-md-3"/>

                            <x-form.selectbox labelName="WIP Batch" name="batch_id" required="required"  class="selectpicker" col="col-md-3">
                                @if (!$batches->isEmpty())
                                    @foreach ($batches as $batch)
                                        <option value="{{ $batch->id }}">{{ $batch->batch_no.' ('.date('d-m-Y',strtotime($batch->batch_start_date)).')' }}</option>
                                    @endforeach
                                @endif
                            </x-form.selectbox>

                            <div class="form-group col-md-6 required">
                                <label for="chalan_no">Memo No.</label>
                                <input type="text" class="form-control" name="memo_no" id="memo_no" onkeyup="setParticularText(this.value)" />
                            </div>
                            <x-form.selectbox labelName="Assemble From Site" name="from_site_id" required="required" onchange="getLocations(this.value,1)" class="selectpicker"  col="col-md-3">
                                @if(!$sites->isEmpty())  
                                    @foreach ($sites as $site)
                                        <option value="{{ $site->id }}">{{ $site->name }}</option>
                                    @endforeach
                                @endif
                            </x-form.selectbox>
                            <x-form.selectbox labelName="Assemble From Location" name="from_location_id" required="required" col="col-md-3" class="selectpicker" />

                            <x-form.selectbox labelName="Assemble To Site" name="to_site_id" required="required" onchange="getLocations(this.value,2)" class="selectpicker"  col="col-md-3">
                                @if(!$sites->isEmpty())  
                                    @foreach ($sites as $site)
                                        <option value="{{ $site->id }}">{{ $site->name }}</option>
                                    @endforeach
                                @endif
                            </x-form.selectbox>
                            <x-form.selectbox labelName="Assemble To Location" name="to_location_id" required="required" col="col-md-3" class="selectpicker" />
                            <x-form.textbox labelName="Number" name="process_number" required="required"  col="col-md-3"/>
                            <x-form.selectbox labelName="Converted To" name="to_product_id" required="required"  col="col-md-3" class="selectpicker">
                                @if (!$products->isEmpty())
                                    @foreach ($products as $product)
                                        @if ($product->category_id == 5)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endif
                                    @endforeach
                                @endif
                            </x-form.selectbox>
                            <x-form.selectbox labelName="Bag Inventory Site" name="bag_site_id" onchange="getLocations(this.value,3)"  required="required" col="col-md-3" class="selectpicker">
                                @if(!$sites->isEmpty())  
                                    @foreach ($sites as $site)
                                        <option value="{{ $site->id }}">{{ $site->name }}</option>
                                    @endforeach
                                @endif
                            </x-form.selectbox>

                            <x-form.selectbox labelName="Bag Inventory Location" name="bag_location_id" col="col-md-3" class="selectpicker"  required="required" onchange="bag_list()" />

                            <div class="col-md-12 pt-5">
                                <div class="row" style="position: relative;border: 1px solid #E4E6EF;padding: 10px 0 0 0; margin: 0 0 40px 0;border-radius:5px;">
                                    <div style="width: 120px;background: #fa8c15;text-align: center;margin: 0 auto;color: white;padding: 5px 0;
                                        position: absolute;top:-16px;left:10px;box-shadow: 1px 2px 5px 0px rgba(0,0,0,0.5);"><img src="images/rice.png" style="width: 20px;margin-right: 5px;"/>Raw Material</div>
                                    <div class="col-md-12 pt-5 material_section">
                                        <table class="table table-bordered">
                                            <thead class="bg-primary">
                                                <th>Raw Material</th>
                                                <th class="text-center">Available Qty</th>
                                                <th class="text-center">Particular</th>
                                                <th class="text-center">Per Unit Qty</th>
                                                <th class="text-center">Qty Needed</th>
                                                <th class="text-center">Class</th>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td  style="width: 300px;">
                                                        <select name="from_product_id" id="from_product_id" class="form-control selectpicker" style="width: 300px;" data-live-search="true" onchange="getStockQty(this.value)" data-live-search-placeholder="Search">
                                                            <option value="">Select Please</option>
                                                            @if (!$products->isEmpty())
                                                            @foreach ($products as $product)
                                                                @if ($product->category_id == 4)
                                                                <option value="{{ $product->id }}" data-cost="{{ $product->cost ? $product->cost : 0 }}">{{ $product->name }}</option>
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                        </select>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="text" name="product_stock_qty" id="product_stock_qty"  class="form-control text-right bg-secondary" readonly>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="text" name="product_particular" id="product_particular"  class="form-control">
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="text" name="product_per_unit_qty" id="product_per_unit_qty" onkeyup="packetRiceCalculation()" class="form-control text-right">
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="text" name="product_required_qty" id="product_required_qty"  class="form-control text-right bg-secondary" readonly>
                                                    </td>
                                                    <td style="width:250px;">
                                                        <select name="item_class_id"  style="width:250px;" id="item_class_id" class="fcs col-md-12 form-control selectpicker" data-live-search="true" data-row="1">    
                                                            <option value="">Select Please</option>                                        
                                                            @if (!$classes->isEmpty())
                                                                @foreach ($classes as $class)
                                                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td  style="width: 300px;">
                                                        <select  style="width: 300px;" name="bag_id" id="bag_id" class="form-control selectpicker" data-live-search="true" onchange="setMaterialData()" data-live-search-placeholder="Search">
                                                            <option value="">Select Please</option>
                                                        
                                                        </select>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="text" name="bag_stock_qty" id="bag_stock_qty"  class="form-control text-right bg-secondary" readonly>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="text" name="bag_particular" id="bag_particular"  class="form-control">
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="text" name="bag_per_unit_qty" id="bag_per_unit_qty" onkeyup="packetRiceCalculation()" class="form-control text-right">
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="text" name="bag_required_qty" id="bag_required_qty"  class="form-control text-right bg-secondary" readonly>
                                                    </td>
                                                    <td style="width:250px;">
                                                        <select name="bag_class_id"  style="width:250px;" id="bag_class_id" class="fcs col-md-12 form-control selectpicker" data-live-search="true" data-row="1">    
                                                            <option value="">Select Please</option>                                        
                                                            @if (!$classes->isEmpty())
                                                                @foreach ($classes as $class)
                                                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" class="text-right font-weight-bolder">Fine Rice Quantity to Build</td>
                                                    <td><input type="text" name="total_rice_qty" id="total_rice_qty" onkeyup="packetRiceCalculation()" class="form-control text-right"></td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" class="text-right font-weight-bolder">Total Bag Used Quantity</td>
                                                    <td><input type="text" name="total_bag_qty" id="total_bag_qty"  class="form-control text-right" onkeyup="perUnitCost()"></td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" class="text-right font-weight-bolder">Per Unit Cost</td>
                                                    <td><input type="text" name="per_unit_cost" id="per_unit_cost"  class="form-control text-right bg-secondary" readonly></td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>
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
});
function getStockQty(product_id)
{
    const site_id       = $(`#from_site_id option:selected`).val();
    const location_id   = $(`#from_location_id option:selected`).val();
    if(site_id && location_id && product_id)
    {
        $('#product_stock_qty').val('');
        $.ajax({
            url:"{{ route('product.stock.qty') }}",
            type:"POST",
            data:{
                site_id:site_id,location_id:location_id,product_id:product_id,_token:_token
            },
            success:function(data){
                $('#product_stock_qty').val(data);
                $('#product_particular').val($('#memo_no').val());
            },
        });
    }else{
        if(!site_id)
        {
            notification('error','Please select from site first!');
        }else if(!location_id)
        {
            notification('error','Please select from location first!');
        }
        $('#from_product_id').val('');
        $('#from_product_id.selectpicker').selectpicker('refresh');
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
function setMaterialData()
{
    const memo_no = $('#memo_no').val();
    const qty = $('#bag_id option:selected').data('stockqty') ? parseFloat($('#bag_id option:selected').data('stockqty')) : 0;
    $('#bag_stock_qty').val(parseFloat(qty));
    $('#bag_particular').val(memo_no);
    
}
function setParticularText(memo_no)
{
    if($('#from_product_id option:selected').val())
    {
        $('#product_particular').val(memo_no);
    }
    if($('#bag_id option:selected').val())
    {
        $('#bag_particular').val(memo_no);
    }
}

function packetRiceCalculation()
{
    const product_per_unit_qty = $('#product_per_unit_qty').val() ? parseFloat($('#product_per_unit_qty').val()) : 0;
    const bag_per_unit_qty     = $('#bag_per_unit_qty').val() ? parseFloat($('#bag_per_unit_qty').val()) : 0;
    const total_rice_qty            = $('#total_rice_qty').val() ? parseFloat($('#total_rice_qty').val()) : 0;

    const product_required_qty = product_per_unit_qty * total_rice_qty;
    const bag_required_qty     = bag_per_unit_qty * total_rice_qty;
    if(product_per_unit_qty > 0)
    {
        $('#product_required_qty').val(product_required_qty);
    }
    if(bag_per_unit_qty > 0)
    {
        $('#bag_required_qty').val(bag_required_qty);
    }
}

function perUnitCost()
{
    const total_rice_qty = $('#total_rice_qty').val() ? parseFloat($('#total_rice_qty').val()) : 0;
    if(total_rice_qty > 0)
    {
        const rice_cost = $('#from_product_id option:selected').data('cost') ? parseFloat($('#from_product_id option:selected').data('cost')) : 0;
        const bag_cost = $('#bag_id option:selected').data('cost') ? parseFloat($('#bag_id option:selected').data('cost')) : 0;
        const total_bag_qty = $('#total_bag_qty').val() ? parseFloat($('#total_bag_qty').val()) : 0;
        const per_unit_cost = ((total_rice_qty * rice_cost) + (total_bag_qty * bag_cost)) / total_rice_qty;
        $('#per_unit_cost').val(parseFloat(per_unit_cost).toFixed(2));

    }else{
        notification('error','Please insert fine rice qunatity!');
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
            if(selector == 1){
                $(`#from_location_id`).empty().append(html);
                $(`#from_location_id.selectpicker`).selectpicker('refresh');
            }else if(selector == 2){
                $(`#to_location_id`).empty().append(html);
                $(`#to_location_id.selectpicker`).selectpicker('refresh');
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
    let url = "{{route('bom.process.store')}}";
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
                    window.location.replace("{{ url('bom/process/view') }}/"+data.process_id);
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