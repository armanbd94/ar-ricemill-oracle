@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link href="css/daterangepicker.min.css" rel="stylesheet" type="text/css" />
@endpush

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
                    <button type="button" id="print-report" class="btn btn-primary btn-sm font-weight-bolder"> 
                        <i class="fas fa-print"></i> Print</button>

                </div>
            </div>

        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom">
            <div class="card-header flex-wrap py-5">
                <form method="POST" id="form-filter" class="col-md-12 px-0">
                    <div class="row">
                        <div class="form-group col-md-3">
                            <label for="name">Choose Your Date</label>
                            <div class="input-group">
                                <input type="text" class="form-control daterangepicker-filed" value="{{ date('Y-m-d') }} To {{ date('Y-m-d') }}">
                                <input type="hidden" id="start_date" name="start_date" value="{{ date('Y-m-d') }}">
                                <input type="hidden" id="end_date" name="end_date" value="{{ date('Y-m-d')}}">
                            </div>
                        </div>
                        <x-form.selectbox labelName="General Head" name="general_head"  col="col-md-3" class="selectpicker">
                            @if (!$general_heads->isEmpty())
                                @foreach ($general_heads as $general_head)
                                    <option value="{{ $general_head->id }}" data-name="{{ $general_head->name }}">{{ $general_head->name }}</option>
                                @endforeach
                            @endif
                        </x-form.selectbox>

                        <x-form.selectbox labelName="Transaction Head" name="transaction_head"  col="col-md-3" class="selectpicker"/>
                        <x-form.selectbox labelName="Warehouse" name="warehouse_id" col="col-md-3" class="selectpicker">
                            @if (!$warehouses->isEmpty())
                            @foreach ($warehouses as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                            @endif
                        </x-form.selectbox>
                        
                        <div class="col-md-12">
                            <div style="margin-top:28px;">    
                                    <button id="btn-filter" class="btn btn-primary btn-sm btn-elevate btn-icon mr-2 float-right" type="button"
                                    data-toggle="tooltip" data-theme="dark" title="Search" onclick="report()">
                                    <i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-body">
                <!--begin: Datatable-->
                <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <div id="report" style="width: 100%;margin:0;padding:0;">
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
                        <div style="width:100%;height:2px;border-bottom:1px dotted #454d55;"></div>
                        <div style="width:100%;height:2px;padding-top:5px;"></div>
                        <h4 class="text-dark text-center py-3"><b> (From To)</b></h4>
                        <table class="table table-borderless" style="width:100%;">
                            <thead class="bg-primary">
                                <tr>
                                    <th class="text-right">Pre Balance: {{ number_format(0,2) }}</th>
                                </tr>
                                <tr>
                                    <th class="text-right">Current Balance: {{ number_format(0,2) }}</th>
                                </tr>
                            </thead>
                        </table>
                        <table class="table table-bordered" style="width:100%;">
                            <thead class="bg-primary">
                                <tr>
                                    <th class="text-center">SL.</th>
                                    <th class="text-cnter">Transaction Date</th>
                                    <th class="text-cnter">Particulars</th>
                                    <th class="text-right">Debit</th>
                                    <th class="text-right">Credit</th>
                                    <th class="text-right">Balance</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                            <tfoot>
                                <tr class="bg-primary">
                                    <td colspan="3" class="text-right text-white font-weight-bolder">Total</td>
                                    <td class="text-right text-white font-weight-bolder">0.00</td>
                                    <td class="text-right text-white font-weight-bolder">0.00</td>
                                    <td class="text-right text-white font-weight-bolder">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <!--end: Datatable-->
            </div>
        </div>
        <!--end::Card-->
    </div>
</div>

@endsection

@push('scripts')
<script src="js/jquery.printarea.js"></script>
<script src="js/moment.js"></script>
<script src="js/knockout-3.4.2.js"></script>
<script src="js/daterangepicker.min.js"></script>
<script>
$('.daterangepicker-filed').daterangepicker({
    callback: function(startDate, endDate, period){
        var start_date = startDate.format('YYYY-MM-DD');
        var end_date   = endDate.format('YYYY-MM-DD');
        var title      = start_date + ' To ' + end_date;
        $(this).val(title);
        $('input[name="start_date"]').val(start_date);
        $('input[name="end_date"]').val(end_date);
    }
});

$(document).on('click','#print-report',function(){
    var mode = 'iframe'; // popup
    var close = mode == "popup";
    var options = {
        mode: mode,
        popClose: close
    };
    $("#report").printArea(options);
});

function report()
{
    var start_date   = $('input[name="start_date"]').val();
    var end_date     = $('input[name="end_date"]').val();
    var general_head = $('#general_head option:selected').val();
    var transaction_head = $('#transaction_head option:selected').val();
    var bank_name = $('#transaction_head option:selected').data('name');
    var warehouse_id = document.getElementById('warehouse_id').value;
    if(transaction_head){
        if(warehouse_id){
            $.ajax({
                url:"{{ url('general-ledger/report') }}",
                type:"POST",
                data:{start_date:start_date,end_date:end_date,general_head:general_head,transaction_head:transaction_head,
                    bank_name:bank_name,warehouse_id:warehouse_id,_token:_token},
                success:function(data){
                    $('#report').empty();
                    $('#report').append(data);
                },
                error: function (xhr, ajaxOption, thrownError) {
                    console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                }
            });
        }else{
            notification('error','Please select warehouse!');
        }
    }else{
        notification('error','Please select a transaction head');
    }
    
}

$(document).on('change','#general_head',function(){
    var parent_name = $('#general_head option:selected').data('name');
    $.ajax({
        url:"{{ url('general-ledger/transaction-heads') }}",
        type:"POST",
        data:{parent_name:parent_name,_token:_token},
        success:function(data){
            $('#transaction_head').empty();
            $('#transaction_head').html(data);
            $('#transaction_head.selectpicker').selectpicker('refresh');
        },
        error: function (xhr, ajaxOption, thrownError) {
            console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
        }
    });
});

</script>
@endpush