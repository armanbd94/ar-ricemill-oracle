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
                    
                    <div class="form-group col-md-12">
                            <label for="start_date">Start Date</label>
                            <input type="text" class="form-control date" name="start_date" id="start_date" readonly />
                    </div>
                    <div class="form-group col-md-12">
                            <label for="end_date">End Date</label>
                            <input type="text" class="form-control date" name="end_date" id="end_date" readonly />
                    </div>
                    <x-form.selectbox labelName="Shift" name="shift_id" required="required" col="col-md-12" class="selectpicker">
                    @if (!$shifts->isEmpty())
                      @foreach ($shifts as $shift)
                      <option value="{{ $shift->id }}" >
                          {{ $shift->name.' ('.date('g:i A',strtotime($shift->start_time)).'-'.date('g:i A',strtotime($shift->end_time)).')' }}
                      </option>
                      @endforeach
                    @endif
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