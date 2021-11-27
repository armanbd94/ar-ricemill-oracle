<div class="row">
    <x-form.textbox labelName="Emergency Contact Name" name="emergency_contact_name" value="{{ isset($employee) ? $employee->emergency_contact_name : '' }}"  col="col-md-3" required="required" />
    <x-form.textbox labelName="Emergency Contact Phone" name="emergency_contact_phone" value="{{ isset($employee) ? $employee->emergency_contact_phone : '' }}"  col="col-md-3" required="required" />
    <x-form.textbox labelName="Emergency Contact Relation" name="emergency_contact_relation" value="{{ isset($employee) ? $employee->emergency_contact_relation : '' }}"  col="col-md-3" required="required" />
    <x-form.textbox labelName="Emergency Contact Address" name="emergency_contact_address" value="{{ isset($employee) ? $employee->emergency_contact_address : '' }}"  col="col-md-3" required="required" />
    <x-form.textbox labelName="Alt. Emergency Contact Name" name="alternative_emergency_contact_name" value="{{ isset($employee) ? $employee->alternative_emergency_contact_name : '' }}"  col="col-md-3"  />
    <x-form.textbox labelName="Alt. Emergency Contact Phone" name="alternative_emergency_contact_phone"  value="{{ isset($employee) ? $employee->alternative_emergency_contact_phone : '' }}" col="col-md-3"  />
    <x-form.textbox labelName="Alt. Emergency Contact Relation" name="alternative_emergency_contact_relation" value="{{ isset($employee) ? $employee->alternative_emergency_contact_relation : '' }}"  col="col-md-3"  />
    <x-form.textbox labelName="Alt. Emergency Contact Address" name="alternative_emergency_contact_address" value="{{ isset($employee) ? $employee->alternative_emergency_contact_address : '' }}"  col="col-md-3"  />
</div>
<div class="d-flex justify-content-between border-top mt-5 pt-10">
    <div class="mr-2">
        <button type="button" class="btn btn-light-primary btn-sm font-weight-bolder text-uppercase"   onclick="show_form(6)">Previous</button>
    </div>
    <div>
        <button type="button"  class="btn btn-primary btn-sm font-weight-bolder text-uppercase" id="save-btn" onclick="store_data()">Submit</button>
    </div>
</div>