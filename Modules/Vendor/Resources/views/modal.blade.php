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
                    <input type="hidden" name="old_trade_name" id="old_trade_name"/>
                    <x-form.textbox labelName="Vendor Name" name="name" required="required" col="col-md-12" placeholder="Enter vendor name"/>
                    <x-form.textbox labelName="Trade Name" name="trade_name" required="required" col="col-md-12" placeholder="Enter trade name"/>
                    <x-form.textbox labelName="Mobile" name="mobile" required="required" col="col-md-12" placeholder="Enter mobile number"/>
                    <x-form.textbox labelName="Email" name="email" type="email" col="col-md-12" placeholder="Enter email address"/>
                    <x-form.textbox labelName="Previous Balance" name="previous_balance" col="col-md-12 pbalance d-none" class="text-right" placeholder="Enter previous balalnce"/>
                    <x-form.textarea labelName="Vendor Address" name="address" col="col-md-12" placeholder="Enter vendor address"/>
                    
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