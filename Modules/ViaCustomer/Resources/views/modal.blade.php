<div class="modal fade" id="store_or_update_modal" tabindex="-1" role="dialog" aria-labelledby="model-1" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">

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
                    <x-form.selectbox labelName="Customer" name="customer_id"  required="required" col="col-md-12" class="selectpicker">
                      @if (!$customers->isEmpty())
                                @foreach ($customers as $value)
                                    <option value="{{ $value->id }}">{{ $value->trade_name.' - '.$value->mobile.')' }}</option>
                                @endforeach
                            @endif
                    </x-form.selectbox>
                    <x-form.textbox labelName="Via Customer Name" name="name" required="required" col="col-md-12" placeholder="Enter via customer name"/>
                    <x-form.textbox labelName="Trade Name" name="trade_name" col="col-md-12" placeholder="Enter trade name"/>
                    <x-form.textbox labelName="Mobile" name="mobile" required="required" col="col-md-12" placeholder="Enter mobile number"/>
                    <x-form.textbox labelName="Email" name="email" type="email" col="col-md-12" placeholder="Enter email address"/>
                    <x-form.textarea labelName="Address" name="address" col="col-md-12" placeholder="Enter address"/>
                    
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