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
                                <a href="#" data-toggle="modal" data-target="#gate_passModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Gate Pass</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <ul class="nav nav-tabs nav-justified" role="tablist">
                                <li role="presentation" class="active"><a href="#individual" aria-controls="individual" role="tab" data-toggle="tab">Individual</a></li>
                                <li role="presentation"><a href="#trips" aria-controls="trips" role="tab" data-toggle="tab">Trips</a></li>
                            </ul>
                            <div class="tab-content bg-white p-15">
                                <div role="tabpanel" class="tab-pane active" id="individual">
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <caption>Individual Gatepass</caption>
                                <thead>
                                  <tr>
                                    <th class="th-sm">GatePass#
                                    </th>
                                    <th class="th-sm">Branch
                                    </th>
                                    <th class="th-sm">Visitor
                                    </th>
                                    <th class="th-sm">InvitedBy
                                    </th>
                                    <th class="th-sm">In
                                    </th>
                                    <th class="th-sm">Out
                                    </th>
                                    <th class="th-sm">Auth
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($individual_gate_passes))
                                <tbody>
                                    @forelse ($individual_gate_passes as $gate_pass)
                                        
                                  
                                  <tr>
                                    @php
                                        $invited_by = App\Models\Employee::find($gate_pass->invited_by_id);
                                        $authorized_by = App\Models\Employee::find($gate_pass->authorized_by_id);
                                    @endphp
                                    <td>{{$gate_pass->gate_pass_number}}</td>
                                    <td>
                                        @if ($gate_pass->branch)
                                        {{$gate_pass->branch ? $gate_pass->branch->name : ""}} <br> 
                                        @endif
                                        {{$gate_pass->gate ? $gate_pass->gate->name : ""}}
                                    </td>
                                    <td>
                                        @if ($gate_pass->group)
                                        <small>{{$gate_pass->group ? $gate_pass->group->name : ""}}</small> <br>
                                        @endif
                                        {{ucfirst($gate_pass->visitor ? $gate_pass->visitor->name : "")}} {{ucfirst($gate_pass->visitor ? $gate_pass->visitor->surname : "")}}
                                        
                                    </td>
                                    <td>{{ucfirst($invited_by->name)}} {{ucfirst($invited_by->surname)}}</td>
                                    <td>
                                        {{$gate_pass->entry}}
                                    </td>
                                    <td>
                                       {{$gate_pass->exit}}
                                    </td>
                                    <td><span class="label label-{{($gate_pass->authorization == 'approved') ? 'success' : (($gate_pass->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($gate_pass->authorization == 'approved') ? 'approved' : (($gate_pass->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td> 
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('gate_passes.show', $gate_pass->id) }}" ><i class="fa fa-eye color-default"></i> View</a></li>
                                                <li><a href="#"  wire:click="edit({{$gate_pass->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#gate_passDeleteModal{{ $gate_pass->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('gate_passes.delete')
                                </td>
                                  </tr>
                                  @empty
                                    <tr>
                                        <td colspan="8">
                                            <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                No Individual Gate Passes Found ....
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
                                    @if (isset($individual_gate_passes))
                                        {{ $individual_gate_passes->links() }} 
                                    @endif 
                                </ul>
                            </nav>  
                            </div>
                            <div role="tabpanel" class="tab-pane" id="trips">
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <caption>Trip Gatepass</caption>
                                <thead>
                                  <tr>
                                    <th class="th-sm">GatePass#
                                    </th>
                                    <th class="th-sm">Trip
                                    </th>
                                     <th class="th-sm">Entry
                                    </th>
                                    <th class="th-sm">Exit
                                    </th>
                                    <th class="th-sm">Workshop
                                    </th>
                                    <th class="th-sm">Logistics
                                    </th>
                                    <th class="th-sm">Auth
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($trip_gate_passes))
                                <tbody>
                                    @forelse ($trip_gate_passes as $gate_pass)
                                  
                                  <tr>
                                    @php
                                        $workshop = App\Models\Employee::find($gate_pass->workshop_authorized_by_id);
                                        $logistics = App\Models\Employee::find($gate_pass->logistics_authorized_by_id);
                                        $authorized_by = App\Models\Employee::find($gate_pass->authorized_by_id);
                                        $trip = App\Models\Trip::find($gate_pass->trip->id);
                                    @endphp
                                    <td>{{$gate_pass->gate_pass_number}}</td>
                                    <td>
                                        @if (isset($trip))
                                        @php
                                             $from = App\Models\Destination::find($trip->from);
                                             $to = App\Models\Destination::find($trip->to);
                                        @endphp
                                            
                                            <strong>Trip Number:</strong>{{$gate_pass->trip ? $gate_pass->trip->trip_number : ""}}
                                            @if ($gate_pass->horse)
                                            <br>
                                            <strong>Horse:</strong>   {{$gate_pass->horse->horse_make ? $gate_pass->horse->horse_make->name : ""}} {{$gate_pass->horse->horse_model ? $gate_pass->horse->horse_model->name : ""}} {{$gate_pass->horse ? $gate_pass->horse->registration_number : ""}} {{$gate_pass->horse->fleet_number ? "(".$gate_pass->horse->fleet_number.")" : ""}}
                                            @endif
                                            @if ($gate_pass->trailers)
                                            <br>
                                            <strong>Trailers:</strong>
                                              [
                                                @foreach ($gate_pass->trailers as $trailer)
                                                   {{$trailer->registration_number}} 
                                                @endforeach
                                              ] 
                                            @endif
                                          
                                            @if (isset($gate_pass->driver))
                                               <br>
                                                <strong>Driver:</strong> 
                                                {{ucfirst($gate_pass->driver->employee ? $gate_pass->driver->employee->name : "")}} {{ucfirst($gate_pass->driver->employee ? $gate_pass->driver->employee->surname : "")}}
                                            @endif
                                            <br>
                                            <strong>From:</strong> {{$trip->loading_point ? $trip->loading_point->name.", " : ""}} {{$from->city}} {{$from->country ? $from->country->name : ""}}    <strong>To:</strong> {{$trip->offloading_point ? $trip->offloading_point->name.", " : ""}} {{$to->city}} {{$to->country ? $to->country->name : ""}}
                                            <br>
                                          

                                            <br>
                                        @endif 
                                    </td>
                                    <td>
                                        {{$gate_pass->entry}}
                                    </td>
                                    <td>
                                        {{$gate_pass->exit}}
                                    </td>
                                    <td><span class="label label-{{($gate_pass->workshop_authorization == 'approved') ? 'success' : (($gate_pass->workshop_authorization == 'rejected') ? 'danger' : 'warning') }}">{{($gate_pass->workshop_authorization == 'approved') ? 'approved' : (($gate_pass->workshop_authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td> 
                                    <td><span class="label label-{{($gate_pass->logistics_authorization == 'approved') ? 'success' : (($gate_pass->logistics_authorization == 'rejected') ? 'danger' : 'warning') }}">{{($gate_pass->logistics_authorization == 'approved') ? 'approved' : (($gate_pass->logistics_authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td> 
                                   
                                    <td><span class="label label-{{($gate_pass->authorization == 'approved') ? 'success' : (($gate_pass->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($gate_pass->authorization == 'approved') ? 'approved' : (($gate_pass->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td> 
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('gate_passes.show', $gate_pass->id) }}" ><i class="fa fa-eye color-default"></i> View</a></li>
                                                <li><a href="#"  wire:click="edit({{$gate_pass->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#gate_passDeleteModal{{ $gate_pass->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('gate_passes.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                      <td colspan="11">
                                          <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                              No Trip Gate Passes Found ....
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
                                    @if (isset($trip_gate_passes))
                                        {{ $trip_gate_passes->links() }} 
                                    @endif 
                                </ul>
                            </nav>    
                            </div>

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>


    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="gate_passModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Create Gate Pass <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Branches</label>
                                <select class="form-control" wire:model.debounce.300ms="selectedBranch">
                                    <option value="">Select Branch</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                @error('selectedBranch') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Gate</label>
                                <select class="form-control" wire:model.debounce.300ms="gate_id">
                                    <option value="">Select Gate</option>
                                    @foreach ($gates as $gate)
                                        <option value="{{ $gate->id }}">{{ $gate->name }}</option>
                                    @endforeach
                                </select>
                                <small><a href="#" data-toggle="modal" data-target="#gateModal" ><i class="fa fa-plus-square-o"></i> New Gate</a></small>
                                @error('gate_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                 
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Companies / Groups</label>
                                <select class="form-control" wire:model.debounce.300ms="group_id">
                                    <option value="">Select Company / Group</option>
                                    @foreach ($groups as $group)
                                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                                    @endforeach
                                </select>
                                <small>  <a href="#" data-toggle="modal" data-target="#groupModal" ><i class="fa fa-plus-square-o"></i> New Company / Group</a></small> 
                                @error('group_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Visitors</label>
                                <select class="form-control" wire:model.debounce.300ms="visitor_id">
                                    <option value="">Select Visitor</option>
                                    @foreach ($visitors as $visitor)
                                        <option value="{{ $visitor->id }}">{{ $visitor->name }} {{ $visitor->surname }}</option>
                                    @endforeach
                                </select>
                                 <small><a href="#" data-toggle="modal" data-target="#visitorModal" ><i class="fa fa-plus-square-o"></i> New Visitor</a></small>
                                @error('visitor_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                               
                            </div>
                        </div>
                       
                    </div>
                   
                    <div class="form-group">
                        <label for="name">Invited By</label>
                        <select class="form-control" wire:model.debounce.300ms="invited_by_id">
                            <option value="">Select Employee</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }} {{ $employee->surname }}</option>
                            @endforeach
                        </select>
                        @error('invited_by_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                   
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Entry<span class="required" style="color: red">*</span></label>
                                <input type="datetime-local" class="form-control" wire:model.debounce.300ms="entry" placeholder="Enter Entry Time" required />
                                @error('entry') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Exit</label>
                                <input type="datetime-local" class="form-control" wire:model.debounce.300ms="exit" placeholder="Enter Exit Time" />
                                @error('exit') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name">Reason</label>
                       <textarea wire:model.debounce.300ms="reason" class="form-control" cols="30" rows="4"></textarea>
                        @error('reason') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-10">
                            <input type="checkbox" wire:model.debounce.300ms="acknowledgement"   class="line-style blue-style" />
                            <label for="one" class="radio-label">I was briefed of the safety instructions before entry</label>
                            @error('acknowledgement') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                    {{-- @if ($acknowledgement == True)
                         <div style="margin-top: 10px;">
                            <label>Signature<span class="required" style="color: red">*</span></label><br>
                           <canvas id="signature-pad" style="border: 1px solid #ccc; width: 100%; max-width: 400px; height: 200px;"></canvas>
                            <input type="hidden" id="signature" wire:model.defer="signature" required>
                            @error('signature') <span style="color:red">{{ $message }}</span> @enderror
                            <br>
                            <button type="button" onclick="clearPad()">Clear</button>
                        </div>
                    @endif --}}
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="gate_passEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Gate Pass <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Type</label>
                            <select class="form-control" wire:model.debounce.300ms="type" disabled>
                                <option value="">Select Type</option>
                                <option value="Individual">Individual</option>
                                <option value="Trip">Trip</option>
                            </select>
                            @error('type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Branches</label>
                                    <select class="form-control" wire:model.debounce.300ms="selectedBranch">
                                        <option value="">Select Branch</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedBranch') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Gate</label>
                                    <select class="form-control" wire:model.debounce.300ms="gate_id">
                                        <option value="">Select Gate</option>
                                        @foreach ($gates as $gate)
                                            <option value="{{ $gate->id }}">{{ $gate->name }}</option>
                                        @endforeach
                                    </select>
                                    <small>  <a href="#" data-toggle="modal" data-target="#gateModal" ><i class="fa fa-plus-square-o"></i> New Gate</a></small> 
                                    @error('gate_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        @if ($type == "Individual")
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Groups</label>
                                    <select class="form-control" wire:model.debounce.300ms="group_id">
                                        <option value="">Select Group</option>
                                        @foreach ($groups as $group)
                                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                                        @endforeach
                                    </select>
                                    <small>  <a href="{{ route('groups.index') }}" target="_blank" ><i class="fa fa-plus-square-o"></i> New Group</a></small> 
                                    @error('group_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Visitors</label>
                                    <select class="form-control" wire:model.debounce.300ms="visitor_id">
                                        <option value="">Select Visitor</option>
                                        @foreach ($visitors as $visitor)
                                            <option value="{{ $visitor->id }}">{{ $visitor->name }} {{ $visitor->surname }}</option>
                                        @endforeach
                                    </select>
                                    @error('visitor_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    <small>  <a href="{{ route('visitors.index') }}" target="_blank" ><i class="fa fa-plus-square-o"></i> New Visitor</a></small> 
                                </div>
                            </div>
                           
                        </div>
                       
                        <div class="form-group">
                            <label for="name">Invited By</label>
                            <select class="form-control" wire:model.debounce.300ms="invited_by_id">
                                <option value="">Select Employee</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->employee_number }} {{ $employee->name }} {{ $employee->surname }}</option>
                                @endforeach
                            </select>
                            @error('invited_by_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        @elseif ($type == "Trip")
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Trips</label>
                                    <select class="form-control" wire:model.debounce.300ms="trip_id">
                                        <option value="">Select Trip</option>
                                        @foreach ($trips as $trip)
                                            <option value="{{ $trip->id }}">{{$trip->trip_number}} | {{$trip->loading_point ? $trip->loading_point->name : ""}}-{{$trip->offloading_point ? $trip->offloading_point->name : ""}} | {{$trip->customer ? $trip->customer->name : ""}}</option>
                                        @endforeach
                                    </select>
                                    @error('trip_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Horses</label>
                                    <select class="form-control" wire:model.debounce.300ms="horse_id" disabled>
                                        <option value="">Select Horse</option>
                                        @foreach ($horses as $horse)
                                            <option value="{{ $horse->id }}">{{ $horse->registration_number }}</option>
                                        @endforeach
                                    </select>
                                    @error('horse_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                           
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="stops"><a href="{{ route('trailers.index') }}" target="_blank" style="color: blue">Trailer(s)</a></label>
                                            <select class="form-control" wire:model.debounce.300ms="trailer_id" multiple disabled>
                                              <option value="">Select Trailer </option>
                                                @foreach ($trailers as $trailer)
                                                    <option value="{{$trailer->id}}">({{$trailer->registration_number}}) {{$trailer->make}} {{$trailer->model}} </option>
                                                @endforeach
                                            </select>
                                            @error('trailer_id') <span class="text-danger error">{{ $message }}</span>@enderror     
                                    </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Drivers</label>
                                    <select class="form-control" wire:model.debounce.300ms="driver_id" disabled>
                                        <option value="">Select Driver</option>
                                        @foreach ($drivers as $driver)
                                            <option value="{{ $driver->id }}">{{ $driver->employee ? $driver->employee->name : ""}} {{ $driver->employee ? $driver->employee->surname : "" }}</option>
                                        @endforeach
                                    </select>
                                    @error('driver_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Entry</label>
                                    <input type="datetime-local" class="form-control" wire:model.debounce.300ms="entry" placeholder="Enter Entry Time"/>
                                    @error('entry') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Exit</label>
                                    <input type="datetime-local" class="form-control" wire:model.debounce.300ms="exit" placeholder="Enter Exit Time" />
                                    @error('exit') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="name">Reason</label>
                           <textarea wire:model.debounce.300ms="reason" class="form-control" cols="30" rows="4"></textarea>
                            @error('reason') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="groupModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Company / Group <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="storeGroup()" >
                <div class="modal-body">
            
                    <div class="form-group">
                        <label for="name">Name<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="group_name" placeholder="Enter Company / Group Name" required />
                        @error('group_name') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="gateModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Gate <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="storeGate()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Branches<span class="required" style="color: red">*</span></label>
                        <select class="form-control" wire:model.debounce.300ms="selectedBranch" required>
                            <option value="">Select Branch</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        @error('selectedBranch') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="name">Name<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="gate_name" placeholder="Enter Gate Name" required />
                        @error('gate_name') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="visitorModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Visitor <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="storeVisitor()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Groups</label>
                        <select class="form-control" wire:model.debounce.300ms="group_id">
                            <option value="">Select Group</option>
                            @foreach ($groups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                        @error('group_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Name" required />
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Surname<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="surname" placeholder="Enter Surname" required />
                                @error('surname') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                  
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">ID Number</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="idnumber" placeholder="Enter Id Number"  />
                                @error('idnumber') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Phonenumber</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="phonenumber" placeholder="Enter Phonenumber"  />
                                @error('phonenumber') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

</div>

