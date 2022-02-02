@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
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
                        <x-form.textbox labelName="To Date" name="to_date" value="{{ date('d-m-Y') }}"  class="date" col="col-md-4"/>

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
<script src="js/bootstrap-datetimepicker.min.js"></script>
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

    $('.date').datetimepicker({format: 'DD-MM-YYYY'});

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
    let to_date = document.getElementById('to_date').value;
    let vendor_id = document.getElementById('vendor_id').value;
    if (to_date) {

        $.ajax({
            url:"{{ route('vendor.balance.summary.data') }}",
            type:"POST",
            data:{to_date:to_date,vendor_id:vendor_id,_token:_token},
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