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
                    <input type="hidden" value="2" name="holiday_type" id="holiday_type"/>
                    <x-form.textbox labelName="Holiday Name" name="name" required="required" col="col-md-12" placeholder="Enter name"/>
                    <x-form.textbox labelName="Short Name" name="short_name" required="required" col="col-md-12" placeholder="Enter Short name"/>
                    
                    <div class="form-group col-md-12">
                            <label for="start_date">Start Date</label>
                            <input type="text" class="form-control date" name="start_date" id="start_date" readonly />
                    </div>
                    <div class="form-group col-md-12">
                            <label for="end_date">End Date</label>
                            <input type="text" class="form-control date" name="end_date" id="end_date" readonly />
                    </div>
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