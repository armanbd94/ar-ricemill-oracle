<div class="row">
    <x-form.textbox labelName="Father Name" name="father_name" value="{{ isset($employee) ? $employee->father_name : '' }}"  col="col-md-4" required="required" />
    <x-form.textbox labelName="Mother Name" name="mother_name" value="{{ isset($employee) ? $employee->mother_name : '' }}"  col="col-md-4" required="required" />
    <x-form.textbox labelName="Date of Birth" name="dob" value="{{ isset($employee) ? $employee->dob : '' }}"  col="col-md-4" class="date" required="required" />
    <x-form.selectbox labelName="Gender" name="gender" required="required" col="col-md-4" class="selectpicker">
        @foreach (GENDER as $key => $value)
            <option value="{{ $key }}" @if(isset($employee)) {{ ($employee->gender == $key) ? 'selected' : '' }} @endif>{{ $value }}</option>
        @endforeach 
    </x-form.selectbox>
    <x-form.selectbox labelName="Marital Status" name="marital_status" required="required" col="col-md-4" class="selectpicker">
        @foreach (MARITAL_STATUS as $key => $value)
            <option value="{{ $key }}"  @if(isset($employee)) {{ ($employee->marital_status == $key) ? 'selected' : '' }} @endif>{{ $value }}</option>
        @endforeach 
    </x-form.selectbox>
    <x-form.selectbox labelName="Blood Group" name="blood_group" col="col-md-4" class="selectpicker">
        @foreach (BLOOD_GROUP as $key => $value)
            <option value="{{ $key }}" @if(isset($employee)) {{ ($employee->blood_group == $key) ? 'selected' : '' }} @endif>{{ $value }}</option>
        @endforeach 
    </x-form.selectbox>
    <x-form.selectbox labelName="Residential Status" name="residential_status" required="required" col="col-md-4" class="selectpicker">
        @foreach (RESIDENTIAL_STATUS as $key => $value)
            <option value="{{ $key }}" @if(isset($employee)) {{ ($employee->residential_status == $key) ? 'selected' : '' }} @endif>{{ $value }}</option>
        @endforeach 
    </x-form.selectbox>
    <x-form.textbox labelName="Religion" name="religion" value="{{ isset($employee) ? $employee->religion : '' }}"  col="col-md-4" required="required" />
    <x-form.textbox labelName="NID No." name="nid_no" value="{{ isset($employee) ? $employee->nid_no : '' }}"  col="col-md-4" required="required" />
    <div class="col-md-6">
        <div class="row">
            <div class="form-group col-md-12">
                <label for="">NID Photo</label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" name="nid_photo" id="nid_photo" onchange="loadFile(event,'show_nid_photo')" required>
                    <label class="custom-file-label" for="nid_photo">Choose file...</label>
                </div>
            </div>
            <div class="form-group col-md-12">
                @if(isset($employee) &&  !empty($employee->nid_photo)) 
                    <img src="{{ asset('storage/'.EMPLOYEE_NID_PHOTO.$employee->nid_photo) }}" class="show-image" id="show_nid_photo" style="width: 150px;">
                @else 
                <img src="images/default.svg" class="show-image" id="show_nid_photo" style="width: 150px;">
                @endif
                @if(isset($employee) &&  !empty($employee->nid_photo)) 
                <input type="hidden" name="old_nid_photo" id="old_nid_photo" value="{{ $employee->nid_photo }}">
                @else
                <input type="hidden" name="old_nid_photo" id="old_nid_photo" >
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="row">
            <div class="form-group col-md-12">
                <label for="">Employee Photograph</label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" name="photograph" id="photograph" onchange="loadFile(event,'show_photograph')" required>
                    <label class="custom-file-label" for="photograph">Choose file...</label>
                </div>
            </div>
            <div class="form-group col-md-12">
                @if(isset($employee) &&  !empty($employee->photograph)) 
                    <img src="{{ asset('storage/'.EMPLOYEE_IMAGE_PATH.$employee->photograph) }}" class="show-image" id="show_photograph" style="width: 150px;">
                @else 
                    <img src="images/default.svg" class="show-image" id="show_photograph" style="width: 150px;">
                @endif
                @if(isset($employee) &&  !empty($employee->photograph)) 
                <input type="hidden" name="old_photograph" id="old_photograph" value="{{ $employee->photograph }}">
                @else
                <input type="hidden" name="old_photograph" id="old_photograph" >
                @endif
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between border-top mt-5 pt-10">
    <div class="mr-2">
        <button type="button" class="btn btn-light-primary btn-sm font-weight-bolder text-uppercase"   onclick="show_form(3)">Previous</button>
    </div>
    <div>
        <button type="button"  class="btn btn-primary btn-sm font-weight-bolder text-uppercase" data-wizard-type="action-next" onclick="show_form(5)">Next</button>
    </div>
</div>