<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div>
                                @include('includes.messages')
                            </div>

                            <div class="panel-title">
                                <a href="#" data-toggle="modal" data-target="#taxModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Tax</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="taxesTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Name
                                    </th>
                                    <th class="th-sm">Abbreviation
                                    </th>
                                    <th class="th-sm">Rate
                                    </th>
                                    <th class="th-sm">Tax#
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($taxes->count()>0)
                                <tbody>
                                    @foreach ($taxes as $tax)
                                  <tr>
                                    <td>{{$tax->name}}</td>
                                    <td>{{$tax->abbreviation}}</td>
                                    <td>{{$tax->rate ? $tax->rate."%" : ""}}</td>
                                    <td>{{$tax->tax_number}}</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('taxes.show', $tax->id) }}" ><i class="fa fa-eye color-default"></i> View</a></li>
                                                <li><a href="#"  wire:click="edit({{$tax->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#taxDeleteModal{{ $tax->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('taxes.delete')
                                </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                 @endif
                              </table>

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>

   
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="taxModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Sales Tax <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Tax Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Tax Name" required />
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Abbreviation<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="abbreviation" placeholder="Enter Tax Abbreviation" required />
                                @error('abbreviation') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Description</label>
                                <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="4"></textarea>
                                @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Your tax number</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="tax_number" placeholder="Enter Tax Number"  />
                                @error('tax_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                  <div class="row">
                    <div class="col-md-6">
                        <div class="mb-10">
                            <label for=""></label>
                            <input type="checkbox" wire:model.debounce.300ms="show_tax_number"   class="line-style" />
                            <label for="one" class="radio-label">Show tax number on invoices?</label>
                            @error('show_tax_number') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-10">
                            <input type="checkbox" wire:model.debounce.300ms="compound_tax"   class="line-style" />
                            <label for="one" class="radio-label">This is a compound tax</label>
                            @error('compound_tax') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                   
                    
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                        <div class="mb-10">
                            <input type="checkbox" wire:model.debounce.300ms="tax_recoverable"   class="line-style" />
                            <label for="one" class="radio-label">This tax is recoverable</label>
                            @error('tax_recoverable') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Tax Rate<span class="required" style="color: red">*</span></label>
                            <input type="number" step="any" min="0"  class="form-control" wire:model.debounce.300ms="rate" placeholder="Enter Rate as %" required />
                            @error('rate') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="taxEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Edit Sales Tax <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Tax Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Tax Name" required />
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Abbreviation<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="abbreviation" placeholder="Enter Tax Abbreviation" required />
                                @error('abbreviation') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Description</label>
                                <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="4"></textarea>
                                @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Your tax number</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="tax_number" placeholder="Enter Tax Number"  />
                                @error('tax_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                  <div class="row">
                    <div class="col-md-6">
                        <div class="mb-10">
                            <label for=""></label>
                            <input type="checkbox" wire:model.debounce.300ms="show_tax_number"   class="line-style" />
                            <label for="one" class="radio-label">Show tax number on invoices?</label>
                            @error('show_tax_number') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-10">
                            <input type="checkbox" wire:model.debounce.300ms="compound_tax"   class="line-style" />
                            <label for="one" class="radio-label">This is a compound tax</label>
                            @error('compound_tax') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                   
                    
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                        <div class="mb-10">
                            <input type="checkbox" wire:model.debounce.300ms="tax_recoverable"   class="line-style" />
                            <label for="one" class="radio-label">This tax is recoverable</label>
                            @error('tax_recoverable') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Tax Rate<span class="required" style="color: red">*</span></label>
                            <input type="number" step="any" min="0"  class="form-control" wire:model.debounce.300ms="rate" placeholder="Enter Rate as %" required />
                            @error('rate') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

