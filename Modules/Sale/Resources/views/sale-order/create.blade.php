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


                    <form action="" id="sale_order_form" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <x-form.selectbox labelName="Customer" name="customer_id" col="col-md-8" onchange="getViaCustomers(this.value)" class="selectpicker" required="required">
                                @if (!$customers->isEmpty())
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}" data-code="{{ $customer->code }}" data-tradename="{{ $customer->trade_name }}"
                                            data-name="{{ $customer->name }}" data-mobile="{{ $customer->mobile }}" data-address="{{ $customer->address }}"
                                            >{{ $customer->trade_name.' - '.$customer->name.' ('.$customer->mobile.')' }}</option>
                                    @endforeach
                                @endif
                            </x-form.selectbox>
                            <x-form.selectbox labelName="Via Customer" name="via_customer_id" col="col-md-4" class="selectpicker" />
                            <div class="form-group col-md-12 customer_data d-none">

                            </div>
                            <x-form.textbox labelName="Order Date" name="order_date" value="{{ date('Y-m-d') }}" required="required" class="date" col="col-md-4"/>
                            <div class="form-group col-md-4 required">
                                <label for="memo_no">Memo No.</label>
                                <input type="text" class="form-control" name="memo_no" id="memo_no"  />
                            </div>
                            
                            
                            <div class="form-group col-md-4 required">
                                <label for="so_no">SO No.</label>
                                <input type="text" class="form-control" name="so_no" id="so_no" />
                            </div>
                            
                            <x-form.textbox labelName="Target Delivery Date" name="delivery_date" value="{{ date('Y-m-d') }}" required="required" class="date" col="col-md-4"/>
                            <x-form.textbox labelName="Shipping Address" name="shipping_address" required="required" col="col-md-8"/>

                            
                            
                            <div class="col-md-12 table-responsive" style="min-height: 500px;">

                                <table class="table table-bordered" id="product_table">
                                    <thead class="bg-primary">
                                        <th>Item</th>
                                        <th>Description</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-right">Rate</th>
                                        <th class="text-right">Subtotal</th>
                                        <th class="text-center"><i class="fas fa-trash text-white"></i></th>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style="width:300px;">                     
                                                <select name="products[1][id]" id="products_1_id"  style="width:300px;" class="fcs col-md-12 form-control selectpicker"  data-live-search="true" data-row="1">    
                                                    <option value="">Select Please</option>    
                                                    @if(!$categories->isEmpty())  
                                                        @foreach ($categories as $category)
                                                            @if (!$category->products->isEmpty())
                                                            <optgroup label="{{ $category->name }}">
                                                                @foreach ($category->products as $product)
                                                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                                @endforeach
                                                            </optgroup>
                                                            @endif
                                                        @endforeach
                                                    @endif                                    
                                                </select>
                                            </td>    
                                            <td style="width: 300px;"><input type="text" class="form-control" style="width: 300px;margin: 0 auto;" name="products[1][description]" id="products_1_description" data-row="1"></td>           
                                            <td style="width: 200px;"><input type="text" class="form-control qty text-center" style="width: 200px;margin: 0 auto;" onkeyup="calculateRowTotal(1)" name="products[1][qty]" id="products_1_qty"  data-row="1"></td>
                                            <td style="width: 200px;"><input type="text" style="width: 200px;margin: 0 auto;" class="text-right form-control net_unit_price" onkeyup="calculateRowTotal(1)" name="products[1][net_unit_price]" id="products_1_net_unit_price" data-row="1"></td>
                                            <td class="subtotal_1 text-right" id="sub_total_1" data-row="1"></td>
                                            <td class="text-center" data-row="1"></td>
                                            <input type="hidden" class="subtotal" id="products_1_subtotal" name="products[1][subtotal]" data-row="1">
                                        </tr>
                                    </tbody>
                                    <tfoot class="bg-primary">
                                        <th colspan="2" class="font-weight-bolder">Total</th>
                                        <th id="total-qty" class="text-center font-weight-bolder">0</th>
                                        <th></th>
                                        <th id="total" class="text-right font-weight-bolder">0.00</th>
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
                        <td style="width: 300px;">                     
                            <select name="products[${count}][id]" style="width:300px;" id="products_${count}_id" class="fcs col-md-12 form-control selectpicker"  data-live-search="true" data-row="${count}">    
                                <option value="">Select Please</option> 
                                @if(!$categories->isEmpty())  
                                    @foreach ($categories as $category)
                                        @if (!$category->products->isEmpty())
                                        <optgroup label="{{ $category->name }}">
                                            @foreach ($category->products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                            @endforeach
                                        </optgroup>
                                        @endif
                                    @endforeach
                                @endif                                          
                            </select>
                        </td>    
                        <td style="width: 300px;"><input type="text" class="form-control" style="width: 300px;margin: 0 auto;" name="products[${count}][description]" id="products_${count}_description" data-row="${count}"></td>            
                        <td style="width: 200px;"><input type="text" class="form-control qty text-center" style="width: 200px;margin: 0 auto;" onkeyup="calculateRowTotal(${count})" name="products[${count}][qty]" id="products_${count}_qty"  data-row="${count}"></td>
                        <td style="width: 200px;"><input type="text" style="width:200px;margin: 0 auto;" class="text-right form-control net_unit_price" onkeyup="calculateRowTotal(${count})" name="products[${count}][net_unit_price]" id="products_${count}_net_unit_price" data-row="${count}"></td>
                        <td class="subtotal_${count} text-right" id="sub_total_${count}" data-row="${count}"></td>
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

function getViaCustomers(customer_id)
{
    $('.customer_data').empty().addClass('d-none');
    const customer_code       = $('#customer_id option:selected').data('code');
    const customer_name       = $('#customer_id option:selected').data('name');
    const customer_trade_name = $('#customer_id option:selected').data('tradename');
    const customer_mobile     = $('#customer_id option:selected').data('mobile');
    const customer_address    = $('#customer_id option:selected').data('address');
    const customer_data = `
                            <table>
                                <tbody>
                                    <tr><td><b>Customer Code</b></td><td><b>:</b> ${customer_code}</td></tr>
                                    <tr><td><b>Trade Name</b></td><td><b>:</b> ${customer_trade_name}</td></tr>
                                    <tr><td><b>Customer Name</b></td><td><b>:</b> ${customer_name}</td></tr>
                                    <tr><td><b>Mobile Number</b></td><td><b>:</b> ${customer_mobile}</td></tr>
                                    <tr><td><b>Address</b></td><td><b>:</b> ${customer_address}</td></tr>
                                </tbody>
                            </table>
                        `;
    $('.customer_data').removeClass('d-none').append(customer_data);
    $.ajax({
        url:"{{ url('customer-wise-via-customer-list') }}/"+customer_id,
        type:"GET",
        dataType:"JSON",
        success:function(data){
            console.log(data);
            var html = '<option value="">Select Please</option>';
            $.each(data, function(key, value) {
                html += `<option value="${value.id}">${value.name} - ${value.mobile}</option>`;
            });
            $(`#via_customer_id`).empty().append(html);
            $(`#via_customer_id.selectpicker`).selectpicker('refresh');
        },
    });
    
}
function store_data(){
    var rownumber = $('table#product_table tbody tr:last').index();
    if (rownumber < 0) {
        notification("error","Please insert material to order table!")
    }else{
        let form = document.getElementById('sale_order_form');
        let formData = new FormData(form);
        let url = "{{route('sale.order.store')}}";
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
                $('#sale_order_form').find('.is-invalid').removeClass('is-invalid');
                $('#sale_order_form').find('.error').remove();
                if (data.status == false) {
                    $.each(data.errors, function (key, value) {
                        var key = key.split('.').join('_');
                        $('#sale_order_form input#' + key).addClass('is-invalid');
                        $('#sale_order_form textarea#' + key).addClass('is-invalid');
                        $('#sale_order_form select#' + key).parent().addClass('is-invalid');
                        $('#sale_order_form #' + key).parent().append(
                            '<small class="error text-danger">' + value + '</small>');
                    });
                } else {
                    notification(data.status, data.message);
                    if (data.status == 'success') {
                        window.location.replace("{{ url('sale/order/view') }}/"+data.sale_id);
                        
                        
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