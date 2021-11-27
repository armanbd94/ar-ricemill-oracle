<div class="row">
    <x-form.textbox labelName="Name" name="name" required="required" value="{{ isset($employee) ? $employee->name : '' }}" col="col-md-4"/>
    <input type="hidden" name="old_name" id="old_name" value="{{ isset($employee) ? $employee->name : '' }}">
    <input type="hidden" name="old_wallet_number" id="old_wallet_number" value="{{ isset($employee) ? $employee->wallet_number : '' }}">
    <x-form.textbox type="email" labelName="Email" name="email" value="{{ isset($employee) ? $employee->email : '' }}" col="col-md-4"/>
    <x-form.textbox labelName="Phone" name="phone" value="{{ isset($employee) ? $employee->phone : '' }}" required="required" col="col-md-4"/>
    <x-form.textbox labelName="Alternative Phone" name="alternative_phone" value="{{ isset($employee) ? $employee->alternative_phone : '' }}" col="col-md-4"/>
   
    <x-form.textbox labelName="City" name="city" value="{{ isset($employee) ? $employee->city : '' }}" col="col-md-4"/>
   
    <x-form.textbox labelName="Zipcode" name="zipcode" value="{{ isset($employee) ? $employee->zipcode : '' }}"  col="col-md-4"/>
    <x-form.textbox labelName="Address" name="address" value="{{ isset($employee) ? $employee->address : '' }}"  col="col-md-8" required="required" />
</div>
<div class="d-flex justify-content-end border-top mt-5 pt-10">
    <div><button type="button"  class="btn btn-primary btn-sm font-weight-bolder text-uppercase" data-wizard-type="action-next" onclick="show_form(2)">Next</button></div>
</div>