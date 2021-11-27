<div class="row">
    <x-form.selectbox labelName="Supervisor Name" name="supervisor_id" required="required" col="col-md-4" class="selectpicker">
        <option value="0">Self</option>
        @if (!$employees->isEmpty())
        @foreach ($employees as $employee)
            <option value="{{ $employee->id }}">{{ $employee->name.' - '.$employee->employee_id }}</option>
        @endforeach 
        @endif
    </x-form.selectbox>
    <x-form.selectbox labelName="Is Supervisor" name="is_supervisor" required="required" col="col-md-4" class="selectpicker">
        @foreach (IS_SUPERVISOR as $key => $value)
            <option value="{{ $key }}">{{ $value }}</option>
        @endforeach 
    </x-form.selectbox>
</div>
<div class="d-flex justify-content-between border-top mt-5 pt-10">
    <div class="mr-2">
        <button type="button" class="btn btn-light-primary btn-sm font-weight-bolder text-uppercase"   onclick="show_form(2)">Previous</button>
    </div>
    <div>
        <button type="button"  class="btn btn-primary btn-sm font-weight-bolder text-uppercase" data-wizard-type="action-next" onclick="show_form(4)">Next</button>
    </div>
</div>