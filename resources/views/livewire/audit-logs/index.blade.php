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

                            @if ($investigation)
                                @php $reg = $investigation['register']; @endphp
                                <div class="alert alert-info" style="padding:10px 14px">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <i class="fas fa-search"></i>
                                            Investigating attendance register <strong>{{ $reg->attendance_number }}</strong>
                                            &middot; {{ $reg->department->name ?? '' }} &middot; {{ $reg->date }} {{ $reg->time ? '@ ' . $reg->time : '' }}
                                            &middot; {{ $reg->isDraft() ? 'Draft' : 'Final' }}
                                            @if ($reg->trashed()) <span class="label label-danger">Deleted</span> @endif
                                            <br><small class="text-muted">Showing the register's own changes and every employee row captured on it, including deleted rows.</small>
                                        </div>
                                        <div class="col-md-4">
                                            <select wire:model="attendanceEmployee" class="form-control">
                                                <option value="">All employees on this register</option>
                                                @foreach ($investigation['employees'] as $emp)
                                                    <option value="{{ $emp->id }}">{{ $emp->name }} {{ $emp->surname }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 text-right">
                                            <a href="{{ route('attendances.show', $reg->id) }}" class="btn btn-default btn-sm"><i class="fa fa-eye"></i> Register</a>
                                        </div>
                                    </div>
                                    @if (filled($attendanceEmployee))
                                        <div style="margin-top:8px">
                                            <strong>Currently on the register:</strong>
                                            @if ($investigation['current'] && filled($investigation['current']->status))
                                                <span class="label label-success">Marked - {{ ucwords(str_replace('_', ' ', $investigation['current']->status)) }}</span>
                                            @else
                                                <span class="label label-default">Not marked</span>
                                            @endif
                                            &middot; <strong>Audit entries for this employee:</strong> {{ $audits->total() }}
                                            @if ($audits->total() === 0)
                                                <span class="text-muted">- no save of this person on this register was ever recorded.</span>
                                            @endif
                                        </div>
                                    @endif
                                    @if (count($investigation['lost']))
                                        <div style="margin-top:8px" class="text-danger">
                                            <i class="fa fa-exclamation-triangle"></i>
                                            {{ count($investigation['lost']) }} row(s) were saved while this register was being edited but never got linked to it. They're flagged <span class="label label-danger">Not linked</span> below. A bug in the old Edit screen caused this, and it's fixed now.
                                        </div>
                                    @endif
                                </div>
                            @endif

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
                                    <tr wire:key="{{ '6b6636-89-' . ($loop->parent?->index ?? 'r') . '-' . $loop->index . '-' . ($audit->id ?? '') }}">
                                        <td>{{ $audit->created_at ? $audit->created_at->format('Y-m-d H:i:s') : '' }}</td>
                                        <td>{{ $audit->user ? trim($audit->user->name.' '.$audit->user->surname) : 'System' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $audit->event == 'created' ? 'success' : ($audit->event == 'deleted' ? 'danger' : ($audit->event == 'restored' ? 'info' : 'warning')) }}">
                                                {{ ucfirst($audit->event) }}
                                            </span>
                                        </td>
                                        <td>{{ class_basename($audit->auditable_type) }}</td>
                                        <td>
                                            {{ $audit->auditable_id }}
                                            @if ($recordLabel = $resolver->label($audit->auditable_type, $audit->auditable_id))
                                                <br><small class="text-muted">{{ $recordLabel }}</small>
                                            @endif
                                            @if ($investigation && $audit->auditable_type === \App\Models\AttendanceRegister::class && in_array($audit->auditable_id, $investigation['lost']))
                                                <br><span class="label label-danger" title="Saved during an edit of this register but never linked to it">Not linked</span>
                                            @endif
                                        </td>
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
                            <div class="col-md-6"><strong>Model:</strong> {{ class_basename($viewingAudit->auditable_type) }} #{{ $viewingAudit->auditable_id }}
                                @if ($viewLabel = $resolver->label($viewingAudit->auditable_type, $viewingAudit->auditable_id)) ({{ $viewLabel }}) @endif
                            </div>
                            <div class="col-md-6"><strong>IP Address:</strong> {{ $viewingAudit->ip_address }}</div>
                            <div class="col-md-12"><strong>URL:</strong> {{ $viewingAudit->url }}</div>
                        </div>
                        <hr>
                        @if (count($viewingChanges))
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr><th style="width:25%">Field</th><th>Old Value</th><th>New Value</th></tr>
                                </thead>
                                <tbody>
                                    @foreach ($viewingChanges as $change)
                                        <tr wire:key="audit-change-{{ $viewingAudit->id }}-{{ $change['field'] }}">
                                            <td><code>{{ $change['field'] }}</code></td>
                                            <td style="word-break: break-word">{{ $change['old'] ?? '' }}</td>
                                            <td style="word-break: break-word">{{ $change['new'] ?? '' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-muted">No field values were recorded for this entry.</p>
                        @endif
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
