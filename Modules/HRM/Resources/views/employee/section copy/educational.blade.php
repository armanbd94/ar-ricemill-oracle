<div class="row">
    <div class="col-md-12" id="education_section">
        <div class="row">
            <div class="form-group col-md-4">
                <label>Degree</label>
                <input type="text" class="form-control" name="education[1][degree]" id="education_1_degree">
            </div>
            <div class="form-group col-md-4">
                <label>Major</label>
                <input type="text" class="form-control" name="education[1][major]" id="education_1_major">
            </div>
            <div class="form-group col-md-4">
                <label>Institute</label>
                <input type="text" class="form-control" name="education[1][institute]" id="education_1_institute">
            </div>
            <div class="form-group col-md-4">
                <label>Passing Year</label>
                <input type="text" class="form-control" name="education[1][passing_year]" id="education_1_passing_year">
            </div>
            <div class="form-group col-md-4">
                <label>Result</label>
                <input type="text" class="form-control" name="education[1][result]" id="education_1_result">
            </div>
        </div>
    </div>
    <div class="col-md-12 text-right border-top pt-5">
        <button type="button" id="add_education" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" data-original-title="Add More">
            <i class="fas fa-plus-square"></i>
        </button>
    </div>
</div>
<div class="d-flex justify-content-between border-top mt-5 pt-10">
    <div class="mr-2">
        <button type="button" class="btn btn-light-primary btn-sm font-weight-bolder text-uppercase"   onclick="show_form(4)">Previous</button>
    </div>
    <div>
        <button type="button"  class="btn btn-primary btn-sm font-weight-bolder text-uppercase" data-wizard-type="action-next" onclick="show_form(6)">Next</button>
    </div>
</div>