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
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                            <div class="panel-title">
                                <div class="row">
                                    <div class="col-lg-2">
                                        <div class="input-group">
                                            <span class="input-group-addon">From</span>
                                            <input type="date" wire:model.debounce.300ms="from" class="form-control" aria-label="...">
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <div class="input-group">
                                            <span class="input-group-addon">To</span>
                                            <input type="date" wire:model.debounce.300ms="to" class="form-control" aria-label="...">
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="input-group">
                                            <span class="input-group-addon">User</span>
                                            <select wire:model.debounce.300ms="selectedUser" class="form-control" aria-label="...">
                                                <option value="">All Users</option>
                                                @foreach ($users as $user)
                                                    <option value="{{$user->id}}">{{$user->name}} {{$user->surname}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <div class="input-group">
                                            <span class="input-group-addon">Event</span>
                                            <select wire:model.debounce.300ms="selectedEvent" class="form-control" aria-label="...">
                                                <option value="">All Events</option>
                                                @foreach ($events as $event)
                                                    <option value="{{$event}}">{{ucfirst($event)}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="input-group">
                                            <span class="input-group-addon">Model</span>
                                            <select wire:model.debounce.300ms="selectedModel" class="form-control" aria-label="...">
                                                <option value="">All Models</option>
                                                @foreach ($models as $model)
                                                    <option value="{{$model}}">{{class_basename($model)}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="panel-title">
                                <a href="#" wire:click.prevent="resetFilters()" class="btn btn-default"><i class="fa fa-times"></i>Clear Filters</a>
                                <a href="#" wire:click.prevent="exportAuditLogsExcel()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                <a href="#" wire:click.prevent="exportAuditLogsCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                            </div>
                            <div class="col-md-5" style="float: right; padding-right:2px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search audit logs...">
                                </div>
                            </div>

                            <table class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th class="th-sm">Date & Time</th>
                                        <th class="th-sm">User</th>
                                        <th class="th-sm">Event</th>
                                        <th class="th-sm">Model</th>
                                        <th class="th-sm">Record ID</th>
                                        <th class="th-sm">IP Address</th>
                                        <th class="th-sm">Actions</th>
                                    </tr>
                                </thead>
                                @if (isset($audits))
                                <tbody>
                                    @forelse ($audits as $audit)
                                    <tr>
                                        <td>{{ $audit->created_at ? $audit->created_at->format('Y-m-d H:i:s') : '' }}</td>
                                        <td>{{ $audit->user ? trim($audit->user->name.' '.$audit->user->surname) : 'System' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $audit->event == 'created' ? 'success' : ($audit->event == 'deleted' ? 'danger' : ($audit->event == 'restored' ? 'info' : 'warning')) }}">
                                                {{ ucfirst($audit->event) }}
                                            </span>
                                        </td>
                                        <td>{{ class_basename($audit->auditable_type) }}</td>
                                        <td>{{ $audit->auditable_id }}</td>
                                        <td>{{ $audit->ip_address }}</td>
                                        <td class="w-10 line-height-35 table-dropdown">
                                            <a href="#" wire:click.prevent="view({{ $audit->id }})"><i class="fa fa-eye color-default"></i> View</a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7">
                                            <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                No Audit Logs Found ....
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
                                    @if (isset($audits))
                                        @if ($audits->count()>0)
                                            {{ $audits->links() }}
                                        @endif
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="auditViewModal" tabindex="-1" role="dialog" aria-labelledby="auditViewModalLabel" data-backdrop-color="blue">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="auditViewModalLabel"><i class="fas fa-history"></i> Audit Details <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></h4>
                </div>
                <div class="modal-body">
                    @if ($viewingAudit)
                        <div class="row">
                            <div class="col-md-6"><strong>Date &amp; Time:</strong> {{ $viewingAudit->created_at ? $viewingAudit->created_at->format('Y-m-d H:i:s') : '' }}</div>
                            <div class="col-md-6"><strong>User:</strong> {{ $viewingAudit->user ? trim($viewingAudit->user->name.' '.$viewingAudit->user->surname) : 'System' }}</div>
                            <div class="col-md-6"><strong>Event:</strong> {{ ucfirst($viewingAudit->event) }}</div>
                            <div class="col-md-6"><strong>Model:</strong> {{ class_basename($viewingAudit->auditable_type) }} #{{ $viewingAudit->auditable_id }}</div>
                            <div class="col-md-6"><strong>IP Address:</strong> {{ $viewingAudit->ip_address }}</div>
                            <div class="col-md-12"><strong>URL:</strong> {{ $viewingAudit->url }}</div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Old Values</h5>
                                <pre style="white-space: pre-wrap; word-break: break-word;">{{ json_encode($viewingAudit->old_values, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                            <div class="col-md-6">
                                <h5>New Values</h5>
                                <pre style="white-space: pre-wrap; word-break: break-word;">{{ json_encode($viewingAudit->new_values, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
