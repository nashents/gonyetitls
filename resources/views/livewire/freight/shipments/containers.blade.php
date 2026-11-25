<div>
    @if ($isHouse && $shipment->containers->isEmpty())
        {{-- LCL client shipment: the shared container is shown above (from the master job). --}}
        <p class="text-muted"><i class="fa fa-info-circle"></i> Consolidated shipment - its cargo travels in the master container shown above. Containers are added and updated on the master job.</p>
    @else
    @unless ($isHouse)
    <div class="mb-10">
        <a href="#" class="btn btn-xs btn-info" data-toggle="modal" data-target="#addContainerModal{{ $shipment->id }}">
            <i class="fa fa-plus"></i> Add Container
        </a>
    </div>
    @endunless

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Container #</th>
                <th>Type</th>
                <th>Seal #</th>
                <th>Shipping Line</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($shipment->containers as $container)
                <tr wire:key="{{ 'e0bf4a-20-' . ($loop->parent?->index ?? 'r') . '-' . $loop->index . '-' . ($container->id ?? '') }}">
                    <td>{{ $container->container_number }}</td>
                    <td>{{ $container->container_type }}</td>
                    <td>{{ $container->seal_number }}</td>
                    <td>{{ $container->shipping_line?->name ?? $container->shipping_line_vendor?->name ?? $container->shipping_line_name }}</td>
                    <td>
                        <span class="label label-info label-wide">{{ $lifecycleStages[$container->status] ?? $container->status }}</span>
                        @php
                            // Every status change records a completed milestone (ShippingContainerService::transitionStatus),
                            // so these rows are the status trail. Newest update first.
                            $statusTrail = $container->milestones->sortByDesc('id');
                        @endphp
                        @if ($statusTrail->isNotEmpty())
                            <ul class="list-unstyled" style="margin:6px 0 0; max-height:110px; overflow-y:auto; font-size:11px; line-height:1.5">
                                @foreach ($statusTrail as $change)
                                    <li class="{{ $loop->first ? '' : 'text-muted' }}" wire:key="container-{{ $container->id }}-trail-{{ $change->id }}">
                                        <strong>{{ $change->milestone_name }}</strong>
                                        &middot; {{ ($change->actual_at ?? $change->created_at)?->format('d M Y H:i') }}
                                        &middot; {{ $change->creator ? trim($change->creator->name . ' ' . $change->creator->surname) : 'System' }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </td>
                    <td>
                        <a href="#" wire:click.prevent="toggleExpand({{ $container->id }})" class="btn btn-xs btn-default"><i class="fa fa-eye"></i> Details</a>
                        @if ($container->nextLifecycleStage())
                            <div style="margin-top:6px; white-space:nowrap;">
                                <input type="datetime-local" class="form-control input-sm" style="display:inline-block; width:auto;"
                                       wire:model.defer="stage_dates.{{ $container->id }}" max="{{ now()->format('Y-m-d\TH:i') }}"
                                       title="When this stage actually happened - free-time clocks start/stop from this date. Leave blank for now.">
                                <a href="#" wire:click.prevent="advanceStage({{ $container->id }})" wire:confirm="Mark this container as '{{ $lifecycleStages[$container->nextLifecycleStage()] }}'?" class="btn btn-xs btn-success">
                                    <i class="fa fa-arrow-right"></i> {{ $lifecycleStages[$container->nextLifecycleStage()] }}
                                </a>
                            </div>
                            @error('stage_dates.' . $container->id) <span class="text-danger error">{{ $message }}</span> @enderror
                        @endif
                    </td>
                </tr>
                @if ($expanded_container_id == $container->id)
                    <tr>
                        <td colspan="6" style="background-color:#f9f9f9;">
                            <strong>Weights:</strong> Tare {{ $container->tare_weight }} / Gross {{ $container->gross_weight }} / Cargo {{ $container->cargo_weight }} / VGM {{ $container->vgm }}
                            @if ($container->temperature)
                                &mdash; Temp: {{ $container->temperature }}
                            @endif

                            <h6 class="underline mt-20 mb-10"><strong>Port &amp; Container Exposure</strong></h6>
                            @if ($container->exposures->count())
                                <table class="table table-condensed table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Charge Type</th><th>Start</th><th>Last Free Day</th><th>Stop</th><th>Chargeable Days</th><th>Est. Exposure</th><th>Actual Charge</th><th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($container->exposures as $exposure)
                                            @php
                                                $statusBadge = [
                                                    'within_free_period' => 'success',
                                                    'expiring_soon' => 'warning',
                                                    'expiring_today' => 'warning',
                                                    'accruing' => 'danger',
                                                    'stopped' => 'default',
                                                ][$exposure->status] ?? 'default';
                                            @endphp
                                            <tr>
                                                <td>{{ $chargeTypes[$exposure->charge_type] ?? $exposure->charge_type }}</td>
                                                <td>{{ $exposure->start_date?->format('d M Y') }}</td>
                                                <td>{{ $exposure->last_free_day?->format('d M Y') }}</td>
                                                <td>{{ $exposure->stop_date?->format('d M Y') }}</td>
                                                <td>{{ $exposure->chargeable_days }}</td>
                                                <td>{{ $exposure->currency?->symbol }}{{ number_format($exposure->estimated_exposure ?? 0, 2) }}</td>
                                                <td>{{ $exposure->actual_charge !== null ? number_format($exposure->actual_charge, 2) : '—' }}</td>
                                                <td><span class="label label-{{ $statusBadge }} label-wide">{{ ucwords(str_replace('_', ' ', $exposure->status)) }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <small class="text-muted">Demurrage and Detention are independent estimates for different possible carrier billing structures &mdash; do not sum them.</small>
                            @else
                                <p class="text-muted">No exposure tracked yet for this container (starts automatically once discharged; requires a free-day policy configured for its shipping line or the generic default).</p>
                            @endif

                            <h6 class="underline mt-20 mb-10"><strong>Milestone History</strong></h6>
                            <table class="table table-condensed">
                                <thead>
                                    <tr><th>Stage</th><th>Status</th><th>Actual</th><th>Recorded</th></tr>
                                </thead>
                                <tbody>
                                    @foreach ($container->milestones as $milestone)
                                        <tr>
                                            <td>{{ $milestone->milestone_name }}</td>
                                            <td>{{ ucfirst($milestone->status) }}</td>
                                            <td>{{ $milestone->actual_at?->format('d M Y H:i') }}</td>
                                            <td>{{ $milestone->created_at?->format('d M Y H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="row mb-20">
                                <div class="col-md-4">
                                    <select class="form-control" wire:model="ad_hoc_milestone_code">
                                        <option value="">Record ad-hoc milestone...</option>
                                        @foreach ($activeStages as $code => $label)
                                            <option value="{{ $code }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="datetime-local" class="form-control" wire:model.defer="ad_hoc_milestone_date" max="{{ now()->format('Y-m-d\TH:i') }}"
                                           title="When this stage actually happened - free-time clocks start/stop from this date. Leave blank for now.">
                                    @error('ad_hoc_milestone_date') <span class="text-danger error">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-2">
                                    <a href="#" wire:click.prevent="recordAdHocMilestone({{ $container->id }})" class="btn btn-xs btn-warning">Record</a>
                                </div>
                            </div>
                            <small class="text-muted">Date = when the stage actually happened (blank = now). Free-time clocks start/stop on that date.
                                <a href="{{ route('freight.settings.container-stages') }}" target="_blank">What does each stage mean?</a></small>

                            <h6 class="underline mt-20 mb-10"><strong>Documents</strong></h6>
                            @livewire('documents.index', ['id' => $container->id, 'category' => 'shipping_container'], key('container-docs-'.$container->id))
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="6" class="text-center">No containers recorded for this shipment yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    @endif

    <!-- Add Container Modal -->
    <div wire:ignore.self class="modal fade" id="addContainerModal{{ $shipment->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="store">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        <h4 class="modal-title">Add Container</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Container Number</label>
                                    <input type="text" class="form-control" wire:model="container_number">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Container Type</label>
                                    <select class="form-control" wire:model="container_type">
                                        <option value="">Select</option>
                                        @foreach (\App\Models\ShippingContainer::CONTAINER_TYPES as $type)
                                            <option value="{{ $type }}">{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Seal Number</label>
                                    <input type="text" class="form-control" wire:model="seal_number">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Shipping Line</label>
                                    <select class="form-control" wire:model="shipping_line_id">
                                        <option value="">Select Shipping Line</option>
                                        @foreach ($shippingLines as $line)
                                            <option value="{{ $line->id }}">{{ $line->name }}</option>
                                        @endforeach
                                    </select>
                                    <small><a href="{{ route('freight.settings.shipping-lines') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Shipping Line</a></small>
                                    <a href="#" wire:click.prevent="refresh('shipping_lines')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tare Wt</label>
                                    <input type="number" step="any" class="form-control" wire:model="tare_weight">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Gross Wt</label>
                                    <input type="number" step="any" class="form-control" wire:model="gross_weight">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Cargo Wt</label>
                                    <input type="number" step="any" class="form-control" wire:model="cargo_weight">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>VGM</label>
                                    <input type="number" step="any" class="form-control" wire:model="vgm">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Temperature</label>
                            <input type="text" class="form-control" wire:model="temperature" placeholder="e.g. -18C">
                        </div>
                        @if ($shipment->cargo_items->count())
                            <div class="form-group">
                                <label>Cargo Lines in this Container</label>
                                @foreach ($shipment->cargo_items as $cargoItem)
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" wire:model="selected_cargo_ids" value="{{ $cargoItem->id }}">
                                            {{ $cargoItem->commodity ?? $cargoItem->cargo?->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn bg-gray" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Container</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        window.addEventListener('hide-addContainerModal-{{ $shipment->id }}', event => {
            $('#addContainerModal{{ $shipment->id }}').modal('hide');
        })
    </script>
</div>
