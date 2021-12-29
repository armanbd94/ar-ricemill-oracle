@extends('layouts.app')

@section('title', $page_title)

@section('content')
<div class="d-flex flex-column-fluid">
    <div class="container-fluid">
        <!--begin::Notice-->
        <div class="card card-custom custom-card">
            <div class="card-header flex-wrap p-0">
                <div class="card-toolbar m-0">
                    <!--begin::Button-->
                    <button type="button" class="btn btn-primary btn-sm mr-3 custom-btn" id="print-invoice"> <i class="fas fa-print"></i> Print</button>

                    <!--end::Button-->
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <div class="card card-custom">
            <div class="card-body">
                <!--begin: Datatable-->
                <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <div class="col-md-12" style="position: relative;">
                        <div class="row" id="report_data">
                        
                        </div>
                        <div class="col-md-12 d-none" id="table-loader" style="position: absolute;top:80px;left:0;">
                            <div style="width: 120px;
                            height: 70px;
                            background: white;
                            text-align: center;
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            border: 1px solid #ddd;
                            border-radius: 5px;
                            margin: 0 auto;">
                                <i class="fas fa-spinner fa-spin fa-3x fa-fw text-primary"></i>
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
report_data();
function report_data()
{
    $.ajax({
        url:"{{ route('todays.purchase.order.report.data') }}",
        type:"POST",
        data:{_token:_token},
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
}

</script>
@endpush