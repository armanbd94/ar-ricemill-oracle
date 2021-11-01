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
                    <!--begin::Button-->
                    <button type="button" id="print-report" class="btn btn-primary btn-sm font-weight-bolder"> 
                        <i class="fas fa-print"></i> Print</button>
                    @if (permission('bank-access'))
                    <a href="{{ route('bank') }}" class="btn btn-primary btn-sm font-weight-bolder ml-3"> 
                        <i class="fas fa-university"></i> Manage Bank</a>
                    @endif
                    @if (permission('bank-transaction-access'))
                    <a href="{{ route('bank.transaction') }}" class="btn btn-primary btn-sm font-weight-bolder ml-3"> 
                        <i class="far fa-money-bill-alt"></i> Bank Transaction</a>
                    @endif
                    <!--end::Button-->
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom">
            <div class="card-header flex-wrap py-5">
                <form method="POST" id="form-filter" class="col-md-12 px-0">
                    <div class="row justify-content-center">
                        <div class="form-group col-md-3">
                            <label for="name">Choose Your Date</label>
                            <div class="input-group">
                                <input type="text" class="form-control daterangepicker-filed">
                                <input type="hidden" id="from_date" name="from_date" >
                                <input type="hidden" id="to_date" name="to_date" >
                            </div>
                        </div>
                        <x-form.selectbox labelName="Bank Name" name="bank_name" required="required" col="col-md-3" class="selectpicker">
                            @if (!$banks->isEmpty())
                                @foreach ($banks as $bank)
                                    <option value="{{ $bank->bank_name }}">{{ $bank->bank_name.' - '.$bank->account_number }}</option>
                                @endforeach
                            @endif
                        </x-form.selectbox>
                        <div class="col-md-1">
                            <div style="margin-top:28px;">    
                                <div style="margin-top:28px;">    
                                    <button id="btn-reset" class="btn btn-danger btn-sm btn-elevate btn-icon float-right" type="button"
                                    data-toggle="tooltip" data-theme="dark" title="Reset">
                                    <i class="fas fa-undo-alt"></i></button>
    
                                    <button id="btn-filter" class="btn btn-primary btn-sm btn-elevate btn-icon mr-2 float-right" type="button"
                                    data-toggle="tooltip" data-theme="dark" title="Search">
                                    <i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
            <div class="card-body">
                <!--begin: Datatable-->
                <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <div class="row" id="report">
                        <style>
                            @media print {

                                body,
                                html {
                                    background: #fff !important;
                                    -webkit-print-color-adjust: exact !important;
                                    font-family: sans-serif;
                                }
                            }
                        </style>
                        <div class="col-md-12 pb-5">
                            <div class="row">
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
                            </div>
                        </div>
                        <div class="col-md-12">
                            <table id="bank-ledger-table" class="table table-bordered table-hover">
                                <thead class="bg-primary">
                                    <tr>
                                        <th>Date</th>
                                        <th>Bank Name</th>
                                        <th>Description</th>
                                        <th>Withdraw / Deposit ID</th>
                                        <th class="text-right">Debit (+)</th>
                                        <th class="text-right">Credit (-)</th>
                                        <th class="text-right">Balance</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
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
<script src="js/moment.js"></script>
<script src="js/knockout-3.4.2.js"></script>
<script src="js/daterangepicker.min.js"></script>
<script src="js/jquery.printarea.js"></script>
<script>
$(document).ready(function(){
    $('.daterangepicker-filed').daterangepicker({
        callback: function(startDate, endDate, period){
            var start_date = startDate.format('YYYY-MM-DD');
            var end_date   = endDate.format('YYYY-MM-DD');
            var title = start_date + ' To ' + end_date;
            $(this).val(title);
            $('input[name="from_date"]').val(start_date);
            $('input[name="to_date"]').val(end_date);
        }
    });

    $(document).on('click','#btn-filter',function(){
        let from_date = $('#from_date').val();
        let to_date   = $('#to_date').val();
        let bank_name = $('#bank_name option:selected').val();
        if(bank_name)
        {
            $.ajax({
                url: "{{route('bank.ledger.data')}}",
                type: "POST",
                data: {from_date:from_date,to_date:to_date,bank_name:bank_name,_token:_token},
                success:function(data){
                    if(data)
                    {
                        $('#bank-ledger-table tbody').html('');
                        $('#bank-ledger-table tbody').html(data);
                    }else{
                        $('#bank-ledger-table tbody').html('');
                    }
                },
                error: function (xhr, ajaxOption, thrownError) {
                    console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                }
            });
        }else{
            notification('error','Please select bank');
        }
        
    });
    $(document).on('click','#btn-reset',function(){
        $('#form-filter')[0].reset();
        $('#form-filter #from_date').val('');
        $('#form-filter #to_date').val('');
        $('#form-filter #bank_name').val('');
        $('#form-filter .selectpicker').selectpicker('refresh');
        $('#bank-ledger-table tbody').html('');
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
});
</script>
@endpush