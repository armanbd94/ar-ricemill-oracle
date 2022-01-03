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
                                <div class="col-md-8"><b>:</b> {{ date('d-M-Y',strtotime($data->process_date)) }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4"><b>WIP Batch</b></div>
                                <div class="col-md-8"><b>:</b> {{ $data->batch->batch_no.' ('.date('d-m-Y',strtotime($data->batch->batch_start_date)).')' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4"><b>Assemble From Site</b></div>
                                <div class="col-md-8"><b>:</b> {{ $data->from_site->name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4"><b>Assemble From Location</b></div>
                                <div class="col-md-8"><b>:</b> {{ $data->from_location->name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4"><b>Assemble To Site</b></div>
                                <div class="col-md-8"><b>:</b> {{ $data->to_site->name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4"><b>Assemble To Location</b></div>
                                <div class="col-md-8"><b>:</b> {{ $data->to_location->name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4"><b>Number</b></div>
                                <div class="col-md-8"><b>:</b> {{ $data->process_number }}</div>
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
                                    <th>Raw Material</th>
                                    <th class="text-center">Particular</th>
                                    <th class="text-center">Per Unit Qty</th>
                                    <th class="text-center">Qty Needed</th>
                                    <th class="text-center">Class</th>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{{ $data->from_product->name }}</td>
                                        <td>{{ $data->product_particular }}</td>
                                        <td class="text-right">{{ $data->product_per_unit_qty }}</td>
                                        <td class="text-right">{{ $data->product_required_qty }}</td>
                                        <td class="text-center">{{ $data->product_class->name }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ $data->bag->material_name }}</td>
                                        <td>{{ $data->bag_particular }}</td>
                                        <td class="text-right">{{ $data->bag_per_unit_qty }}</td>
                                        <td class="text-right">{{ $data->bag_required_qty }}</td>
                                        <td class="text-center">{{ $data->bag_class->name }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-right font-weight-bolder">Fine Rice Quantity to Build</td>
                                        <td class="text-right font-weight-bolder">{{ $data->total_rice_qty }}</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-right font-weight-bolder">Total Bag Used Quantity</td>
                                        <td class="text-right font-weight-bolder">{{ $data->total_bag_qty }}</td>
                                        <td></td>
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

