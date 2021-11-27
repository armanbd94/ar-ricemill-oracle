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
                    <div class="form-group col-md-12">
                            <label for="date">Date</label>
                            <input type="text" class="form-control date" value="{{date('Y-m-d')}}" name="date" id="date" readonly/>
                    </div>
                    <x-form.selectbox labelName="Employee Name" name="emp_id" onchange="getEmployeeDetails(this.value)" required="required" col="col-md-12" class="selectpicker">
                        @if (!$employees->isEmpty())
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                        @endif
                    </x-form.selectbox>
                    <!-- <div class="form-group col-md-12">
                            <label for="wallet_number">Wallet Number</label>
                            <input type="text" class="form-control" name="wallet_number" id="wallet_number" readonly />
                    </div>
                    <x-form.selectbox labelName="Route Name" name="employee_route_id" required="required" col="col-md-12" class="selectpicker">
                        @if (!$employees_route->isEmpty())
                        @foreach ($employees_route as $route)
                            <option value="{{ $route->id }}">{{ $route->name }}</option>
                        @endforeach
                        @endif
                    </x-form.selectbox> -->
                    <div class="form-group col-md-12 required">
                            <label for="start_time">Start Time</label>
                            <input type="text" class="form-control timepicker" name="start_time" id="start_time" readonly />
                    </div>
                    <div class="form-group col-md-12 required">
                            <label for="end_time">End Time</label>
                            <input type="text" class="form-control timepicker" name="end_time" id="end_time" readonly />
                    </div>
                    <!-- <x-form.selectbox labelName="Deletable" name="deletable" required="required" col="col-md-12" class="selectpicker">
                        @foreach ($deletable as $key => $item)
                            <option value="{{ $key }}">{{ $item }}</option>
                        @endforeach
                    </x-form.selectbox> -->
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