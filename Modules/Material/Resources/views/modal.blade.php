<div class="modal fade" id="store_or_update_modal" tabindex="-1" role="dialog" aria-labelledby="model-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">

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
                    <div class="col-md-9">
                        <div class="row">
                            <input type="hidden" name="update_id" id="update_id"/>
                            <input type="hidden" name="has_opening_stock" id="has_opening_stock" value="2"/>
                            <x-form.textbox labelName="Material Name" name="material_name" required="required" col="col-md-6" placeholder="Enter material name"/>
                            <div class="col-md-6 form-group required">
                                <label for="material_code">Material Code</label>
                                <div class="input-group" id="code_section">
                                    <input type="text" class="form-control" name="material_code" id="material_code">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-primary" id="generate-code"  data-toggle="tooltip" data-theme="dark" title="Generate Code"
                                        style="border-top-right-radius: 0.42rem;border-bottom-right-radius: 0.42rem;border:0;cursor: pointer;">
                                            <i class="fas fa-retweet text-white"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <x-form.selectbox labelName="Category" name="category_id" required="required" col="col-md-6" class="selectpicker">
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </x-form.selectbox>
                            <x-form.selectbox labelName="Material Type" name="type" required="required"  col="col-md-6" class="selectpicker" >
                                @foreach (MATERIAL_TYPE as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </x-form.selectbox>
                            <x-form.selectbox labelName="Unit" name="unit_id" required="required"  col="col-md-6" class="selectpicker" onchange="populate_unit(this.value)">
                                @if (!$units->isEmpty())
                                    @foreach ($units as $unit)
                                        @if ($unit->base_unit == null)
                                        <option value="{{ $unit->id }}">{{ $unit->unit_name.' ('.$unit->unit_code.')' }}</option>
                                        @endif
                                    @endforeach
                                @endif
                            </x-form.selectbox>
                            <x-form.selectbox labelName="Purchase Unit" name="purchase_unit_id" required="required" col="col-md-6" class="selectpicker"/>
                            <x-form.textbox labelName="Opening Cost" name="opening_cost" col="col-md-6 material-cost d-none" required="required" placeholder="0"/>
                            <x-form.textbox labelName="Opening Stock Quantity" name="opening_stock_qty" col="col-md-6 material-qty d-none" required="required" placeholder="0"/>
                            <x-form.selectbox labelName="Opening Stock Warehouse" name="opening_warehouse_id" required="required"  col="col-md-6 opening-warehouse-id d-none" class="selectpicker">
                                @if (!$warehouses->isEmpty())
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                @endif
                            </x-form.selectbox>
                            <x-form.textbox labelName="Stock Alert Quantity" name="alert_qty" col="col-md-6" placeholder="0"/>
                            <div class="col-md-6 form-group">
                                <label for="tax_id">Material Tax</label>
                                <select name="tax_id" id="tax_id" required="required" class="form-control selectpicker">
                                    <option value="0" selected>No Tax</option>
                                    @if (!$taxes->isEmpty())
                                        @foreach ($taxes as $tax)
                                            <option value="{{ $tax->id }}"  {{ isset($product) ? (($product->tax_id == $tax->id) ? 'selected' : '')  : '' }}>{{ $tax->name }}</option>
                                        @endforeach 
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="tax_method">Tax Method<span class="text-danger">*</span> <i class="fas fa-info-circle" data-toggle="tooltip" 
                                    data-theme="dark" title="Exclusive: price = Actual price + Tax. Inclusive: Actual price = Price - Tax"></i></label>
                                <select name="tax_method" id="tax_method" required="required" class="form-control selectpicker">
                                @foreach (TAX_METHOD as $key => $value)
                                    <option value="{{ $key }}" @if($key == 1){{ 'selected' }} @endif >{{ $value }}</option>
                                @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4" style="padding-top: 25px;">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="opening_stock">
                                    <label class="custom-control-label" for="opening_stock">This product has opening stock</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label for="logo" class="form-control-label">Material Image</label>
                                <div class="col=md-12 px-0  text-center">
                                    <div id="material_image">
                    
                                    </div>
                                </div>
                                <input type="hidden" name="old_material_image" id="old_material_image">
                            </div>
                        </div>
                    </div>
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
