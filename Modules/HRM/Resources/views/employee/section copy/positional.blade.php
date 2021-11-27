<div class="row">
    <x-form.selectbox labelName="Branch" name="warehouse_id" required="required" col="col-md-4" class="selectpicker">
        @if (!$warehouses->isEmpty())
        @foreach ($warehouses as $warehouse)
            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
        @endforeach 
        @endif
    </x-form.selectbox>
    <x-form.selectbox labelName="Department" name="department_id" required="required" col="col-md-4" onchange="getDivisionList(this.value)" class="selectpicker">
        @if (!$departments->isEmpty())
        @foreach ($departments as $department)
            <option value="{{ $department->id }}">{{ $department->name }}</option>
        @endforeach 
        @endif
    </x-form.selectbox>
    <x-form.selectbox labelName="Division" name="division_id" required="required" col="col-md-4" class="selectpicker"/>
    <x-form.textbox labelName="Employee ID" name="employee_id"  col="col-md-4" required="required" />
    
    <x-form.selectbox labelName="Current Designation" name="current_designation_id" required="required" col="col-md-4" class="selectpicker">
        @if (!$designations->isEmpty())
        @foreach ($designations as $designation)
            <option value="{{ $designation->id }}">{{ $designation->name }}</option>
        @endforeach 
        @endif
    </x-form.selectbox>
    <x-form.selectbox labelName="Joining Designation" name="joining_designation_id" col="col-md-4" class="selectpicker">
        @if (!$designations->isEmpty())
        @foreach ($designations as $designation)
            <option value="{{ $designation->id }}">{{ $designation->name }}</option>
        @endforeach 
        @endif
    </x-form.selectbox>
    <x-form.selectbox labelName="Job Status" name="job_status" required="required" col="col-md-4" class="selectpicker">
        @foreach (JOB_STATUS as $key => $value)
            <option value="{{ $key }}">{{ $value }}</option>
        @endforeach 
    </x-form.selectbox>
    <x-form.textbox labelName="Joining Date" name="joining_date" class="date"  col="col-md-4" />
    <x-form.textbox labelName="Probation Start Date" name="probation_start" class="date"  col="col-md-4" />
    <x-form.textbox labelName="Probation End Date" name="probation_end" class="date"  col="col-md-4" />
    <x-form.textbox labelName="Confirmation Date" name="confirmation_date" class="date"  col="col-md-4" />
    <x-form.selectbox labelName="Duty Type" name="duty_type" required="required" col="col-md-4" class="selectpicker">
        @foreach (DUTY_TYPE as $key => $value)
            <option value="{{ $key }}">{{ $value }}</option>
        @endforeach 
    </x-form.selectbox>
    <div class="form-group col-md-4">
        <label for="contract_start">Contract Start Date</label>
        <input type="text" class="form-control date" name="contract_start" id="contract_start">
    </div>
    <div class="form-group col-md-4">
        <label for="contract_end">Contract End Date</label>
        <input type="text" class="form-control date" name="contract_end" id="contract_end">
    </div>
    <x-form.selectbox labelName="Rate Type" name="rate_type" required="required" col="col-md-4" class="selectpicker">
        @foreach (RATE_TYPE as $key => $value)
            <option value="{{ $key }}">{{ $value }}</option>
        @endforeach 
    </x-form.selectbox>
    <x-form.textbox labelName="Rate" name="rate"  col="col-md-4" required="required" />
    <x-form.textbox labelName="Joining Rate" name="joining_rate"  col="col-md-4" />
    <x-form.selectbox labelName="Overtime" name="overtime" required="required" col="col-md-4" class="selectpicker">
        @foreach (OVERTIME as $key => $value)
            <option value="{{ $key }}">{{ $value }}</option>
        @endforeach 
    </x-form.selectbox>
    <x-form.selectbox labelName="Pay Frequency" name="pay_freequency" required="required" col="col-md-4" class="selectpicker">
        @foreach (PAY_FREQUENCY as $key => $value)
            <option value="{{ $key }}">{{ $value }}</option>
        @endforeach 
    </x-form.selectbox>
    <x-form.textbox labelName="Bank Name" name="bank_name"  col="col-md-4" />
    <x-form.textbox labelName="Bank Account No." name="account_no"  col="col-md-4" />
    <x-form.textbox labelName="Termination Date" name="termination_date" class="date"  col="col-md-4" />
    <x-form.textarea labelName="Termination Reason" name="termination_reason"  col="col-md-8" />
</div>
<div class="d-flex justify-content-between border-top mt-5 pt-10">
    <div class="mr-2">
        <button type="button" class="btn btn-light-primary btn-sm font-weight-bolder text-uppercase"   onclick="show_form(1)">Previous</button>
    </div>
    <div>
        <button type="button"  class="btn btn-primary btn-sm font-weight-bolder text-uppercase" data-wizard-type="action-next" onclick="show_form(3)">Next</button>
    </div>
</div>