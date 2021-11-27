@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
<link href="css/pages/wizard/wizard-4.css" rel="stylesheet" type="text/css" />
<style>
    .wizard.wizard-4 .wizard-nav .wizard-steps{
        flex-direction: column;
    }
    .wizard.wizard-4 .wizard-nav .wizard-steps .wizard-step .wizard-wrapper{
        flex: 1;
        display: flex;
        align-items: center;
        flex-wrap: unset !important;
        color: #3F4254;
        padding: 2rem 2.5rem !important;
        height: 100px !important;
    }
    .wizard.wizard-4 .wizard-nav .wizard-steps .wizard-step{
        width: 100% !important;

    }
</style>
@endpush

@section('content')
<div class="d-flex flex-column-fluid">
    <div class="container-fluid">
        <!--begin::Notice-->
        <div class="card card-custom gutter-b">
            <div class="card-header flex-wrap py-5">
                <div class="card-title">
                    <h3 class="card-label"><i class="{{ $page_icon }} text-primary"></i> {{ $sub_title }}</h3>
                </div>
                <div class="card-toolbar">
                    <!--begin::Button-->
                    <a href="{{ route('role') }}" class="btn btn-secondary btn-sm font-weight-bolder">
                        <i class="fas fa-arrow-circle-left"></i> Back
                    </a>
                    <!--end::Button-->
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom">
            <div class="card-body">
                <div class="wizard wizard-4" id="kt_wizard" data-wizard-state="first" data-wizard-clickable="true">
                    <div class="row">
                        <div class="col-md-3">
                            <!--begin: Wizard Nav-->
                            <div class="wizard-nav">
                                <div class="wizard-steps">
                                    <!--begin::Wizard Step 1 Nav-->
                                    <div class="wizard-step step step-1" data-wizard-type="step" data-wizard-state="current">
                                        <div class="wizard-wrapper">
                                            <div class="wizard-number">1</div>
                                            <div class="wizard-label">
                                                <div class="wizard-title">Basic Info</div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Wizard Step 1 Nav-->
                                    <!--begin::Wizard Step 2 Nav-->
                                    <div class="wizard-step step step-2" data-wizard-type="step" data-wizard-state="pending">
                                        <div class="wizard-wrapper">
                                            <div class="wizard-number">2</div>
                                            <div class="wizard-label">
                                                <div class="wizard-title">Positional Information</div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Wizard Step 2 Nav-->

                                    <!--begin::Wizard Step 3 Nav-->
                                    <div class="wizard-step step step-3" data-wizard-type="step" data-wizard-state="pending">
                                        <div class="wizard-wrapper">
                                            <div class="wizard-number">3</div>
                                            <div class="wizard-label">
                                                <div class="wizard-title">Supervisor</div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Wizard Step 3 Nav-->
                                    <!--begin::Wizard Step 4 Nav-->
                                    <div class="wizard-step step step-4" data-wizard-type="step" data-wizard-state="pending">
                                        <div class="wizard-wrapper">
                                            <div class="wizard-number">4</div>
                                            <div class="wizard-label">
                                                <div class="wizard-title">Biographical Information</div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Wizard Step 4 Nav-->
                                    <!--begin::Wizard Step 5 Nav-->
                                    <div class="wizard-step step step-5" data-wizard-type="step" data-wizard-state="pending">
                                        <div class="wizard-wrapper">
                                            <div class="wizard-number">5</div>
                                            <div class="wizard-label">
                                                <div class="wizard-title">Educational Information</div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Wizard Step 5 Nav-->
                                    <!--begin::Wizard Step 6 Nav-->
                                    <div class="wizard-step step step-6" data-wizard-type="step" data-wizard-state="pending">
                                        <div class="wizard-wrapper">
                                            <div class="wizard-number">6</div>
                                            <div class="wizard-label">
                                                <div class="wizard-title">Job Experience</div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Wizard Step 6 Nav-->
                                    <!--begin::Wizard Step 7 Nav-->
                                    <div class="wizard-step step step-7" data-wizard-type="step" data-wizard-state="pending">
                                        <div class="wizard-wrapper">
                                            <div class="wizard-number">7</div>
                                            <div class="wizard-label">
                                                <div class="wizard-title">Emergency Contacts</div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Wizard Step 7 Nav-->
                                </div>
                            </div>
                            <!--end: Wizard Nav-->
                        </div>
                        <div class="col-md-9">
                            <!--begin: Wizard Body-->
                            <div class="card card-custom card-shadowless rounded-top-0">
                                <div class="card-body p-0">
                                    <div class="row justify-content-center py-8 px-8 py-lg-15 px-lg-10">
                                        <div class="col-xl-12 col-xxl-12">
                                            <!--begin: Wizard Form-->
                                            <form class="form mt-0 mt-lg-10 fv-plugins-bootstrap fv-plugins-framework" id="store_or_update_form" method="POST">
                                                @csrf
                                                <input type="hidden" name="update_id" id="update_id">
                                                <!-- begin: Basic Information-->
                                                <div class="pb-5 step step-1" data-wizard-type="step-content" data-wizard-state="current">
                                                    @include('hrm::employee.section.basic')
                                                </div>
                                                <!-- end: Basic Information -->
                                                <!-- begin: Positional Information -->
                                                <div class="pb-5 step step-2" data-wizard-type="step-content">
                                                    @include('hrm::employee.section.positional')
                                                </div>
                                                <!-- end: Positional Information -->

                                                <!-- begin: Supervisor -->
                                                <div class="pb-5 step step-3" data-wizard-type="step-content">
                                                    @include('hrm::employee.section.supervisor')
                                                </div>
                                                <!-- end: Supervisor -->
                                                <!-- begin: Biographical Information -->
                                                <div class="pb-5 step step-4" data-wizard-type="step-content">
                                                    @include('hrm::employee.section.biographical')
                                                </div>
                                                <!-- end: Biographical Information -->
                                                <!-- begin: Educational Information -->
                                                <div class="pb-5 step step-5" data-wizard-type="step-content">
                                                    @include('hrm::employee.section.educational')
                                                </div>
                                                <!-- end: Educational Information -->
                                                <!-- begin: Job Experience -->
                                                <div class="pb-5 step step-6" data-wizard-type="step-content">
                                                    @include('hrm::employee.section.experience')
                                                </div>
                                                <!-- end: Job Experience -->
                                                <!-- begin: Emergency Contacts -->
                                                <div class="pb-5 step  step-7" data-wizard-type="step-content">
                                                    @include('hrm::employee.section.emergency')
                                                </div>
                                                <!-- end: Emergency Contacts -->
                                               
                                            </form>
                                            <!--end: Wizard Form-->
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end: Wizard Bpdy-->
                        </div>
                    </div>
                   
                   
                </div>
            </div>
        </div>
        <!--end::Card-->
    </div>
</div>
@endsection

@push('scripts')
<script src="js/bootstrap-datetimepicker.min.js"></script>
<script>
$(document).ready(function(){
    $('.date').datetimepicker({format: 'YYYY-MM-DD',ignoreReadonly: true});  


});

function store_data(){
    let form = document.getElementById('store_or_update_form');
    let formData = new FormData(form);
    let url = "{{route('employee.store.or.update')}}";
    let id = $('#update_id').val();
    let method;
    if (id) {
        method = 'update';
    } else {
        method = 'add';
    }
    $.ajax({
        url: url,
        type: "POST",
        data: formData,
        dataType: "JSON",
        contentType: false,
        processData: false,
        cache: false,
        beforeSend: function(){
            $('#save-btn').addClass('spinner spinner-white spinner-right');
        },
        complete: function(){
            $('#save-btn').removeClass('spinner spinner-white spinner-right');
        },
        success: function (data) {
            $('#store_or_update_form').find('.is-invalid').removeClass('is-invalid');
            $('#store_or_update_form').find('.error').remove();
            if (data.status == false) {
                $.each(data.errors, function (key, value) {
                    var key = key.split('.').join('_');
                    $('#store_or_update_form input#' + key).addClass('is-invalid');
                    $('#store_or_update_form textarea#' + key).addClass('is-invalid');
                    $('#store_or_update_form select#' + key).parent().addClass('is-invalid');
                    $('#store_or_update_form #' + key).parent().append(
                    '<small class="error text-danger">' + value + '</small>');
                    
                });
            } else {
                notification(data.status, data.message);
                if (data.status == 'success') {
                    window.location.replace("{{ route('employee') }}");
                }
            }

        },
        error: function (xhr, ajaxOption, thrownError) {
            console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
        }
    });
}

function loadFile(event, target_id) {
    var output = document.getElementById(target_id);
    output.src = URL.createObjectURL(event.target.files[0]);
};

//Show Form Step by Step
function show_form(step)
{
    $('.step').attr('data-wizard-state','pending');
    $('.step-'+step).attr('data-wizard-state','current');
}

//Fetch Division List Department ID Wise
function getDivisionList(department_id){
    $.ajax({
        url:"{{ url('department-id-wise-division-list') }}/"+department_id,
        type:"GET",
        dataType:"JSON",
        success:function(data){
            html = `<option value="">Select Please</option>`;
            $.each(data, function(key, value) {
                html += '<option value="'+ key +'">'+ value +'</option>';
            });
            $('#division_id').empty();
            $('#division_id').append(html);
            $('.selectpicker').selectpicker('refresh');
        },
    });
}

//Fetch Upazila List District ID Wise
function getUpazilaList(district_id){
    $.ajax({
        url:"{{ url('district-id-wise-upazila-list') }}/"+district_id,
        type:"GET",
        dataType:"JSON",
        success:function(data){
            html = `<option value="">Select Please</option>`;
            $.each(data, function(key, value) {
                html += '<option value="'+ key +'">'+ value +'</option>';
            });
            $('#upazila_id').empty();
            $('#upazila_id').append(html);
            $('.selectpicker').selectpicker('refresh');
        },
    });
}


/*****************************************
* Begin :: Dynamic Education Input Field *
*******************************************/
var education_count = 1;
function dynamic_education_field(row)
{
    var html = `<div class="row education border-top pt-5">
                    <div class="form-group col-md-4">
                        <label>Degree</label>
                        <input type="text" class="form-control" name="education[`+row+`][degree]" id="education_`+row+`_degree">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Major</label>
                        <input type="text" class="form-control" name="education[`+row+`][major]" id="education_`+row+`_major">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Institute</label>
                        <input type="text" class="form-control" name="education[`+row+`][institute]" id="education_`+row+`_institute">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Passing Year</label>
                        <input type="text" class="form-control" name="education[`+row+`][passing_year]" id="education_`+row+`_passing_year">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Result</label>
                        <input type="text" class="form-control" name="education[`+row+`][result]" id="education_`+row+`_result">
                    </div>
                    <div class="form-group col-md-4 text-right" style="padding-top: 28px;">
                        <button type="button" class="btn btn-danger btn-sm remove_education" data-toggle="tooltip" data-placement="top" data-original-title="Remove">
                            <i class="fas fa-minus-square"></i>
                        </button>
                    </div>
                </div>`;
    $('#education_section').append(html);
}
$(document).on('click','#add_education',function(){
    education_count++;
    dynamic_education_field(education_count);
});
$(document).on('click','.remove_education',function(){
    education_count--;
    $(this).closest('.education').remove();
});
/*****************************************
* End :: Dynamic Education Input Field *
******************************************/

/*****************************************
* Begin :: Dynamic Experience Input Field *
*******************************************/
var experience_count = 1;
function dynamic_experience_field(row)
{
    var html = `<div class="row experience border-top pt-5">
                    <div class="form-group col-md-4">
                        <label>Designation</label>
                        <input type="text" class="form-control" name="experience[`+row+`][designation]" id="experience_`+row+`_designation">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Company Name</label>
                        <input type="text" class="form-control" name="experience[`+row+`][company]" id="experience_`+row+`_company">
                    </div>
                    <div class="form-group col-md-4">
                        <label>From Date</label>
                        <input type="text" class="form-control" name="experience[`+row+`][from_date]" id="experience_`+row+`_from_date">
                    </div>
                    <div class="form-group col-md-4">
                        <label>To Date</label>
                        <input type="text" class="form-control" name="experience[`+row+`][to_date]" id="experience_`+row+`_to_date">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Responsiblities</label>
                        <input type="text" class="form-control" name="experience[`+row+`][responsibility]" id="experience_`+row+`_responsibility">
                    </div>
                    <div class="form-group col-md-4 text-right" style="padding-top: 28px;">
                        <button type="button" class="btn btn-danger btn-sm remove_experience" data-toggle="tooltip" data-placement="top" data-original-title="Remove">
                            <i class="fas fa-minus-square"></i>
                        </button>
                    </div>
                </div>`;
    $('#experience_section').append(html);
}
$(document).on('click','#add_experience',function(){
    experience_count++;
    dynamic_experience_field(experience_count);
});
$(document).on('click','.remove_experience',function(){
    experience_count--;
    $(this).closest('.experience').remove();
});
/*****************************************
* End :: Dynamic Experience Input Field *
******************************************/
</script>
@endpush