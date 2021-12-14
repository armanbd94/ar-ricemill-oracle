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
                    <a href="{{ route('bom.re.process') }}" class="btn btn-warning btn-sm font-weight-bolder custom-btn">
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
                                <div class="col-md-8"><b>:</b> {{ date('d-M-Y',strtotime($data->packing_date)) }}</div>
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
                                <div class="col-md-4"><b>Converted Item</b></div>
                                <div class="col-md-8"><b>:</b> {{ $data->to_product->name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4"><b>Memo No.</b></div>
                                <div class="col-md-8"><b>:</b> {{ $data->memo_no }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4"><b>Number</b></div>
                                <div class="col-md-8"><b>:</b> {{ $data->packing_number }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4"><b>Bag Inventory Site</b></div>
                                <div class="col-md-8"><b>:</b> {{ $data->bag_site->name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4"><b>Bag Inventory Location</b></div>
                                <div class="col-md-8"><b>:</b> {{ $data->bag_location->name }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="row pt-5">
                        <div class="col-md-12 table-responsive">
                            <table class="table table-bordered">
                                <thead class="bg-primary">
                                    <th>From Site</th>
                                    <th>From Location</th>
                                    <th>Item</th>
                                    <th>Description</th>
                                    <th class="text-center">Converted Qty</th>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{{ $data->from_site->name }}</td>
                                        <td>{{ $data->from_location->name }}</td>
                                        <td>{{ $data->from_product->name }}</td>
                                        <td>{{ $data->product_description }}</td>
                                        <td class="text-center">{{ $data->product_qty }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ $data->bag_site->name }}</td>
                                        <td>{{ $data->bag_location->name }}</td>
                                        <td>{{ $data->bag->material_name }}</td>
                                        <td>{{ $data->bag_description }}</td>
                                        <td class="text-center">{{ $data->bag_qty }}</td>
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

