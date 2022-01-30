@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link href="css/daterangepicker.min.css" rel="stylesheet" type="text/css" />
<style>
    .apply-btn,.cancel-btn{display: block !important;}
    .calendar-header .arrow,.calendar-header .arrow button{display: block !important;}
</style>
@endpush

@section('content')
<div class="d-flex flex-column-fluid">
    <div class="container-fluid">
        <!--begin::Notice-->
        <div class="card card-custom custom-card">
            <div class="card-header flex-wrap p-0">
                <div class="card-toolbar m-0">
                    <button type="button" class="btn btn-primary btn-sm mr-3 custom-btn" id="print-invoice"> <i class="fas fa-print"></i> Print</button>
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <div class="card card-custom">
            <div class="card-header flex-wrap py-5">
                <form method="POST" id="form-filter" class="col-md-12 px-0">
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label for="name">Choose Your Date</label>
                            <div class="input-group">
                                <input type="text" class="form-control daterangepicker-filed" value="{{ date('01-m-Y').' To '.date('d-m-Y') }}">
                                <input type="hidden" id="start_date" name="start_date" value="{{ date('01-m-Y') }}">
                                <input type="hidden" id="end_date" name="end_date"  value="{{ date('d-m-Y') }}">
                            </div>
                        </div>

                        <x-form.selectbox labelName="Product" name="product_id" col="col-md-4" class="selectpicker">
                            {!! $products !!}
                        </x-form.selectbox>

                        <div class="col-md-4">
                            <div style="margin-top:28px;">     
                                    <button id="btn-reset" class="btn btn-danger btn-sm btn-elevate btn-icon float-right custom-btn" type="button"
                                    data-toggle="tooltip" data-theme="dark" title="Reset">
                                    <i class="fas fa-undo-alt"></i></button>
    
                                    <button id="btn-filter" class="btn btn-primary btn-sm btn-elevate btn-icon mr-2 float-right custom-btn" type="button"
                                    data-toggle="tooltip" data-theme="dark" onclick="report_data()" title="Search">
                                    <i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-body">
                <!--begin: Datatable-->
                <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <div class="col-md-12 px-0" style="position: relative;">
                        <div id="invoice" style="width: 100%;">
                            <style>
                    
                                .invoice {
                                    /* position: relative; */
                                    background: #fff !important;
                                    /* min-height: 680px; */
                                }
                    
                                .invoice header {
                                    padding: 10px 0;
                                    margin-bottom: 20px;
                                    border-bottom: 1px solid #000;
                                }
                    
                                .invoice .company-details {
                                    text-align: right
                                }
                    
                                .invoice .company-details .name {
                                    margin-top: 0;
                                    margin-bottom: 0;
                                }
                    
                                .invoice .contacts {
                                    margin-bottom: 20px;
                                }
                    
                                .invoice .invoice-to {
                                    text-align: left;
                                }
                    
                                .invoice .invoice-to .to {
                                    margin-top: 0;
                                    margin-bottom: 0;
                                }
                    
                                .invoice .invoice-details {
                                    text-align: right;
                                }
                    
                                .invoice .invoice-details .invoice-id {
                                    margin-top: 0;
                                    color: #000;
                                }
                    
                                .invoice main {
                                    padding-bottom: 50px
                                }
                    
                                .invoice main .thanks {
                                    margin-top: -100px;
                                    font-size: 2em;
                                    margin-bottom: 50px;
                                }
                    
                                .invoice main .notices {
                                    padding-left: 6px;
                                    border-left: 6px solid #000;
                                }
                    
                                .invoice table {
                                    width: 100%;
                                    border-collapse: collapse;
                                    border-spacing: 0;
                                    margin-bottom: 20px;
                                }
                    
                                .invoice table th {
                                    background: #000;
                                    color: #fff;
                                    padding: 5px;
                                    border-bottom: 1px solid #fff
                                }
                    
                                .invoice table td {
                                    padding: 5px;
                                    border-bottom: 1px solid #fff
                                }
                                .invoice #product_table td{
                                    border: 1px solid #000 !important;
                                }
                                .invoice #product_table tbody tr:last-child td {
                                    border: 1px solid #000 !important;
                                }
                                #info-table td{padding:0px !important;}
                    
                                .invoice table th {
                                    white-space: nowrap;
                                }
                    
                                .invoice table td h3 {
                                    margin: 0;
                                    color: #000;
                                }
                    
                                .invoice table .qty {
                                    text-align: center;
                                }
                    
                                .invoice table .price,
                                .invoice table .discount,
                                .invoice table .tax,
                                .invoice table .total {
                                    text-align: right;
                                }
                    
                                .invoice table .no {
                                    color: #fff;
                                    background: #000
                                }
                    
                                .invoice table .total {
                                    background: #000;
                                    color: #fff
                                }
                    
                                .invoice table tbody tr:last-child td {
                                    border: none
                                }
                    
                                .invoice table tfoot td {
                                    background: 0 0;
                                    border-bottom: none;
                                    white-space: nowrap;
                                    text-align: right;
                                    padding: 10px 20px;
                                    border-top: 1px solid #aaa;
                                    font-weight: bold;
                                }
                    
                                .invoice table tfoot tr:first-child td {
                                    border-top: none
                                }
                    
                                /* .invoice table tfoot tr:last-child td {
                                    color: #000;
                                    border-top: 1px solid #000
                                } */
                    
                                .invoice table tfoot tr td:first-child {
                                    border: none
                                }
                    
                                .invoice footer {
                                    width: 100%;
                                    text-align: center;
                                    color: #777;
                                    border-top: 1px solid #aaa;
                                    padding: 8px 0
                                }
                    
                                .invoice a {
                                    content: none !important;
                                    text-decoration: none !important;
                                    color: #000 !important;
                                }
                    
                                .page-header,
                                .page-header-space {
                                    height: 100px;
                                }
                    
                                .page-footer,
                                .page-footer-space {
                                    height: 20px;
                    
                                }
                    
                                .page-footer {
                                    position: fixed;
                                    bottom: 0;
                                    width: 100%;
                                    text-align: center;
                                    color: #777;
                                    border-top: 1px solid #aaa;
                                    padding: 8px 0
                                }
                    
                                .page-header {
                                    position: fixed;
                                    top: 0mm;
                                    width: 100%;
                                    border-bottom: 1px solid black;
                                }
                    
                                .page {
                                    page-break-after: always;
                                }
                                .dashed-border{
                                    width:180px;height:2px;margin:0 auto;padding:0;border-top:1px dashed #454d55 !important;
                                }
                    
                                @media screen {
                                    .no_screen {display: none;}
                                    .no_print {display: block;}
                                    thead {display: table-header-group;} 
                                    tfoot {display: table-footer-group;}
                                    button {display: none;}
                                    body {margin: 0;}
                                }
                    
                                @media print {
                    
                                    body,
                                    html {
                                        background: #fff !important;
                                        -webkit-print-color-adjust: exact !important;
                                        font-family: sans-serif;
                                        /* font-size: 12px !important; */
                                        /* margin-bottom: 100px !important; */
                                    }
                                    html, body, div, span, applet, object, iframe, h1, h2, h3, h4, h5, h6, p, blockquote, pre, a, abbr, acronym, address, big, cite, code, del, dfn, em, font, ins, kbd, q, s, samp, small, strike, strong, sub, sup, tt, var, dl, dt, dd, ol, ul, li, fieldset, form, label, legend,tbody td  {
                                        font-size: 12pt !important;
                                    }
                                    /* .print_body {page-break-after: always;} */
                                    .m-0 {
                                        margin: 0 !important;
                                    }
                    
                                    h1,
                                    h2,
                                    h3,
                                    h4,
                                    h5,
                                    h6 {
                                        margin: 0 !important;
                                    }
                    
                                    .no_screen {
                                        display: block !important;
                                    }
                    
                                    .no_print {
                                        display: none;
                                    }
                    
                                    a {
                                        content: none !important;
                                        text-decoration: none !important;
                                        color: #000 !important;
                                    }
                    
                                    .text-center {
                                        text-align: center !important;
                                    }
                    
                                    .text-left {
                                        text-align: left !important;
                                    }
                    
                                    .text-right {
                                        text-align: right !important;
                                    }
                    
                                    .float-left {
                                        float: left !important;
                                    }
                    
                                    .float-right {
                                        float: right !important;
                                    }
                    
                                    .text-bold {
                                        font-weight: bold !important;
                                    }
                    
                                    .invoice {
                                        /* font-size: 11px!important; */
                                        overflow: hidden !important;
                                        background: #fff !important;
                                        margin-bottom: 100px !important;
                                    }
                    
                                    .invoice footer {
                                        position: absolute;
                                        bottom: 0;
                                        left: 0;
                                        /* page-break-after: always */
                                    }
                    
                                    /* .invoice>div:last-child {
                                        page-break-before: always
                                    } */
                                    .hidden-print {
                                        display: none !important;
                                    }
                                    .dashed-border{
                                        width:180px;height:2px;margin:0 auto;padding:0;border-top:1px dashed #454d55 !important;
                                    }
                                }
                    
                                @page {
                                    /* size: auto; */
                                    margin: 5mm 5mm;
                    
                                }
                            </style>
                            <div class="invoice overflow-auto" id="report_data">
                            
                            </div>
                            <div class="col-md-12 d-none" id="table-loader" style="position: absolute;top:80px;left:45%;">
                                <div style="table-loading-icon">
                                    <i class="fas fa-spinner fa-spin fa-3x fa-fw text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end: Datatable-->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="js/jquery.printarea.js"></script>
<script src="js/moment.js"></script>
<script src="js/knockout-3.4.2.js"></script>
<script src="js/daterangepicker.min.js"></script>
<script>
$(document).ready(function () {
    //QR Code Print
    $(document).on('click','#print-invoice',function(){
        var mode  = 'iframe'; // popup
        var close = mode == "popup";
        var options = {
            mode: mode,
            popClose: close
        };
        $("#invoice").printArea(options);
    });

    $('.daterangepicker-filed').daterangepicker({
        callback: function(startDate, endDate, period){
            var start_date = startDate.format('DD-MM-YYYY');
            var end_date   = endDate.format('DD-MM-YYYY');
            var title      = start_date + ' To ' + end_date;
            $(this).val(title);
            $('input[name="start_date"]').val(start_date);
            $('input[name="end_date"]').val(end_date);
        }
    });

    $('#btn-reset').click(function () {
        $('#form-filter')[0].reset();
        $('.daterangepicker-filed').val('');
        $('input[name="start_date"]').val('');
        $('input[name="end_date"]').val('');
        $('#product_id').val('');
        $('#product_id.selectpicker').selectpicker('refresh');
        $('#report_data').empty();
    });
});
report_data();
function report_data()
{
    let start_date = document.getElementById('start_date').value;
    let end_date   = document.getElementById('end_date').value;
    let product_id = document.getElementById('product_id').value;
    let group = product_id ? $('#product_id option:selected').data('group') : '';
    if (start_date && end_date) {

        $.ajax({
            url:"{{ route('sales.item.details.data') }}",
            type:"POST",
            data:{start_date:start_date,end_date:end_date,product_id:product_id,group:group,_token:_token},
            beforeSend: function(){
                $('#table-loader').removeClass('d-none');
            },
            complete: function(){
                $('#table-loader').addClass('d-none');
            },
            success:function(data){
                $('#report_data').empty().html(data);
            },
            error: function (xhr, ajaxOption, thrownError) {
                console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
            }
        });
  
    } else {
        notification('error','Please choose date!');
    }
    

}

</script>
@endpush