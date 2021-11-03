<div class="modal fade" id="store_or_update_modal" tabindex="-1" role="dialog" aria-labelledby="model-1" aria-hidden="true">
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
                    <input type="hidden" name="update_id" id="update_id"/>
                    <x-form.selectbox labelName="Vendor Name" name="vendor" required="required"  col="col-md-12" class="selectpicker">
                        @if (!$vendors->isEmpty())
                        @foreach ($vendors as $vendor)
                            <option value="{{ $vendor->id }}" data-coaid="{{ $vendor->coa->id }}" data-name="{{ $vendor->name }}">{{ $vendor->name.' - '.$vendor->mobile }}</option>
                        @endforeach
                        @endif
                    </x-form.selectbox>
                    <x-form.selectbox labelName="Advance Type" name="type" required="required"  col="col-md-12" class="selectpicker">
                        <option value="debit">Payment</option>
                        <option value="credit">Receive</option>
                    </x-form.selectbox>
                    <x-form.textbox labelName="Amount" name="amount" required="required" col="col-md-12" placeholder="Enter amount"/>
                    <x-form.selectbox labelName="Payment Method" name="payment_method" required="required"  col="col-md-12" class="selectpicker">
                      @foreach (PAYMENT_METHOD as $key => $value)
                      <option value="{{ $key }}">{{ $value }}</option>
                      @endforeach
                    </x-form.selectbox>
                    <x-form.selectbox labelName="Account" name="account_id" required="required"  col="col-md-12" class="selectpicker"/>
                    <div class="form-group col-md-12 d-none cheque_number required">
                        <label for="cheque_number">Cheque No.</label>
                        <input type="text" class="form-control" name="cheque_number" id="cheque_number">
                    </div>
                </div>
            </div>
            <!-- /modal body -->

            <!-- Modal Footer -->
            <div class="modal-footer">
            <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary btn-sm" id="save-btn"></button>
            </div>
            <!-- /modal footer -->
        </form>
      </div>
      <!-- /modal content -->

    </div>
  </div>