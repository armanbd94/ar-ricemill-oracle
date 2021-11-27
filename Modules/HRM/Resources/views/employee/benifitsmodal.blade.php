<div class="modal fade" id="store_or_update_modal_benifit" tabindex="-1" role="dialog" aria-labelledby="model-1" aria-hidden="true">
    <div class="modal-dialog" role="document">

        <!-- Modal Content -->
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header bg-primary">
                <h3 class="modal-title text-white" id="model-1"></h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i aria-hidden="true" class="ki ki-close text-white"></i>
                </button>
            </div>
            <!-- /modal header -->
            <form id="store_or_update_form" method="post">
                @csrf
                <!-- Modal Body -->
                <div class="modal-body">
                    <div class="row">

                        <div class="form-group col-md-6">
                            <label for="start_date"><b style="font-size:15px">Allowances</b></label>
                            <label for="end_date">End Date</label>
                            <input type="text" class="form-control date" name="end_date" id="end_date" readonly />
                            @if (!$allowances->isEmpty())
                            @foreach ($allowances as $allowance)
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input benifits_checked" value="{{ $allowance->id }}" id="allowance_{{ $allowance->id }}">
                                <label class="custom-control-label" for="allowance_{{ $allowance->id }}">{{ $allowance->name }}</label>
                            </div>
                            @endforeach
                            @endif
                        </div>
                        <div class="form-group col-md-6">
                            <label for="end_date"><b style="font-size:15px">Deductions</b></label>
                            <label for="end_date">End Date</label>
                            <input type="text" class="form-control date" name="end_date" id="end_date" readonly />
                            @if (!$deducts->isEmpty())
                            @foreach ($deducts as $deduct)
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input benifits_checked" value="{{ $deduct->id }}" id="deduct_{{ $deduct->id }}">
                                <label class="custom-control-label" for="deduct_{{ $deduct->id }}">{{ $deduct->name }}</label>
                            </div>
                            @endforeach
                            @endif
                        </div>
                    </div>
                </div>
                <!-- /modal body -->

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary btn-sm" id="save-btn-benifit">Save</button>
                </div>
                <!-- /modal footer -->
            </form>
        </div>
        <!-- /modal content -->

    </div>
</div>