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
                    <a href="{{ route('sale.invoice') }}" class="btn btn-danger btn-sm mr-3 custom-btn"><i class="fas fa-window-close"></i> Cancel</a>
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


                    <form action="" id="cash_sale_form" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                        <input type="hidden" name="order_id" value="{{ $sale->id }}">
                        <input type="hidden" class="form-control" name="order_total_qty" value="{{ $sale->total_qty }}" />
                        <input type="hidden" class="form-control" name="customer_coa_id" value="{{ $sale->customer->coa->id }}" />
                        <input type="hidden" class="form-control" name="customer_trade_name" value="{{ $sale->customer->trade_name }}" />
                        <div class="row">
                            <x-form.textbox labelName="Invoice Date" name="invoice_date" value="{{ $invoice->invoice_date }}" required="required" class="date" col="col-md-3"/>
                            <div class="form-group col-md-3 required">
                                <label for="memo_no">Memo No.</label>
                                <input type="text" class="form-control" name="memo_no" id="memo_no" value="{{ $sale->memo_no }}"  />
                            </div>
                            <div class="form-group col-md-3 required">
                                <label for="challan_no">Challan Number</label>
                                <input type="text" class="form-control" name="challan_no" id="challan_no" value="{{ $invoice->challan_no }}" />
                            </div>
                            <x-form.selectbox labelName="Customer" name="customer_id" col="col-md-3" class="selectpicker" required="required">
                                @if (!$customers->isEmpty())
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ $sale->customer_id == $customer->id ? 'selected' : 'disabled' }} data-code="{{ $customer->code }}" data-tradename="{{ $customer->trade_name }}"
                                            data-name="{{ $customer->name }}" data-mobile="{{ $customer->mobile }}" data-address="{{ $customer->address }}"
                                            >{{ $customer->trade_name.' - '.$customer->name.' ('.$customer->mobile.')' }}</option>
                                    @endforeach
                                @endif
                            </x-form.selectbox>
                            <x-form.selectbox labelName="Via Customer" name="via_customer_id" col="col-md-3" class="selectpicker" >
                                @if (!$via_customers->isEmpty())
                                    @foreach ($via_customers as $customer)
                                        <option value="{{ $customer->id }}" {{ $sale->via_customer_id == $customer->id ? 'selected' : 'disabled' }}  >{{ $customer->trade_name.' - '.$customer->name.' ('.$customer->mobile.')' }}</option>
                                    @endforeach
                                @endif
                            </x-form.selectbox>
                            <x-form.textbox labelName="Shipping Address" name="shipping_address"  value="{{ $sale->shipping_address }}" required="required" col="col-md-3"/>
                            <x-form.textbox labelName="Transport No." name="transport_no"  value="{{ $invoice->transport_no }}" col="col-md-3"/>
                            <x-form.textbox labelName="Truck Fare" name="truck_fare"  value="{{ $invoice->truck_fare }}" col="col-md-3"/>
                            <x-form.selectbox labelName="Terms" name="terms" col="col-md-3" class="selectpicker" >
                                <option value="1" {{ $invoice->terms == 1 ? 'selected' : '' }} >Office Payable</option>
                                <option value="2" {{ $invoice->terms == 2 ? 'selected' : '' }} >Customer Payable</option>
                            </x-form.selectbox>
                            <x-form.textbox labelName="Driver's Mobile No." name="transport_no"  value="{{ $invoice->transport_no }}"  col="col-md-3"/>
                            
                            <div class="col-md-12 table-responsive" style="min-height: 500px;">

                                <table class="table table-bordered" id="product_table">
                                    <thead class="bg-primary">
                                        <th>Item</th>
                                        <th class="text-center">Site</th>
                                        <th class="text-center">Location</th>
                                        <th>Description</th>
                                        <th class="text-center">Available Qty</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-right">Rate</th>
                                        <th class="text-right">Subtotal</th>
                                        <th class="text-center">Class</th>
                                        <th class="text-center"><i class="fas fa-trash text-white"></i></th>
                                    </thead>
                                    <tbody>
                                        @if (!$invoice->products->isEmpty())
                                            @foreach ($invoice->products as $key => $item)
                                            <tr>
                                                <td style="width:250px;">                     
                                                    <select name="products[{{ $key + 1 }}][id]" id="products_{{ $key + 1 }}_id"  style="width:250px;" class="fcs col-md-12 form-control selectpicker" onchange="setProductDetails({{ $key + 1 }})"  data-live-search="true" data-row="{{ $key + 1 }}">    
                                                        <option value="">Select Please</option>     
                                                        @if(!$sale->products->isEmpty())  
                                                            @foreach ($sale->products as $product)
                                                                @php
                                                                    $remaining_qty = 0;
                                                                    $invoice_product_qty = DB::table('sale_invoice_products as sip')
                                                                                            ->leftJoin('sale_invoices as si','sip.sale_id','=','si.id')
                                                                                            ->leftJoin('sale_orders as so','si.order_id','=','so.id')
                                                                                            ->where(['so.memo_no'=>$sale->memo_no,'sip.product_id'=>$product->id])
                                                                                            ->sum('qty');
                                                                    $remaining_qty = ($product->pivot->qty - ($invoice_product_qty ? $invoice_product_qty : 0)) + $item->pivot->qty;
                                                                    $locations = DB::table('locations')->where('site_id',$item->pivot->site_id)->get();
                                                                    $available_qty = $item->pivot->qty;
                                                                    $stock_qty = DB::table('site_product')->where([
                                                                                        'site_id'     => $item->pivot->site_id,
                                                                                        'location_id' => $item->pivot->location_id,
                                                                                        'product_id' => $item->id,
                                                                                    ])->value('qty');
                                                                    $available_qty += $stock_qty ? $stock_qty : 0;
                                                                @endphp
                                                                @if($remaining_qty > 0)
                                                                <option value="{{ $product->id }}"  {{ $product->id == $item->id ? 'selected' : '' }}
                                                                    data-qty="{{ $remaining_qty }}" data-rate={{ $product->pivot->net_unit_price }}>{{ $product->name }}</option>
                                                                    @endif
                                                            @endforeach
                                                        @endif                                   
                                                    </select>
                                                </td>  
                                                <td style="width:250px;">                                                  
                                                    <select name="products[{{ $key + 1 }}][site_id]"  style="width:250px;" id="products_{{ $key + 1 }}_site_id" class="fcs col-md-12 site_id form-control selectpicker" onchange="getLocations(this.value,{{ $key + 1 }})"  data-live-search="true" data-row="{{ $key + 1 }}">                                            
                                                        <option value="">Select Please</option>  
                                                        @if(!$sites->isEmpty())  
                                                            @foreach ($sites as $site)
                                                                <option value="{{ $site->id }}" {{ $site->id == $item->pivot->site_id ? 'selected' : '' }}>{{ $site->name }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </td>  
                                                <td style="width:250px;">                                                  
                                                    <select name="products[{{ $key + 1 }}][location_id]"  style="width:250px;" onchange="product_stock_qty({{ $key + 1 }})" id="products_{{ $key + 1 }}_location_id" class="fcs col-md-12 location_id form-control selectpicker"  data-live-search="true" data-row="{{ $key + 1 }}">                                            
                                                        <option value="">Select Please</option>  
                                                        @if(!$locations->isEmpty())  
                                                                @foreach ($locations as $location)
                                                                    <option value="{{ $location->id }}" {{ $location->id == $item->pivot->location_id ? 'selected' : '' }}>{{ $location->name }}</option>
                                                                @endforeach
                                                            @endif
                                                    </select>
                                                </td> 
                                                
                                                <td><input type="text" class="form-control" style="width: 150px;margin: 0 auto;" name="products[{{ $key + 1 }}][description]" id="products_{{ $key + 1 }}_description"  value="{{ $item->pivot->description }}" data-row="{{ $key + 1 }}"></td>                                    
                                                <td><input type="text" class="form-control text-center" style="width: 120px;margin: 0 auto;" name="products[{{ $key + 1 }}][stock_qty]" id="products_{{ $key + 1 }}_stock_qty" value="{{ $available_qty }}"  data-row="{{ $key + 1 }}"></td>
                                                <td><input type="text" class="form-control qty text-center" style="width: 120px;margin: 0 auto;" onkeyup="calculateRowTotal({{ $key + 1 }})" name="products[{{ $key + 1 }}][qty]" id="products_{{ $key + 1 }}_qty" value="{{ $item->pivot->qty }}"  data-row="{{ $key + 1 }}"></td>
                                                <td><input type="text" style="width: 120px;margin: 0 auto;" class="text-right form-control net_unit_price" onkeyup="calculateRowTotal({{ $key + 1 }})" name="products[{{ $key + 1 }}][net_unit_price]" id="products_{{ $key + 1 }}_net_unit_price" value="{{ $item->pivot->net_unit_price }}" data-row="{{ $key + 1 }}"></td>
                                                <td class="subtotal_{{ $key + 1 }} text-right" id="sub_total_{{ $key + 1 }}" data-row="{{ $key + 1 }}">{{ $item->pivot->total }}</td>
                                                <td style="width:250px;">
                                                    <select name="products[{{ $key + 1 }}][item_class_id]" style="width:250px;" id="products_{{ $key + 1 }}_item_class_id" class="fcs col-md-12 form-control selectpicker" data-live-search="true" data-row="{{ $key + 1 }}">    
                                                        <option value="">Select Please</option>                                        
                                                        @if (!$classes->isEmpty())
                                                            @foreach ($classes as $class)
                                                                <option value="{{ $class->id }}" {{ $class->id == $item->pivot->item_class_id ? 'selected' : '' }}>{{ $class->name }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </td>
                                                <td class="text-center" data-row="{{ $key + 1 }}">
                                                @if ($key != 0)
                                                <button type="button" class="btn btn-danger btn-sm remove-product"><i class="fas fa-trash"></i></button>
                                                @endif
                                                </td>
                                                <input type="hidden" class="subtotal" id="products_{{ $key + 1 }}_subtotal" name="products[{{ $key + 1 }}][subtotal]" data-row="{{ $key + 1 }}" value="{{ $item->pivot->total }}">
                                            </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                    <tfoot class="bg-primary">
                                        <th colspan="5" class="font-weight-bolder">Total</th>
                                        <th id="total-qty" class="text-center font-weight-bolder">{{ $invoice->total_qty }}</th>
                                        <th></th>
                                        <th id="total" class="text-right font-weight-bolder">{{ $invoice->grand_total }}</th>
                                        <th></th>
                                        <th class="text-center"><button type="button" data-toggle="tooltip" data-theme="dark" title="Add More" class="btn btn-success btn-sm add-product"><i class="fas fa-plus"></i></button></th>
                                    </tfoot>
                                </table>
                            </div>

            
                            <div class="col-md-12">
                                <input type="hidden" name="item" value="{{ $invoice->item }}">
                                <input type="hidden" name="total_qty" value="{{ $invoice->total_qty }}">
                                <input type="hidden" name="grand_total" value="{{ $invoice->grand_total }}">
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
    @if (!$invoice->products->isEmpty())
    count = "{{ count($invoice->products) }}";
    @endif
    $('#product_table').on('click','.add-product',function(){
        count++;
        product_row_add(count);
    }); 

    function product_row_add(count){
        var html = `<tr>
                        <td style="width:250px;">                     
                            <select name="products[${count}][id]" style="width:250px;" id="products_${count}_id" class="fcs col-md-12 form-control selectpicker" onchange="setProductDetails(${count})"  data-live-search="true" data-row="${count}">    
                                <option value="">Select Please</option>  
                                @if(!$sale->products->isEmpty())  
                                    @foreach ($sale->products as $product)
                                        @php
                                            $remaining_qty = 0;
                                            $invoice_product_qty = DB::table('sale_invoice_products as sip')
                                                                    ->leftJoin('sale_invoices as si','sip.sale_id','=','si.id')
                                                                    ->leftJoin('sale_orders as so','si.order_id','=','so.id')
                                                                    ->where(['so.memo_no'=>$sale->memo_no,'sip.product_id'=>$product->id])
                                                                    ->sum('qty');
                                            $remaining_qty = $product->pivot->qty - ($invoice_product_qty ? $invoice_product_qty : 0)
                                        @endphp
                                        @if($remaining_qty > 0)
                                        <option value="{{ $product->id }}"  
                                            data-qty="{{ $remaining_qty }}" data-rate={{ $product->pivot->net_unit_price }}>{{ $product->name }}</option>
                                            @endif
                                    @endforeach
                                @endif                                             
                            </select>
                        </td>    
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
                            <select name="products[${count}][location_id]" style="width:250px;" onchange="product_stock_qty(${count})" id="products_${count}_location_id" class="fcs col-md-12 location_id form-control selectpicker"  data-live-search="true" data-row="${count}">                                            
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
function setProductDetails(row){
    $(`#products_${row}_stock_qty`).val('');
    $(`#products_${row}_site_id`).val('');
    $(`#products_${row}_location_id`).empty();
    $(`#products_${row}_site_id.selectpicker,#products_${row}_location_id.selectpicker`).selectpicker('refresh');
    const qty = $(`#products_${row}_id option:selected`).data('qty') ? parseFloat($(`#products_${row}_id option:selected`).data('qty')) : 0;
    const price = $(`#products_${row}_id option:selected`).data('rate') ? parseFloat($(`#products_${row}_id option:selected`).data('rate')) : 0;
    $(`#products_${row}_qty`).val(qty);
    $(`#products_${row}_net_unit_price`).val(price);
    calculateRowTotal(row);
} 
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
function product_stock_qty(row)
{
    const site_id       = $(`#products_${row}_site_id option:selected`).val();
    const location_id   = $(`#products_${row}_location_id option:selected`).val();
    const product_id   = $(`#products_${row}_id option:selected`).val();
    if(site_id && location_id && product_id)
    {
        $.ajax({
            url:"{{ route('product.stock.qty') }}",
            type:"POST",
            data:{
                site_id:site_id,location_id:location_id,product_id:product_id,_token:_token
            },
            dataType:"JSON",
            success:function(data){
                $(`#products_${row}_stock_qty`).val(data);
            },
        });
        
    }
}
function getLocations(site_id,row)
{
    if($(`#products_${row}_id option:selected`).val())
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
    }else{
        notification('warning','Please select product first!');
    }
    
}

function store_data(){
    var rownumber = $('table#product_table tbody tr:last').index();
    if (rownumber < 0) {
        notification("error","Please insert product to invoice table!")
    }else{
        let form = document.getElementById('cash_sale_form');
        let formData = new FormData(form);
        let url = "{{route('sale.invoice.update')}}";
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
                        window.location.replace("{{ url('sale/invoice') }}");
                        
                        
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