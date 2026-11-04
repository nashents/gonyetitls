<div>
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="bulkTripsModal" tabindex="-1" role="dialog" aria-labelledby="bulkTripsLabel">
        <div class="modal-dialog" role="document" style="width: 96%; max-width: 1500px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="bulkTripsLabel"><i class="fa fa-truck"></i> Bulk Schedule Trips
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    </h4>
                    <small class="text-muted">Each truck becomes its own trip with its own transport order, awaiting authorization under Pending. Trip expenses can be added below; fuel and allowances are added per trip afterwards.</small>
                </div>
                <form wire:submit.prevent="store()">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group" style="margin-top: 25px;">
                                    <label style="font-weight: bold;"><input type="checkbox" wire:model="sameJob"> Same job for all trucks</label><br>
                                    <small class="text-muted">
                                        @if ($sameJob)
                                            Every truck gets the customer, cargo, route and rate below; only driver, trailers and loads differ.
                                        @else
                                            Each trip has its own customer, cargo, route and rate. A new truck starts with the previous trip's details.
                                        @endif
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Status<span class="required" style="color: red">*</span></label>
                                    <select wire:model="trip_status" class="form-control" required>
                                        @foreach (\App\Http\Livewire\Trips\BulkCreate::STATUSES as $status)
                                            <option value="{{ $status }}">{{ $status }}</option>
                                        @endforeach
                                    </select>
                                    @error('trip_status') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Default Start Date<span class="required" style="color: red">*</span></label>
                                    <input type="date" wire:model.lazy="start_date" class="form-control" required>
                                    <small class="text-muted">Resets every truck's date.</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Default Transporter Rate</label>
                                    <input type="number" step="any" min="0" wire:model.lazy="transporter_rate" class="form-control" placeholder="For subcontracted trucks">
                                    <small class="text-muted">Fills trucks with Transporter Freight ticked (ticked by default for non-own-fleet trucks).</small>
                                </div>
                            </div>
                        </div>

                        @if ($sameJob)
                            <div class="panel panel-default" style="padding: 10px 10px 0; margin-bottom: 10px;">
                                @include('livewire.trips._bulk_job_fields', ['prefix' => '', 'job' => $this->job(0)])
                            </div>
                        @endif

                        {{-- Truck picker --}}
                        <div class="panel panel-default" style="padding: 10px; margin-bottom: 10px;">
                            <div class="row">
                                <div class="col-md-3">
                                    <select wire:model="pickerTransporterId" class="form-control">
                                        <option value="">All transporters</option>
                                        @foreach ($transporters as $transporter)
                                            <option value="{{ $transporter->id }}">{{ $transporter->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" wire:model.debounce.300ms="truckSearch" class="form-control" placeholder="Search trucks by reg / fleet number...">
                                </div>
                                <div class="col-md-5">
                                    <button type="button" wire:click="addPickedTrucks" class="btn btn-default border-primary btn-rounded" @if (empty($pickedHorses)) disabled @endif>
                                        <i class="fa fa-plus"></i> Add {{ count($pickedHorses) ?: '' }} Selected Truck(s)
                                    </button>
                                </div>
                            </div>
                            <div style="max-height: 140px; overflow-y: auto; margin-top: 8px;">
                                @forelse ($trucks as $truck)
                                    <label wire:key="bulk-trip-pick-{{ $truck->id }}" style="display: inline-block; width: 260px; font-weight: normal;">
                                        <input type="checkbox" wire:model="pickedHorses" value="{{ $truck->id }}"> {{ $truck->identifier_label }}
                                        <small class="text-muted">{{ optional($truck->transporter)->name }}</small>
                                    </label>
                                @empty
                                    <small class="text-muted">No more available trucks match.</small>
                                @endforelse
                            </div>
                        </div>

                        @error('rows') <div class="alert alert-danger">{{ $message }}</div> @enderror

                        @if (count($rows) === 0)
                            <p class="text-muted text-center">Tick the trucks above and click <strong>Add Selected Truck(s)</strong>. Each truck's assigned driver and trailers are filled in for you.</p>
                        @elseif ($sameJob)
                            @php($liquid = $this->job(0)['cargo_type'] === 'Liquid')
                            <div style="overflow-x: auto;">
                                <table class="table table-bordered table-sm">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Truck</th>
                                            <th style="min-width: 180px">Driver<span style="color: red">*</span></th>
                                            <th style="min-width: 170px">Trailer(s)</th>
                                            <th style="min-width: 130px">Trip Ref</th>
                                            <th style="width: 150px">Start Date<span style="color: red">*</span></th>
                                            @if ($liquid)
                                                <th style="width: 110px">Litreage</th>
                                            @else
                                                <th style="width: 100px">Weight</th>
                                                <th style="width: 90px">Quantity</th>
                                            @endif
                                            <th class="text-right">Freight</th>
                                            <th style="min-width: 190px">Transporter Freight</th>
                                            <th style="min-width: 130px">Comments</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($rows as $i => $row)
                                            <tr wire:key="bulk-trip-row-{{ $row['horse_id'] }}">
                                                <td><strong>{{ $row['label'] }}</strong><br><small class="text-muted">{{ $row['transporter_name'] }}</small></td>
                                                @include('livewire.trips._bulk_truck_cells', ['cell' => true])
                                                <td>
                                                    <a href="#" wire:click.prevent="removeRow({{ $i }})" class="text-danger" title="Remove truck"><i class="fa fa-times"></i></a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="{{ $liquid ? 6 : 7 }}" class="text-right">{{ count($rows) }} trip(s)</th>
                                            <th class="text-right">{{ number_format($this->totalFreight, 2) }}</th>
                                            <th colspan="3"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            @foreach ($rows as $i => $row)
                                @php($job = $this->job($i))
                                @php($liquid = $job['cargo_type'] === 'Liquid')
                                <div class="panel panel-default" wire:key="bulk-trip-card-{{ $row['horse_id'] }}" style="padding: 10px 10px 0; margin-bottom: 10px;">
                                    <div style="margin-bottom: 8px;">
                                        <strong>Trip {{ $i + 1 }}: {{ $row['label'] }}</strong>
                                        <small class="text-muted">{{ $row['transporter_name'] }}</small>
                                        <span style="float: right;">
                                            @if ($i > 0)
                                                <a href="#" wire:click.prevent="copyPrevious({{ $i }})" title="Copy customer, cargo, route and rate from trip {{ $i }}"><i class="fa fa-copy"></i> Copy previous</a>
                                                &nbsp;
                                            @endif
                                            <a href="#" wire:click.prevent="removeRow({{ $i }})" class="text-danger" title="Remove truck"><i class="fa fa-times"></i> Remove</a>
                                        </span>
                                    </div>
                                    @include('livewire.trips._bulk_job_fields', ['prefix' => "rows.$i.", 'job' => $job])
                                    <table class="table table-bordered table-sm" style="margin-bottom: 10px;">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="min-width: 180px">Driver<span style="color: red">*</span></th>
                                                <th style="min-width: 170px">Trailer(s)</th>
                                                <th style="min-width: 130px">Trip Ref</th>
                                                <th style="width: 150px">Start Date<span style="color: red">*</span></th>
                                                @if ($liquid)
                                                    <th style="width: 110px">Litreage</th>
                                                @else
                                                    <th style="width: 100px">Weight</th>
                                                    <th style="width: 90px">Quantity</th>
                                                @endif
                                                <th class="text-right">Freight</th>
                                                <th style="min-width: 190px">Transporter Freight</th>
                                                <th style="min-width: 130px">Comments</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                @include('livewire.trips._bulk_truck_cells')
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach
                        @endif

                        @include('livewire.trips._bulk_expenses')
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded" wire:loading.attr="disabled" @if (count($rows) === 0) disabled @endif>
                            <i class="fa fa-save"></i> Schedule {{ count($rows) ?: '' }} Trip(s)
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
