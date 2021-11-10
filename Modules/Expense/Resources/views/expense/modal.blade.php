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
                    <div class="form-group col-md-12 required">
                      <label for="date">Date</label>
                      <input type="text" class="form-control date" name="date" id="date" value="{{ date('Y-m-d') }}" readonly />
                  </div>

                  <x-form.selectbox labelName="Expense Type" name="expense_item_id" col="col-md-12" required="required" class="selectpicker">
                    @if (!$expense_items->isEmpty())
                        @foreach ($expense_items as $value)
                        <option value="{{ $value->id }}">{{ $value->name }}</option>
                        @endforeach
                    @endif
                  </x-form.selectbox>
                  <x-form.selectbox labelName="Payment Type" name="payment_type" required="required" onchange="account_list(this.value)"  col="col-md-12" class="selectpicker">
                      @foreach (SALE_PAYMENT_METHOD as $key => $value)
                      <option value="{{ $key }}">{{ $value }}</option>
                      @endforeach
                  </x-form.selectbox>
                  <x-form.selectbox labelName="Account" name="account_id" required="required"  col="col-md-12" class="selectpicker"/>
                  <div class="form-group col-md-12 required">
                    <label for="amount">Amount</label>
                    <input type="text" class="form-control" name="amount" id="amount">
                  </div>
                  <div class="form-group col-md-12">
                    <label for="remarks">Remarks</label>
                    <textarea  class="form-control" name="remarks" id="remarks" cols="30" rows="3"></textarea>
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