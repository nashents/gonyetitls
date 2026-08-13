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
                                FanTracker Device Mapping
                                <span class="badge badge-info">{{ $trackers->count() }} tracker(s) on account</span>
                                @if ($this->fanTrackerEnabled)
                                    <a href="#" wire:click.prevent="runAutoMatch" wire:loading.attr="disabled" wire:target="runAutoMatch"
                                       class="btn btn-default border-primary btn-rounded btn-wide" style="float: right">
                                        <span wire:loading.remove wire:target="runAutoMatch"><i class="fa fa-magic"></i> Run Auto-Match</span>
                                        <span wire:loading wire:target="runAutoMatch"><i class="fa fa-spinner fa-spin"></i> Matching...</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%;">
                            @if (! $this->fanTrackerEnabled)
                                <p class="text-muted">
                                    FanTracker isn't active for your company yet. Set it up under
                                    <a href="{{ route('company_integrations.index') }}">Integrations</a> first.
                                </p>
                            @else
                                @if ($apiError)
                                    <p class="text-danger">FanTracker request failed: {{ $apiError }}</p>
                                @endif

                                <p class="text-muted">
                                    <strong>Run Auto-Match</strong> links any tracker whose FanTracker label already
                                    matches a Horse/Trailer/Vehicle's registration number — this is the same match
                                    `php artisan fantracker:match-vehicles` runs. Trackers that don't auto-match
                                    (usually because they're labelled with something other than a registration
                                    number) can be linked by hand below instead.
                                </p>

                                <div class="form-group" style="max-width:320px;">
                                    <input type="text" class="form-control" wire:model.debounce.300ms="search" placeholder="Search by tracker label...">
                                </div>

                                <table class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="th-sm">Tracker Label</th>
                                            <th class="th-sm">Tracker ID</th>
                                            <th class="th-sm">Mapped To</th>
                                            <th class="th-sm">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($trackers as $tracker)
                                        @php $mapping = $mappings->get((string) $tracker['id']); @endphp
                                        <tr>
                                            <td>{{ $tracker['label'] ?? '-' }}</td>
                                            <td>{{ $tracker['id'] }}</td>
                                            <td>
                                                @if ($mapping)
                                                    <span class="badge bg-success">{{ $mapping->local_reference ?? ('#' . $mapping->local_id) }}</span>
                                                @else
                                                    <span class="badge bg-secondary">Unmapped</span>
                                                @endif
                                            </td>
                                            <td class="w-10 line-height-35">
                                                <a href="#" class="btn btn-default btn-sm" wire:click.prevent="openMapModal({{ $tracker['id'] }}, '{{ addslashes($tracker['label'] ?? '') }}')">
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

                                        @if ($trackers->isEmpty())
                                        <tr>
                                            <td colspan="4" class="text-center">
                                                No FanTracker trackers found on this account{{ $search ? ' matching "'.$search.'"' : '' }}.
                                            </td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- MAP TRACKER MODAL --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="mapTrackerModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fa fa-link"></i> Map Tracker {{ $mappingLabel }}
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
</div>
