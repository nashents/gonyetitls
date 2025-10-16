<div>
    <style>
        .modal-lg {
        max-width: 80%;
    }
    </style>
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
                                
                            </div>
                            <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                                <div class="panel-title">
                                    <h5>Date Range</h5>
                                    <div class="row">
                                    <div class="col-lg-2" style="margin-right: 7px">
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                      From
                                      </span>
                                      <input type="date" wire:model.debounce.300ms="from"  class="form-control" aria-label="...">
                                        </div>
                                        <!-- /input-group -->
                                    </div>
                                    <div class="col-lg-2" style="margin-left: 7px">
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                      To
                                      </span>
                                      <input type="date" wire:model.debounce.300ms="to"  class="form-control" aria-label="...">
                                        </div>
                                        <!-- /input-group -->
                                    </div>
                             
                                   
                                    <!-- /input-group -->
                                </div>
                              
                                </div>
                                <div class="row">
                                        <div class="col-md-3">
                                            <div class="input-group">
                                                <span class="input-group-addon">Ticket Status</span>
                                                <select wire:model.debounce.300ms="booking_status" class="form-control" aria-label="..." >
                                                    <option value="all">All</option>
                                                    <option value="0">Closed</option>
                                                    <option value="1">Open</option>
                                                </select>
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                        <div class="col-md-3">
                                            <div class="input-group">
                                                <span class="input-group-addon">Service Types</span>
                                                <select wire:model.debounce.300ms="service_type_id" class="form-control" aria-label="..." >
                                                    <option value="all">Select Type</option>
                                                    @foreach ($service_types as $service_type)
                                                        <option value="{{ $service_type->id }}">{{ $service_type->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                        <div class="col-md-3">
                                            <div class="input-group">
                                                <span class="input-group-addon">Stations</span>
                                                <select wire:model.debounce.300ms="station_id" class="form-control" aria-label="..." >
                                                    <option value="all">Select Station</option>
                                                    @foreach ($stations as $station)
                                                        <option value="{{ $station->id }}">{{ $station->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                        <div class="col-md-3">
                                            <div class="input-group">
                                                <span class="input-group-addon">Mechanics</span>
                                                <select wire:model.debounce.300ms="employee_id" class="form-control" aria-label="..." >
                                                    <option value="all">Select Mechanic</option>
                                                    @foreach ($employees as $employee)
                                                        <option value="{{ $employee->id }}">{{ $employee->name }} {{ $employee->surname }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                      
                                </div>
                                <div class="row">
                                      <div class="col-md-3">
                                            <div class="input-group">
                                                <span class="input-group-addon">Equipment</span>
                                                <select wire:model.debounce.300ms="filter" class="form-control" aria-label="..." >
                                                    <option value="">Select Option</option>
                                                    <option value="asset">Asset</option>
                                                    <option value="horse">Horse</option>
                                                    <option value="trailer">Trailer</option>
                                                    <option value="vehicle">Vehicle</option>
                                                </select>
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                        @if ($filter)
                                             <div class="col-md-3">
                                                <div class="input-group">
                                                    @if ($filter == "horse")
                                                         <span class="input-group-addon">
                                                            Horses
                                                        </span>
                                                        <select wire:model.debounce.300ms="selectedHorse" class="form-control" aria-label="..." >
                                                            <option value="">Select Horse</option>
                                                            @foreach ($horses as $horse)
                                                                <option value="{{ $horse->id }}">{{ $horse->registration_number }} {{ $horse->fleet_number ? "(".$horse->fleet_number.")" : "" }}</option>
                                                            @endforeach
                                                        </select>
                                                    @elseif($filter == "vehicle")
                                                         <span class="input-group-addon">
                                                            Horses
                                                        </span>
                                                        <select wire:model.debounce.300ms="selectedVehicle" class="form-control" aria-label="..." >
                                                            <option value="">Select Vehicle</option>
                                                            @foreach ($vehicles as $vehicle)
                                                                <option value="{{ $vehicle->id }}">{{ $vehicle->registration_number }} {{ $vehicle->fleet_number ? "(".$vehicle->fleet_number.")" : "" }}</option>
                                                            @endforeach
                                                        </select>
                                                    @elseif($filter == "asset")
                                                         <span class="input-group-addon">
                                                            Assets
                                                        </span>
                                                        <select wire:model.debounce.300ms="selectedAsset" class="form-control" aria-label="..." >
                                                            <option value="">Select Asset</option>
                                                            @foreach ($assets as $asset)
                                                                @if ($asset->product)
                                                                    <option value="{{ $asset->id }}">{{ $asset->product ? $asset->product->name : "" }} {{ $asset->product->identification_number ? "(".$asset->product->identification_number.")" : "" }} </option>
                                                                @endif
                                                            @endforeach
                                                        </select>
                                                    @elseif($filter == "trailer")
                                                         <span class="input-group-addon">
                                                            Trailers
                                                        </span>
                                                        <select wire:model.debounce.300ms="selectedTrailer" class="form-control" aria-label="..." >
                                                            <option value="">Select Trailer</option>
                                                            @foreach ($trailers as $trailer)
                                                                <option value="{{ $trailer->id }}">{{ $trailer->registration_number }} {{ $trailer->fleet_number ? "(".$trailer->fleet_number.")" : "" }}</option>
                                                            @endforeach
                                                        </select>
                                                    @endif
                                                   
                                                </div>
                                            <!-- /input-group -->
                                            </div>
                                        @endif
                                </div>
                                <div class="panel-title">
                                    <a href="{{route('bookings.create')}}"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Booking</a>
                                    <a href="#" wire:click="exportBookingsExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                    <a href="#" wire:click="exportBookingsCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                    <a href="#" wire:click="exportBookingsPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a> 
                                    <br>
                                    <br>
                                   
                                </div>

                       

                                <div class="col-md-5" style="float: right; padding-right:2px">
                                    <div class="form-group">
                                        <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search Bookings...">
                                    </div>
                                </div>

                                @if ($selectedRows)
                                <br>
                                <br>
                                <div class="row">
                                    <div class="col-lg-2" >
                                        <div class="dropdown">
                                            <button class="btn btn-default border-primary btn-rounded btn-wide dropdown-toggle" type="button" id="menu12" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                <i class="fa fa-bars"></i> Bulk Actions
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu bg-gray" aria-labelledby="menu12">
                                                <li><a href="#"  wire:click="showBulkyAuthorize()"><i class="fa fa-gavel"></i>Close Booking(s)</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-lg-3" style="margin-top: 5px; margin-left: -30px;">
                                    <span >selected {{ count($selectedRows) }} booking(s) to close.</span>
                                    </div>
                                </div>
                                <br>
                                @endif

                                <table  class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <thead >
                                        <tr>
                                        <th class="th-sm">
                                            <input type="checkbox" wire:model.debounce.300ms="selectPageRows" >
                                        </th>
                                        <th class="th-sm">Booking#
                                        </th>
                                        <th class="th-sm">RequestedBy
                                        </th>
                                        <th class="th-sm">AssignedTo
                                        </th>
                                        <th class="th-sm">BookingFor
                                        </th>
                                        <th class="th-sm" style="width: 20%">Timelines
                                        </th>
                                        <th class="th-sm" style="width: 15%">Narration
                                        </th>
                                        <th class="th-sm">Station
                                        </th>
                                        <th class="th-sm">Auth
                                        </th>
                                        <th class="th-sm">Status
                                        </th>
                                        <th class="th-sm">Actions
                                        </th>
                                      </tr>
                                    </thead>
                                    @if (isset($bookings))
                                    <tbody>
                                        @forelse  ($bookings as $booking)
                                        
                                      <tr>
                                        <td><input type="checkbox" wire:model.debounce.300ms="selectedRows" id="{{ $booking->id }}" value="{{ $booking->id }}"></td>
                                        <td>
                                            {{$booking->booking_number}}
                                            <br>
                                            <small><strong>CreatedBy: </strong> {{ucfirst($booking->user ? $booking->user->name : "")}} {{ucfirst($booking->user ? $booking->user->surname : "")}}</small>
                                        </td>
                                        <td>{{ucfirst($booking->employee ? $booking->employee->name : "")}} {{ucfirst($booking->employee ? $booking->employee->surname : "")}}</td>
                                        <td>
                                            @if (isset($booking->employees) && $booking->employees->count()>0)
                                                @foreach ($booking->employees as $mechanic)
                                                    {{ $mechanic->name }} {{ $mechanic->surname }}
                                                    <br>
                                                @endforeach
                                            @elseif(isset($booking->vendor))
                                                {{ucfirst($booking->vendor->name)}}  
                                            @endif
                                        </td>
                                        <td>
                                            @if (isset($booking->horse))
                                                Horse |  {{$booking->horse->registration_number}} {{$booking->horse->fleet_number ? "(".$booking->horse->fleet_number.")" : ""}}
                                                @elseif(isset($booking->vehicle))
                                                Vehicle |  {{$booking->vehicle->registration_number}} {{$booking->vehicle->fleet_number ? "(".$booking->vehicle->fleet_number.")" : ""}}
                                                @elseif(isset($booking->asset))
                                                Asset | {{$booking->asset->product->brand ? $booking->asset->product->brand->name : ""}} {{ucfirst($booking->asset->product ? $booking->asset->product->name : "")}}  {{$booking->asset->serial_number}}
                                                @elseif(isset($booking->trailer))
                                                Trailer | {{$booking->trailer->registration_number}} {{$booking->trailer->fleet_number ? "(".$booking->trailer->fleet_number.")" : ""}} 
                                            @endif
                                        </td>
                                         <td>
                                            <strong>In: </strong> {{$booking->in_date}} {{$booking->in_time}} <br>
                                            <strong>Estimate: </strong> {{$booking->estimated_out_date}} {{$booking->estimated_out_time}} <br>
                                            <strong>Completed: </strong> {{$booking->out_date}} {{$booking->out_time}} <br>
                                            <strong>Out: </strong> {{$booking ? $booking->workshop_out_date : ""}} {{$booking ? $booking->workshop_out_time : ""}} <br>
                                        </td>
                                        <td>
                                            <strong>
                                                Service Type: 
                                            </strong>{{ucfirst($booking->service_type ? $booking->service_type->name : "")}} <br>
                                            {{Str::limit($booking->description,100,'...')}}</td>
                                        <td>{{ optional(\App\Models\Station::find($booking->station_id))->name ?? $booking->station }}</td>
                                        <td><span class="badge bg-{{($booking->authorization == 'approved') ? 'success' : (($booking->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($booking->authorization == 'approved') ? 'approved' : (($booking->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                        <td><span class="badge bg-{{$booking->status == 1 ? "warning" : "success"}}">{{$booking->status == 1 ? "Open" : "Closed"}}</span></td>
                                        <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    @php
                                                        $roles = Auth::user()->roles;
                                                        foreach($roles as $role){
                                                            $role_names[] = $role->name;
                                                        }
                                                    @endphp             
                                                    <li><a href="{{route('bookings.show', $booking->id)}}"><i class="fa fa-eye color-default"></i>View</a></li>
                                                    <li><a href="{{route('bookings.edit', $booking->id)}}"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                    @if ($booking->status == 1)
                                                    {{-- <li><a href="#"  wire:click="showBooking({{$booking->id}})"><i class="fa fa-window-close color-success"></i> Close Booking</a></li> --}}
                                                    @endif
                                                    @if ($booking->authorization == "pending" || $booking->authorization == "rejected")
                                                    <li><a href="#" data-toggle="modal" data-target="#bookingDeleteModal{{$booking->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                                    @elseif (in_array('Super Admin', $role_names))
                                                    <li><a href="#" data-toggle="modal" data-target="#bookingDeleteModal{{$booking->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                                    @endif
                                                   

                                                </ul>
                                            </div>
                                            @include('bookings.delete')

                                    </td>
                                      </tr>
                                      @empty
                                      <tr>
                                        <td colspan="11">
                                            <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                No Bookings Found ....
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
                                        @if (isset($bookings))
                                            @if ($bookings->count()>0)
                                                {{ $bookings->links() }} 
                                            @endif
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


        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="bulkyCloseTicketModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fas fa-window-close"></i> Close Booking(s)<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="authorizeSelectedRows()" >
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Decision</label>
                        <select class="form-control" wire:model.debounce.300ms="status" required>
                            <option value="">Select Decision</option>
                            <option value="0">Close</option>
                            <option value="1">Open</option>
                        </select>
                            @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="reason">Comments</label>
                           <textarea wire:model.debounce.300="comments" class="form-control" cols="30" rows="5"></textarea>
                            @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="closeTicketModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fas fa-window-close"></i> Close Booking {{ $booking ? $booking->booking_number : "" }}<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                        <p> Assigned to | 
                            @if (isset($booking->employees) && $booking->employees->count()>0)
                                @foreach ($booking->employees as $employee)
                                    {{ $employee->name }} {{ $employee->surname }},
                                @endforeach
                            @endif 
                        </p>
                    </div>
                    <form wire:submit.prevent="closeBooking()" >
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Decision</label>
                        <select class="form-control" wire:model.debounce.300ms="status" required>
                            <option value="">Select Decision</option>
                            <option value="0">Close</option>
                            <option value="1">Open</option>
                        </select>
                            @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="reason">Comments</label>
                           <textarea wire:model.debounce.300="comments" class="form-control" cols="30" rows="5"></textarea>
                            @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
