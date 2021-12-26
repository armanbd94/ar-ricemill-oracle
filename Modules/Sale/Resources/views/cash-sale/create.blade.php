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


                    <form action="" id="cash_sale_form" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <x-form.textbox labelName="Invoice Date" name="sale_date" value="{{ date('Y-m-d') }}" required="required" class="date" col="col-md-4"/>
                            <div class="form-group col-md-4 required">
                                <label for="memo_no">Memo No.</label>
                                <input type="text" class="form-control" name="memo_no" id="memo_no"  />
                            </div>
                            
                            <div class="form-group col-md-4 required">
                                <label for="customer_name">Customer Name</label>
                                <input type="text" class="form-control" name="customer_name" id="customer_name" />
                            </div>
                            <div class="form-group col-md-4 required">
                                <label for="do_number">DO Number</label>
                                <input type="text" class="form-control" name="do_number" id="do_number" />
                            </div>
                            
                            <x-form.textbox labelName="Delivery Date" name="delivery_date" value="{{ date('Y-m-d') }}" required="required" class="date" col="col-md-4"/>

                            <x-form.selectbox labelName="Deposit To" name="account_id" col="col-md-4" class="selectpicker" required="required">
                                @if (!$coas->isEmpty())
                                    @foreach ($coas as $coa)
                                        <option value="{{ $coa->id }}">{{ $coa->name }}</option>
                                    @endforeach
                                @endif
                            </x-form.selectbox>
                            
                            <div class="col-md-12 table-responsive" style="min-height: 500px;">

                                <table class="table table-bordered" id="product_table">
                                    <thead class="bg-primary">
                                        <th class="text-center">Site</th>
                                        <th class="text-center">Location</th>
                                        <th>Item</th>
                                        <th>Description</th>
                                        <th class="text-center">Available Qty</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-right">Rate</th>
                                        <th class="text-right">Subtotal</th>
                                        <th class="text-center">Class</th>
                                        <th class="text-center"><i class="fas fa-trash text-white"></i></th>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style="width:250px;">                                                  
                                                <select name="products[1][site_id]"  style="width:250px;" id="products_1_site_id" class="fcs col-md-12 site_id form-control selectpicker" onchange="getLocations(this.value,1)"  data-live-search="true" data-row="1">                                            
                                                    <option value="">Select Please</option>  
                                                    @if(!$sites->isEmpty())  
                                                        @foreach ($sites as $site)
                                                            <option value="{{ $site->id }}">{{ $site->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </td>  
                                            <td style="width:250px;">                                                  
                                                <select name="products[1][location_id]"  style="width:250px;" onchange="product_list(1)" id="products_1_location_id" class="fcs col-md-12 location_id form-control selectpicker"  data-live-search="true" data-row="1">                                            
                                                    <option value="">Select Please</option>  
                                                </select>
                                            </td> 
                                            <td style="width:250px;">                     
                                                <select name="products[1][id]" id="products_1_id"  style="width:250px;" class="fcs col-md-12 form-control selectpicker" onchange="setProductDetails(1)"  data-live-search="true" data-row="1">    
                                                    <option value="">Select Please</option>                                        
                                                </select>
                                            </td>    
                                            <td><input type="text" class="form-control" style="width: 150px;margin: 0 auto;" name="products[1][description]" id="products_1_description" data-row="1"></td>                                    
                                            <td><input type="text" class="form-control text-center" style="width: 120px;margin: 0 auto;" name="products[1][stock_qty]" id="products_1_stock_qty"  data-row="1"></td>
                                            <td><input type="text" class="form-control qty text-center" style="width: 120px;margin: 0 auto;" onkeyup="calculateRowTotal(1)" name="products[1][qty]" id="products_1_qty"  data-row="1"></td>
                                            <td><input type="text" style="width: 120px;margin: 0 auto;" class="text-right form-control net_unit_price" onkeyup="calculateRowTotal(1)" name="products[1][net_unit_price]" id="products_1_net_unit_price" data-row="1"></td>
                                            <td class="subtotal_1 text-right" id="sub_total_1" data-row="1"></td>
                                            <td style="width:250px;">
                                                <select name="products[1][item_class_id]"  style="width:250px;" id="products_1_item_class_id" class="fcs col-md-12 form-control selectpicker" data-live-search="true" data-row="1">    
                                                    <option value="">Select Please</option>                                        
                                                    @if (!$classes->isEmpty())
                                                        @foreach ($classes as $class)
                                                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </td>
                                            <td class="text-center" data-row="1"></td>
                                            <input type="hidden" class="subtotal" id="products_1_subtotal" name="products[1][subtotal]" data-row="1">
                                        </tr>
                                    </tbody>
                                    <tfoot class="bg-primary">
                                        <th colspan="5" class="font-weight-bolder">Total</th>
                                        <th id="total-qty" class="text-center font-weight-bolder">0</th>
                                        <th></th>
                                        <th id="total" class="text-right font-weight-bolder">0.00</th>
                                        <th></th>
                                        <th class="text-center"><button type="button" data-toggle="tooltip" data-theme="dark" title="Add More" class="btn btn-success btn-sm add-product"><i class="fas fa-plus"></i></button></th>
                                    </tfoot>
                                </table>
                            </div>

            
                            <div class="col-md-12">
                                <input type="hidden" name="item">
                                <input type="hidden" name="total_qty">
                                <input type="hidden" name="grand_total">
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

    $('#product_table').on('click','.remove-product',function(){
        $(this).closest('tr').remove();
        calculateTotal();
    });

    var count = 1;
    $('#product_table').on('click','.add-product',function(){
        count++;
        product_row_add(count);
    }); 

    function product_row_add(count){
        var html = `<tr>
                        <td style="width:250px;">                                                  
                            <select name="products[${count}][site_id]"  style="width:250px;" id="products_${count}_site_id" class="fcs col-md-12 site_id form-control selectpicker" onchange="getLocations(this.value,${count})"  data-live-search="true" data-row="${count}">                                            
                                <option value="">Select Please</option>  
                                @if(!$sites->isEmpty())  
                                    @foreach ($sites as $site)
                                        <option value="{{ $site->id }}">{{ $site->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </td>  
                        <td style="width:250px;">                                                  
                            <select name="products[${count}][location_id]" style="width:250px;" onchange="product_list(${count})" id="products_${count}_location_id" class="fcs col-md-12 location_id form-control selectpicker"  data-live-search="true" data-row="${count}">                                            
                                <option value="">Select Please</option>  
                            </select>
                        </td>  
                        <td style="width:250px;">                     
                            <select name="products[${count}][id]" style="width:250px;" id="products_${count}_id" class="fcs col-md-12 form-control selectpicker" onchange="setProductDetails(${count})"  data-live-search="true" data-row="${count}">    
                                <option value="">Select Please</option>                                        
                            </select>
                        </td>    
                        <td><input type="text" class="form-control" style="width: 150px;margin: 0 auto;" name="products[${count}][description]" id="products_${count}_description" data-row="${count}"></td>                                    
                        <td><input type="text" class="form-control text-center" style="width: 120px;margin: 0 auto;" name="products[${count}][stock_qty]" id="products_${count}_stock_qty"  data-row="${count}"></td>
                        <td><input type="text" class="form-control qty text-center" style="width: 120px;margin: 0 auto;" onkeyup="calculateRowTotal(${count})" name="products[${count}][qty]" id="products_${count}_qty"  data-row="${count}"></td>
                        <td><input type="text" style="width: 120px;margin: 0 auto;" class="text-right form-control net_unit_price" onkeyup="calculateRowTotal(${count})" name="products[${count}][net_unit_price]" id="products_${count}_net_unit_price" data-row="${count}"></td>
                        <td class="subtotal_${count} text-right" id="sub_total_${count}" data-row="${count}"></td>
                        <td>
                            <select name="products[${count}][item_class_id]" id="products_${count}_item_class_id" class="fcs col-md-12 form-control selectpicker" data-live-search="true" data-row="${count}">    
                                <option value="">Select Please</option>                                        
                                @if (!$classes->isEmpty())
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </td>
                        <td class="text-center" data-row="${count}"><button type="button" class="btn btn-danger btn-sm remove-product"><i class="fas fa-trash"></i></button></td>
                        <input type="hidden" class="subtotal" id="products_${count}_subtotal" name="products[${count}][subtotal]" data-row="${count}">
                    </tr>`;
        $('#product_table tbody').append(html);
        $('#product_table .selectpicker').selectpicker();
    }
});

function calculateRowTotal(row)
{
    let price = $(`#products_${row}_net_unit_price`).val() ? parseFloat($(`#products_${row}_net_unit_price`).val()) : 0;
    let qty = $(`#products_${row}_qty`).val() ? parseFloat($(`#products_${row}_qty`).val()) : 0;
    if(qty < 0 || qty == ''){
        qty = 0;
        $(`#products_${row}_qty`).val('');
    }
    if(price < 0 || price == ''){
        price = 0;
        $(`#products_${row}_net_unit_price`).val('');
    }

    $(`.subtotal_${row}`).text(parseFloat(qty * price));
    $(`#products_${row}_subtotal`).val(parseFloat(qty * price));
    
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

    var item = $('#product_table tbody tr:last').index()+1;
    $('input[name="item"]').val(item);
}
function product_list(row)
{
    const site_id       = $(`#products_${row}_site_id option:selected`).val();
    const location_id   = $(`#products_${row}_location_id option:selected`).val();
    if(site_id && location_id)
    {
        $.ajax({
            url:"{{ route('product.list') }}",
            type:"POST",
            data:{
                site_id:site_id,location_id:location_id,_token:_token
            },
            success:function(data){
                $(`#products_${row}_id`).empty().append(data);
                $(`#products_${row}_id.selectpicker`).selectpicker('refresh');
            },
        });
        $('#available_qty').val('');
    }
}
function getLocations(site_id,row)
{
    $.ajax({
        url:"{{ url('site-wise-location-list') }}/"+site_id,
        type:"GET",
        dataType:"JSON",
        success:function(data){
            $(`#products_${row}_location_id`).empty();
            var html = '<option value="">Select Please</option>';
            $.each(data, function(key, value) {
                html += '<option value="'+ key +'">'+ value +'</option>';
            });
            $(`#products_${row}_location_id`).append(html);
            $(`#products_${row}_location_id.selectpicker`).selectpicker('refresh');
        },
    });
}

function store_data(){
    var rownumber = $('table#product_table tbody tr:last').index();
    if (rownumber < 0) {
        notification("error","Please insert material to order table!")
    }else{
        let form = document.getElementById('cash_sale_form');
        let formData = new FormData(form);
        let url = "{{route('sale.cash.store')}}";
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
                $('#cash_sale_form').find('.is-invalid').removeClass('is-invalid');
                $('#cash_sale_form').find('.error').remove();
                if (data.status == false) {
                    $.each(data.errors, function (key, value) {
                        var key = key.split('.').join('_');
                        $('#cash_sale_form input#' + key).addClass('is-invalid');
                        $('#cash_sale_form textarea#' + key).addClass('is-invalid');
                        $('#cash_sale_form select#' + key).parent().addClass('is-invalid');
                        $('#cash_sale_form #' + key).parent().append(
                            '<small class="error text-danger">' + value + '</small>');
                    });
                } else {
                    notification(data.status, data.message);
                    if (data.status == 'success') {
                        window.location.replace("{{ url('sale/cash/view') }}/"+data.sale_id);
                        
                        
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