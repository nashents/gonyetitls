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
                                EzyTrack Device Mapping
                                <span class="badge badge-info">{{ $devices->total() }} device(s) seen</span>
                                @if ($this->ezyTrackEnabled)
                                    <a href="#" wire:click.prevent="openImportModal" class="btn btn-default border-primary btn-rounded btn-wide" style="float: right">
                                        <i class="fa fa-upload"></i> Import
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%;">
                            @if (! $this->ezyTrackEnabled)
                                <p class="text-muted">
                                    EzyTrack isn't active for your company yet. Enable it (no credentials needed) under
                                    <a href="{{ route('company_integrations.index') }}">Integrations</a> first.
                                </p>
                            @else
                                <div class="alert alert-info">
                                    <strong>Give these to EzyTrack</strong> to configure their push to this system:
                                    <ul class="mb-0">
                                        <li>Endpoint URL: <code>{{ $webhookUrl }}</code></li>
                                        <li>Auth: Bearer token</li>
                                        <li>Token: <code>{{ $webhookTokenMasked }}</code> &mdash; full value lives in this server's <code>.env</code> as <code>EZYTRACK_WEBHOOK_TOKEN</code>; ask an administrator for it.</li>
                                    </ul>
                                </div>

                                <p class="text-muted">
                                    Devices below are every EzyTrack tracker that has pushed us a position, identified only
                                    by its serial number. Map each one to the Horse/Trailer/Vehicle it's fitted to so it
                                    shows up correctly on the Live Fleet Map.
                                </p>

                                <div class="d-flex align-items-center mb-2">
                                    <label class="mr-2 mb-0">Show</label>
                                    <select class="form-control form-control-sm w-auto" wire:model="perPage">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                    <span class="ml-2">per page</span>
                                </div>

                                <table class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="th-sm">Serial No.</th>
                                            <th class="th-sm">IMEI</th>
                                            <th class="th-sm">Last Seen</th>
                                            <th class="th-sm">Last Position</th>
                                            <th class="th-sm">Mapped To</th>
                                            <th class="th-sm">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($devices as $device)
                                        @php $mapping = $mappings->get($device->serial_number); @endphp
                                        <tr>
                                            <td>{{ $device->serial_number }}</td>
                                            <td>{{ $device->imei }}</td>
                                            <td>{{ $device->last_seen_at ? $device->last_seen_at->diffForHumans() : '-' }}</td>
                                            <td>
                                                @if ($device->hasPosition())
                                                    {{ number_format($device->latitude, 5) }}, {{ number_format($device->longitude, 5) }}
                                                    <br><small class="text-muted">{{ optional($device->last_gps_at)->diffForHumans() }}</small>
                                                @else
                                                    <span class="text-muted">No fix yet</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($mapping)
                                                    <span class="badge bg-success">{{ $mapping->local_reference ?? ('#' . $mapping->local_id) }}</span>
                                                @else
                                                    <span class="badge bg-secondary">Unmapped</span>
                                                @endif
                                            </td>
                                            <td class="w-10 line-height-35">
                                                <a href="#" class="btn btn-default btn-sm" wire:click.prevent="openMapModal({{ $device->id }})">
                                                    <i class="fa fa-link"></i> {{ $mapping ? 'Re-map' : 'Map' }}
                                                </a>
                                                @if ($mapping)
                                                    <a href="#" class="btn btn-danger btn-sm"
                                                       wire:click.prevent="unmap({{ $mapping->id }})"
                                                       onclick="return confirm('Remove this mapping?')">
                                                        <i class="fa fa-unlink"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach

                                        @if ($devices->isEmpty())
                                        <tr>
                                            <td colspan="6" class="text-center">
                                                No EzyTrack devices have pushed us a position yet.
                                            </td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>

                                {{ $devices->links() }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- MAP DEVICE MODAL --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="mapDeviceModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fa fa-link"></i> Map Device {{ $mappingSerial }}
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </h4>
                </div>
                <form wire:submit.prevent="saveMapping()">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Fleet Unit Type<span class="required" style="color:red">*</span></label>
                            <select class="form-control" wire:model="entityType">
                                <option value="vehicle">Vehicle</option>
                                <option value="horse">Horse</option>
                                <option value="trailer">Trailer</option>
                            </select>
                            @error('entityType') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Unit<span class="required" style="color:red">*</span></label>
                            <select class="form-control" wire:model="localId">
                                <option value="">Select {{ ucfirst($entityType) }}</option>
                                @foreach ($this->entityOptions as $option)
                                    <option value="{{ $option->id }}">{{ $option->fleet_number ?? $option->registration_number }}</option>
                                @endforeach
                            </select>
                            @error('localId') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
                            <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i> Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- IMPORT DEVICES MODAL --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="importDevicesModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fa fa-upload"></i> Import Devices from EzyTrack
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </h4>
                </div>
                <div class="modal-body">
                    <p class="text-muted">
                        Upload EzyTrack's <strong>Asset Listing</strong> export (Asset Id, Asset Type, Name, Description, Serial Number columns).
                        Each row is matched by registration number — extracted from "Name", stripping any trailing
                        <code>(Int Sim)</code>-style suffix — against your Horses (Asset Type "Trucks"), Trailers, or Vehicles,
                        and linked to the device in the "Serial Number" column.
                    </p>
                    <div class="form-group">
                        <label>Excel / CSV File<span class="required" style="color:red">*</span></label>
                        <input type="file" class="form-control" wire:model="importFile">
                        @error('importFile') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        <div wire:loading wire:target="importFile" class="text-muted mt-1">
                            <i class="fa fa-spinner fa-spin"></i> Uploading...
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
                        <button type="button" wire:click.prevent="import" wire:loading.attr="disabled" wire:target="import"
                                class="btn bg-success btn-wide btn-rounded">
                            <span wire:loading.remove wire:target="import"><i class="fa fa-upload"></i> Import</span>
                            <span wire:loading wire:target="import"><i class="fa fa-spinner fa-spin"></i> Importing...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
