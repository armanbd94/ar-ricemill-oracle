@extends('layouts.app')

@section('title', $page_title)


@section('content')
<div class="d-flex flex-column-fluid">
    <div class="container-fluid">
        <!--begin::Notice-->
        <div class="card card-custom gutter-b">
            <div class="card-header flex-wrap py-5">
                <div class="card-title">
                    <h3 class="card-label"><i class="{{ $page_icon }} text-primary"></i> {{ $sub_title }}</h3>
                </div>
                <div class="card-toolbar">
                    <!--begin::Button-->
                    <button type="button" class="btn btn-primary btn-sm mr-3" id="print-invoice"> <i class="fas fa-print"></i> Print</button>
                    @if (permission('supplier-payment-access'))
                    <a href="{{ url('supplier-payment') }}" class="btn btn-warning btn-sm font-weight-bolder"> 
                        <i class="fas fa-arrow-left"></i> Back</a>
                    @endif
                    <!--end::Button-->
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom">
            <div class="card-body">
                <div class="col-md-12 col-lg-12 d-flex flex-wrap justify-content-center">
                    <div class="col-md-6">
                        <div class="card card-custom card-border">
                            <div class="card-body">
                                <div id="printableArea" style="width: 100%;margin:0;padding:0;">
                                    <link href="{{asset('css/print.css')}}" rel="stylesheet" type="text/css" />
                                    <table width="100%" style="margin:0;padding:0;">
                                        <tr>
                                            <td width="100%" class="text-center">
                                                <h3 style="margin:0;">{{ config('settings.title') ? config('settings.title') : env('APP_NAME') }}</h3>
                                                @if(config('settings.contact_no'))<p style="font-weight: normal;margin:0;"><b>Contact: </b>{{ config('settings.contact_no') }}, @if(config('settings.email'))<b>Email: </b>{{ config('settings.email') }}@endif</p>@endif
                                                @if(config('settings.address'))<p style="font-weight: normal;margin:0;">{{ config('settings.address') }}</p>@endif
                                                <p style="font-weight: normal;margin:0;"><b>Date: </b>{{ date('d-M-Y') }}</p>
                                            </td>
                                        </tr>
                                    </table>
                                    <div style="width:100%;height:2px;padding:5px 0;"></div>
                                    <div style="width:100%;height:2px;border-bottom:1px dotted #454d55;padding-bottom:25px;"></div>
                                    <div style="width:100%;height:2px;padding-top:5px;"></div>
                                    <table class="table table-borderless" style="width:100%;" id="invoice-table" border="0">
                                        <tr>
                                            <td width="25%"><b>Voucher No</b></td><td width="5%"><b>:</b></td><td width="70%" class="text-left">{{ $data->voucher_no }}</td>
                                        </tr>
                                        <tr>
                                            <td width="25%"><b>Name</b></td><td width="5%"><b>:</b></td><td width="70%" class="text-left">{{ $data->coa->supplier->name }}</td>
                                        </tr>
                                        <tr>
                                            <td width="25%"><b>Payment Type</b></td width="5%"><td><b>:</b></td><td width="70%" class="text-left">{{ PAYMENT_METHOD[$payment_type] }}</td>
                                        </tr>
                                        <tr>
                                            <td width="25%"><b>Amount</b></td><td width="5%"><b>:</b></td><td width="70%" class="text-left">{{ number_format($data->debit,2,'.',',') }}Tk</td>
                                        </tr>
                                        <tr>
                                            <td width="25%"><b>Remarks</b></td><td width="5%"><b>:</b></td><td width="70%" class="text-left">{{ $data->description }}</td>
                                        </tr>
                                    </table>

                                    
                                    <table style="width: 100%;">
                                        <tr>
                                            <td class="text-center">
                                                <div class="font-size-10" style="width:250px;float:right;padding-top:50px;">
                                                    <p style="margin:0;padding:0;"><b class="text-uppercase">{{ $data->created_by }}</b>
                                                        <br> {{ date('d-M-Y h:i:s A',strtotime($data->created_at)) }}</p>
                                                    <p class="dashed-border m-0"></p>
                                                    <p style="margin:0;padding:0;">Signature</p>
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
        $("#printableArea").printArea(options);
    });
});

</script>
@endpush