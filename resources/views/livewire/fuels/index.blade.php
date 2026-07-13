<div>
    <style id="fuel-orders-ui-polish">
        .fuel-toolbar { margin-bottom: 15px; }
        .fuel-toolbar .input-group { margin-bottom: 8px; }
        .fuel-meta, .auth-meta { display: block; color: #6c757d; line-height: 1.35; }
        .fuel-balance-card { border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px 12px; background: #f9fafb; margin-top: 8px; }
        .fuel-balance-card strong { display: block; font-size: 12px; color: #6c757d; text-transform: uppercase; }
        .fuel-balance-card span { font-size: 16px; font-weight: 700; }
        .fuel-form-section { border: 1px solid #e5e7eb; border-radius: 6px; padding: 15px; margin-bottom: 15px; background: #fff; }
        .fuel-form-section-title { font-size: 14px; font-weight: 700; margin-bottom: 12px; color: #2f3640; border-bottom: 1px solid #eee; padding-bottom: 8px; }
        .fuel-table th { white-space: nowrap; }
        .fuel-table td { vertical-align: top !important; }
        .row-fillup-initial { border-left: 4px solid #28a745; }
        .row-fillup-topup { border-left: 4px solid #ffc107; }
        .amount-cell, .qty-cell { text-align: right; white-space: nowrap; }
        .guard-alert ul { margin-bottom: 0; padding-left: 20px; }
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
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="panel-title">
                                <h5>Fuel Orders Management</h5>
                                <div class="row">
                                    <div class="col-lg-3">
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                        Fueling Station
                                      </span>
                                      <select wire:model.debounce.300ms="container_id" class="form-control" aria-label="..." >
                                        <option value="">Select Fueling Station</option>
                                        @foreach ($containers as $container)
                                        <option value="{{ $container->id }}">{{ $container->name }}</option>
                                        @endforeach 
                                     </select>
                                        </div>
                                        <!-- /input-group -->
                                    </div>
                                <div class="col-lg-3">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  Filter By
                                  </span>
                                  <select wire:model.debounce.300ms="fuel_filter" class="form-control" aria-label="..." >
                                    <option value="created_at">Order Created At</option>
                              </select>
                                    </div>
                                    <!-- /input-group -->
                                </div>
                        
                               
                                <!-- /input-group -->
                            </div>
                            <div class="row">
                                @if ($fuel_filter == "created_at")
                                <div class="col-lg-2" style="margin-right: 1px">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  From
                                  </span>
                                  <input type="date" wire:model.debounce.300ms="from"  class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                <div class="col-lg-2" >
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  To
                                  </span>
                                  <input type="date" wire:model.debounce.300ms="to"  class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                @elseif ($fuel_filter == "start_date")
                                <div class="col-lg-2" style="margin-right:1px">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  From
                                  </span>
                                  <input type="datetime-local" wire:model.debounce.300ms="from"  class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                <div class="col-lg-2" style="margin-left: 1px">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  To
                                  </span>
                                  <input type="datetime-local" wire:model.debounce.300ms="to"  class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                @endif
                            </div>
                            <a href="" data-toggle="modal" data-target="#fuelModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Fuel Order</a>
                            <a href="#" wire:click="exportFuelsExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                            <a href="#" wire:click="exportFuelsCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                            <a href="#" wire:click="exportFuelsPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                            
                            </div>
                            <div class="col-md-5" style="float: right;">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search Fuel Orders....">
                                </div>
                               
                            </div>
                            <br>
                            {{-- Legend --}}
                            <div class="mb-10" style="font-size:12px;">
                                <span class="badge badge-success">&nbsp;</span> Initial fill
                                &nbsp;&nbsp;
                                <span class="badge badge-warning">&nbsp;</span> Top Up
                            </div>

                            <table class="table table-hover table-bordered table-sm align-middle" cellspacing="0" width="100%">
                                <thead class="thead-light">
                                    <tr>
                                        <th>FOrder#</th>
                                        <th>Fuel Request</th>
                                        <th>Date</th>
                                        <th>Order For</th>
                                        <th>Station</th>
                                        <th>Fill Up</th>
                                        <th class="text-right">Qty (l)</th>
                                        <th class="text-right">Amount</th>
                                        <th>Auth</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @isset($fuels)
                                        @forelse($fuels as $fuel)
                                            @php
                                                $isInitial   = $fuel->fillup == 1;
                                                $rowAccent    = $isInitial ? '#E8F5E9' : '#FFF8E1'; // light green / light amber
                                                $borderAccent = $isInitial ? '#2E7D32' : '#F57C00'; // darker green / darker amber
                                                $fillupLabel = $isInitial ? 'Initial' : 'Top Up';
                                                $fillupBadge = $isInitial ? 'success' : 'warning';

                                                $datePattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                                $formattedDate = preg_match($datePattern, $fuel->date)
                                                    ? \Carbon\Carbon::parse($fuel->date)->format('d M Y g:i A')
                                                    : $fuel->date;

                                                $fuelRequest = $fuel->fuel_request ?? null;
                                            @endphp

                                            <tr 
                                                style="background-color:{{ $rowAccent }}; border-left: 6px solid {{ $borderAccent }};"
                                            >

                                                {{-- FOrder# --}}
                                                <td>
                                                    <strong>{{ $fuel->order_number }}</strong>
                                                    <div class="fuel-meta text-muted">
                                                        <small><strong>By:</strong> {{ optional($fuel->user)->name }} {{ optional($fuel->user)->surname }}</small><br>
                                                        <small><strong>On:</strong> {{ $fuel->created_at }}</small>
                                                    </div>
                                                </td>

                                                {{-- Fuel Request --}}
                                                <td>
                                                    @if($fuelRequest)
                                                        <a href="{{ route('fuel_requests.show', $fuelRequest->id) }}" target="_blank">
                                                            {{ $fuelRequest->request_number }}
                                                        </a><br>
                                                        <div class="fuel-meta text-muted">
                                                            <small><strong>Requested By:</strong> {{ optional($fuelRequest->employee)->name }} {{ optional($fuelRequest->employee)->surname }}</small><br>
                                                            <small>
                                                                <strong>Requested For:</strong>
                                                                @if($fuelRequest->horse) <br>
                                                                    {{ $fuelRequest->horse->registration_number }}
                                                                    {{ $fuelRequest->horse->fleet_number ? '(' . $fuelRequest->horse->fleet_number . ')' : '' }}
                                                                @elseif($fuelRequest->vehicle) <br>
                                                                    {{ $fuelRequest->vehicle->registration_number }}
                                                                    {{ $fuelRequest->vehicle->fleet_number ? '(' . $fuelRequest->vehicle->fleet_number . ')' : '' }}
                                                                @elseif($fuelRequest->asset) <br>
                                                                    {{ optional($fuelRequest->asset->product->brand)->name }}
                                                                    {{ optional($fuelRequest->asset->product)->name }}
                                                                @else
                                                                    Other
                                                                @endif
                                                            </small><br>
                                                            <small><strong>Fuel Type:</strong> {{ $fuelRequest->fuel_type }}</small><br>
                                                            <small><strong>Requested Qty:</strong> {{ $fuelRequest->quantity ? $fuelRequest->quantity . 'l' : '-' }}</small>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>

                                                {{-- Date --}}
                                                <td><small>{{ $formattedDate }}</small></td>

                                                {{-- Order For --}}
                                                <td>
                                                    @if($fuel->type === 'Horse' && isset($fuel->horse))
                                                        <span class="badge badge-info">Horse</span>
                                                        {{ $fuel->horse->registration_number }}
                                                        {{ $fuel->horse->fleet_number ? '(' . $fuel->horse->fleet_number . ')' : '' }}
                                                    @elseif($fuel->type === 'Vehicle' && isset($fuel->vehicle))
                                                        <span class="badge badge-info">Vehicle</span>
                                                        {{ $fuel->vehicle->registration_number }}
                                                        {{ $fuel->vehicle->fleet_number ? '(' . $fuel->vehicle->fleet_number . ')' : '' }}
                                                    @elseif($fuel->type === 'Asset' && isset($fuel->asset))
                                                        <span class="badge badge-info">Asset</span>
                                                        {{ optional($fuel->asset->product->brand)->name }}
                                                        {{ optional($fuel->asset->product)->name }}
                                                    @elseif($fuel->type === 'Other')
                                                        <span class="badge badge-secondary">Other</span>
                                                    @endif

                                                    @if(in_array($fuel->type, ['Horse', 'Vehicle']) && isset($fuel->trip))
                                                        @php
                                                            $from = App\Models\Destination::find($fuel->trip->from);
                                                            $to   = App\Models\Destination::find($fuel->trip->to);
                                                        @endphp
                                                        <div class="fuel-meta text-muted">
                                                            <small><strong>Trip:</strong> {{ $fuel->trip->trip_number }}{{ $fuel->trip->trip_ref ? '/' . $fuel->trip->trip_ref : '' }}
                                                            {{ optional($from?->country)->name }} {{ $from?->city }} —
                                                            {{ optional($to?->country)->name }} {{ $to?->city }}</small>
                                                        </div>
                                                    @endif
                                                    @if ($fuel->comments)
                                                        <div class="fuel-meta text-muted">
                                                            <small><strong>Comments:</strong> {{ $fuel->comments }}</small>
                                                        </div>
                                                    @endif
                                                </td>

                                                {{-- Station --}}
                                                <td>
                                                    @if ($fuel->container)
                                                        {{ ucfirst($fuel->container->name) }}
                                                    @elseif ($fuel->source_horse)
                                                        Truck: {{ $fuel->source_horse->registration_number }} {{ $fuel->source_horse->fleet_number ? "(".$fuel->source_horse->fleet_number.")" : "" }}
                                                    @endif
                                                    <div class="fuel-meta text-muted">
                                                        <small><strong>Fuel Type:</strong> {{ $fuel->container->fuel_type ?? $fuel->fuel_type ?? '' }}</small>
                                                    </div>
                                                </td>

                                                {{-- Fill Up --}}
                                                <td><span class="badge badge-{{ $fillupBadge }}">{{ $fillupLabel }}</span></td>

                                                {{-- Qty --}}
                                                <td class="text-right">
                                                    <span class="font-weight-bold">{{ number_format($fuel->quantity ?? 0, 2) }}</span>
                                                    <div>
                                                        @if($fuel->is_full_tank)
                                                            <span class="badge badge-success mt-1">Full Tank</span>
                                                        @else
                                                            <span class="badge badge-warning mt-1">Partial</span>
                                                        @endif
                                                    </div>
                                                </td>

                                                {{-- Amount --}}
                                                <td class="text-right">
                                                    {{ optional($fuel->currency)->symbol }}{{ number_format($fuel->amount ?? 0, 2) }}
                                                    <div class="fuel-meta text-muted"><small>{{ optional($fuel->currency)->name }}</small></div>
                                                </td>

                                                {{-- Authorization --}}
                                                <td>
                                                    @php
                                                        $authStatus = $fuel->authorization;
                                                        $badgeClass = match($authStatus) {
                                                            'approved' => 'success',
                                                            'rejected' => 'danger',
                                                            default    => 'warning',
                                                        };
                                                    @endphp
                                                    <span class="badge badge-{{ $badgeClass }}">{{ ucfirst($authStatus ?? 'pending') }}</span>

                                                    @if($fuel->authorized_by_id)
                                                        @php $authorizer = App\Models\User::find($fuel->authorized_by_id); @endphp
                                                        <div class="fuel-meta text-muted"><small><strong>Auth By:</strong> {{ optional($authorizer)->name }} {{ optional($authorizer)->surname }}</small></div>
                                                    @endif
                                                    @if($fuel->authorization_date)
                                                        <div class="fuel-meta text-muted"><small><strong>Date:</strong> {{ $fuel->authorization_date }}</small></div>
                                                    @endif
                                                    @if($fuel->reason)
                                                        <div class="fuel-meta text-muted"><small><strong>Comments:</strong> {{ $fuel->reason }}</small></div>
                                                    @endif
                                                </td>

                                                {{-- Actions --}}
                                                <td class="text-center line-height-35 table-dropdown">
                                                    <div class="dropdown">
                                                        <button class="btn btn-default btn-sm dropdown-toggle" type="button"
                                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="fa fa-bars"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-right">
                                                            <li>
                                                                <a href="{{ route('fuels.show', $fuel->id) }}">
                                                                    <i class="fa fa-eye color-default"></i> View
                                                                </a>
                                                            </li>
                                                            @if($fuel->authorization === 'approved')
                                                                <li>
                                                                    <a href="{{ route('fuels.preview', $fuel->id) }}">
                                                                        <i class="fa fa-file-invoice color-primary"></i> Preview
                                                                    </a>
                                                                </li>
                                                            @endif
                                                            @if($fuel->authorization === 'pending' || in_array('Super Admin', $role_names))
                                                                <li>
                                                                    <a href="#" wire:click.prevent="edit({{ $fuel->id }})">
                                                                        <i class="fa fa-edit color-success"></i> Edit
                                                                    </a>
                                                                </li>
                                                            @endif
                                                            @if(
                                                                $fuel->authorization === 'pending'
                                                                || ($fuel->authorization === 'approved' && $fuel->authorized_by_id == Auth::id())
                                                            )
                                                                <li>
                                                                    <a href="#" wire:click.prevent="delete({{ $fuel->id }})">
                                                                        <i class="fa fa-trash color-danger"></i> Delete
                                                                    </a>
                                                                </li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                </td>

                                            </tr>

                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center text-muted py-3" style="font-size: 17px;">
                                                    No Fuel Orders Found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    @else
                                        <tr>
                                            <td colspan="10" class="text-center">
                                                <img src="{{ asset('images/nodata.png') }}" alt="No data" style="max-width: 300px; padding: 2rem 0;">
                                            </td>
                                        </tr>
                                    @endisset
                                </tbody>
                            </table>

                              <nav class="text-center" style="float: right">
                                <ul class="pagination rounded-corners">
                                    @if (isset($fuels))
                                        {{ $fuels->links() }} 
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                <center> <strong>Are you sure you want to delete this Fuel Order?</strong> </center>
                </div>
                <form wire:submit.prevent="destroy()" >
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="fuelModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-70" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Fuel Order <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="mb-10">
                        <input type="checkbox" wire:model.debounce.300ms="attach_fuel_request"   class="line-style" />
                        <label for="one" class="radio-label">Attach fuel request to this order.</label>
                        @error('attach_fuel_request') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                    @if (!is_null($attach_fuel_request) && $attach_fuel_request == "True")
                        <div class="form-group">
                            <label for="horses">Fuel Requisitions<span class="required" style="color: red">*</span></label>
                            <input type="text" wire:model.debounce.300ms="searchRequest" placeholder="Search requests..." class="form-control">
                            <select wire:model.debounce.300ms="selectedFuelRequest" class="form-control" required size="4">
                                <option value="">Select Fuel Request</option>
                                @foreach ($fuel_requests as $fuel_request)
                                    <option value="{{$fuel_request->id}}">CreatedBy: {{$fuel_request->user?->name}} {{$fuel_request->user?->surname}} RequestedBy: {{$fuel_request->employee?->name}} {{$fuel_request->employee?->surname}} RequestFor:
                                        @if ($fuel_request->horse)
                                            {{$fuel_request->horse?->registration_number}} {{$fuel_request->horse?->fleet_number ? "(".$fuel_request->horse?->fleet_number.")" : ""}}
                                        @elseif ($fuel_request->vehicle)
                                            {{$fuel_request->vehicle?->registration_number}} {{$fuel_request->vehicle?->fleet_number ? "(".$fuel_request->vehicle?->fleet_number.")" : ""}}
                                        @elseif ($fuel_request->asset)
                                            {{$fuel_request->asset->product->brand ? $fuel_request->asset->product->brand->name : ""}} {{$fuel_request->asset->product ? $fuel_request->asset->product->name : ""}}
                                        @else
                                            Other
                                        @endif
                                        FuelType: {{$fuel_request->fuel_type}} Qty: {{$fuel_request->quantity ? $fuel_request->quantity."l" : ""}}
                                    </option>
                                @endforeach
                            </select>
                            @error('selectedFuelRequest') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    @endif
                    <div class="row">
                    <div class="form-group">
                             <div class="col-sm-10">
                                <label for="gender">Fuel Order For?<span class="required" style="color: red">*</span></label>
                                <div class="mb-10">
                                    <input type="radio" wire:model.debounce.300ms="type" value="Horse"  class="line-style" required  />
                                    <label for="one" class="radio-label">Horse</label>
                                    <input type="radio" wire:model.debounce.300ms="type" value="Vehicle"  class="line-style" />
                                    <label for="one" class="radio-label">Vehicle</label>
                                    <input type="radio" wire:model.debounce.300ms="type" value="Asset"  class="line-style"  />
                                    <label for="one" class="radio-label">Asset</label>
                                    <input type="radio" wire:model.debounce.300ms="type" value="Other"  class="line-style"  />
                                    <label for="one" class="radio-label">Other</label>
                                </div>  
                            </div>
                            @error('type') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                     @if ($type == "Horse" || $type == "Vehicle")
                        <div class="row">
                            <div class="col-md-3">
                                @if ($type == "Horse")
                                    <div class="form-group">
                                        <label for="horses">Horses<span class="required" style="color: red">*</span></label>
                                        <input type="text" wire:model.debounce.300ms="searchHorse" placeholder="Search horses..." class="form-control">
                                        <select wire:model.debounce.300ms="selectedHorse" class="form-control" required size="4">
                                            <option value="">Select Horse</option>
                                            @foreach ($horses as $horse)
                                                <option value="{{$horse->id}}">{{$horse->registration_number}} {{$horse->fleet_number ? "(".$horse->fleet_number.")" : ""}}</option>
                                            @endforeach
                                        </select>
                                        @error('selectedHorse') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                @elseif($type == "Vehicle")
                                    <div class="form-group">
                                        <label for="vehicles">Vehicles<span class="required" style="color: red">*</span></label>
                                        <input type="text" wire:model.debounce.300ms="searchHorse" placeholder="Search horses..." class="form-control">
                                        <select wire:model.debounce.300ms="selectedVehicle" class="form-control" required size="4">
                                            <option value="">Select Vehicle</option>
                                            @foreach ($vehicles as $vehicle)
                                                <option value="{{$vehicle->id}}"> {{$vehicle->registration_number}} {{$vehicle->fleet_number ? "(".$vehicle->fleet_number.")" : ""}} </option>
                                            @endforeach
                                        </select>
                                        @error('selectedVehicle') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="employees">Employees(Driver)<span class="required" style="color: red">*</span></label>
                                    <input type="text" wire:model.debounce.300ms="searchEmployee" placeholder="Search employees..." class="form-control">
                                    <select wire:model.debounce.300ms="employee_id" class="form-control" required size="4">
                                        <option value="">Select Employee</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{$employee->id}}">{{ $employee->name }} {{$employee->surname}}</option>
                                        @endforeach
                                    </select>
                                    @error('employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="trips">Trips</label>
                                    <input type="text" wire:model.debounce.300ms="searchTrip" placeholder="Search trips..." class="form-control">
                                    <select wire:model.debounce.300ms="selectedTrip" class="form-control" size="4">
                                        <option value="">Select Trip</option>
                                            @foreach ($trips as $trip)
                                            <option value="{{$trip->id}}">{{ $trip->trip_number }}{{ $trip->trip_ref ? "/".$trip->trip_ref : "" }} | {{$trip->start_date}} |
                                                @if ($trip->horse)
                                                    {{ $trip->horse ? $trip->horse->registration_number : "" }} {{ $trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : "" }}   
                                                @endif
                                                @if ($trip->vehicle)
                                                    {{ $trip->vehicle ? $trip->vehicle->registration_number : "" }} {{ $trip->vehicle->fleet_number ? "(".$trip->vehicle->fleet_number.")" : "" }}   
                                                @endif
                                                |
                                                @php
                                                    $from = $trip->fromDestination ?? App\Models\Destination::find($trip->from);
                                                    $to = $trip->toDestination ?? App\Models\Destination::find($trip->to);
                                                @endphp
                                                From: {{$from ? $from->country->name : ""}} {{$from ? $from->city : ""}} {{$trip->loading_point ? $trip->loading_point->name : ""}} To: {{$to ? $to->country->name : ""}} {{$to ? $to->city : ""}} {{$trip->offloading_point ? $trip->offloading_point->name : ""}}</option>
                                            @endforeach
                                    </select>
                                    @error('selectedTrip') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    @elseif($type == "Asset")
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="drivers">Categories</label>
                                <select wire:model.debounce.300ms="selectedCategory" class="form-control">
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{$category->id}}">{{$category->name}} </option>
                                    @endforeach
                                </select>
                                    @error('selectedCategory') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="drivers">Sub Categories</label>
                                    <select wire:model.debounce.300ms="selectedCategoryValue" class="form-control">
                                        <option value="">Select Sub Category</option>
                                        @foreach ($category_values as $category_value)
                                            <option value="{{$category_value->id}}">{{$category_value->name}} </option>
                                        @endforeach
                                    </select>
                                    @error('selectedCategoryValue') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="drivers">Assets<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="asset_id" class="form-control" required size="4">
                                        <option value="">Select Asset</option>
                                        @if (!is_null($selectedCategory))
                                        @foreach ($assets as $asset)
                                            <option value="{{$asset->id}}">{{$asset->product->brand ? $asset->product->brand->name : ""}} {{$asset->product ? $asset->product->name : ""}}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                    @error('asset_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="row">
                        @if ($type == "Horse" || $type == "Vehicle")
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="mileage">Mileage<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="mileage" placeholder="Enter Horse Mileage" required>
                                    @error('mileage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="hours">Hours</label>
                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="hours" placeholder="Enter Engine Hours" >
                                    @error('hours') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                @if ($type == "Horse")
                                    <div class="form-group">
                                        <label>Fuel Source<span class="required" style="color: red">*</span></label>
                                        <div class="mb-10">
                                            <input type="radio" wire:model.debounce.300ms="fuel_source" value="station" class="line-style" required/>
                                            <label for="one" class="radio-label">Fueling Station</label>
                                            <input type="radio" wire:model.debounce.300ms="fuel_source" value="truck" class="line-style" required/>
                                            <label for="one" class="radio-label">Another Truck</label>
                                        </div>
                                    </div>
                                @endif
                                @if ($type == "Vehicle" || $fuel_source == "station")
                                <div class="form-group">
                                    <label for="vendors">Fueling Station<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="selectedContainer" class="form-control" required>
                                        <option value="">Select Fueling Station</option>
                                        @foreach ($containers as $container)
                                            <option value="{{$container->id}}">{{$container->name}}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedContainer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    <small>  <a href="{{ route('containers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Fueling Station</a></small>
                                    @if (!is_null($selectedContainer) && isset($selected_container) )
                                        @if ($selected_container->purchase_type == "Bulk Buy")
                                            <div class="fuel-balance-card">
                                                @if (isset($container_balance))
                                                    <strong>Quantity Balance</strong>
                                                    <span>{{ number_format($this->effectiveContainerBalance ?? $container_balance ?? 0, 2) }} L</span>
                                                @endif
                                                @if (isset($account_balance))
                                                    <strong class="mt-1">Account Balance</strong>
                                                    <span>{{$selected_container->currency?->symbol}}{{ number_format($this->effectiveAccountBalance ?? $account_balance ?? 0, 2) }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    @endif
                                </div>
                                @elseif ($type == "Horse" && $fuel_source == "truck")
                                <div class="form-group">
                                    <label for="source_horse">Source Truck<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="selectedSourceHorse" class="form-control" required>
                                        <option value="">Select Source Truck</option>
                                        @foreach ($horses as $horse)
                                            @continue($horse->id == $selectedHorse)
                                            <option value="{{$horse->id}}">{{$horse->registration_number}} {{$horse->fleet_number ? "(".$horse->fleet_number.")" : ""}}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedSourceHorse') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                                @endif
                            </div>
                        @else
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="vendors">Fueling Station<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="selectedContainer" class="form-control" required>
                                        <option value="">Select Fueling Station</option>
                                        @foreach ($containers as $container)
                                            <option value="{{$container->id}}">{{$container->name}}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedContainer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    <small>  <a href="{{ route('containers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Fueling Station</a></small> 
                                    @if (!is_null($selectedContainer) && isset($selected_container) )
                                        @if ($selected_container->purchase_type == "Bulk Buy")
                                            <div class="fuel-balance-card">
                                                @if (isset($container_balance))
                                                    <strong>Quantity Balance</strong>
                                                    <span>{{ number_format($this->effectiveContainerBalance ?? $container_balance ?? 0, 2) }} L</span>
                                                @endif
                                                @if (isset($account_balance))
                                                    <strong class="mt-1">Account Balance</strong>
                                                    <span>{{$selected_container->currency?->symbol}}{{ number_format($this->effectiveAccountBalance ?? $account_balance ?? 0, 2) }}</span>
                                                @endif
                                            </div>
                                        @endif 
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                    @if (isset($selected_container) && $selected_container->purchase_type == "Bulk Buy")
                        <div class="form-group">
                            <label for="exampleInputEmail13">Select where to deduct from<span class="required" style="color: red">*</span></label>
                            <div class="mb-10">
                                <input type="radio" wire:model.debounce.300ms="deduct_from" value="account"  class="line-style"  required/>
                                <label for="one" class="radio-label">Account Balance($)</label>
                                <input type="radio" wire:model.debounce.300ms="deduct_from" value="quantity"  class="line-style"  required/>
                                <label for="one" class="radio-label">Quantity Balance(l)</label>
                            </div>     
                        </div>
                    @endif
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="date">Fuel Type<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="fuel_type" required @if($fuel_type_locked) disabled @endif>
                                    <option value="">Select Fuel Type</option>
                                    <option value="Diesel">Diesel</option>
                                    <option value="Petrol">Petrol</option>
                                </select>
                                @error('fuel_type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="date">Fill Up Date<span class="required" style="color: red">*</span></label>
                                <input type="datetime-local" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter FillUp Date" required>
                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <label for="gender">Type for fillup<span class="required" style="color: red">*</span></label>
                                    <div class="radio">
                                        <label>
                                        <input type="radio" wire:model.debounce.300ms="fillup" id="optionsRadios1" value="1" required {{ isset($fuel_trip) ? "disabled" : "" }}>
                                        Initial
                                        </label>
                                    </div>
                                    <div class="radio">
                                        <label>
                                        <input type="radio"  wire:model.debounce.300ms="fillup" id="optionsRadios2" value="0" >
                                        Top Up
                                        </label>
                                    </div>
                                    @if (isset($fuel_trip))
                                    <small style="color:red">An initial fuel order created already.</small>
                                    @endif
                                </div>   
                                @error('fillup') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="quantity">Quantity<span class="required text-danger">*</span></label>
                                @if (isset($selected_container) && $selected_container->purchase_type == "Bulk Buy")
                                    <input type="number" step="any"  max="{{$container_balance}}" class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Qty"  required/>
                                @else
                                    <input type="number" step="any"  class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Qty"  required/>
                                @endif
                                @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                @if (isset($trip_fuel) && isset($horse_fuel_total))
                                    @if ($trip_fuel > $horse_fuel_total)
                                    <small style="color: red">Total horse fuel is less than trip fuel.</small>
                                    @endif
                                @endif
                              
                                @if (isset($selected_container) && $selected_container->purchase_type == "Bulk Buy")
                                    @if (isset($container_balance) && isset($quantity))
                                        @if (($this->effectiveContainerBalance ?? $container_balance) < $quantity)
                                        <small class="text-danger">Fuel order exceeds {{ number_format($this->effectiveContainerBalance ?? $container_balance ?? 0, 2) }} litres, which is the fueling station balance.</small>
                                        @endif
                                    @endif
                                @endif
                                @if (isset($fuel_tank_capacity) && $fuel_tank_capacity > 0)
                                    @if ($fuel_tank_capacity < $quantity)
                                        <small style="color: red">Fuel order exceeds {{ $fuel_tank_capacity }} litres, which is horse fuel tank capacity.</small>
                                    @endif
                                @else   
                                    @if ($selected_horse)
                                        <small style="color: green">Horse <a href="{{ route('horses.show',$selected_horse->id) }}" target="_blank" style="color: blue">Horse {{ $selected_horse->registration_number }}</a> fuel tank capacity not set.</small>
                                    @endif
                                @endif
                               
                            </div>
                        </div>
                       <div class="col-md-4">
                            <div class="form-group">
                                <label for="is_full_tank">
                                    Full Tank Refill? <span class="required text-danger">*</span>
                                </label>

                                <select class="form-control"
                                        wire:model.defer="is_full_tank"
                                        required>
                                    <option value="">Select Option</option>
                                    <option value="1">Yes - Tank Filled Completely</option>
                                    <option value="0">No - Partial Refill</option>
                                </select>

                                @error('is_full_tank')
                                    <span class="text-danger error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="currencies">Currencies</label>
                                <select class="form-control" wire:model.debounce.300ms="selectedCurrency" >
                                    <option value="">Select Currency </option>
                                    @foreach ($currencies as $currency)
                                    <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                    @endforeach
                                </select>
                                @error('selectedCurrency') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                            @if (!is_null($selectedCurrency))
                                @if ($company)
                                    @if ($selectedCurrency != $company->currency_id)
                                    <div class="form-group">
                                        <label for="customer">Conversion Rate<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{$company->currency ? "To ".$company->currency->name : ""}}" required>
                                        @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                        <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{$company->currency ? $company->currency->name." ?" : ""}}</small>
                                        <small>{{$exchange_amount ? "The converted amount is: ".$exchange_amount : ""}}</small> <br>
                                    </div> 
                                    @endif
                                @endif
                            @endif
                        </div>
                    </div>
                   
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unit_price">Rate</label>
                                <input type="number" class="form-control" step="any" min="0"  wire:model.debounce.300ms="unit_price" placeholder="Enter Fuel Price" >
                                @error('unit_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="amount">Total</label>
                                <input type="number" step="any" min="0" class="form-control"  wire:model.debounce.300ms="amount" placeholder="Enter Fillup Total">
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                @if (isset($selected_container) && $selected_container->purchase_type == "Bulk Buy" && $deduct_from == "account")
                                    @if (isset($account_balance) && isset($amount))
                                        @if ($account_balance < $amount)
                                        <small class="text-danger">Fuel total amount exceeds {{$selected_container->currency?->symbol}}{{ number_format($this->effectiveAccountBalance ?? $account_balance ?? 0, 2) }}, which is the fueling station money account balance.</small>
                                        @endif
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                   
                    <div class="form-group">
                        <label for="file">Comments</label>
                       <textarea wire:model.debounce.300ms="comments" class="form-control" cols="30" rows="4" placeholder="Write fuel order comments..."></textarea>
                        @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    @if($this->fuelOrderBlocked)
                        <div class="alert alert-danger guard-alert text-left" style="width:100%;">
                            <strong>Cannot save fuel order:</strong>
                            <ul>
                                @foreach($this->fuelOrderIssues as $issue)
                                    <li>{{ $issue }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @error('fuel_guard')
                        <div class="alert alert-danger text-left" style="width:100%;">{{ $message }}</div>
                    @enderror
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded" wire:loading.attr="disabled" wire:target="store" @if($this->fuelOrderBlocked) disabled @endif><i class="fa fa-save"></i>Save</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="fuelEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-70" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Fuel Order <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                    <div class="modal-body">
                                <div class="mb-10">
                        <input type="checkbox" wire:model.debounce.300ms="attach_fuel_request"   class="line-style" />
                        <label for="one" class="radio-label">Attach fuel request to this order.</label>
                        @error('attach_fuel_request') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                    @if (!is_null($attach_fuel_request) && $attach_fuel_request == "True")
                        <div class="form-group">
                            <label for="horses">Fuel Requisitions<span class="required" style="color: red">*</span></label>
                            <input type="text" wire:model.debounce.300ms="searchRequest" placeholder="Search requests..." class="form-control">
                            <select wire:model.debounce.300ms="selectedFuelRequest" class="form-control" required size="4">
                                <option value="">Select Fuel Request</option>
                                @foreach ($fuel_requests as $fuel_request)
                                    <option value="{{$fuel_request->id}}">CreatedBy: {{$fuel_request->user?->name}} {{$fuel_request->user?->surname}} RequestedBy: {{$fuel_request->employee?->name}} {{$fuel_request->employee?->surname}} RequestFor:
                                        @if ($fuel_request->horse)
                                            {{$fuel_request->horse?->registration_number}} {{$fuel_request->horse?->fleet_number ? "(".$fuel_request->horse?->fleet_number.")" : ""}}
                                        @elseif ($fuel_request->vehicle)
                                            {{$fuel_request->vehicle?->registration_number}} {{$fuel_request->vehicle?->fleet_number ? "(".$fuel_request->vehicle?->fleet_number.")" : ""}}
                                        @elseif ($fuel_request->asset)
                                            {{$fuel_request->asset->product->brand ? $fuel_request->asset->product->brand->name : ""}} {{$fuel_request->asset->product ? $fuel_request->asset->product->name : ""}}
                                        @else
                                            Other
                                        @endif
                                        FuelType: {{$fuel_request->fuel_type}} Qty: {{$fuel_request->quantity ? $fuel_request->quantity."l" : ""}}
                                    </option>
                                @endforeach
                            </select>
                            @error('selectedFuelRequest') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    @endif
                    <div class="row">
                    <div class="form-group">
                             <div class="col-sm-10">
                                <label for="gender">Fuel Order For?<span class="required" style="color: red">*</span></label>
                                <div class="mb-10">
                                    <input type="radio" wire:model.debounce.300ms="type" value="Horse"  class="line-style" required  />
                                    <label for="one" class="radio-label">Horse</label>
                                    <input type="radio" wire:model.debounce.300ms="type" value="Vehicle"  class="line-style" />
                                    <label for="one" class="radio-label">Vehicle</label>
                                    <input type="radio" wire:model.debounce.300ms="type" value="Asset"  class="line-style"  />
                                    <label for="one" class="radio-label">Asset</label>
                                    <input type="radio" wire:model.debounce.300ms="type" value="Other"  class="line-style"  />
                                    <label for="one" class="radio-label">Other</label>
                                </div>  
                            </div>
                            @error('type') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                     @if ($type == "Horse" || $type == "Vehicle")
                        <div class="row">
                            <div class="col-md-3">
                                @if ($type == "Horse")
                                    <div class="form-group">
                                        <label for="horses">Horses<span class="required" style="color: red">*</span></label>
                                        <input type="text" wire:model.debounce.300ms="searchHorse" placeholder="Search horses..." class="form-control">
                                        <select wire:model.debounce.300ms="selectedHorse" class="form-control" required size="4">
                                            <option value="">Select Horse</option>
                                            @foreach ($horses as $horse)
                                                <option value="{{$horse->id}}">{{$horse->registration_number}} {{$horse->fleet_number ? "(".$horse->fleet_number.")" : ""}}</option>
                                            @endforeach
                                        </select>
                                        @error('selectedHorse') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                @elseif($type == "Vehicle")
                                    <div class="form-group">
                                        <label for="vehicles">Vehicles<span class="required" style="color: red">*</span></label>
                                        <input type="text" wire:model.debounce.300ms="searchHorse" placeholder="Search horses..." class="form-control">
                                        <select wire:model.debounce.300ms="selectedVehicle" class="form-control" required size="4">
                                            <option value="">Select Vehicle</option>
                                            @foreach ($vehicles as $vehicle)
                                                <option value="{{$vehicle->id}}"> {{$vehicle->registration_number}} {{$vehicle->fleet_number ? "(".$vehicle->fleet_number.")" : ""}} </option>
                                            @endforeach
                                        </select>
                                        @error('selectedVehicle') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="employees">Employees(Driver)<span class="required" style="color: red">*</span></label>
                                    <input type="text" wire:model.debounce.300ms="searchEmployee" placeholder="Search employees..." class="form-control">
                                    <select wire:model.debounce.300ms="employee_id" class="form-control" required size="4">
                                        <option value="">Select Employee</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{$employee->id}}">{{ $employee->name }} {{$employee->surname}}</option>
                                        @endforeach
                                    </select>
                                    @error('employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="trips">Trips</label>
                                    <input type="text" wire:model.debounce.300ms="searchTrip" placeholder="Search trips..." class="form-control">
                                    <select wire:model.debounce.300ms="selectedTrip" class="form-control" size="4">
                                        <option value="">Select Trip</option>
                                            @foreach ($trips as $trip)
                                            <option value="{{$trip->id}}">{{ $trip->trip_number }}{{ $trip->trip_ref ? "/".$trip->trip_ref : "" }} | {{$trip->start_date}} |
                                                @if ($trip->horse)
                                                    {{ $trip->horse ? $trip->horse->registration_number : "" }} {{ $trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : "" }}   
                                                @endif
                                                @if ($trip->vehicle)
                                                    {{ $trip->vehicle ? $trip->vehicle->registration_number : "" }} {{ $trip->vehicle->fleet_number ? "(".$trip->vehicle->fleet_number.")" : "" }}   
                                                @endif
                                                |
                                                @php
                                                    $from = $trip->fromDestination ?? App\Models\Destination::find($trip->from);
                                                    $to = $trip->toDestination ?? App\Models\Destination::find($trip->to);
                                                @endphp
                                                From: {{$from ? $from->country->name : ""}} {{$from ? $from->city : ""}} {{$trip->loading_point ? $trip->loading_point->name : ""}} To: {{$to ? $to->country->name : ""}} {{$to ? $to->city : ""}} {{$trip->offloading_point ? $trip->offloading_point->name : ""}}</option>
                                            @endforeach
                                    </select>
                                    @error('selectedTrip') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    @elseif($type == "Asset")
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="drivers">Categories</label>
                                <select wire:model.debounce.300ms="selectedCategory" class="form-control">
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{$category->id}}">{{$category->name}} </option>
                                    @endforeach
                                </select>
                                    @error('selectedCategory') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="drivers">Sub Categories</label>
                                    <select wire:model.debounce.300ms="selectedCategoryValue" class="form-control">
                                        <option value="">Select Sub Category</option>
                                        @foreach ($category_values as $category_value)
                                            <option value="{{$category_value->id}}">{{$category_value->name}} </option>
                                        @endforeach
                                    </select>
                                    @error('selectedCategoryValue') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="drivers">Assets<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="asset_id" class="form-control" required size="4">
                                        <option value="">Select Asset</option>
                                        @if (!is_null($selectedCategory))
                                        @foreach ($assets as $asset)
                                            <option value="{{$asset->id}}">{{$asset->product->brand ? $asset->product->brand->name : ""}} {{$asset->product ? $asset->product->name : ""}}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                    @error('asset_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="row">
                        @if ($type == "Horse" || $type == "Vehicle")
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="mileage">Mileage<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="mileage" placeholder="Enter Horse Mileage" required>
                                    @error('mileage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="hours">Hours</label>
                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="hours" placeholder="Enter Engine Hours" >
                                    @error('hours') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                @if ($type == "Horse")
                                    <div class="form-group">
                                        <label>Fuel Source<span class="required" style="color: red">*</span></label>
                                        <div class="mb-10">
                                            <input type="radio" wire:model.debounce.300ms="fuel_source" value="station" class="line-style" required/>
                                            <label for="one" class="radio-label">Fueling Station</label>
                                            <input type="radio" wire:model.debounce.300ms="fuel_source" value="truck" class="line-style" required/>
                                            <label for="one" class="radio-label">Another Truck</label>
                                        </div>
                                    </div>
                                @endif
                                @if ($type == "Vehicle" || $fuel_source == "station")
                                <div class="form-group">
                                    <label for="vendors">Fueling Station<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="selectedContainer" class="form-control" required>
                                        <option value="">Select Fueling Station</option>
                                        @foreach ($containers as $container)
                                            <option value="{{$container->id}}">{{$container->name}}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedContainer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    <small>  <a href="{{ route('containers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Fueling Station</a></small>
                                    @if (!is_null($selectedContainer) && isset($selected_container) )
                                        @if ($selected_container->purchase_type == "Bulk Buy")
                                            <div class="fuel-balance-card">
                                                @if (isset($container_balance))
                                                    <strong>Quantity Balance</strong>
                                                    <span>{{ number_format($this->effectiveContainerBalance ?? $container_balance ?? 0, 2) }} L</span>
                                                @endif
                                                @if (isset($account_balance))
                                                    <strong class="mt-1">Account Balance</strong>
                                                    <span>{{$selected_container->currency?->symbol}}{{ number_format($this->effectiveAccountBalance ?? $account_balance ?? 0, 2) }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    @endif
                                </div>
                                @elseif ($type == "Horse" && $fuel_source == "truck")
                                <div class="form-group">
                                    <label for="source_horse">Source Truck<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="selectedSourceHorse" class="form-control" required>
                                        <option value="">Select Source Truck</option>
                                        @foreach ($horses as $horse)
                                            @continue($horse->id == $selectedHorse)
                                            <option value="{{$horse->id}}">{{$horse->registration_number}} {{$horse->fleet_number ? "(".$horse->fleet_number.")" : ""}}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedSourceHorse') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                                @endif
                            </div>
                        @else
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="vendors">Fueling Station<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="selectedContainer" class="form-control" required>
                                        <option value="">Select Fueling Station</option>
                                        @foreach ($containers as $container)
                                            <option value="{{$container->id}}">{{$container->name}}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedContainer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    <small>  <a href="{{ route('containers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Fueling Station</a></small> 
                                    @if (!is_null($selectedContainer) && isset($selected_container) )
                                        @if ($selected_container->purchase_type == "Bulk Buy")
                                            <div class="fuel-balance-card">
                                                @if (isset($container_balance))
                                                    <strong>Quantity Balance</strong>
                                                    <span>{{ number_format($this->effectiveContainerBalance ?? $container_balance ?? 0, 2) }} L</span>
                                                @endif
                                                @if (isset($account_balance))
                                                    <strong class="mt-1">Account Balance</strong>
                                                    <span>{{$selected_container->currency?->symbol}}{{ number_format($this->effectiveAccountBalance ?? $account_balance ?? 0, 2) }}</span>
                                                @endif
                                            </div>
                                        @endif 
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="date">Fuel Type<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="fuel_type" required @if($fuel_type_locked) disabled @endif>
                                    <option value="">Select Fuel Type</option>
                                    <option value="Diesel">Diesel</option>
                                    <option value="Petrol">Petrol</option>
                                </select>
                                @error('fuel_type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="date">Fill Up Date<span class="required" style="color: red">*</span></label>
                                <input type="datetime-local" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter FillUp Date" required>
                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <label for="gender">Type for fillup<span class="required" style="color: red">*</span></label>
                                    <div class="radio">
                                        <label>
                                        <input type="radio" wire:model.debounce.300ms="fillup" id="optionsRadios1" value="1" required {{ isset($fuel_trip) ? "disabled" : "" }}>
                                        Initial
                                        </label>
                                    </div>
                                    <div class="radio">
                                        <label>
                                        <input type="radio"  wire:model.debounce.300ms="fillup" id="optionsRadios2" value="0" >
                                        Top Up
                                        </label>
                                    </div>
                                    @if (isset($fuel_trip))
                                    <small style="color:red">An initial fuel order created already.</small>
                                    @endif
                                </div>   
                                @error('fillup') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="quantity">Quantity<span class="required text-danger">*</span></label>
                                @if (isset($selected_container) && $selected_container->purchase_type == "Bulk Buy")
                                    <input type="number" step="any"  max="{{$container_balance}}" class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Qty"  required/>
                                @else
                                    <input type="number" step="any"  class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Qty"  required/>
                                @endif
                                @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                @if (isset($trip_fuel) && isset($horse_fuel_total))
                                    @if ($trip_fuel > $horse_fuel_total)
                                    <small style="color: red">Total horse fuel is less than trip fuel.</small>
                                    @endif
                                @endif
                              
                                @if (isset($selected_container) && $selected_container->purchase_type == "Bulk Buy")
                                    @if (isset($container_balance) && isset($quantity))
                                        @if (($this->effectiveContainerBalance ?? $container_balance) < $quantity)
                                        <small class="text-danger">Fuel order exceeds {{ number_format($this->effectiveContainerBalance ?? $container_balance ?? 0, 2) }} litres, which is the fueling station balance.</small>
                                        @endif
                                    @endif
                                @endif
                                @if (isset($fuel_tank_capacity) && $fuel_tank_capacity > 0)
                                    @if ($fuel_tank_capacity < $quantity)
                                        <small style="color: red">Fuel order exceeds {{ $fuel_tank_capacity }} litres, which is horse fuel tank capacity.</small>
                                    @endif
                                @else   
                                    @if ($selected_horse)
                                        <small style="color: green">Horse <a href="{{ route('horses.show',$selected_horse->id) }}" target="_blank" style="color: blue">Horse {{ $selected_horse->registration_number }}</a> fuel tank capacity not set.</small>
                                    @endif
                                @endif
                               
                            </div>
                        </div>
                       <div class="col-md-4">
                            <div class="form-group">
                                <label for="is_full_tank">
                                    Full Tank Refill? <span class="required text-danger">*</span>
                                </label>

                                <select class="form-control"
                                        wire:model.defer="is_full_tank"
                                        required>
                                    <option value="">Select Option</option>
                                    <option value="1">Yes - Tank Filled Completely</option>
                                    <option value="0">No - Partial Refill</option>
                                </select>

                                @error('is_full_tank')
                                    <span class="text-danger error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="currencies">Currencies</label>
                                <select class="form-control" wire:model.debounce.300ms="selectedCurrency" >
                                    <option value="">Select Currency </option>
                                    @foreach ($currencies as $currency)
                                    <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                    @endforeach
                                </select>
                                @error('selectedCurrency') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                            @if (!is_null($selectedCurrency))
                                @if ($company)
                                    @if ($selectedCurrency != $company->currency_id)
                                    <div class="form-group">
                                        <label for="customer">Conversion Rate<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{$company->currency ? "To ".$company->currency->name : ""}}" required>
                                        @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                        <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{$company->currency ? $company->currency->name." ?" : ""}}</small>
                                        <small>{{$exchange_amount ? "The converted amount is: ".$exchange_amount : ""}}</small> <br>
                                    </div> 
                                    @endif
                                @endif
                            @endif
                        </div>
                    </div>
                   
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unit_price">Rate</label>
                                <input type="number" class="form-control" step="any" min="0"  wire:model.debounce.300ms="unit_price" placeholder="Enter Fuel Price" >
                                @error('unit_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="amount">Total</label>
                                <input type="number" step="any" min="0" class="form-control"  wire:model.debounce.300ms="amount" placeholder="Enter Fillup Total">
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                @if (isset($selected_container) && $selected_container->purchase_type == "Bulk Buy" && $deduct_from == "account")
                                    @if (isset($account_balance) && isset($amount))
                                        @if ($account_balance < $amount)
                                        <small class="text-danger">Fuel total amount exceeds {{$selected_container->currency?->symbol}}{{ number_format($this->effectiveAccountBalance ?? $account_balance ?? 0, 2) }}, which is the fueling station money account balance.</small>
                                        @endif
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                   
                    <div class="form-group">
                        <label for="file">Comments</label>
                       <textarea wire:model.debounce.300ms="comments" class="form-control" cols="30" rows="4" placeholder="Write fuel order comments..."></textarea>
                        @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    @if($this->fuelOrderBlocked)
                        <div class="alert alert-danger guard-alert text-left" style="width:100%;">
                            <strong>Cannot update fuel order:</strong>
                            <ul>
                                @foreach($this->fuelOrderIssues as $issue)
                                    <li>{{ $issue }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @error('fuel_guard')
                        <div class="alert alert-danger text-left" style="width:100%;">{{ $message }}</div>
                    @enderror
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded" wire:loading.attr="disabled" wire:target="update" @if($this->fuelOrderBlocked) disabled @endif><i class="fa fa-refresh"></i>Update</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="fuelTopupModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i>Fuel Top Up  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="storeTopup()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group">
                                 <div class="col-sm-10">
                                    <label for="gender">Fuel Order For?<span class="required" style="color: red">*</span></label>
                                    <div class="mb-10">
                                        <input type="radio" wire:model.debounce.300ms="type" value="Horse"  class="line-style"  />
                                        <label for="one" class="radio-label">Horse</label>
                                        <input type="radio" wire:model.debounce.300ms="type" value="Vehicle"  class="line-style" />
                                        <label for="one" class="radio-label">Vehicle</label>
                                        <input type="radio" wire:model.debounce.300ms="type" value="Asset"  class="line-style"  />
                                        <label for="one" class="radio-label">Asset</label>
                                        <input type="radio" wire:model.debounce.300ms="type" value="Other"  class="line-style"  />
                                        <label for="one" class="radio-label">Other</label>
                                    </div>  
                                </div>
                                @error('type') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                       
                         @if (isset($type) && $type == "Horse")
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="horses">Horses<span class="required" style="color: red">*</span></label>
                                   <select wire:model.debounce.300ms="selectedHorse" class="form-control" required>
                                       <option value="">Select Horse</option>
                                      @foreach ($horses as $horse)
                                          <option value="{{$horse->id}}">{{$horse->registration_number}} {{$horse->fleet_number ? "(".$horse->fleet_number.")" : ""}}</option>
                                      @endforeach
                                   </select>
                                    @error('selectedHorse') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="trips">Trips</label>
                                   <select wire:model.debounce.300ms="selectedTrip" class="form-control" >
                                       <option value="">Select Trip</option>
                                      @foreach ($trips as $trip)
                                      <option value="{{$trip->id}}">{{ $trip->trip_number }}{{ $trip->trip_ref ? "/".$trip->trip_ref : "" }} | {{ $trip->horse ? $trip->horse->registration_number : "" }} | <strong>From:</strong> {{$trip->loading_point ? $trip->loading_point->name : ""}} <strong>To:</strong> {{$trip->offloading_point ? $trip->offloading_point->name : ""}}</option>
                                      @endforeach
                                   </select>
                                    @error('selectedTrip') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                     
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="mileage">Mileage<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="mileage" placeholder="Enter Horse Mileage" required>
                                    @error('mileage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                              <div class="col-md-4">
                            <div class="form-group">
                                <label for="hours">Hours</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="hours" placeholder="Enter Engine Hours" >
                                @error('hours') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="vendors">Fueling Station<span class="required" style="color: red">*</span></label>
                                   <select wire:model.debounce.300ms="selectedContainer" class="form-control" required>
                                       <option value="">Select Fueling Station</option>
                                      @foreach ($containers as $container)
                                          <option value="{{$container->id}}">{{$container->name}}</option>
                                      @endforeach
                                   </select>
                                    @error('selectedContainer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    <small>  <a href="{{ route('containers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Fueling Station</a></small> 
                                    @if (!is_null($selectedContainer) && isset($selected_container) )
                                        @if ($selected_container->purchase_type == "Bulk Buy")
                                            <div class="fuel-balance-card">
                                                @if (isset($container_balance))
                                                    <strong>Quantity Balance</strong>
                                                    <span>{{ number_format($this->effectiveContainerBalance ?? $container_balance ?? 0, 2) }} L</span>
                                                @endif
                                                @if (isset($account_balance))
                                                    <strong class="mt-1">Account Balance</strong>
                                                    <span>{{$selected_container->currency?->symbol}}{{ number_format($this->effectiveAccountBalance ?? $account_balance ?? 0, 2) }}</span>
                                                @endif
                                            </div>
                                        @endif 
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date">Fill Up Date<span class="required" style="color: red">*</span></label>
                                    <input type="datetime-local" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter FillUp Date" required>
                                    @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="col-sm-10">
                                        <label for="gender">Type for fillup<span class="required" style="color: red">*</span></label>
                                        <div class="radio">
                                            <label>
                                            <input type="radio" wire:model.debounce.300ms="fillup" id="optionsRadios1" value="1" required>
                                            Initial
                                            </label>
                                        </div>
                                        <div class="radio">
                                            <label>
                                            <input type="radio"  wire:model.debounce.300ms="fillup" id="optionsRadios2" value="0" required>
                                            Top Up
                                            </label>
                                        </div>
                                        </div>
                                        @error('fillup') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quantity">Quantity</label>

                                    @if (isset($selected_container) && $selected_container->purchase_type == "Bulk Buy")
                                        <input type="number" step="any"  max="{{$container_balance}}" class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Qty"  />
                                    @else
                                        <input type="number" step="any"  class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Qty"  />
                                    @endif
                                    @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    @if (isset($trip_fuel) && isset($horse_fuel_total))
                                        @if ($trip_fuel > $horse_fuel_total)
                                        <small style="color: red">Total horse fuel is less than trip fuel.</small>
                                        @endif
                                    @endif
                                
                                    @if (isset($selected_container) && $selected_container->purchase_type == "Bulk Buy")
                                        @if (isset($container_balance) && isset($quantity))
                                            @if ($container_balance < $quantity)
                                            <small style="color: red">Fuel order exceeds {{ $container_balance }} litres, which is the fueling station balance.</small>
                                            @endif
                                        @endif
                                    @endif
                                
                                    @if (isset($fuel_tank_capacity) && $fuel_tank_capacity > 0)
                                        @if ($fuel_tank_capacity < $quantity)
                                            <small style="color: red">Fuel order exceeds {{ $fuel_tank_capacity }} litres, which is horse fuel tank capacity.</small>
                                        @endif
                                    @else   
                                        @if ($selected_horse)
                                            <small style="color: green">Horse <a href="{{ route('horses.show',$selected_horse->id) }}" target="_blank" style="color: blue">Horse {{ $selected_horse->registration_number }}</a> fuel tank capacity not set.</small>
                                        @endif
                                    @endif
                                   
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="currencies">Currencies</label>
                                    <select class="form-control" wire:model.debounce.300ms="selectedCurrency" >
                                        <option value="">Select Currency </option>
                                        @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedCurrency') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                                @if (!is_null($selectedCurrency))
                                @if ($company)
                                    @if ($selectedCurrency != $company->currency_id)
                                    <div class="form-group">
                                        <label for="customer">Conversion Rate<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{$company->currency ? "To ".$company->currency->name : ""}}" required>
                                        @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                        <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{$company->currency ? $company->currency->name." ?" : ""}}</small>
                                        <small>{{$exchange_amount ? "The converted amount is: ".$exchange_amount : ""}}</small> <br>
                                    </div> 
                                    @endif
                                @endif
                            @endif
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="unit_price">Rate</label>
                                    <input type="number" class="form-control" step="any" min="0"  wire:model.debounce.300ms="unit_price" placeholder="Enter Fuel Price" >
                                    @error('unit_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="amount">Total</label>
                                    <input type="number" step="any" min="0" class="form-control"  wire:model.debounce.300ms="amount" placeholder="Enter Fillup Total">
                                    @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="file">Comments</label>
                           <textarea wire:model.debounce.300ms="comments" class="form-control" cols="30" rows="4" placeholder="Write fuel order comments..."></textarea>
                            @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
    
                        @elseif (isset($type) && $type == "Vehicle")
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="vehicles">Vehicles<span class="required" style="color: red">*</span></label>
                                   <select wire:model.debounce.300ms="selectedVehicle" class="form-control" required>
                                       <option value="">Select Vehicle</option>
                                      @foreach ($vehicles as $vehicle)
                                          <option value="{{$vehicle->id}}"> {{$vehicle->registration_number}} {{$vehicle->fleet_number ? "(".$vehicle->fleet_number.")" : ""}} </option>
                                      @endforeach
                                   </select>
                                    @error('selectedVehicle') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="employees">Employees<span class="required" style="color: red">*</span></label>
                                   <select wire:model.debounce.300ms="employee_id" class="form-control" required>
                                       <option value="">Select Employee</option>
                                      @foreach ($employees as $employee)
                                          <option value="{{$employee->id}}">{{ $employee->name }} {{$employee->surname}}</option>
                                      @endforeach
                                   </select>
                                    @error('employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="trips">Trips</label>
                           <select wire:model.debounce.300ms="selectedTrip" class="form-control" >
                               <option value="">Select Trip</option>
                              @foreach ($trips as $trip)
                              <option value="{{$trip->id}}">{{ $trip->trip_number }}{{ $trip->trip_ref ? "/".$trip->trip_ref : "" }} | {{ $trip->horse ? $trip->horse->registration_number : "" }} | <strong>From:</strong> {{$trip->loading_point ? $trip->loading_point->name : ""}} <strong>To:</strong> {{$trip->offloading_point ? $trip->offloading_point->name : ""}}</option>
                              @endforeach
                           </select>
                            @error('selectedTrip') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="mileage">Mileage<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="mileage" required placeholder="Enter Vehicle Mileage">
                                    @error('mileage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                              <div class="col-md-4">
                            <div class="form-group">
                                <label for="hours">Hours</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="hours" placeholder="Enter Engine Hours" >
                                @error('hours') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="vendors">Fueling Station<span class="required" style="color: red">*</span></label>
                                   <select wire:model.debounce.300ms="selectedContainer" class="form-control" required>
                                       <option value="">Select Fueling Station</option>
                                      @foreach ($containers as $container)
                                          <option value="{{$container->id}}">{{$container->name}}</option>
                                      @endforeach
                                   </select>
                                    @error('selectedContainer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    <small>  <a href="{{ route('containers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Fueling Station</a></small> 
                                    @if (!is_null($selectedContainer) && isset($selected_container) )
                                        @if ($selected_container->purchase_type == "Bulk Buy")
                                            <div class="fuel-balance-card">
                                                @if (isset($container_balance))
                                                    <strong>Quantity Balance</strong>
                                                    <span>{{ number_format($this->effectiveContainerBalance ?? $container_balance ?? 0, 2) }} L</span>
                                                @endif
                                                @if (isset($account_balance))
                                                    <strong class="mt-1">Account Balance</strong>
                                                    <span>{{$selected_container->currency?->symbol}}{{ number_format($this->effectiveAccountBalance ?? $account_balance ?? 0, 2) }}</span>
                                                @endif
                                            </div>
                                        @endif 
                                    @endif
                                </div>
                            </div>
                        </div>
                     
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date">Fill Up Date<span class="required" style="color: red">*</span></label>
                                    <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter FillUp Date" required>
                                    @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                 <div class="col-sm-10">
                                    <label for="gender">Type for fillup<span class="required" style="color: red">*</span></label>
                                    <div class="radio">
                                        <label>
                                        <input type="radio" wire:model.debounce.300ms="fillup" id="optionsRadios1" value="1" required>
                                        Initial
                                        </label>
                                    </div>
                                    <div class="radio">
                                        <label>
                                        <input type="radio"  wire:model.debounce.300ms="fillup" id="optionsRadios2" value="0" required>
                                        Top Up
                                        </label>
                                    </div>
                                </div>
                                @error('fillup') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quantity">Quantity</label>
                                    @if (isset($selected_container) && $selected_container->purchase_type == "Bulk Buy")
                                        <input type="number" step="any"  max="{{$container_balance}}" class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Qty"  />
                                    @else
                                        <input type="number" step="any"  class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Qty"  />
                                    @endif
                                    @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    @if (isset($trip_fuel) && isset($horse_fuel_total))
                                        @if ($trip_fuel > $horse_fuel_total)
                                        <small style="color: red">Total horse fuel is less than trip fuel.</small>
                                        @endif
                                    @endif
                                
                                    @if (isset($selected_container) && $selected_container->purchase_type == "Bulk Buy")
                                        @if (isset($container_balance) && isset($quantity))
                                            @if ($container_balance < $quantity)
                                            <small style="color: red">Fuel order exceeds {{ $container_balance }} litres, which is the fueling station balance.</small>
                                            @endif
                                        @endif
                                    @endif
                                
                                    @if (isset($fuel_tank_capacity) && $fuel_tank_capacity > 0)
                                        @if ($fuel_tank_capacity < $quantity)
                                            <small style="color: red">Fuel order exceeds {{ $fuel_tank_capacity }} litres, which is horse fuel tank capacity.</small>
                                        @endif
                                    @else   
                                        @if ($selected_horse)
                                            <small style="color: green">Horse <a href="{{ route('horses.show',$selected_horse->id) }}" target="_blank" style="color: blue">Horse {{ $selected_horse->registration_number }}</a> fuel tank capacity not set.</small>
                                        @endif
                                    @endif
                                </div>
                                
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="currencies">Currencies</label>
                                    <select class="form-control" wire:model.debounce.300ms="selectedCurrency" >
                                        <option value="">Select Currency </option>
                                        @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedCurrency') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                               @if (!is_null($selectedCurrency))
                                @if ($company)
                                    @if ($selectedCurrency != $company->currency_id)
                                    <div class="form-group">
                                        <label for="customer">Conversion Rate<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{$company->currency ? "To ".$company->currency->name : ""}}" required>
                                        @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                        <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{$company->currency ? $company->currency->name." ?" : ""}}</small>
                                        <small>{{$exchange_amount ? "The converted amount is: ".$exchange_amount : ""}}</small> <br>
                                    </div> 
                                    @endif
                                    @endif
                                @endif
                            </div>
                        </div>
                       
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="unit_price">Rate</label>
                                    <input type="number" class="form-control" step="any" min="0"  wire:model.debounce.300ms="unit_price" placeholder="Enter Fuel Price" >
                                    @error('unit_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="amount">Total</label>
                                    <input type="number" step="any" min="0" class="form-control"  wire:model.debounce.300ms="amount" placeholder="Enter Fillup Total">
                                    @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                      
                        <div class="form-group">
                            <label for="file">Comments</label>
                           <textarea wire:model.debounce.300ms="comments" class="form-control" cols="30" rows="4" placeholder="Write fuel order comments..."></textarea>
                            @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
    
                    @elseif (isset($type) && $type == "Asset")
    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="drivers">Categories<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedCategory" class="form-control" required >
                                   <option value="">Select Category</option>
                                  @foreach ($categories as $category)
                                      <option value="{{$category->id}}">{{$category->name}} </option>
                                  @endforeach
                               </select>
                                @error('selectedCategory') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="drivers">Sub Categories<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedCategoryValue" class="form-control" required >
                                   <option value="">Select Sub Category</option>
                                  @foreach ($category_values as $category_value)
                                      <option value="{{$category_value->id}}">{{$category_value->name}} </option>
                                  @endforeach
                               </select>
                                @error('selectedCategoryValue') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="drivers">Assets<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="asset_id" class="form-control" required >
                                   <option value="">Select Asset</option>
                                   @if (!is_null($selectedCategory))
                                   @foreach ($assets as $asset)
                                      <option value="{{$asset->id}}">{{$asset->product->brand ? $asset->product->brand->name : ""}} {{$asset->product ? $asset->product->name : ""}}</option>
                                  @endforeach
                                  @endif
                               </select>
                                @error('asset_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vendors">Fueling Station<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedContainer" class="form-control" required>
                                   <option value="">Select Fueling Station</option>
                                  @foreach ($containers as $container)
                                      <option value="{{$container->id}}">{{$container->name}}</option>
                                  @endforeach
                               </select>
                                @error('selectedContainer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small>  <a href="{{ route('containers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Fueling Station</a></small> 
                                @if (!is_null($selectedContainer) && isset($selected_container) )
                                    @if ($selected_container->purchase_type == "Bulk Buy")
                                        @if (isset($container_balance))
                                            <br>
                                            <small style="color:green">Available fuel balance is {{ number_format($container_balance ?? 0 , 2) }}ltrs</small>    
                                        @endif
                                        @if (isset($account_balance))
                                            <br>
                                            <small style="color:green">Available account balance is {{$selected_container->currency?->symbol}}{{ number_format($account_balance ?? 0 , 2) }}</small>    
                                        @endif
                                    @endif 
                                @endif
                        </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date">Fill Up Date<span class="required" style="color: red">*</span></label>
                                <input type="datetime-local" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter FillUp Date" required>
                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                 <div class="col-sm-10">
                                    <label for="gender">Type for fillup<span class="required" style="color: red">*</span></label>
                                    <div class="radio">
                                        <label>
                                        <input type="radio" wire:model.debounce.300ms="fillup" id="optionsRadios1" value="1" required>
                                        Initial
                                        </label>
                                    </div>
                                    <div class="radio">
                                        <label>
                                        <input type="radio"  wire:model.debounce.300ms="fillup" id="optionsRadios2" value="0" required>
                                        Top Up
                                        </label>
                                    </div>
                                </div>
                                @error('fillup') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="quantity">Quantity</label>
                                @if (isset($selected_container) && $selected_container->purchase_type == "Bulk Buy")
                                    <input type="number" step="any"  max="{{$container_balance}}" class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Qty"  />
                                @else
                                    <input type="number" step="any"  class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Qty"  />
                                @endif
                                @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                @if (isset($trip_fuel) && isset($horse_fuel_total))
                                    @if ($trip_fuel > $horse_fuel_total)
                                    <small style="color: red">Total horse fuel is less than trip fuel.</small>
                                    @endif
                                @endif
                              
                                @if (isset($selected_container) && $selected_container->purchase_type == "Bulk Buy")
                                    @if (isset($container_balance) && isset($quantity))
                                        @if (($this->effectiveContainerBalance ?? $container_balance) < $quantity)
                                        <small class="text-danger">Fuel order exceeds {{ number_format($this->effectiveContainerBalance ?? $container_balance ?? 0, 2) }} litres, which is the fueling station balance.</small>
                                        @endif
                                        
                                    @endif
                                @endif
                               
                                @if (isset($fuel_tank_capacity) && $fuel_tank_capacity > 0)
                                    @if ($fuel_tank_capacity < $quantity)
                                        <small style="color: red">Fuel order exceeds {{ $fuel_tank_capacity }} litres, which is horse fuel tank capacity.</small>
                                    @endif
                                @else   
                                    @if ($selected_horse)
                                        <small style="color: green">Horse <a href="{{ route('horses.show',$selected_horse->id) }}" target="_blank" style="color: blue">Horse {{ $selected_horse->registration_number }}</a> fuel tank capacity not set.</small>
                                    @endif
                                @endif
                            </div>
                            
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="currencies">Currencies</label>
                                <select class="form-control" wire:model.debounce.300ms="selectedCurrency" >
                                    <option value="">Select Currency </option>
                                    @foreach ($currencies as $currency)
                                    <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                    @endforeach
                                </select>
                                @error('selectedCurrency') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                            @if (!is_null($selectedCurrency))
                                @if ($company)
                                    @if ($selectedCurrency != $company->currency_id)
                                    <div class="form-group">
                                        <label for="customer">Conversion Rate<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{$company->currency ? "To ".$company->currency->name : ""}}" required>
                                        @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                        <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{$company->currency ? $company->currency->name." ?" : ""}}</small>
                                        <small>{{$exchange_amount ? "The converted amount is: ".$exchange_amount : ""}}</small> <br>
                                    </div> 
                                    @endif
                                @endif
                            @endif
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unit_price">Rate</label>
                                <input type="number" class="form-control" step="any" min="0"  wire:model.debounce.300ms="unit_price" placeholder="Enter Fuel Price" >
                                @error('unit_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="amount">Total</label>
                                <input type="number" step="any" min="0" class="form-control"  wire:model.debounce.300ms="amount" placeholder="Enter Fillup Total">
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="file">Comments</label>
                       <textarea wire:model.debounce.300ms="comments" class="form-control" cols="30" rows="4" placeholder="Write fuel order comments..."></textarea>
                        @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
    
                    @elseif (isset($type) && $type == "Other")
                    <div class="form-group">
                        <label for="vendors">Fueling Station<span class="required" style="color: red">*</span></label>
                       <select wire:model.debounce.300ms="selectedContainer" class="form-control" required>
                           <option value="">Select Fueling Station</option>
                          @foreach ($containers as $container)
                              <option value="{{$container->id}}">{{$container->name}}</option>
                          @endforeach
                       </select>
                        @error('selectedContainer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        <small>  <a href="{{ route('containers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Fueling Station</a></small> 
                        @if (!is_null($selectedContainer) && isset($selected_container) )
                            @if ($selected_container->purchase_type == "Bulk Buy")
                                @if (isset($container_balance))
                                    <br>
                                    <small style="color:green">Available fuel balance is {{ number_format($container_balance ?? 0 , 2) }}ltrs</small>    
                                @endif
                                @if (isset($account_balance))
                                    <br>
                                    <small style="color:green">Available account balance is {{$selected_container->currency?->symbol}}{{ number_format($account_balance ?? 0 , 2) }}</small>    
                                @endif
                            @endif 
                        @endif
                    </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date">Fill Up Date<span class="required" style="color: red">*</span></label>
                                    <input type="datetime-local" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter FillUp Date" required>
                                    @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                             <div class="col-md-6">
                                <div class="form-group">
                                    <div class="col-sm-10">
                                        <label for="gender">Type for fillup<span class="required" style="color: red">*</span></label>
                                        <div class="radio">
                                            <label>
                                            <input type="radio" wire:model.debounce.300ms="fillup" id="optionsRadios1" value="1" required>
                                            Initial
                                            </label>
                                        </div>
                                        <div class="radio">
                                            <label>
                                            <input type="radio"  wire:model.debounce.300ms="fillup" id="optionsRadios2" value="0" required>
                                            Top Up
                                            </label>
                                        </div>
                                    </div>
                                    @error('fillup') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                              
                                <div class="form-group">
                                    <label for="quantity">Quantity</label>
                                    @if (isset($selected_container) && $selected_container->purchase_type == "Bulk Buy")
                                        <input type="number" step="any"  max="{{$container_balance}}" class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Qty"  />
                                    @else
                                        <input type="number" step="any"  class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Qty"  />
                                    @endif
                                    @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    @if (isset($trip_fuel) && isset($horse_fuel_total))
                                        @if ($trip_fuel > $horse_fuel_total)
                                        <small style="color: red">Total horse fuel is less than trip fuel.</small>
                                        @endif
                                    @endif
                                
                                    @if (isset($selected_container) && $selected_container->purchase_type == "Bulk Buy")
                                        @if (isset($container_balance) && isset($quantity))
                                            @if ($container_balance < $quantity)
                                            <small style="color: red">Fuel order exceeds {{ $container_balance }} litres, which is the fueling station balance.</small>
                                            @endif
                                        @endif
                                    @endif
                                
                                    @if (isset($fuel_tank_capacity) && $fuel_tank_capacity > 0)
                                        @if ($fuel_tank_capacity < $quantity)
                                            <small style="color: red">Fuel order exceeds {{ $fuel_tank_capacity }} litres, which is horse fuel tank capacity.</small>
                                        @endif
                                    @else   
                                        @if ($selected_horse)
                                            <small style="color: green">Horse <a href="{{ route('horses.show',$selected_horse->id) }}" target="_blank" style="color: blue">Horse {{ $selected_horse->registration_number }}</a> fuel tank capacity not set.</small>
                                        @endif
                                    @endif
                                </div>
                                
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="currencies">Currencies</label>
                                    <select class="form-control" wire:model.debounce.300ms="selectedCurrency" >
                                        <option value="">Select Currency </option>
                                        @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedCurrency') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                                @if (!is_null($selectedCurrency))
                                @if ($company)
                                    @if ($selectedCurrency != $company->currency_id)
                                    <div class="form-group">
                                        <label for="customer">Conversion Rate<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{$company->currency ? "To ".$company->currency->name : ""}}" required>
                                        @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                        <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{$company->currency ? $company->currency->name." ?" : ""}}</small>
                                        <small>{{$exchange_amount ? "The converted amount is: ".$exchange_amount : ""}}</small> <br>
                                    </div> 
                                    @endif
                                @endif
                            @endif
                            </div>
                        </div>
                       
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="unit_price">Rate</label>
                                    <input type="number" class="form-control" step="any" min="0"  wire:model.debounce.300ms="unit_price" placeholder="Enter Fuel Price" >
                                    @error('unit_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="amount">Total</label>
                                    <input type="number" step="any" min="0" class="form-control"  wire:model.debounce.300ms="amount" placeholder="Enter Fillup Total">
                                    @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                            <div class="form-group">
                                <label for="file">Comments<span class="required" style="color: red">*</span></label>
                                    <textarea wire:model.debounce.300ms="comments" class="form-control" cols="30" rows="4" required placeholder="Write fuel order comments..."></textarea>
                                @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                    @endif
                </div>
                <div class="modal-footer">
                    @if($this->fuelOrderBlocked)
                        <div class="alert alert-danger guard-alert text-left" style="width:100%;">
                            <strong>Cannot save fuel order:</strong>
                            <ul>
                                @foreach($this->fuelOrderIssues as $issue)
                                    <li>{{ $issue }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @error('fuel_guard')
                        <div class="alert alert-danger text-left" style="width:100%;">{{ $message }}</div>
                    @enderror
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded" wire:loading.attr="disabled" wire:target="store" @if($this->fuelOrderBlocked) disabled @endif><i class="fa fa-save"></i>Save</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>


</div>

