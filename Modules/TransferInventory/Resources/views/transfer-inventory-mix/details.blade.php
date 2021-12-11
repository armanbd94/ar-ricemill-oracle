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
                    <a href="{{ route('transfer.inventory.mix') }}" class="btn btn-warning btn-sm font-weight-bolder">
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
                                <div class="col-md-4"><b>Transfer Date</b></div>
                                <div class="col-md-8"><b>:</b> {{ date('d-M-Y',strtotime($transfer->transfer_date)) }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4"><b>WIP Batch</b></div>
                                <div class="col-md-8"><b>:</b> {{ $transfer->batch->batch_no }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4"><b>Mix Item</b></div>
                                <div class="col-md-8"><b>:</b> {{ $transfer->product->name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4"><b>Mix Item Class</b></div>
                                <div class="col-md-8"><b>:</b> {{ $transfer->category->name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4"><b>Transfer To</b></div>
                                <div class="col-md-8"><b>:</b> {{ $transfer->to_site->name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4"><b>To Location</b></div>
                                <div class="col-md-8"><b>:</b> {{ $transfer->to_location->name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4"><b>Memo No.</b></div>
                                <div class="col-md-8"><b>:</b> {{ $transfer->memo_no }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4"><b>Number</b></div>
                                <div class="col-md-8"><b>:</b> {{ $transfer->transfer_number }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="row pt-5">
                        <div class="col-md-12">
                            <div style="width: 150px;background: #fa8c15;text-align: center;margin: 0 auto;color: white;padding: 5px 0;box-shadow: 1px 2px 5px 0px rgba(0,0,0,0.5);">
                                <img src="images/supply.png" style="width: 20px;margin-right: 5px;"/>Materials
                            </div>
                        </div>
                        <div class="col-md-12 table-responsive pt-5">
                            <table class="table table-bordered">
                                <thead class="bg-primary">
                                    <th>Item</th>
                                    <th>Transfer From</th>
                                    <th>From Location</th>
                                    <th>Description</th>
                                    <th class="text-center">Class</th>
                                    <th class="text-center">Unit</th>
                                    <th class="text-center">Transfer Qty</th>
                                </thead>
                                <tbody>
                                    @if (!$materials->isEmpty())
                                        @foreach ($materials as $item)
                                            <tr>
                                                <td>{{ $item->material->material_name }}</td>
                                                <td>{{ $item->from_site->name }}</td>
                                                <td>{{ $item->from_location->name }}</td>
                                                <td>{{ $item->description }}</td>
                                                <td class="text-center">{{ $item->material->category->name }}</td>
                                                <td class="text-center">{{ $item->material->unit->unit_name }}</td>
                                                <td class="text-center">{{ $item->qty }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
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

