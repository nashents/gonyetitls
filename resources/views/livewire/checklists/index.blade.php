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
                                <a href="{{ route('checklists.create') }}" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Inspection</a>
                               
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="checklistsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Inspection#
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">CheckedBy
                                    </th>
                                    <th class="th-sm">DrivenBy
                                    </th>
                                    <th class="th-sm">Inpection For
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($checklists->count()>0)
                                <tbody>
                                    @foreach ($checklists as $checklist)
                                  <tr>
                                    <td>{{$checklist->checklist_number}}</td>
                                    <td>{{$checklist->date}}</td>
                                    <td>{{$checklist->user ? $checklist->user->name : ""}} {{$checklist->user ? $checklist->user->surname : ""}}</td>
                                    <td>
                                        @if ($checklist->employee)
                                        {{$checklist->employee ? $checklist->employee->name : ""}} {{$checklist->employee ? $checklist->employee->surname : ""}}        
                                        @elseif($checklist->driver)
                                        {{$checklist->driver->employee ? $checklist->driver->employee->name : ""}} {{$checklist->driver->employee ? $checklist->driver->employee->surname : ""}}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($checklist->horse)
                                        {{$checklist->horse->registration_number}} | {{$checklist->horse->horse_make ? $checklist->horse->horse_make->name : ""}} {{$checklist->horse->horse_model ? $checklist->horse->horse_model->name : ""}} 
                                        @elseif($checklist->vehicle)
                                        {{$checklist->vehicle->registration_number}} | {{$checklist->vehicle->vehicle_make ? $checklist->vehicle->vehicle_make->name : ""}} {{$checklist->vehicle->vehicle_model ? $checklist->vehicle->vehicle_model->name : ""}} 
                                        @elseif ($checklist->trailer)
                                        {{$checklist->trailer ? $checklist->trailer->registration_number : ""}} | {{$checklist->trailer ? $checklist->trailer->make : ""}} {{$checklist->trailer ? $checklist->trailer->model : ""}} 
                                        @endif
                                        
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('checklists.show', $checklist->id) }}" ><i class="fa fa-eye color-default"></i> View</a></li>
                                                <li><a href="#"  wire:click="edit({{$checklist->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#checklistDeleteModal{{ $checklist->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('checklists.delete')
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


    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="checklistModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Checkilist <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Checklist Date<span class="required" style="color: red">*</span></label>
                                <input type="datetime-local" class="form-control" wire:model.debounce.300ms="date" placeholder="Date" required>
                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">DrivenBy<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="employee_id" required>
                                    <option value="">Select Employee</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name}} {{ $employee->surname}}</option>
                                    @endforeach
                                </select>
                                @error('employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                   
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Horses</label>
                                <select class="form-control" wire:model.debounce.300ms="horse_id">
                                    <option value="">Select Horse</option>
                                    @foreach ($horses as $horse)
                                        <option value="{{ $horse->id }}">{{ $horse->registration_number }} | {{ $horse->horse_make ? $horse->horse_make->name : "" }} {{ $horse->horse_model ? $horse->horse_model->name : "" }} </option>
                                    @endforeach
                                </select>
                                @error('horse_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Trailers</label>
                                <select class="form-control" wire:model.debounce.300ms="trailer_id">
                                    <option value="">Select Trailer</option>
                                    @foreach ($trailers as $trailer)
                                        <option value="{{ $trailer->id }}"> {{ $trailer->registration_number }} | {{ $trailer->make}} {{ $trailer->model }} </option>
                                    @endforeach
                                </select>
                                @error('trailer_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Vehicles</label>
                                <select class="form-control" wire:model.debounce.300ms="vehicle_id">
                                    <option value="">Select Vehicle</option>
                                    @foreach ($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}">{{ $vehicle->registration_number }} | {{ $vehicle->vehicle_make ? $vehicle->vehicle_make->name : "" }} {{ $vehicle->vehicle_model ? $vehicle->vehicle_model->name : "" }} </option>
                                    @endforeach
                                </select>
                                @error('vehicle_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                 
                    <table  class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                        {{-- <caption>  <small>  <a href="{{ route('checklist_items.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Item</a></small>  </caption> --}}
                        <thead >
                         <tr>
                            <th class="th-sm">Item
                            </th>
                            <th class="th-sm">Actions
                            </th>
                          </tr>
                        </thead>
    
                        <tbody>
                            @foreach ($checklist_items as $checklist_item)
                          <tr>
                            <td>
                                {{$checklist_item->name}}
                             <div class="row">
                                <div  class="col-md-12">
                                    <div class="form-group">
                                        <label for="exampleInputEmail13">Comments</label>
                                        <textarea  wire:model.debounce.300ms="comments.{{$checklist_item->id}}" wire:key="{{ $checklist_item->id }}" class="form-control" cols="15" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                            </td>
                            <td>
                                <div class="mb-10">
                                    <input type="radio" wire:model.debounce.300ms="status.{{$checklist_item->id}}" wire:key="{{ $checklist_item->id }}" value="{{$available}}"  class="line-style" required/>
                                    <label for="one" class="radio-label">Available</label>
                                </div>
                                <div class="mb-10">
                                    <input type="radio" wire:model.debounce.300ms="status.{{$checklist_item->id}}"wire:key="{{ $checklist_item->id }}"  value="{{$notavailable}}" class="line-style" required/>
                                    <label for="three" class="radio-label">Not Available</label>
                                </div>
                            </td>
    
                          </tr>
    
    
                          {{-- @endforeach --}}
                          @endforeach
                        </tbody>
    
                      </table>  
                  

                    <div class="form-group">
                        <label for="name">Comments</label>
                       <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="4" placeholder="Enter Comments"></textarea>
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="checklistEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit checklist <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >

                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Name<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Name" required />
                        @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="name">Description</label>
                       <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="10" placeholder="Enter item description"></textarea>
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

