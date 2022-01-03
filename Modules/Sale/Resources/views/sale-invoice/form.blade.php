@extends('layouts.app')

@section('title', $page_title)

@section('content')
<div class="d-flex flex-column-fluid">
    <div class="container-fluid">
        <div class="card card-custom card-border">
            <div class="card-header bg-primary">
                <div class="card-title">
                    <h3 class="card-label text-white">Sale Order Memo</h3>
                </div>
            </div>
            <div class="card-body">
                <form class="form-inline col-md-12 justify-content-center" action="{{ url('sale/invoice/create') }}" method="get">
                    <div class="form-group col-md-6">
                        <input type="text" name="memo_no"  class="form-control w-100" id="memo_no" placeholder="Enter Sale Order Memo No" required="required">
                    </div>
                    <button type="submit" class="btn btn-primary btn-md">Serach</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
