<div>
    <style>
        .modal-lg { max-width: 80%; }
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

                            {{-- Date Range --}}
                            <div class="panel-title">
                               
                                <div class="row">
                                    <div class="col-lg-3" >
                                        <div class="input-group">
                                            <span class="input-group-addon">From</span>
                                            <input type="date" wire:model.debounce.300ms="from" wire:change="dateRange()" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-lg-3" >
                                        <div class="input-group">
                                            <span class="input-group-addon">To</span>
                                            <input type="date" wire:model.debounce.300ms="to" wire:change="dateRange()" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Search --}}
                            <div class="col-md-5" style="float: right; padding-right: 2px; margin-bottom: 10px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search incidents...">
                                </div>
                            </div>

                            {{-- Table --}}
                        <table class="table table-striped table-bordered table-sm table-hover" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th class="th-sm" style="width: 10%">Incident#</th>
                                    <th class="th-sm" style="width: 15%">Type & Category</th>
                                    <th class="th-sm" style="width: 18%">MOT & Asset</th>
                                    <th class="th-sm" style="width: 15%">Person Involved</th>
                                    <th class="th-sm" style="width: 18%">Location & Dates</th>
                                    <th class="th-sm" style="width: 12%">Risk Snapshot</th>
                                    <th class="th-sm" style="width: 4%">Auth</th>
                                    <th class="th-sm" style="width: 6%">Status</th>
                                    <th class="th-sm" style="width: 8%">Actions</th>
                                </tr>
                            </thead>

                            @if (isset($incidents) && $incidents->count())
                                <tbody>
                                @forelse ($incidents as $incident)
                                    <tr>
                                        {{-- Incident meta --}}
                                        <td>
                                            <strong>{{ $incident->incident_number }}</strong>
                                            <br>
                                            <small>
                                                <strong>By:</strong>
                                                {{ ucfirst($incident->user->name ?? '') }}
                                                {{ ucfirst($incident->user->surname ?? '') }}
                                                <br>
                                                <strong>On:</strong> {{ $incident->created_at->format('d M Y H:i') }}
                                            </small>
                                        </td>

                                        {{-- Type & Category (from form: incident_type + category) --}}
                                        <td>
                                             <span class="badge bg-default">{{ $incident->incident_type }}</span>
                                            <br>
                                            <small>
                                                <strong>Category:</strong> {{ $incident->category ?? 'Other Incident' }} <br>
                                                 @if(!empty($incident->occupation))
                                                    <strong>Occupation:</strong> {{ $incident->occupation }}
                                                @endif
                                            </small>
                                        </td>

                                        {{-- MOT & Asset (Horse / Vehicle / Trailer) --}}
                                         <td>
                                            @if($incident->horse)
                                                <i class="fa fa-truck"></i>
                                                Horse | {{ $incident->horse->registration_number }}
                                                {{ $incident->horse->fleet_number ? '(' . $incident->horse->fleet_number . ')' : '' }}
                                            @elseif($incident->vehicle)
                                                <i class="fa fa-car"></i>
                                                Vehicle | {{ $incident->vehicle->registration_number }}
                                                {{ $incident->vehicle->fleet_number ? '(' . $incident->vehicle->fleet_number . ')' : '' }}
                                            @elseif($incident->trailer)
                                                <i class="fa fa-truck"></i>
                                                Trailer | {{ $incident->trailer->registration_number }}
                                                {{ $incident->trailer->fleet_number ? '(' . $incident->trailer->fleet_number . ')' : '' }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        {{-- Person Involved (Driver / Employee) --}}
                                        <td>
                                            @if ($incident->driver && $incident->driver->employee)
                                                <strong>Driver</strong><br>
                                                {{ ucfirst($incident->driver->employee->name) }}
                                                {{ ucfirst($incident->driver->employee->surname) }}
                                            @elseif ($incident->employee)
                                                <strong>Employee</strong><br>
                                                {{ ucfirst($incident->employee->name) }}
                                                {{ ucfirst($incident->employee->surname) }}
                                            @else
                                                <span class="text-muted">Not captured</span>
                                            @endif
                                            @if (!empty($incident->experience))
                                                <br><small class="text-muted">
                                                    <strong>Experience:</strong> {{ $incident->experience }} yrs
                                                </small>
                                            @endif
                                        </td>

                                        {{-- Location & Dates (date, report_date, location) --}}
                                        <td>
                                            <small class="text-muted">
                                                <strong>Location:</strong> {{ $incident->location }}<br>
                                              
                                                <strong>Incident:</strong> {{ $incident->date ? \Carbon\Carbon::parse($incident->date)->format('d M Y H:i') : 'N/A' }}
                                                <br>
                                                <strong>Reported:</strong> {{ $incident->report_date ? \Carbon\Carbon::parse($incident->report_date)->format('d M Y H:i') : 'N/A' }}
                                           
                                            </small>
                                        </td>

                                        {{-- Risk Snapshot (loss_potential, occurance, media_coverage) --}}
                                        <td>
                                            <small class="text-muted">
                                                @if (!empty($incident->loss_potential))
                                                    <strong>Loss:</strong> {{ $incident->loss_potential }}<br>
                                                @endif
                                                @if (!empty($incident->occurance))
                                                    <strong>Prob:</strong> {{ $incident->occurance }}<br>
                                                @endif
                                                @if (!empty($incident->media_coverage))
                                                    <strong>Media:</strong> {{ $incident->media_coverage }}
                                                @endif
                                            </small>
                                        </td>

                                        {{-- Authorization --}}
                                        <td>
                                            @php
                                                $auth = $incident->authorization;
                                                $authClass = $auth === 'approved'
                                                    ? 'success'
                                                    : ($auth === 'rejected' ? 'danger' : 'warning');
                                            @endphp
                                            <span class="badge bg-{{ $authClass }}">
                                                {{ ucfirst($auth ?? 'pending') }}
                                            </span>
                                        </td>

                                        {{-- Open / Closed --}}
                                        <td>
                                            <span class="badge bg-{{ $incident->status == 1 ? 'warning' : 'success' }}">
                                                {{ $incident->status == 1 ? 'Open' : 'Closed' }}
                                            </span>
                                        </td>

                                        {{-- Actions --}}
                                        <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button"
                                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a href="{{ route('incidents.show', $incident->id) }}">
                                                            <i class="fa fa-eye color-default"></i> View
                                                        </a>
                                                    </li>
                                                     <li><a href="#" wire:click="authorize({{$incident->id}})"><i class="fas fa-gavel color-success"></i> Authorization</a></li>
                                                </ul>
                                            </div>

                                            @include('incidents.delete')
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9">
                                            <div style="text-align:center; color:grey; padding:5px; font-size:17px">
                                                No incidents found...
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            @else
                                <tbody>
                                    <tr>
                                        <td colspan="9" class="text-center">
                                            <img style="padding-top:7%; max-width:250px;" src="{{ asset('images/nodata.png') }}" alt="No data">
                                        </td>
                                    </tr>
                                </tbody>
                            @endif
                        </table>

                            {{-- Pagination --}}
                            <nav class="text-center" style="float: right">
                                <ul class="pagination rounded-corners">
                                    @if(isset($incidents))
                                        {{ $incidents->links() }}
                                    @endif
                                </ul>
                            </nav>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

      <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="authorizationModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-gavel"></i> Authorize Incident <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Authorize</label>
                    <select class="form-control" wire:model.debounce.300ms="authorize">
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