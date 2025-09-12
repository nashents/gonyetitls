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
                                <a href="" data-toggle="modal" data-target="#qualificationModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Qualification</a>
                               
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search qualifications...">
                                </div>
                            </div>
                            <table class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Code
                                    </th>
                                    <th class="th-sm">Name
                                    </th>
                                    <th class="th-sm">Category
                                    </th>
                                    <th class="th-sm">Level
                                    </th>
                                    <th class="th-sm">Expires?
                                    </th>
                                    <th class="th-sm">Validity(Months)
                                    </th>
                                    <th class="th-sm">Description
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($qualifications))
                                <tbody>
                                    @forelse ($qualifications as $qualification)
                                  <tr>
                                    <td>{{$qualification->code}}</td>
                                    <td>{{$qualification->name}}</td>
                                    <td>{{$qualification->category}}</td>
                                    <td>{{$qualification->level}}</td>
                                    <td>{{$qualification->is_expiring == True ? "Yes" : "No"}}</td>
                                    <td>{{$qualification->validity_months}}</td>
                                    <td>{{$qualification->description}}</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#"  wire:click="edit({{$qualification->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#qualificationDeleteModal{{ $qualification->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('qualifications.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="8">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Qualifications Found ....
                                        </div>
                                       
                                    </td>
                                  </tr>  
                                    @endforelse
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                 @endif
                              </table>
                               <nav class="text-center" style="float: right">
                                <ul class="pagination rounded-corners">
                                    @if (isset($qualifications))
                                        {{ $qualifications->links() }} 
                                    @endif 
                                </ul>
                            </nav>  

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="qualificationModal" taqualificationdex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Qualifications <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="name">Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Eg ACCA Professional" required>
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="qualification_number">Code</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="code" placeholder="eg ACCA, CIS-1">
                                @error('code') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>               
                    <div class="row">
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="name">Category</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="category" placeholder="Accounting, Safety" >
                                @error('category') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="qualification_number">Level</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="level" placeholder="internal level scale (1-10)">
                                @error('level') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>   
                   
                    <div class="mb-10">
                        <input type="checkbox" wire:model.debounce.300ms="is_expiring"   class="line-style" />
                        <label for="one" class="radio-label">Qualification Expires?</label>
                        @error('is_expiring') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div> 
                    @if ($is_expiring && $is_expiring == True)
                        <div class="form-group">
                            <label for="qualification_number">Validity</label>
                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="validity_months" placeholder="Qualification Validity in months">
                            @error('validity_months') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>  
                    @endif
                    <div class="form-group">
                        <label for="name">Description</label>
                        <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="4"></textarea>
                        @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="qualificationEditModal" taqualificationdex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Edit Qualifications <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="name">Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Eg ACCA Professional" required>
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="qualification_number">Code</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="code" placeholder="eg ACCA, CIS-1">
                                @error('code') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>               
                    <div class="row">
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="name">Category</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="category" placeholder="Accounting, Safety" >
                                @error('category') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="qualification_number">Level</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="level" placeholder="internal level scale (1-10)">
                                @error('level') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>   
                   
                    <div class="mb-10">
                        <input type="checkbox" wire:model.debounce.300ms="is_expiring"   class="line-style" />
                        <label for="one" class="radio-label">Qualification Expires?</label>
                        @error('is_expiring') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div> 
                    @if ($is_expiring && $is_expiring == True)
                        <div class="form-group">
                            <label for="qualification_number">Validity</label>
                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="validity_months" placeholder="Qualification Validity in months">
                            @error('validity_months') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>  
                    @endif
                    <div class="form-group">
                        <label for="name">Description</label>
                        <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="4"></textarea>
                        @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

