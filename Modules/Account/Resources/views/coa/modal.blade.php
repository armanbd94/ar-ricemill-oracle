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
  
                    <x-form.selectbox labelName="Parent Head" name="parent_name" onchange="fetch_parent_data(this.value)" required="required" col="col-md-12" class="selectpicker">
                        @if (!$accounts->isEmpty())
                            @foreach ($accounts as $value)
                                <option value="{{ $value->id }}">{{ $value->name }}</option>
                            @endforeach
                        @endif
                    </x-form.selectbox>
                    <x-form.textbox labelName="Head Code" name="code" required="required" col="col-md-12"/>
                    <x-form.textbox labelName="Head Name" name="name" required="required" col="col-md-12"/>
                    <x-form.textbox labelName="Head Level" name="level" required="required" col="col-md-12"/>
                    <x-form.selectbox labelName="Head Type" name="type" required="required" col="col-md-12" class="selectpicker">
                        <option value="A">A</option>
                        <option value="L">L</option>
                        <option value="I">I</option>
                        <option value="E">E</option>
                    </x-form.selectbox>
                    <div class="form-group col-md-12 d-flex justify-content-between">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="transaction" id="transaction" value="2" onchange="set_checkbox_value('transaction')">
                            <label class="custom-control-label" for="transaction">Is Transaction</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="general_ledger" id="general_ledger" value="2" onchange="set_checkbox_value('general_ledger')">
                            <label class="custom-control-label" for="general_ledger">Is General Ledger</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="status" id="status" value="1" checked onchange="set_checkbox_value('status')">
                            <label class="custom-control-label" for="status">Is Active</label>
                        </div>
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
