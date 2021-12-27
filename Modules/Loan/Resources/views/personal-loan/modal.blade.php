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
                    <input type="hidden" name="loan_type" id="loan_type" value='1'/>
                    <input type="hidden" class="form-control" name="voucher_no" id="voucher_no" value="{{ $voucher_no }}" readonly />
                    <x-form.selectbox labelName="Name" name="person_id" onchange="getPersonDetails(this.value)" required="required" col="col-md-12" class="selectpicker">
                        @if (!$person_employees->isEmpty())
                        @foreach ($person_employees as $pemployees)
                            <option value="{{ $pemployees->id }}">{{ $pemployees->name.'-'.$pemployees->phone }}</option>
                        @endforeach
                        @endif
                    </x-form.selectbox>
                    <div class="form-group col-md-12">
                            <label for="date">Date</label>
                            <input type="text" class="form-control date" value="{{date('Y-m-d')}}" name="adjusted_date" id="adjusted_date" readonly/>
                    </div>
                    <x-form.textbox labelName="Amount" name="amount" required="required" col="col-md-12" placeholder="Enter Amount"/>
                    <x-form.textbox labelName="Adjust Amount" name="adjust_amount" required="required" col="col-md-12" placeholder="Enter Adjust Amount"/>
                    
                    <x-form.selectbox labelName="Payment Method" name="payment_method" onchange="account_list(this.value)" required="required"  col="col-md-12" class="selectpicker">
                        @foreach (PAYMENT_METHOD as $key => $value)
                        <option value="{{ $key }}" >{{ $value }}</option>
                        @endforeach
                    </x-form.selectbox>
                    <x-form.selectbox labelName="Account" name="account_id" required="required"  col="col-md-12" class="selectpicker"/>
                    
                    <div class="form-group col-md-12">
                        <label for="shipping_cost">Note</label>
                        <textarea  class="form-control" name="purpose" id="purpose" cols="30" rows="3"></textarea>
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