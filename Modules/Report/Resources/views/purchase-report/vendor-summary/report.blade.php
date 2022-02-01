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
                    <button type="button" class="btn btn-primary btn-sm custom-btn" id="print-invoice"> <i class="fas fa-print"></i> Print</button>
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

                        <x-form.selectbox labelName="Vendor" name="vendor_id" col="col-md-4" class="selectpicker">
                            @if (!$vendors->isEmpty())
                                @foreach ($vendors as $value)
                                    <option value="{{ $value->id }}">{{ $value->trade_name.' - '.$value->name.' - '.$value->address }}</option>
                                @endforeach
                            @endif
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
                            <x-invoice-style />
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
        $('#vendor_id').val('');
        $('#vendor_id.selectpicker').selectpicker('refresh');
        $('#report_data').empty();
        report_data();
    });
});
report_data();
function report_data()
{
    let start_date = document.getElementById('start_date').value;
    let end_date   = document.getElementById('end_date').value;
    let vendor_id = document.getElementById('vendor_id').value;
    if (start_date && end_date) {

        $.ajax({
            url:"{{ route('purchase.vendor.summary.data') }}",
            type:"POST",
            data:{start_date:start_date,end_date:end_date,vendor_id:vendor_id,_token:_token},
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