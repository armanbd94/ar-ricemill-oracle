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
                    <a href="{{ route('build.disassembly') }}" class="btn btn-warning btn-sm font-weight-bolder custom-btn">
                        <i class="fas fa-arrow-left"></i> Back</a>
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom">
            <div class="card-body">
                <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <div class="row pb-5">
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4"><b>Date</b></div>
                                <div class="col-md-8"><b>:</b> {{ date('d-M-Y',strtotime($data->build_date)) }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4"><b>WIP Batch</b></div>
                                <div class="col-md-8"><b>:</b> {{ $data->batch->batch_no }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4"><b>From Site</b></div>
                                <div class="col-md-8"><b>:</b> {{ $data->from_site->name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4"><b>From Location</b></div>
                                <div class="col-md-8"><b>:</b> {{ $data->from_location->name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4"><b>To Site</b></div>
                                <div class="col-md-8"><b>:</b> {{ $data->to_site->name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4"><b>To Location</b></div>
                                <div class="col-md-8"><b>:</b> {{ $data->to_location->name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4"><b>Material Item</b></div>
                                <div class="col-md-8"><b>:</b> {{ $data->material->material_name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4"><b>Converted Item</b></div>
                                <div class="col-md-8"><b>:</b> {{ $data->product->name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4"><b>Class</b></div>
                                <div class="col-md-8"><b>:</b> {{ $data->item_class->name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4"><b>Approx. Main Product Ratio</b></div>
                                <div class="col-md-8"><b>:</b> {{ $data->build_ratio }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4"><b>Quantity To Build (KGs)</b></div>
                                <div class="col-md-8"><b>:</b> {{ number_format($data->build_qty,2,'.',',') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4"><b>RM Needed (KGs)</b></div>
                                <div class="col-md-8"><b>:</b> {{ number_format($data->required_qty,2,'.',',') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4"><b>Memo No.</b></div>
                                <div class="col-md-8"><b>:</b> {{ $data->memo_no }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="row pt-5">
                        <div class="col-md-12 table-responsive pt-5">
                            <table class="table table-bordered">
                                <thead class="bg-dark">
                                    <th></th>
                                    <th class="text-center">Fine Rice</th>
                                    @if (!$data->by_products->isEmpty())
                                        @foreach($data->by_products as $by_product)
                                        <th class="text-center">{{ $by_product->name }}</th>
                                        @endforeach
                                    @endif
                                    <th class="text-center">Total Milling</th>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="font-weight-bold">Converted Quantity</td>
                                        <td class="text-right">{{ number_format($data->converted_qty,2,'.',',') }}</td>
                                        @if (!$data->by_products->isEmpty())
                                            @foreach ($data->by_products as $by_product)
                                                <td class="text-right">{{ number_format($by_product->pivot->qty,2,'.',',') }}</td>
                                            @endforeach
                                        @endif
                                        <td class="text-right">{{ number_format($data->total_milling_qty,2,'.',',') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Convertion Ratio(%)</td>
                                        <td class="text-right">{{ $data->convertion_ratio }}</td>
                                        @if (!$data->by_products->isEmpty())
                                            @foreach ($data->by_products as $by_product)
                                                <td class="text-right">{{ $by_product->pivot->ratio }}</td>
                                            @endforeach
                                        @endif
                                        <td class="text-right">{{ $data->total_milling_ratio }}</td>
                                    </tr>
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Card-->
    </div>
</div>

@endsection

