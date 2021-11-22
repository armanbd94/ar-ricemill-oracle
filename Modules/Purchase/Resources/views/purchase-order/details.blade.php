@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<style>
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
                    <button type="button" class="btn btn-primary btn-sm mr-3" id="print-invoice"> <i class="fas fa-print"></i> Print</button>
                    
                    <a href="{{ route('purchase.order') }}" class="btn btn-warning btn-sm font-weight-bolder"> 
                        <i class="fas fa-arrow-left"></i> Back</a>
                    <!--end::Button-->
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom" style="padding-bottom: 100px !important;">
            <div class="card-body" style="padding-bottom: 100px !important;">
                <div class="col-md-12 col-lg-12"  style="width: 100%;">
                    <div id="invoice">
                        <style>
                            body,html {
                                background: #fff !important;
                                -webkit-print-color-adjust: exact !important;
  
                            }

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
                                    /* background: #fff !important; */
                                    -webkit-print-color-adjust: exact !important;
                                    font-family: sans-serif;
                                    /* font-size: 12px !important; */
                                    /* margin-bottom: 100px !important; */
                                }
                                html, body, div, span, applet, object, iframe, h1, h2, h3, h4, h5, h6, p, blockquote, pre, a, abbr, acronym, address, big, cite, code, del, dfn, em, font, ins, kbd, q, s, samp, small, strike, strong, sub, sup, tt, var, dl, dt, dd, ol, ul, li, fieldset, form, label, legend,  {
                                    font-size: 10pt !important;
                                }
                                #product_table tbody td{
                                    font-size: 9pt !important;
                                }
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
                        <div class="invoice overflow-auto">
                            <div>
                                <table>
                                    <tr>
                                        <td class="text-center">
                                            <h2 class="name m-0" style="text-transform: uppercase;"><b>{{ config('settings.title') ? config('settings.title') : env('APP_NAME') }}</b></h2>
                                            @if(config('settings.contact_no'))<p style="font-weight: normal;margin:0;"><b>Contact No.: </b>{{ config('settings.contact_no') }}, @if(config('settings.email'))<b>Email: </b>{{ config('settings.email') }}@endif</p>@endif
                                            @if(config('settings.address'))<p style="font-weight: normal;margin:0;">{{ config('settings.address') }}</p>@endif
                                            <p style="font-weight: normal;font-weight:bold;    margin: 10px auto 5px auto;
                            font-weight: bold;background: black;border-radius: 10px;width: 200px;color: white;text-align: center;padding:5px 0;}">PURCHASE MEMO</p>
                                            <p style="font-weight: normal;margin:0;"><b>Date: </b>{{ date('d-M-Y') }}</p>
                                        </td>
                                    </tr>
                                </table>
                                <div style="width: 100%;height:3px;border-top:1px solid #000;border-bottom:1px solid #000;"></div>
                                <table style="margin-bottom: 0px;margin-top:10px;" id="info-table">
                                    <tr>
                                        <td width="40%">
                                            <table>
                                                <tr>
                                                    <td colspan="2"><b>Billing To</b></td>
                                                </tr>
                                                <tr>
                                                    <td><b>Vendor Name</b></td>
                                                    <td><b>: {{ $purchase->vendor->name}}</b></td>
                                                </tr>
                                                <tr>
                                                    <td><b>Mobile No.</b></td>
                                                    <td><b>: </b>{{ $purchase->vendor->mobile }}</td>
                                                </tr>
                                                @if($purchase->vendor->address)
                                                <tr>
                                                    <td><b>Address</b></td>
                                                    <td><b>: </b>{{ $purchase->vendor->address }}</td>
                                                </tr>
                                                @endif
                                                @if($purchase->via_vendor_id)
                                                <tr>
                                                    <td><b>Via Vendor Name</b></td>
                                                    <td><b>: </b>{{ $purchase->via_vendor->name }}</td>
                                                </tr>
                                                @endif
                                            </table>
                                        </td>
                                        <td width="20%"></td>
                                        <td width="40%">
                                            <table>
                                                <tr><td colspan="2"></td></tr>
                                                <tr>
                                                    <td><b>Memo No.</b></td>
                                                    <td><b>: #{{ $purchase->memo_no }}</b></td>
                                                </tr>
                                                <tr>
                                                    <td><b>Order Date</b></td>
                                                    <td><b>: </b> {{ date('d-M-Y',strtotime($purchase->order_date)) }}</td>
                                                </tr>
                                                <tr>
                                                    <td><b>Delivery Date</b></td>
                                                    <td><b>: </b> {{ date('d-M-Y',strtotime($purchase->delivery_date)) }}</td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                <table cellspacing="0" cellpadding="0" id="product_table">
                                    <tbody>
                                        <tr>
                                            <td class="text-center font-weight-bolder">SL</td>
                                            <td class="text-left font-weight-bolder">ITEM</td>
                                            <td class="text-center font-weight-bolder">CLASS</td>
                                            <td class="text-center font-weight-bolder">UNIT</td>
                                            <td class="text-center font-weight-bolder">QUANTITY</td>
                                            <td class="text-right font-weight-bolder">RATE</td>
                                            <td class="text-right font-weight-bolder">SUBTOTAL</td>
                                        </tr>
                                        @if (!$purchase->materials->isEmpty())
                                            @foreach ($purchase->materials as $key => $item)
                                                @php
                                                    $unit_name = '';
                                                    if($item->pivot->purchase_unit_id)
                                                    {
                                                        $unit_name = DB::table('units')->where('id',$item->pivot->purchase_unit_id)->value('unit_name');
                                                    }
                                                @endphp
                                                <tr>
                                                    <td class="text-center">{{ $key+1 }}</td>
                                                    <td class="text-left">{{ $item->material_name }}</td>
                                                    <td class="text-center">{{ $item->category->name }}</td>
                                                    <td class="text-center">{{ $unit_name }}</td>
                                                    <td class="text-center">{{ $item->pivot->qty }}</td>
                                                    <td class="text-right">{{ $item->pivot->net_unit_cost }}</td>
                                                    <td class="text-right"> {{ $item->pivot->total }}</td>
                                                </tr>
                                            @endforeach
                                        @endif
                                        <tr>
                                            <td colspan="6"  class="text-right font-weight-bolder">TOTAL</td>
                                            <td class="text-right font-weight-bolder"> {{ $purchase->grand_total }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table style="width: 100%;margin-top:10px;">
                                    <tr>
                                        <td class="text-center">
                                            <div class="font-size-10" style="width:250px;float:left;">
                                                <p style="margin:0;padding:0;"><b class="text-uppercase">{{ $purchase->received_by }}</b></p>
                                                <p class="dashed-border"></p>
                                                <p style="margin:0;padding:0;">Received By</p>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="font-size-10" style="width:250px;float:right;">
                                                <p style="margin:0;padding:0;"><b class="text-uppercase">{{ $purchase->created_by }}</b>
                                                    <br> {{ date('d-M-Y h:i:s A',strtotime($purchase->created_at)) }}</p>
                                                <p class="dashed-border"></p>
                                                <p style="margin:0;padding:0;">Generated By</p>
                                            </div>
                                        </td>

                                        
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!--end::Card-->
    </div>
</div>
@endsection

@push('scripts')
<script src="js/jquery.printarea.js"></script>
<script>
$(document).ready(function () {
    //QR Code Print
    $(document).on('click','#print-invoice',function(){
        var mode = 'iframe'; // popup
        var close = mode == "popup";
        var options = {
            mode: mode,
            popClose: close
        };
        $("#invoice").printArea(options);
    });
});

</script>
@endpush