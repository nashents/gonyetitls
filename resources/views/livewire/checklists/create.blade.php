<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="panel">
                        <div class="panel-body">
                            <form wire:submit.prevent="store()" >
                                <h5 class="underline mt-30">Inspection For ?</h5>
                                <div class="mb-10">
                                    <input type="radio" wire:model.debounce.300ms="type" value="Horse"  class="line-style"  />
                                    <label for="one" class="radio-label">Horse</label>
                                    <input type="radio" wire:model.debounce.300ms="type" value="Trailer"  class="line-style"  />
                                    <label for="one" class="radio-label">Trailer</label>
                                    <input type="radio" wire:model.debounce.300ms="type" value="Vehicle"  class="line-style" />
                                    <label for="one" class="radio-label">Vehicle</label>
                                   
                                </div>       
                       
                                <hr>  
                                @if (!is_null($type))
                                    @if ($type == "Horse")
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="name">Horses<span class="required" style="color: red">*</span></label>
                                                <select class="form-control" wire:model.debounce.300ms="horse_id" required>
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
                                                <label for="name">Mileage<span class="required" style="color: red">*</span></label>
                                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="mileage" placeholder="Enter Odometer Mileage" required >
                                                @error('mileage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="name">Driver<span class="required" style="color: red">*</span></label>
                                                <select class="form-control" wire:model.debounce.300ms="driver_id" required>
                                                    <option value="">Select Driver</option>
                                                    @foreach ($drivers as $driver)
                                                        <option value="{{ $driver->id }}">{{ $driver->employee ? $driver->employee->name : ""}} {{$driver->employee ? $driver->employee->surname : ""}}</option>
                                                    @endforeach
                                                </select>
                                                @error('driver_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                    @elseif ($type == "Vehicle")
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="name">Vehicles<span class="required" style="color: red">*</span></label>
                                                <select class="form-control" wire:model.debounce.300ms="vehicle_id" required>
                                                    <option value="">Select Vehicle</option>
                                                    @foreach ($vehicles as $vehicle)
                                                        <option value="{{ $vehicle->id }}">{{ $vehicle->registration_number }} | {{ $vehicle->vehicle_make ? $vehicle->vehicle_make->name : "" }} {{ $vehicle->vehicle_model ? $vehicle->vehicle_model->name : "" }} </option>
                                                    @endforeach
                                                </select>
                                                @error('vehicle_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="name">Mileage<span class="required" style="color: red">*</span></label>
                                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="mileage" placeholder="Enter Odometer Mileage" required>
                                                @error('mileage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="name">Employee<span class="required" style="color: red">*</span></label>
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
                                    @elseif ($type == "Trailer")
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="name">Trailers<span class="required" style="color: red">*</span></label>
                                                <select class="form-control" wire:model.debounce.300ms="trailer_id" required>
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
                                                <label for="name">Mileage</label>
                                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="mileage" placeholder="Enter Odometer Mileage" >
                                                @error('mileage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="name">Driver<span class="required" style="color: red">*</span></label>
                                                <select class="form-control" wire:model.debounce.300ms="driver_id" required>
                                                    <option value="">Select Driver</option>
                                                    @foreach ($drivers as $driver)
                                                        <option value="{{ $driver->id }}">{{ $driver->employee ? $driver->employee->name : ""}} {{$driver->employee ? $driver->employee->surname : ""}}</option>
                                                    @endforeach
                                                </select>
                                                @error('driver_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                @endif
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="name">Inspection Type<span class="required" style="color: red">*</span></label>
                                                <select class="form-control" wire:model.debounce.300ms="selectedChecklistCategory" required>
                                                    <option value="">Select Inspection Type</option>
                                                    @foreach ($checklist_categories as $checklist_categories)
                                                        <option value="{{ $checklist_categories->id }}">{{ $checklist_categories->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('selectedChecklistCategory') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="name">Checklist Date<span class="required" style="color: red">*</span></label>
                                                <input type="datetime-local" class="form-control" wire:model.debounce.300ms="date" placeholder="Date" required>
                                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                               
                                
                            
                                @if (!is_null($selectedChecklistCategory))
                                <small>  <a href="{{ route('checklist_categories.show',$selectedChecklistCategory) }}" target="_blank"><i class="fa fa-plus-square-o"></i> New </a></small> <br> 
                                <div  style="height: 500px; overflow: auto">
                                <table id="inspectionsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <caption><strong>{{$checklist_category->name}}</strong></caption>
                                    <thead >
                                     <tr>
                                        <th class="th-sm">Item
                                        </th>
                                        <th class="th-sm">Actions
                                        </th>
                                      </tr>
                                    </thead>
                
                                    <tbody>
                                       @foreach ($category_checklists as $key => $category_checklist)
                                        
                                      <tr>
                                        <td>{{$category_checklist->checklist_item ? $category_checklist->checklist_item->name : ""}} | <small>{{$category_checklist->checklist_item ? $category_checklist->checklist_item->notes : ""}}</small> 
                                            <input type="hidden" wire:model.debounce.300ms="checklist_item_id.{{$category_checklist->checklist_item->id}}" value="{{$category_checklist->checklist_item->id}}">
                                         <br>
                                         <div class="form-group">
                                            <label for="exampleInputEmail13">Observations</label>
                                           <textarea  wire:model.debounce.300ms="comments.{{$category_checklist->checklist_item->id}}" wire:key="{{ $category_checklist->checklist_item->id }}" class="form-control" cols="15" rows="3"></textarea>
                                        </div>
                                        </td>
                                        <td>
                                            <div class="mb-10">
                                                <input type="radio" wire:model.debounce.300ms="status.{{$category_checklist->checklist_item->id}}" wire:key="{{ $category_checklist->checklist_item->id }}" value="{{$yes}}"  class="line-style" required/>
                                                <label for="one" class="radio-label">Yes</label>
                                            </div>
                                            <div class="mb-10">
                                                <input type="radio" wire:model.debounce.300ms="status.{{$category_checklist->checklist_item->id}}"wire:key="{{ $category_checklist->checklist_item->id }}"  value="{{$no}}" class="line-style" required/>
                                                <label for="three" class="radio-label">No</label>
                                            </div>
                                        </td>
                 
                                      </tr>
                
                                      @endforeach
                                    </tbody>
                
                                  </table>
                                </div>
                                 
                                  @endif
            
                                <div class="form-group">
                                    <label for="name">Overal Inspection Remarks</label>
                                   <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="4" placeholder="Enter Remarks"></textarea>
                                    @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                               
                            <div class="modal-footer">
                                <div class="btn-group" role="group">
                                    <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                                    <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Save</button>
                                </div>
                                <!-- /.btn-group -->
                            </div>
                        </form>
                        </div>
                    </div>
                </div>
                <!-- /.col-md-6 -->


            </div>

        </div>
        <!-- /.container-fluid -->
    </section>


</div>
