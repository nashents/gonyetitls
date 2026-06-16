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

                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <div class="panel-title">
                               
                                <div class="row">
                                
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
                                @if ($fuel_filter == "created_at")
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
                                @elseif ($fuel_filter == "start_date")
                                <div class="col-lg-2" style="margin-right: 42px">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  From
                                  </span>
                                  <input type="datetime-local" wire:model.debounce.300ms="from"  class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                <div class="col-lg-2" style="margin-left: 42px">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  To
                                  </span>
                                  <input type="datetime-local" wire:model.debounce.300ms="to"  class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                @endif
                               
                                <!-- /input-group -->
                            </div>
                        
                            @if ($selectedRows)
                            <br>
                            <div class="row">
                                <div class="col-lg-2" >
                                    <div class="dropdown">
                                        <button class="btn btn-default border-primary btn-rounded btn-wide dropdown-toggle" type="button" id="menu12" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                            <i class="fa fa-bars"></i> Bulk Actions
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu bg-gray" aria-labelledby="menu12">
                                            <li><a href="#"  wire:click="showBulkyAuthorize()"><i class="fa fa-gavel"></i>Authorize Fuel Orders</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-lg-6" style="margin-top: 3px; margin-left: -20px;">
                                <span >selected {{ count($selectedRows) }} fuel order(s) to authorize.</span>
                                </div>
                            </div>
                            <br>
                            @endif
                            
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
                                                    {{ ucfirst(optional($fuel->container)->name) }}
                                                    <div class="fuel-meta text-muted">
                                                        <small><strong>Fuel Type:</strong> {{ $fuel->container->fuel_type ?? '' }}</small>
                                                    </div>
                                                </td>

                                                {{-- Fill Up --}}
                                                <td><span class="badge badge-{{ $fillupBadge }}">{{ $fillupLabel }}</span></td>

                                                {{-- Qty --}}
                                                <td class="text-right">
                                                    <span class="font-weight-bold">{{ number_format($fuel->quantity, 2) }}</span>
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
                                                    {{ optional($fuel->currency)->symbol }}{{ number_format($fuel->amount, 2) }}
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
                                                            <li><a href="{{route('fuels.show',$fuel->id)}}"  ><i class="fa fa-eye color-default"></i>View</a></li>
                                                            <li><a href="#" wire:click="authorize({{$fuel->id}})"><i class="fas fa-gavel color-success"></i> Authorization</a></li>
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
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>


    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="bulkyAuthorizationModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-gavel"></i>Bulk Authorize Fuel Order(s)<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="authorizeSelectedRows()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Authorize<span class="required" style="color: red">*</span></label>
                    <select class="form-control" wire:model.debounce.300ms="authorize" required>
                        <option value="">Select Decision</option>
                        <option value="approved">Approve</option>
                        <option value="rejected">Reject</option>
                    </select>
                        @error('authorize') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="fuelAuthorizationModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-gas-pump"></i> Authorize Fuel Order <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Authorize<span class="required" style="color: red">*</span></label>
                    <select class="form-control" wire:model.debounce.300ms="authorize" required>
                        <option value="">Select Decision</option>
                        <option value="approved">Approve</option>
                        <option value="rejected">Reject</option>
                    </select>
                        @error('authorize') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="comment">Comment</label>
                  <textarea class="form-control" wire:model.debounce.300ms="comments" cols="30" rows="3"></textarea>
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
