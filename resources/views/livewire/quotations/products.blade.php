<div>
    <x-loading/>
    <a href="" data-toggle="modal" data-target="#addQuotationProductModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Quotation Item</a>
    <br>
    <br>
    <table id="quotation_productsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
        <thead >
          
            <th class="th-sm">Description
            </th>
            <th class="th-sm">Freight
            </th>
            <th class="th-sm">Actions
            </th>
          </tr>
        </thead>

        <tbody>
            @foreach ($quotation_products as $quotation_product)
          <tr>
            <td>
                {{$quotation_product->description}}
            </td>
            <td>
                @if ($quotation_product->freight)
                    {{$quotation_product->quotation->currency ? $quotation_product->quotation->currency->symbol : ""}}{{number_format($quotation_product->freight,2)}}
                @endif
            </td>
            <td class="w-10 line-height-35 table-dropdown">
                <div class="dropdown">
                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-bars"></i>
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="#" wire:click="edit({{$quotation_product->id }})"><i class="fa fa-edit color-success"></i>Edit</a></li>
                         <li><a href="#" wire:click="removeShow({{ $quotation_product->id }})"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                    </ul>
                </div>
              {{-- @include('quotation_products.delete') --}}
        </td>
        </tr>
          @endforeach
        </tbody>
      </table>

      <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="removeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                   <center> <strong>Are you sure you want to delete this Quotation Product</strong> </center>
                </div>
                <form wire:submit.prevent="removeQuotationProduct()" >
                <div class="modal-footer no-border">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fa fa-trash"></i>Delete</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="addQuotationProductModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="cargo">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Quotation Item(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="currency">From<span class="required" style="color: red">*</span></label>
                              <select class="form-control" wire:model.debounce.300ms="from.0" required>
                                  <option value="">Select Starting Location</option>
                                  @foreach ($destinations as $destination)
                                      <option value="{{$destination->id}}">{{$destination->country ? $destination->country->name : ""}} {{$destination->city}}</option>
                                  @endforeach
                              </select>
                                @error('from.0') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="to">To<span class="required" style="color: red">*</span></label>
                              <select class="form-control" wire:model.debounce.300ms="to.0" required>
                                  <option value="">Select Ending Location</option>
                                  @foreach ($destinations as $destination)
                                      <option value="{{$destination->id}}">{{$destination->country ? $destination->country->name : ""}} {{$destination->city}}</option>
                                  @endforeach
                              </select>
                                @error('to.0') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="currency">LP</label>
                              <select class="form-control" wire:model.debounce.300ms="loading_point_id.0" >
                                  <option value="">Select Loading Point</option>
                                  @foreach ($loading_points as $loading_point)
                                      <option value="{{$loading_point->id}}">{{$loading_point->name}}</option>
                                  @endforeach
                              </select>
                                @error('loading_point_id.0') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="currency">OP</label>
                              <select class="form-control" wire:model.debounce.300ms="offloading_point_id.0" >
                                  <option value="">Select Offloading Point</option>
                                  @foreach ($offloading_points as $offloading_point)
                                  <option value="{{$offloading_point->id}}">{{$offloading_point->name}}</option>
                                  @endforeach
                              </select>
                                @error('offloading_point_id.0') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                  
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="customer">Cargo(s)<span class="required" style="color: red">*</span></label>
                                  <select class="form-control" wire:model.debounce.300ms="cargo_id.0" required>
                                      <option value="">Select Cargo</option>
                                      @foreach ($cargos as $cargo)
                                          <option value="{{$cargo->id}}">{{$cargo->name}}</option>
                                      @endforeach
                                  </select>
                                    @error('cargo_id.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="customer">Additional Info<span class="required" style="color: red">*</span></label>
                                   <textarea wire:models.debounce.300ms="description.0" class="form-control" cols="30" rows="5"></textarea>
                                    @error('description.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="amount">Weight(Tons)<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="weight.0" placeholder="Weight in tons" required>
                                @error('weight.0') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        </div>
                        <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="rate">Rate</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="rate.0" placeholder="Rate" >
                                @error('rate.0') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="freight">Freight<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="freight.0" placeholder="Freight" required>
                                @error('freight.0') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <hr>
                    @foreach ($inputs as $key => $value)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="currency">From<span class="required" style="color: red">*</span></label>
                              <select class="form-control" wire:model.debounce.300ms="from.{{ $value }}" required>
                                  <option value="">Select Starting Location</option>
                                  @foreach ($destinations as $destination)
                                      <option value="{{$destination->id}}">{{$destination->country ? $destination->country->name : ""}} {{$destination->city}}</option>
                                  @endforeach
                              </select>
                                @error('from.0') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="to">To<span class="required" style="color: red">*</span></label>
                              <select class="form-control" wire:model.debounce.300ms="to.{{ $value }}" required>
                                  <option value="">Select Ending Location</option>
                                  @foreach ($destinations as $destination)
                                      <option value="{{$destination->id}}">{{$destination->country ? $destination->country->name : ""}} {{$destination->city}}</option>
                                  @endforeach
                              </select>
                                @error('to.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="currency">LP</label>
                              <select class="form-control" wire:model.debounce.300ms="loading_point_id.{{ $value }}" >
                                  <option value="">Select Loading Point</option>
                                  @foreach ($loading_points as $loading_point)
                                      <option value="{{$loading_point->id}}">{{$loading_point->name}}</option>
                                  @endforeach
                              </select>
                                @error('loading_point_id.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="currency">OP</label>
                              <select class="form-control" wire:model.debounce.300ms="offloading_point_id.{{ $value }}">
                                  <option value="">Select Offloading Point</option>
                                  @foreach ($offloading_points as $offloading_point)
                                  <option value="{{$offloading_point->id}}">{{$offloading_point->name}}</option>
                                  @endforeach
                              </select>
                                @error('offloading_point_id.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="customer">Cargo(s)<span class="required" style="color: red">*</span></label>
                                  <select class="form-control" wire:model.debounce.300ms="cargo_id.{{ $value }}" required>
                                      <option value="">Select Cargo</option>
                                      @foreach ($cargos as $cargo)
                                          <option value="{{$cargo->id}}">{{$cargo->name}}</option>
                                      @endforeach
                                  </select>
                                    @error('cargo_id.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="customer">Additional Info<span class="required" style="color: red">*</span></label>
                                   <textarea wire:models.debounce.300ms="description.{{ $value }}" class="form-control" cols="30" rows="5"></textarea>
                                    @error('description.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="amount">Weight(Tons)<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="weight.{{ $value }}" placeholder="Weight.tons" required>
                                @error('weight.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        </div>
                        <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="rate">Rate</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="rate.{{ $value }}" placeholder="Rate" >
                                @error('rate.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="freight">Freight<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="freight.{{ $value }}" placeholder="Freight" required>
                                @error('freight.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label for=""></label>
                                <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                            </div>
                        </div>
                    </div>
                    <hr>
                    @endforeach
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i>Product</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Save</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="editQuotationProductModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="cargo">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Quotation Item<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-9">
                            <div class="form-group">
                                <label for="freight">Description<span class="required" style="color: red">*</span></label>
                                <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="10"></textarea>
                                @error('description') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="freight">Freight<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="freight" placeholder="Freight" required>
                                @error('freight') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                 
                        
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>
</div>
