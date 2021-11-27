<div class="row">
    <div class="col-md-12" id="experience_section">
        <div class="row">
            <div class="form-group col-md-4">
                <label>Designation</label>
                <input type="text" class="form-control" name="experience[1][designation]" id="experience_1_designation">
            </div>
            <div class="form-group col-md-4">
                <label>Company Name</label>
                <input type="text" class="form-control" name="experience[1][company]" id="experience_1_company">
            </div>
            <div class="form-group col-md-4">
                <label>From Date</label>
                <input type="text" class="form-control date" name="experience[1][from_date]" id="experience_1_from_date">
            </div>
            <div class="form-group col-md-4">
                <label>To Date</label>
                <input type="text" class="form-control date" name="experience[1][to_date]" id="experience_1_to_date">
            </div>
            <div class="form-group col-md-4">
                <label>Responsiblities</label>
                <input type="text" class="form-control" name="experience[1][responsibility]" id="experience_1_responsibility">
            </div>
        </div>
    </div>
    <div class="col-md-12 text-right border-top pt-5">
        <button type="button" id="add_experience" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" data-original-title="Add More">
            <i class="fas fa-plus-square"></i>
        </button>
    </div>
</div>
<div class="d-flex justify-content-between border-top mt-5 pt-10">
    <div class="mr-2">
        <button type="button" class="btn btn-light-primary btn-sm font-weight-bolder text-uppercase"   onclick="show_form(5)">Previous</button>
    </div>
    <div>
        <button type="button"  class="btn btn-primary btn-sm font-weight-bolder text-uppercase" data-wizard-type="action-next" onclick="show_form(7)">Next</button>
    </div>
</div>