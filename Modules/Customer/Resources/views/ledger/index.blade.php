@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link href="plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
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
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Manage</button>
                        <div class="dropdown-menu" style="">
                            <a class="dropdown-item" href="{{ url('customer') }}">Customer</a>
                            <a class="dropdown-item" href="{{ url('credit-customer') }}">Credit Customer</a>
                            <a class="dropdown-item" href="{{ url('paid-customer') }}">Paid Customer</a>
                            <a class="dropdown-item" href="{{ url('customer-advance') }}">Customer Advance</a>
                        </div>
                    </div>
                    <!--end::Button-->
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom">
            <div class="card-header flex-wrap py-5">
                <form method="POST" id="form-filter" class="col-md-12 px-0">
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label for="name">Choose Your Date</label>
                            <div class="input-group">
                                <input type="text" class="form-control daterangepicker-filed" value="{{ date('Y-m-').'-01' }} To {{ date('Y-m-d') }}">
                                <input type="hidden" id="start_date" name="start_date" value="{{ date('Y-m-').'-01' }}">
                                <input type="hidden" id="end_date" name="end_date" value="{{ date('Y-m-d')}}">
                            </div>
                        </div>
                        <x-form.selectbox labelName="District" name="district_id" col="col-md-4" class="selectpicker" onchange="getUpazilaList(this.value,1);customer_list();" >
                            @if (!$locations->isEmpty())
                                @foreach ($locations as $location)
                                    @if ($location->type == 1 && $location->parent_id == null)
                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </x-form.selectbox>
                        <x-form.selectbox labelName="Upazila" name="upazila_id" col="col-md-4" class="selectpicker" onchange="getRouteList(this.value,1);customer_list();" >
                            @if (!$locations->isEmpty())
                                @foreach ($locations as $location)
                                    @if ($location->type == 2 && $location->parent_id == auth()->user()->district_id)
                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </x-form.selectbox>

                        <x-form.selectbox labelName="Route" name="route_id" col="col-md-4" class="selectpicker" onchange="getAreaList(this.value,1);customer_list();">
                            @if (!$locations->isEmpty())
                                @foreach ($locations as $location)
                                    @if ($location->type == 3 && $location->grand_parent_id == auth()->user()->district_id)
                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </x-form.selectbox>

                        <x-form.selectbox labelName="Area" name="area_id" col="col-md-4" class="selectpicker" onchange="customer_list()">
                            @if (!$locations->isEmpty())
                                @foreach ($locations as $location)
                                    @if ($location->type == 4 && $location->grand_grand_parent_id == auth()->user()->district_id)
                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </x-form.selectbox>
                        <x-form.selectbox labelName="Customer" name="customer_id" col="col-md-4" class="selectpicker"/>
                        
                        <div class="col-md-12">
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
                </form>
            </div>
            <div class="card-body">
                <!--begin: Datatable-->
                <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <div class="row">
                        <div class="col-sm-12">
                            <table id="dataTable" class="table table-bordered table-hover">
                                <thead class="bg-primary">
                                    <tr>
                                        <th>Date</th>
                                        <th>Narration</th>
                                        <th>Voucher No</th>
                                        <th>Debit</th>
                                        <th>Credit</th>
                                        <th>Balance</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr class="bg-primary">
                                        <th></th>
                                        <th></th>
                                        <th style="text-align: right !important;font-weight:bold;">Total</th>
                                        <th style="text-align: right !important;font-weight:bold;"></th>
                                        <th style="text-align: right !important;font-weight:bold;"></th>
                                        <th style="text-align: right !important;font-weight:bold;"></th>
                                    </tr>
                                </tfoot>
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
<script src="plugins/custom/datatables/datatables.bundle.js" type="text/javascript"></script>
<script src="js/moment.js"></script>
<script src="js/knockout-3.4.2.js"></script>
<script src="js/daterangepicker.min.js"></script>
<script>
$('.daterangepicker-filed').daterangepicker({
    callback: function(startDate, endDate, period){
        var start_date = startDate.format('YYYY-MM-DD');
        var end_date   = endDate.format('YYYY-MM-DD');
        var title = start_date + ' To ' + end_date;
        $(this).val(title);
        $('input[name="start_date"]').val(start_date);
        $('input[name="end_date"]').val(end_date);
    }
});
var table;
$(document).ready(function(){

    table = $('#dataTable').DataTable({
        "processing": true, //Feature control the processing indicator
        "serverSide": true, //Feature control DataTable server side processing mode
        "order": [], //Initial no order
        "responsive": true, //Make table responsive in mobile device
        "bInfo": true, //TO show the total number of data
        "bFilter": false, //For datatable default search box show/hide
        "lengthMenu": [
            [5, 10, 15, 25, 50, 100, 1000, 10000, -1],
            [5, 10, 15, 25, 50, 100, 1000, 10000, "All"]
        ],
        "pageLength": 25, //number of data show per page
        "language": { 
            processing: `<i class="fas fa-spinner fa-spin fa-3x fa-fw text-primary"></i> `,
            emptyTable: '<strong class="text-danger">No Data Found</strong>',
            infoEmpty: '',
            zeroRecords: '<strong class="text-danger">No Data Found</strong>'
        },
        "ajax": {
            "url": "{{route('customer.ledger.datatable.data')}}",
            "type": "POST",
            "data": function (data) {
                data.district_id = $("#form-filter #district_id").val();
                data.upazila_id  = $("#form-filter #upazila_id").val();
                data.route_id    = $("#form-filter #route_id").val();
                data.area_id     = $("#form-filter #area_id").val();
                data.customer_id = $("#form-filter #customer_id").val();
                data.start_date  = $("#form-filter #start_date").val();
                data.end_date    = $("#form-filter #end_date").val();
                data._token      = _token;
            }
        },
        "columnDefs": [
            {
                "targets": [0,2],
                "className": "text-center"
            },
            {
                "targets": [3,4,5],
                "className": "text-right"
            },
        ],
        "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6' <'float-right'B>>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'<'float-right'p>>>",

        "buttons": [
            {
                'extend':'colvis','className':'btn btn-secondary btn-sm text-white','text':'Column','columns': ':gt(0)'
            },
            {
                "extend": 'print',
                'text':'Print',
                'className':'btn btn-secondary btn-sm text-white',
                "title":'Customers Ledger From ' +$('#form-filter #start_date').val() + ' To ' +$('#form-filter #end_date').val(),
                "orientation": "portrait", //portrait
                "pageSize": "A4", //A3,A5,A6,legal,letter
                "exportOptions": {
                    columns: function (index, data, node) {
                        return table.column(index).visible();
                    }
                },
                customize: function (win) {
                    $(win.document.body).addClass('bg-white');
                    $(win.document.body).find('table thead').css({'background':'#034d97'});
                    $(win.document.body).find('table tfoot tr').css({'background-color':'#034d97'});
                    $(win.document.body).find('h1').css('text-align', 'center');
                    $(win.document.body).find('h1').css('font-size', '15px');
                    $(win.document.body).find('table').css( 'font-size', 'inherit' );
                },
                footer:true
            },
            {
                "extend": 'csv',
                'text':'CSV',
                'className':'btn btn-secondary btn-sm text-white',
                "title": "{{ $page_title }} List",
                "filename": 'Customers Ledger From ' +$('#form-filter #start_date').val() + ' To ' +$('#form-filter #end_date').val(),
                "exportOptions": {
                    columns: function (index, data, node) {
                        return table.column(index).visible();
                    }
                },
                footer:true
            },
            {
                "extend": 'excel',
                'text':'Excel',
                'className':'btn btn-secondary btn-sm text-white',
                "title": "{{ $page_title }} List",
                "filename": 'Customers Ledger From ' +$('#form-filter #start_date').val() + ' To ' +$('#form-filter #end_date').val(),
                "exportOptions": {
                    columns: function (index, data, node) {
                        return table.column(index).visible();
                    }
                },
                footer:true
            },
            {
                "extend": 'pdf',
                'text':'PDF',
                'className':'btn btn-secondary btn-sm text-white',
                "title": "{{ $page_title }} List",
                "filename": 'Customers Ledger From ' +$('#form-filter #start_date').val() + ' To ' +$('#form-filter #end_date').val(),
                "orientation": "portrait", //portrait
                "pageSize": "A4", //A3,A5,A6,legal,letter
                "exportOptions": {
                    columns: function (index, data, node) {
                        return table.column(index).visible();
                    }
                },
                footer:true,
                customize: function(doc) {
                    doc.defaultStyle.fontSize = 7; //<-- set fontsize to 16 instead of 10 
                    doc.styles.tableHeader.fontSize = 7;
                    doc.styles.tableFooter.fontSize = 7;
                    doc.pageMargins = [5,5,5,5];
                }
            },
        ],
        "footerCallback": function ( row, data, start, end, display ) {
            var api = this.api(), data;

            // Remove the formatting to get integer data for summation
            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    i.replace(/[\$,]/g, '')*1 :
                    typeof i === 'number' ?
                        i : 0;
            };
            var debit=0;
            var credit=0;
            var balance = 0;
            var currency_symbol = "{{ config('settings.currency_symbol') }}";
            var currency_position = "{{ config('settings.currency_position') }}";
            // Total over all pages
            for (let index = 3; index <= 4; index++) {
                // Total over this page
                pageTotal = api
                    .column( index, { page: 'current'} )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );
                    if(index == 3){
                        debit = pageTotal;
                    }else{
                        credit = pageTotal;
                    }
                    
                    
                
                var total = (currency_position == 1) ? currency_symbol+' '+number_format(pageTotal) : number_format(pageTotal)+' '+currency_symbol;
                // Update footer
                $(api.column( index ).footer()).html('= '+total+' Tk');
            }
            balance = (currency_position == 1) ? currency_symbol+' '+number_format((debit - credit)) : number_format((debit - credit))+' '+currency_symbol;
            $(api.column(5).footer()).html('= '+balance+' Tk');
        }
    });

    $('#btn-filter').click(function () {
        table.ajax.reload();
    });

    $('#btn-reset').click(function () {
        $('#form-filter')[0].reset();
        $('#form-filter .selectpicker').selectpicker('refresh');
        $('#form-filter #start_date').val('');
        $('#form-filter #end_date').val('');
        table.ajax.reload();
        customer_list();
    });
});
customer_list();
function customer_list()
{
    let district_id = document.getElementById('district_id').value;
    let upazila_id = document.getElementById('upazila_id').value;
    let route_id = document.getElementById('route_id').value;
    let area_id = document.getElementById('area_id').value;
    $.ajax({
        url:"{{ url('customer-list') }}",
        type:"POST",
        data:{district_id:district_id,upazila_id:upazila_id,route_id:route_id,area_id:area_id,_token:_token},
        dataType:"JSON",
        success:function(data){
            html = `<option value="">Select Please</option>`;
            $.each(data, function(key, value) {
                html += `<option value="${value.id}">${value.name} - ${value.mobile} (${value.shop_name})</option>`;
            });
            $('#form-filter #customer_id').empty().append(html);
            $('#form-filter #customer_id.selectpicker').selectpicker('refresh');
      
        },
    });

}
function getUpazilaList(district_id,selector,upazila_id=''){
    $.ajax({
        url:"{{ url('district-id-wise-upazila-list') }}/"+district_id,
        type:"GET",
        dataType:"JSON",
        success:function(data){
            html = `<option value="">Select Please</option>`;
            $.each(data, function(key, value) {
                html += '<option value="'+ key +'">'+ value +'</option>';
            });
            if(selector == 1)
            {
                $('#form-filter #upazila_id').empty();
                $('#form-filter #upazila_id').append(html);
            }else{
                $('#store_or_update_form #upazila_id').empty();
                $('#store_or_update_form #upazila_id').append(html);
            }
            $('.selectpicker').selectpicker('refresh');
            if(upazila_id){
                $('#store_or_update_form #upazila_id').val(upazila_id);
                $('#store_or_update_form #upazila_id.selectpicker').selectpicker('refresh');
            }
      
        },
    });
}
function getRouteList(upazila_id,selector,route_id=''){
    $.ajax({
        url:"{{ url('upazila-id-wise-route-list') }}/"+upazila_id,
        type:"GET",
        dataType:"JSON",
        success:function(data){
            html = `<option value="">Select Please</option>`;
            $.each(data, function(key, value) {
                html += '<option value="'+ key +'">'+ value +'</option>';
            });
            if(selector == 1)
            {
                $('#form-filter #route_id').empty();
                $('#form-filter #route_id').append(html);
            }else{
                $('#store_or_update_form #route_id').empty();
                $('#store_or_update_form #route_id').append(html);
            }
            $('.selectpicker').selectpicker('refresh');
            if(route_id){
                $('#store_or_update_form #route_id').val(route_id);
                $('#store_or_update_form #route_id.selectpicker').selectpicker('refresh');
            }
      
        },
    });
}
function getAreaList(route_id,selector,area_id=''){
    $.ajax({
        url:"{{ url('route-id-wise-area-list') }}/"+route_id,
        type:"GET",
        dataType:"JSON",
        success:function(data){
            html = `<option value="">Select Please</option>`;
            $.each(data, function(key, value) {
                html += '<option value="'+ key +'">'+ value +'</option>';
            });
            if(selector == 1)
            {
                $('#form-filter #area_id').empty();
                $('#form-filter #area_id').append(html);
            }else{
                $('#store_or_update_form #area_id').empty();
                $('#store_or_update_form #area_id').append(html);
            }
            $('.selectpicker').selectpicker('refresh');
            if(area_id){
                $('#store_or_update_form #area_id').val(area_id);
                $('#store_or_update_form #area_id.selectpicker').selectpicker('refresh');
            }
      
        },
    });
}
</script>
@endpush
