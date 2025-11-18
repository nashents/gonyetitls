<div>

    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-11 ">
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
                                    <div class="row">
                                        <div class="col-md-4">
                                            @if ($type == "Horse")
                                                <div class="form-group">
                                                    <label for="name">Horses<span class="required" style="color: red">*</span></label>
                                                    <select class="form-control" wire:model.debounce.300ms="horse_id" required>
                                                        <option value="">Select Horse</option>
                                                        @foreach ($horses as $horse)
                                                            <option value="{{ $horse->id }}">{{ $horse->registration_number }} {{$horse->fleet_number ? "(".$horse->fleet_number.")" : ""}} </option>
                                                        @endforeach
                                                    </select>
                                                    @error('horse_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            @elseif ($type == "Vehicle")
                                                <div class="form-group">
                                                    <label for="name">Vehicles<span class="required" style="color: red">*</span></label>
                                                    <select class="form-control" wire:model.debounce.300ms="vehicle_id" required>
                                                        <option value="">Select Vehicle</option>
                                                        @foreach ($vehicles as $vehicle)
                                                            <option value="{{ $vehicle->id }}">{{ $vehicle->registration_number }} {{$vehicle->fleet_number ? "(".$vehicle->fleet_number.")" : ""}} </option>
                                                        @endforeach
                                                    </select>
                                                    @error('vehicle_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            @elseif ($type == "Trailer")
                                                <div class="form-group">
                                                    <label for="name">Trailers<span class="required" style="color: red">*</span></label>
                                                    <select class="form-control" wire:model.debounce.300ms="trailer_id" required>
                                                        <option value="">Select Trailer</option>
                                                        @foreach ($trailers as $trailer)
                                                            <option value="{{ $trailer->id }}"> {{ $trailer->registration_number }} {{$trailer->fleet_number ? "(".$trailer->fleet_number.")" : ""}} </option>
                                                        @endforeach
                                                    </select>
                                                    @error('trailer_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="name">Mileage<span class="required" style="color: red">*</span></label>
                                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="mileage" placeholder="Enter Odometer Mileage" required >
                                                @error('mileage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            @if (in_array($type,['Trailer','Horse']))
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
                                            @else
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
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="name">Checklists<span class="required" style="color: red">*</span></label>
                                                <select class="form-control" wire:model.debounce.300ms="selectedChecklistCategory" required>
                                                    <option value="">Select Checklist</option>
                                                    @foreach ($checklist_categories as $checklist_categories)
                                                        <option value="{{ $checklist_categories->id }}">{{ $checklist_categories->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('selectedChecklistCategory') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="name">Inspection Date<span class="required" style="color: red">*</span></label>
                                                <input type="datetime-local" class="form-control" wire:model.debounce.300ms="date" placeholder="Date" required>
                                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                      
                                    </div>
                               
                                
                            
                                @if (!is_null($selectedChecklistCategory))
                                <small>  <a href="{{ route('checklist_categories.show',$selectedChecklistCategory) }}" target="_blank"><i class="fa fa-plus-square-o"></i> Add items to checklist </a></small><a href="#" wire:click.prevent="refresh('checklist_categories')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> <br> 
                                <div  style="height: 500px; overflow: auto">
                                @if ($checklist_category->name != "Tyre Inspection")

                                <table id="inspectionsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <caption><strong>{{$checklist_category->name}}</strong></caption>
                                    <thead >
                                     <tr>
                                        <th class="th-sm">Item
                                        </th>
                                        <th class="th-sm">Status
                                        </th>
                                      </tr>
                                    </thead>
                
                                    <tbody>
                                       @foreach ($category_checklists as $key => $category_checklist)
                                      <tr>
                                        <td>{{$category_checklist->checklist_sub_category ? $category_checklist->checklist_sub_category->name : ""}} {{$category_checklist->checklist_item ? $category_checklist->checklist_item->name : ""}} <small>(<strong>{{$category_checklist->condition}}</strong>)</small>
                                            <input type="hidden" wire:model.debounce.300ms="checklist_item_id.{{$category_checklist->id}}" value="{{$category_checklist->checklist_item->id}}">
                                         <br>
                                         <div class="form-group">
                                            <label for="exampleInputEmail13">Observations</label>
                                           <textarea  wire:model.debounce.300ms="comments.{{$category_checklist->id}}" wire:key="{{ $category_checklist->id }}" class="form-control" cols="15" rows="2"></textarea>
                                        </div>
                                        </td>
                                        <td>
                                            <div class="mb-10">
                                                <input type="radio" wire:model.debounce.300ms="status.{{$category_checklist->id}}" wire:key="{{ $category_checklist->id }}" value="{{$yes}}"  class="line-style" required/>
                                                <label for="one" class="radio-label">Yes</label>
                                            </div>
                                            <div class="mb-10">
                                                <input type="radio" wire:model.debounce.300ms="status.{{$category_checklist->id}}"wire:key="{{ $category_checklist->id }}"  value="{{$no}}" class="line-style" required/>
                                                <label for="three" class="radio-label">No</label>
                                            </div>
                                        </td>
                 
                                      </tr>
                
                                      @endforeach
                                    </tbody>
                
                                  </table>

                                @else
                                    <!--
                                        This is the table for all tyre inspections
                                    -->
                                    
                                    <table id="inspectionsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <caption><strong>{{$checklist_category->name}}</strong></caption>
                                    <thead >
                                     <tr>
                                        <th class="th-sm" style="width: 15%;">Tyre
                                        </th>
                                        <th class="th-sm" style="width: 7%;">Depth
                                        </th>
                                        <th class="th-sm" style="width: 7%;">PSI
                                        </th>
                                         <th class="th-sm" style="width: 8%;">Wear
                                        </th>
                                        <th class="th-sm" style="width: 8%;">Sidewall
                                        </th>
                                        <th class="th-sm" style="width: 8%;">Valve
                                        </th>
                                        <th class="th-sm" style="width: 10%;">Nuts Torqued
                                        </th>
                                        <th class="th-sm" style="width: 7%;">Axle
                                        </th>
                                        <th class="th-sm" style="width: 10%;">Rim
                                        </th>
                                        <th class="th-sm" style="width: 7%;">Rating
                                        </th>
                                        <th class="th-sm" style="width: 10%;">Actions
                                        </th>
                                      </tr>
                                    </thead>
                
                                    <tbody>
                                        @if (isset($tyre_assignments))
                                            
                                       
                                       @foreach ($tyre_assignments as $key => $tyre_assignment)
                                        
                                      <tr>
                                        <td>
                                            <strong>{{$tyre_assignment->tyre->product ? $tyre_assignment->tyre->product->name : ""}} {{$tyre_assignment->tyre->product->brand ? $tyre_assignment->tyre->product->brand->name : ""}} {{$tyre_assignment->tyre->serial_number}}</strong> <br> {{$tyre_assignment->axle}} {{$tyre_assignment->position}}
                                            <input type="hidden" wire:model.debounce.300ms="tyre_id.{{$tyre_assignment->tyre->id}}" value="{{$tyre_assignment->tyre->id}}">
                                            <input type="hidden" wire:model.debounce.300ms="tyre_assignment_id.{{$tyre_assignment->tyre->id}}" value="{{$tyre_assignment->id}}">
                                         <br>
                                         <div class="form-group">
                                            <label for="exampleInputEmail13">Notes</label>
                                           <textarea  wire:model.debounce.300ms="notes.{{$tyre_assignment->tyre->id}}" wire:key="{{$tyre_assignment->tyre->id}}" class="form-control" cols="15" rows="3"></textarea>
                                        </div>
                                        </td>
                                        <td>
                                            <div class="form-group">
                                                <input type="number" step="any" wire:model.debounce.300ms="tread_depth_mm.{{$tyre_assignment->tyre->id}}" wire:key="{{$tyre_assignment->tyre->id}}" class="form-control">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group">
                                                <input type="number" step="any" wire:model.debounce.300ms="pressure_psi.{{$tyre_assignment->tyre->id}}" wire:key="{{$tyre_assignment->tyre->id}}" class="form-control">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group">
                                                <select wire:model.debounce.300ms="wear_pattern.{{$tyre_assignment->tyre->id}}" wire:key="{{$tyre_assignment->tyre->id}}" class="form-control">
                                                    <option value="" disabled>Select Wear Pattern</option>
                                                    <option value="Even">Even</option>
                                                    <option value="Inner Edge">Inner Edge</option>
                                                    <option value="Outer Edge">Outer Edge</option>
                                                    <option value="Center">Center</option>
                                                    <option value="Cupping">Cupping</option>
                                                    <option value="Feathering">Feathering</option>
                                                </select>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group">
                                                <select wire:model.debounce.300ms="sidewall_damage.{{$tyre_assignment->tyre->id}}" wire:key="{{$tyre_assignment->tyre->id}}" class="form-control">
                                                    <option value="" disabled>Select Sidewall Damage</option>
                                                    <option value="None">None</option>
                                                    <option value="Cuts">Cuts</option>
                                                    <option value="Bulges">Bulges</option>
                                                    <option value="Cracks">Cracks</option>
                                                </select>
                                            </div>
                                        </td>
                                        <td>
                                           <div class="mb-10">
                                                <input type="radio" wire:model.debounce.300ms="valve_ok.{{$tyre_assignment->tyre->id}}" wire:key="{{ $tyre_assignment->tyre->id }}" value="1"  class="line-style" required/>
                                                <label for="one" class="radio-label">Tight</label>
                                            </div>
                                            <div class="mb-10">
                                                <input type="radio" wire:model.debounce.300ms="valve_ok.{{$tyre_assignment->tyre->id}}"wire:key="{{ $tyre_assignment->tyre->id }}"  value="0" class="line-style" required/>
                                                <label for="three" class="radio-label">Leaking</label>
                                            </div> 
                                        </td>
                                        <td>
                                           <div class="mb-10">
                                                <input type="radio" wire:model.debounce.300ms="wheel_nuts_torqued.{{$tyre_assignment->tyre->id}}" wire:key="{{ $tyre_assignment->tyre->id }}" value="1"  class="line-style" required/>
                                                <label for="one" class="radio-label">Yes</label>
                                            </div>
                                            <div class="mb-10">
                                                <input type="radio" wire:model.debounce.300ms="wheel_nuts_torqued.{{$tyre_assignment->tyre->id}}"wire:key="{{ $tyre_assignment->tyre->id }}"  value="0" class="line-style" required/>
                                                <label for="three" class="radio-label">No</label>
                                            </div> 
                                        </td>
                                        <td>
                                           <div class="mb-10">
                                                <input type="radio" wire:model.debounce.300ms="axle_match.{{$tyre_assignment->tyre->id}}" wire:key="{{ $tyre_assignment->tyre->id }}" value="1"  class="line-style" required/>
                                                <label for="one" class="radio-label">Yes</label>
                                            </div>
                                            <div class="mb-10">
                                                <input type="radio" wire:model.debounce.300ms="axle_match.{{$tyre_assignment->tyre->id}}"wire:key="{{ $tyre_assignment->tyre->id }}"  value="0" class="line-style" required/>
                                                <label for="three" class="radio-label">No</label>
                                            </div> 
                                        </td>
                                        <td>
                                             <div class="form-group">
                                                <select wire:model.debounce.300ms="rim_condition.{{$tyre_assignment->tyre->id}}" wire:key="{{$tyre_assignment->tyre->id}}" class="form-control">
                                                    <option value="" disabled>Select Sidewall Damage</option>
                                                    <option value="OK">OK</option>
                                                    <option value="Bent">Bent</option>
                                                    <option value="Cracked">Cracked</option>
                                                    <option value="Rusty">Rusty</option>
                                                </select>
                                            </div>
                                        </td>
                                         <td>
                                           <div class="mb-10">
                                                <input type="radio" wire:model.debounce.300ms="rating.{{$tyre_assignment->tyre->id}}" wire:key="{{ $tyre_assignment->tyre->id }}" value="1"  class="line-style" required/>
                                                <label for="one" class="radio-label">1</label>
                                            </div>
                                            <div class="mb-10">
                                                <input type="radio" wire:model.debounce.300ms="rating.{{$tyre_assignment->tyre->id}}"wire:key="{{ $tyre_assignment->tyre->id }}"  value="2" class="line-style" required/>
                                                <label for="three" class="radio-label">2</label>
                                            </div> 
                                            <div class="mb-10">
                                                <input type="radio" wire:model.debounce.300ms="rating.{{$tyre_assignment->tyre->id}}"wire:key="{{ $tyre_assignment->tyre->id }}"  value="3" class="line-style" required/>
                                                <label for="three" class="radio-label">3</label>
                                            </div> 
                                            <div class="mb-10">
                                                <input type="radio" wire:model.debounce.300ms="rating.{{$tyre_assignment->tyre->id}}"wire:key="{{ $tyre_assignment->tyre->id }}"  value="4" class="line-style" required/>
                                                <label for="three" class="radio-label">4</label>
                                            </div> 
                                            <div class="mb-10">
                                                <input type="radio" wire:model.debounce.300ms="rating.{{$tyre_assignment->tyre->id}}"wire:key="{{ $tyre_assignment->tyre->id }}"  value="5" class="line-style" required/>
                                                <label for="three" class="radio-label">5</label>
                                            </div> 
                                        </td>
                                        <td>
                                             <div class="form-group">
                                                <select wire:model.debounce.300ms="action_required.{{$tyre_assignment->tyre->id}}" wire:key="{{$tyre_assignment->tyre->id}}" class="form-control">
                                                    <option value="" disabled>Select Action</option>
                                                    <option value="None">None</option>
                                                    <option value="Rotate">Rotate</option>
                                                    <option value="Repair">Repair</option>
                                                    <option value="Replace">Replace</option>
                                                </select>
                                            </div>
                                        </td>
                                       
                 
                                      </tr>
                
                                      @endforeach
                                       @endif
                                    </tbody>
                
                                  </table>
                                    @endif
                                </div>
                                 
                                  @endif
            
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">Overal Inspection Remarks</label>
                                            <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="4" placeholder="Enter Remarks"></textarea>
                                            @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">Next Inspection Date</label>
                                            <input type="datetime-local" class="form-control" wire:model.debounce.300ms="next_inspection_at" placeholder="Date">
                                            @error('next_inspection_at') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
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
