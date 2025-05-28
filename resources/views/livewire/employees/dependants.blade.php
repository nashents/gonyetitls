<div>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
        <a href="" data-toggle="modal" data-target="#dependantModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Dependant</a>
        <br>
        <br>
        <br>
        <table id="dependantsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead >
                <th class="th-sm">Type
                </th>
                <th class="th-sm">Name
                </th>
                <th class="th-sm">Gender
                </th>
                <th class="th-sm">DOB
                </th>
              </tr>
            </thead>
            <tbody>
                @if (isset($dependants))
                @forelse ($dependants as $dependant)
                <tr>
                    <td>
                    {{$dependant->type}}
                    </td>
                    <td>
                    {{$dependant->name}}  {{$dependant->surname}}
                    </td>
                    <td>
                    {{$dependant->gender}}
                    </td>
                    <td>
                    {{$dependant->dob}}
                    </td>
                </tr>
                @empty
                    <tr>
                    <td colspan="4">
                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                            No Dependants Found ....
                        </div> 
                    </td>
                    </tr>  
                @endforelse
            </tbody>
            @else
                <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
             @endif
          </table>

          <div data-backdrop="static" data-keyboard="false" class="modal fade" id="dependantDeleteModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content bg-danger">
                    <div class="modal-body">
                       <center> <strong>Are you sure you want to delete this Dependant</strong> </center>
                    </div>
                    <form wire:submit.prevent="delete()"  >
                        <input type="hidden" name="_method" value="DELETE">
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
        
         
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="dependantModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="border">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Dependant(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="country">Type<span class="required" style="color: red">*</span></label>
                            <select wire:model.debounce.300ms="type" class="form-control" required>
                                <option value="">Select Option</option>
                                <option value="Child">Child</option>
                                <option value="Parent">Parent</option>
                                <option value="Relative">Relative</option>
                                <option value="Spouse">Spouse</option>
                            </select>
                        @error('type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="name"  required />
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Surname<span class="required" style="color: red">*</span></label>
                                <input type="text" step="any" class="form-control" wire:model.debounce.300ms="surname" required />
                                @error('surname') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="gender">Gender<span class="required" style="color: red">*</span></label>
                                    <div class="col-sm-10">
                                    <div class="radio">
                                        <label>
                                        <input type="radio" wire:model.debounce.300ms="gender" id="optionsRadios1" value="Male" required >
                                        Male
                                        </label>
                                    </div>
                                    <div class="radio">
                                        <label>
                                        <input type="radio"  wire:model.debounce.300ms="gender" id="optionsRadios2" value="Female" required>
                                        Female
                                        </label>
                                    </div>
                                </div>
                                @error('gender') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">DOB</label>
                                <input type="date" step="any" class="form-control" wire:model.debounce.300ms="dob"/>
                                @error('dob') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="dependantEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="border">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Depandant <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="country">Type<span class="required" style="color: red">*</span></label>
                            <select wire:model.debounce.300ms="type" class="form-control" required>
                                <option value="">Select Option</option>
                                <option value="Child">Child</option>
                                <option value="Parent">Parent</option>
                                <option value="Relative">Relative</option>
                                <option value="Spouse">Spouse</option>
                            </select>
                        @error('type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="name"  required />
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Surname<span class="required" style="color: red">*</span></label>
                                <input type="text" step="any" class="form-control" wire:model.debounce.300ms="surname" required />
                                @error('surname') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="gender">Gender<span class="required" style="color: red">*</span></label>
                                    <div class="col-sm-10">
                                    <div class="radio">
                                        <label>
                                        <input type="radio" wire:model.debounce.300ms="gender" id="optionsRadios1" value="Male" required >
                                        Male
                                        </label>
                                    </div>
                                    <div class="radio">
                                        <label>
                                        <input type="radio"  wire:model.debounce.300ms="gender" id="optionsRadios2" value="Female" required>
                                        Female
                                        </label>
                                    </div>
                                </div>
                                @error('gender') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">DOB</label>
                                <input type="date" step="any" class="form-control" wire:model.debounce.300ms="dob"/>
                                @error('dob') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
