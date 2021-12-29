@php
    $grand_total = $discount_amount = $net_total = $paid_amount = $due_amount = 0;
@endphp
<div class="col-sm-12 table-responsive">
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
        <div class="invoice overflow-auto">
            <div>
                <table style="margin-bottom:10px !important;">
                    <tr>
                        <td class="text-center">
                            @if (config('settings.logo'))
                            <a href="{{ url('dashboard') }}">
                                <img src="{{ asset('storage/'.LOGO_PATH.config('settings.logo'))}}" style="max-width: 60px;" alt="Logo" />
                            </a>
                            @endif
                            <h2 class="name m-0" style="text-transform: uppercase;"><b>{{ config('settings.title') ? config('settings.title') : env('APP_NAME') }}</b></h2>
                            {{-- @if(config('settings.contact_no'))<p style="font-weight: normal;margin:0;"><b>Contact No.: </b>{{ config('settings.contact_no') }}, @if(config('settings.email'))<b>Email: </b>{{ config('settings.email') }}@endif</p>@endif --}}
                            @if(config('settings.address'))<p style="font-weight: normal;margin:0;">{{ config('settings.address') }}</p>@endif
                            <p style="font-weight: normal;font-weight:bold;    margin: 10px auto 5px auto;
                            font-weight: bold;background: black;border-radius: 10px;width: 250px;color: white;text-align: center;padding:5px 0;}">TODAYS PURCHASE REPORT</p>
                            <p style="font-weight: normal;margin:0;font-weight:bold;">Date: {{ date('d-M-Y',strtotime($date))  }}</p>
                            
                        </td>
                    </tr>
                </table>
                <table cellspacing="0" cellpadding="0" id="product_table">
                    <tbody>
                        <tr>
                            <td class="text-center font-weight-bolder">Sl</td>
                            <td class="text-center font-weight-bolder">Memo No.</td>
                            <td class="text-center font-weight-bolder">Supplier Name</td>
                            <td class="text-center font-weight-bolder">Item</td>
                            <td class="text-center font-weight-bolder">Total Qty</td>
                            <td class="text-center font-weight-bolder">Grand Total</td>
                            <td class="text-center font-weight-bolder">Discount Amount</td>
                            <td class="text-right font-weight-bolder">Net Total</td>
                            <td class="text-right font-weight-bolder">Paid Amount</td>
                            <td class="text-right font-weight-bolder">Due Amount</td>
                        </tr>
                        @if (!$report_data->isEmpty())
                        @foreach ($report_data as $key => $value)
                        <tr>
                            <td class="text-center"> {{ $key + 1 }} </td>
                            <td class="text-center"> {{ $value->memo_no }} </td>
                            <td class="text-center"> {{ $value->supplier->company_name.' - '.$value->supplier->name }} </td>
                            <td class="text-center"> {{ number_format($value->item,2,'.',',') }} </td>
                            <td class="text-center"> {{ number_format($value->total_qty,2,'.',',') }} </td>
                            <td class="text-right"> {{ number_format($value->grand_total,2,'.',',') }} </td>
                            <td class="text-right"> {{ number_format(($value->discount_amount ?? 0),2,'.',',') }} </td>
                            <td class="text-right"> {{ number_format($value->net_total,2,'.',',') }} </td>
                            <td class="text-right"> {{ number_format($value->paid_amount ?? 0,2,'.',',') }} </td>
                            <td class="text-right"> {{ number_format($value->due_amount,2,'.',',') }} </td>
                        </tr>
                        @php
                        $grand_total += $value->grand_total;
                        $discount_amount += $value->discount_amount;
                        $net_total += $value->net_total;
                        $paid_amount += $value->paid_amount ?? 0;
                        $due_amount += $value->due_amount;
                        @endphp
                        @endforeach
                        @else
                        <tr><td colspan="10" class="text-center" style="color: red;font-weight:bold;">No Data Found</td></tr>
                        @endif
                        <tr>
                            <td style="font-weight:bold;" colspan="5">Total</td>
                            <td style="text-align: right !important;font-weight:bold;">{{ number_format($grand_total,2,'.',',') }}</td>
                            <td style="text-align: right !important;font-weight:bold;">{{ number_format($discount_amount,2,'.',',') }}</td>
                            <td style="text-align: right !important;font-weight:bold;">{{ number_format($net_total,2,'.',',') }}</td>
                            <td style="text-align: right !important;font-weight:bold;">{{ number_format($paid_amount,2,'.',',') }}</td>
                            <td style="text-align: right !important;font-weight:bold;">{{ number_format($due_amount,2,'.',',') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>