<div>
    <div wire:ignore.self
         data-backdrop="static"
         data-keyboard="false"
         class="modal"
         id="statusModal"
         tabindex="-1"
         role="dialog">

        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title">
                        <i class="fas fa-edit"></i>
                        Update Trip {{ $trip_number }} Status
                    </h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form wire:submit.prevent="update">
                    <div class="modal-body">
                       
                        {{-- ===== STATUS / DATE / NOTES ===== --}}
                        <div class="row">
                          
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Trip Statuses <span class="text-danger">*</span></label>
                                    <select class="form-control"
                                            wire:model="selectedStatus"
                                            required>
                                        <option value="">Select Status</option>
                                        @foreach ([
                                            'Scheduled', 'Started',
                                            'Loading Point', 'Loaded',
                                            'InTransit',
                                            'Offloading Point', 'Offloaded',
                                            'OnHold', 'Cancelled',
                                        ] as $s)
                                            <option value="{{ $s }}">{{ $s }}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedStatus')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Status Date <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control"
                                           wire:model.debounce.300ms="trip_status_date"
                                           required>
                                    @error('trip_status_date')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Status Notes</label>
                                    <textarea class="form-control"
                                              wire:model.debounce.300ms="trip_status_description"
                                              rows="2"
                                              placeholder="Additional notes..."></textarea>
                                    @error('trip_status_description')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- ===== MILEAGE / HOURS ===== --}}
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Starting Mileage</label>
                                    <input type="number" step="any" class="form-control"
                                           wire:model.debounce.300ms="starting_mileage"
                                           placeholder="Starting mileage">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Ending Mileage</label>
                                    @if ($starting_mileage)
                                        <input type="number" step="any"
                                               min="{{ $starting_mileage }}"
                                               class="form-control"
                                               wire:model.debounce.300ms="ending_mileage"
                                               placeholder="Ending mileage">
                                    @else
                                        <input type="number" step="any" class="form-control"
                                               wire:model.debounce.300ms="ending_mileage"
                                               placeholder="Ending mileage" disabled>
                                        <small class="text-danger">Set starting mileage first</small>
                                    @endif
                                    @error('ending_mileage')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Starting Hours</label>
                                    <input type="number" step="any" class="form-control"
                                           wire:model.debounce.300ms="starting_hours"
                                           placeholder="Starting hours">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Ending Hours</label>
                                    @if ($starting_hours)
                                        <input type="number" step="any"
                                               min="{{ $starting_hours }}"
                                               class="form-control"
                                               wire:model.debounce.300ms="ending_hours"
                                               placeholder="Ending hours">
                                    @else
                                        <input type="number" step="any" class="form-control"
                                               wire:model.debounce.300ms="ending_hours"
                                               placeholder="Ending hours" disabled>
                                        <small class="text-danger">Set starting hours first</small>
                                    @endif
                                    @error('ending_hours')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- ============================================================ --}}
                        {{-- Only show delivery note fields for Loaded / Offloaded        --}}
                        {{-- ============================================================ --}}
                        @if (in_array($selectedStatus, ['Loaded', 'Offloaded']))

                            @php
                                $units_of_measures = App\Models\UnitsOfMeasure::orderBy('name', 'asc')->get();
                                $user              = Auth::user();
                                $employee          = $user->employee;
                                $department_names  = $employee?->departments?->pluck('name')->toArray() ?? [];
                                $role_names        = $user?->roles?->pluck('name')->toArray() ?? [];
                                $canEditRates      = !(
                                    $employee->company->rates_managed_by_finance == 1 &&
                                    ! (in_array('Finance', $department_names) || in_array('Super Admin', $role_names))
                                );
                            @endphp

                            {{-- ======================================================== --}}
                            {{-- PATH A — NEW: per-TTO cards                              --}}
                            {{-- ======================================================== --}}
                            @if ($useTtoPath && $trip_transport_orders->count() > 0)

                                @foreach ($trip_transport_orders as $tto)
                                    @php
                                        $transport_order = $tto->transport_order;
                                        $cargo_type      = $transport_order?->cargo?->type;
                                        $ttoId           = $tto->id;
                                        $hasTransporter  = ($transport_order?->transporter_agreement == true)
                                                        || $trip_transporter_agreement;
                                    @endphp

                                    <div class="card mb-3 border">
                                        <div class="card-header bg-light py-2">
                                            <strong>
                                                <i class="fas fa-truck mr-1"></i>
                                                Transport Order: {{ $transport_order?->reference ?? "Order# $ttoId" }}
                                            </strong>
                                            @if ($cargo_type)
                                                <span class="badge badge-secondary ml-2">{{ $cargo_type }}</span>
                                            @endif
                                        </div>
                                        <div class="card-body">
                                            @include('livewire.trips.partials.freight-calc-radios')
                                            @include('livewire.trips.partials.tto-loading-fields', [
                                                'ttoId'             => $ttoId,
                                                'cargo_type'        => $cargo_type,
                                                'units_of_measures' => $units_of_measures,
                                                'canEditRates'      => $canEditRates,
                                                'hasTransporter'    => $hasTransporter,
                                            ])
                                            @if ($selectedStatus === 'Offloaded')
                                                @include('livewire.trips.partials.tto-offloading-fields', [
                                                    'ttoId'             => $ttoId,
                                                    'cargo_type'        => $cargo_type,
                                                    'units_of_measures' => $units_of_measures,
                                                    'canEditRates'      => $canEditRates,
                                                    'hasTransporter'    => $hasTransporter,
                                                ])
                                            @endif
                                        </div>
                                    </div>
                                @endforeach

                            {{-- ======================================================== --}}
                            {{-- PATH B — LEGACY: single flat delivery note               --}}
                            {{-- ======================================================== --}}
                            @else

                                @php $hasTransporter = $trip_transporter_agreement; @endphp

                                <div class="card mb-3 border">
                                    <div class="card-header bg-light py-2">
                                        <strong>
                                            <i class="fas fa-truck mr-1"></i>
                                            Loading / Offloading Details
                                        </strong>
                                        @if ($trip_cargo_type)
                                            <span class="badge badge-secondary ml-2">{{ $trip_cargo_type }}</span>
                                        @endif
                                    </div>
                                    <div class="card-body">

                                        @include('livewire.trips.partials.freight-calc-radios')

                                        {{-- Loading Details --}}
                                        <h6 class="font-weight-bold mt-3 mb-2 border-bottom pb-1">Loading Details</h6>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Date <span class="text-danger">*</span></label>
                                                    <input type="datetime-local" class="form-control"
                                                           wire:model.debounce.300ms="loaded_date"
                                                           {{ $selectedStatus === 'Offloaded' ? 'disabled' : '' }}
                                                           required>
                                                    @error('loaded_date')
                                                        <span class="text-danger small">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Distance</label>
                                                    <input type="number" step="any" class="form-control"
                                                           wire:model.debounce.300ms="distance"
                                                           placeholder="Trip distance"
                                                           {{ $selectedStatus === 'Offloaded' ? 'disabled' : '' }}>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Weight @if ($trip_cargo_type === 'Solid') <span class="text-danger">*</span> @endif</label>
                                                    <input type="number" step="any" class="form-control"
                                                           wire:model.debounce.300ms="loaded_weight"
                                                           placeholder="Loading weight"
                                                           {{ $selectedStatus === 'Offloaded' ? 'disabled' : '' }}
                                                           {{ $trip_cargo_type === 'Solid' ? 'required' : '' }}>
                                                    @error('loaded_weight')
                                                        <span class="text-danger small">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        @if ($trip_cargo_type === 'Solid')
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Quantity</label>
                                                        <input type="number" step="any" class="form-control"
                                                               wire:model.debounce.300ms="loaded_quantity"
                                                               placeholder="Loaded quantity"
                                                               {{ $selectedStatus === 'Offloaded' ? 'disabled' : '' }}>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Unit of Measure</label>
                                                        <select class="form-control"
                                                                wire:model.debounce.300ms="units_of_measure_id">
                                                            <option value="">Select UOM</option>
                                                            @foreach ($units_of_measures as $uom)
                                                                <option value="{{ $uom->id }}">{{ $uom->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif ($trip_cargo_type === 'Liquid')
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Litreage @ Ambient</label>
                                                        <input type="number" step="any" class="form-control"
                                                               wire:model.debounce.300ms="loaded_litreage"
                                                               placeholder="Litreage @ ambient"
                                                               {{ $selectedStatus === 'Offloaded' ? 'disabled' : '' }}>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Litreage @ 20°C <span class="text-danger">*</span></label>
                                                        <input type="number" step="any" class="form-control"
                                                               wire:model.debounce.300ms="loaded_litreage_at_20"
                                                               placeholder="Litreage @ 20°C"
                                                               {{ $selectedStatus === 'Offloaded' ? 'disabled' : '' }}
                                                               required>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Unit of Measure</label>
                                                        <select class="form-control"
                                                                wire:model.debounce.300ms="units_of_measure_id">
                                                            <option value="">Select UOM</option>
                                                            @foreach ($units_of_measures as $uom)
                                                                <option value="{{ $uom->id }}">{{ $uom->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Currency <span class="text-danger">*</span></label>
                                                    <select class="form-control"
                                                            wire:model.debounce.300ms="currency_id"
                                                            disabled>
                                                        <option value="">Select Currency</option>
                                                        @foreach ($currencies as $currency)
                                                            <option value="{{ $currency->id }}">
                                                                {{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Customer Rate <span class="text-danger">*</span></label>
                                                    <input type="number" step="any" class="form-control"
                                                           wire:model.debounce.300ms="loaded_rate"
                                                           placeholder="Customer rate"
                                                           {{ ($selectedStatus === 'Offloaded' || ! $canEditRates) ? 'disabled' : '' }}
                                                           required>
                                                    @error('loaded_rate')
                                                        <span class="text-danger small">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Customer Freight <span class="text-danger">*</span></label>
                                                    <input type="number" step="any" class="form-control"
                                                           wire:model.debounce.300ms="loaded_freight"
                                                           placeholder="Customer freight"
                                                           {{ ($selectedStatus === 'Offloaded' || ! $canEditRates) ? 'disabled' : '' }}
                                                           required>
                                                    @error('loaded_freight')
                                                        <span class="text-danger small">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        @if ($hasTransporter)
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Transporter Rate</label>
                                                        <input type="number" step="any" class="form-control"
                                                               wire:model.debounce.300ms="transporter_loaded_rate"
                                                               placeholder="Transporter rate"
                                                               {{ ($selectedStatus === 'Offloaded' || ! $canEditRates) ? 'disabled' : '' }}>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Transporter Freight</label>
                                                        <input type="number" step="any" class="form-control"
                                                               wire:model.debounce.300ms="transporter_loaded_freight"
                                                               placeholder="Transporter freight"
                                                               {{ ($selectedStatus === 'Offloaded' || ! $canEditRates) ? 'disabled' : '' }}>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Offloading Details --}}
                                        @if ($selectedStatus === 'Offloaded')

                                            <h6 class="font-weight-bold mt-4 mb-2 border-bottom pb-1">Offloading Details</h6>

                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Date <span class="text-danger">*</span></label>
                                                        <input type="datetime-local" class="form-control"
                                                               wire:model.debounce.300ms="offloaded_date"
                                                               required>
                                                        @error('offloaded_date')
                                                            <span class="text-danger small">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>
                                                            Distance
                                                            @if (in_array($freight_calculation, ['rate_weight_distance', 'rate_distance']))
                                                                <span class="text-danger">*</span>
                                                            @endif
                                                        </label>
                                                        <input type="number" step="any" class="form-control"
                                                               wire:model.debounce.300ms="offloaded_distance"
                                                               placeholder="Trip distance"
                                                               {{ in_array($freight_calculation, ['rate_weight_distance', 'rate_distance']) ? 'required' : '' }}>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Weight @if ($trip_cargo_type === 'Solid') <span class="text-danger">*</span> @endif</label>
                                                        <input type="number" step="any" class="form-control"
                                                               wire:model.debounce.300ms="offloaded_weight"
                                                               placeholder="Offloaded weight"
                                                               {{ $trip_cargo_type === 'Solid' ? 'required' : '' }}>
                                                    </div>
                                                </div>
                                            </div>

                                            @if ($trip_cargo_type === 'Solid')
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Quantity</label>
                                                            <input type="number" step="any" class="form-control"
                                                                   wire:model.debounce.300ms="offloaded_quantity"
                                                                   placeholder="Offloaded quantity">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Unit of Measure</label>
                                                            <select class="form-control"
                                                                    wire:model.debounce.300ms="units_of_measure_id"
                                                                    disabled>
                                                                <option value="">Select UOM</option>
                                                                @foreach ($units_of_measures as $uom)
                                                                    <option value="{{ $uom->id }}">{{ $uom->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            @elseif ($trip_cargo_type === 'Liquid')
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Litreage @ Ambient</label>
                                                            <input type="number" step="any" class="form-control"
                                                                   wire:model.debounce.300ms="offloaded_litreage"
                                                                   placeholder="Litreage @ ambient">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Litreage @ 20°C <span class="text-danger">*</span></label>
                                                            <input type="number" step="any" class="form-control"
                                                                   wire:model.debounce.300ms="offloaded_litreage_at_20"
                                                                   placeholder="Litreage @ 20°C"
                                                                   required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Unit of Measure</label>
                                                            <select class="form-control"
                                                                    wire:model.debounce.300ms="units_of_measure_id"
                                                                    disabled>
                                                                <option value="">Select UOM</option>
                                                                @foreach ($units_of_measures as $uom)
                                                                    <option value="{{ $uom->id }}">{{ $uom->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Currency <span class="text-danger">*</span></label>
                                                        <select class="form-control"
                                                                wire:model.debounce.300ms="currency_id"
                                                                disabled>
                                                            <option value="">Select Currency</option>
                                                            @foreach ($currencies as $currency)
                                                                <option value="{{ $currency->id }}">
                                                                    {{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Customer Rate <span class="text-danger">*</span></label>
                                                        <input type="number" step="any" class="form-control"
                                                               wire:model.debounce.300ms="offloaded_rate"
                                                               placeholder="Offloading rate"
                                                               {{ ! $canEditRates ? 'disabled' : '' }}
                                                               required>
                                                        @error('offloaded_rate')
                                                            <span class="text-danger small">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Customer Freight <span class="text-danger">*</span></label>
                                                        <input type="number" step="any" class="form-control"
                                                               wire:model.debounce.300ms="offloaded_freight"
                                                               placeholder="Offloading freight"
                                                               {{ ! $canEditRates ? 'disabled' : '' }}
                                                               required>
                                                        @error('offloaded_freight')
                                                            <span class="text-danger small">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            @if ($hasTransporter)
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Transporter Rate</label>
                                                            <input type="number" step="any" class="form-control"
                                                                   wire:model.debounce.300ms="transporter_offloaded_rate"
                                                                   placeholder="Transporter offloading rate"
                                                                   {{ ! $canEditRates ? 'disabled' : '' }}>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Transporter Freight</label>
                                                            <input type="number" step="any" class="form-control"
                                                                   wire:model.debounce.300ms="transporter_offloaded_freight"
                                                                   placeholder="Transporter offloading freight"
                                                                   {{ ! $canEditRates ? 'disabled' : '' }}>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="form-group mt-2">
                                                <label>Comments</label>
                                                <textarea class="form-control"
                                                          wire:model.debounce.300ms="comments"
                                                          placeholder="Additional offloading notes..."
                                                          rows="3"></textarea>
                                            </div>

                                        @endif {{-- /Offloaded --}}

                                    </div>{{-- /card-body --}}
                                </div>{{-- /card --}}

                            @endif {{-- /path A vs B --}}

                        @endif {{-- /in_array Loaded|Offloaded --}}

                    </div>{{-- /modal-body --}}

                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button"
                                    class="btn btn-gray btn-wide btn-rounded"
                                    data-dismiss="modal">
                                <i class="fa fa-times"></i> Close
                            </button>
                            <button type="submit"
                                    class="btn bg-success btn-wide btn-rounded">
                                <i class="fa fa-refresh"></i> Update
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>