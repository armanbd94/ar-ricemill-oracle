<div class="modal fade" id="approve_status_modal" tabindex="-1" role="dialog" aria-labelledby="model-1" aria-hidden="true">
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
        <!-- Modal Body -->
        <form id="approve_status_form" method="POST">
            @csrf
            <div class="modal-body">
                <input type="hidden" name="purchase_id" id="purchase_id">
                <div class="row">
                    <div class="form-group col-md-12 required">
                        <label for="">Status</label>
                        <select class="form-control" name="status" id="purchase_status" required>
                            <option value="">Select Please</option>
                            <option value="1">Approve</option>
                            <option value="2">Cancel</option>
                            <option value="3">Pending</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary btn-sm" id="status-btn">Change Status</button>
            </div>
        </form>
        <!-- /modal body -->
      </div>
      <!-- /modal content -->

    </div>
  </div>