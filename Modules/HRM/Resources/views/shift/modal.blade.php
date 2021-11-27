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
                    <x-form.textbox labelName="Shift Name" name="name" required="required" col="col-md-12" placeholder="Enter name"/>
                    
                    <div class="form-group col-md-12">
                            <label for="start_time">Start Time</label>
                            <input type="text" class="form-control timepicker" name="start_time" id="start_time" readonly />
                    </div>
                    <div class="form-group col-md-12">
                            <label for="end_time">End Time</label>
                            <input type="text" class="form-control timepicker" name="end_time" id="end_time" readonly />
                    </div>
                    <x-form.selectbox labelName="Shift Type" name="night_status" required="required" col="col-md-12" class="selectpicker">
                        <option value="1">Day</option>
                        <option value="2">Night</option>
                    </x-form.selectbox>
                    <x-form.selectbox labelName="Deletable" name="deletable" required="required" col="col-md-12" class="selectpicker">
                        @foreach ($deletable as $key => $item)
                            <option value="{{ $key }}">{{ $item }}</option>
                        @endforeach
                    </x-form.selectbox>
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