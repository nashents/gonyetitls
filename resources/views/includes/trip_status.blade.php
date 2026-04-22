{{-- resources/views/livewire/trips/modals/status-modal.blade.php --}}

<div wire:ignore.self
     data-backdrop="static"
     data-keyboard="false"
     class="modal"
     id="statusModal"
     tabindex="-1"
     role="dialog">

    <div class="modal-dialog mw-100 w-50" role="document">
        <div class="modal-content">

            {{-- ===== HEADER ===== --}}
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-edit"></i>
                    Update Trip {{ $trip?->trip_number }} Status
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form wire:submit.prevent="update">
                <div class="modal-body">

                    {{-- ===== TRIP-LEVEL STATUS FIELDS ===== --}}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Trip Status <span class="text-danger">*</span></label>
                                <select class="form-control"
                                       wire:model.debounce.300ms="selectedStatus"
                                        required>
                                    <option value="">Select Status</option>
                                    @foreach ([
                                        'Scheduled', 'Started',
                                        'Loading Point', 'Loaded',
                                        'InTransit',
                                        'Offloading Point', 'Offloaded',
                                        'OnHold', 'Cancelled',
                                    ] as $status)
                                        <option value="{{ $status }}">{{ $status }}</option>
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
                                <input type="datetime-local"
                                       class="form-control"
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

                    {{-- ===== MILEAGE / HOURS (always shown) ===== --}}
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Starting Mileage</label>
                                <input type="number" step="any"
                                       class="form-control"
                                      wire:model.debounce.300ms="starting_mileage"
                                       placeholder="Starting mileage">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Ending Mileage</label>
                                @if (isset($starting_mileage))
                                    <input type="number" step="any"
                                           min="{{ $starting_mileage }}"
                                           class="form-control"
                                          wire:model.debounce.300ms="ending_mileage"
                                           placeholder="Ending mileage">
                                @else
                                    <input type="number" step="any"
                                           class="form-control"
                                          wire:model.debounce.300ms="ending_mileage"
                                           placeholder="Ending mileage"
                                           disabled>
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
                                <input type="number" step="any"
                                       class="form-control"
                                      wire:model.debounce.300ms="starting_hours"
                                       placeholder="Starting hours">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Ending Hours</label>
                                @if (isset($starting_hours))
                                    <input type="number" step="any"
                                           min="{{ $starting_hours }}"
                                           class="form-control"
                                          wire:model.debounce.300ms="ending_hours"
                                           placeholder="Ending hours">
                                @else
                                    <input type="number" step="any"
                                           class="form-control"
                                          wire:model.debounce.300ms="ending_hours"
                                           placeholder="Ending hours"
                                           disabled>
                                    <small class="text-danger">Set starting hours first</small>
                                @endif
                                @error('ending_hours')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- ===== PER-TRANSPORT-ORDER SECTIONS ===== --}}
                    @if ($trip_transport_orders && $trip_transport_orders->count() > 0)

                        @php
                            $units_of_measures = App\Models\UnitsOfMeasure::orderBy('name','asc')->get();
                            $user              = Auth::user();
                            $employee          = $user->employee;
                            $department_names  = $employee?->departments?->pluck('name')->toArray() ?? [];
                            $role_names        = $user?->roles?->pluck('name')->toArray() ?? [];
                            $canEditRates      = !(
                                $user->employee->company->rates_managed_by_finance == 1 &&
                                !(in_array('Finance', $department_names) || in_array('Super Admin', $role_names))
                            );
                            $isFinanceOrAdmin  = (
                                $user->employee->company->rates_managed_by_finance == 1 &&
                                (in_array('Finance', $department_names) || in_array('Super Admin', $role_names))
                            );
                        @endphp

                        @foreach ($trip_transport_orders as $tto)
                            @php
                                $transport_order = $tto->transport_order;
                                $cargo_type      = $transport_order?->cargo?->type;
                                $ttoId           = $tto->id;
                                $dn              = $deliveryNotes[$ttoId] ?? [];
                                $hasTransporter  = ($transport_order?->transporter_agreement == true)
                                || ($trip?->transporter_agreement == true);
                            @endphp

                            @if (in_array($selectedStatus, ['Loaded', 'Offloaded']))

                                <div class="card mb-3 border">
                                    <div class="card-header bg-light py-2">
                                        <strong>
                                            <i class="fas fa-truck mr-1"></i>
                                            Transport Order:
                                            {{ $transport_order?->reference ?? "Order# $ttoId" }}
                                        </strong>
                                        @if ($cargo_type)
                                            <span class="badge badge-secondary ml-2">
                                                {{ $cargo_type }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="card-body">

                                        {{-- ── Freight Calculation Method (read-only display) ── --}}
                                        <h6 class="font-weight-bold mt-1 mb-2 border-bottom pb-1">
                                            Freight Calculation Method
                                        </h6>

                                        <div class="mb-10">
                                            <input type="radio" wire:model.debounce.300ms="freight_calculation" value="flat_rate"  class="line-style" required />
                                            <label for="one" class="radio-label">Flat Rate</label>
                                            <input type="radio" wire:model.debounce.300ms="freight_calculation" value="rate_weight"  class="line-style" required />
                                            <label for="one" class="radio-label">Rate * Weight/Litreage</label>
                                            <input type="radio" wire:model.debounce.300ms="freight_calculation" value="rate_weight_distance"  class="line-style" required />
                                            <label for="one" class="radio-label">Rate * Distance * Weight/Litreage</label>
                                            <input type="radio" wire:model.debounce.300ms="freight_calculation" value="rate_distance"  class="line-style" required />
                                            <label for="one" class="radio-label">Rate * Distance</label>
                                            @error('freight_calculation') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>

                                        {{-- ── Loading Details ── --}}
                                        <h6 class="font-weight-bold mt-3 mb-2 border-bottom pb-1">
                                            Loading Details
                                        </h6>

                                        <div class="row">
                                            {{-- Loading date --}}
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Date <span class="text-danger">*</span></label>
                                                    <input type="datetime-local"
                                                          class="form-control"
                                                          wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.loaded_date"
                                                           {{ $selectedStatus === 'Offloaded' ? 'disabled' : '' }}
                                                           required>
                                                    @error("deliveryNotes.$ttoId.loaded_date")
                                                        <span class="text-danger small">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- Distance --}}
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Distance</label>
                                                    <input type="number" step="any"
                                                           class="form-control"
                                                          wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.distance"
                                                           placeholder="Trip distance"
                                                           {{ $selectedStatus === 'Offloaded' ? 'disabled' : '' }}>
                                                    @error("deliveryNotes.$ttoId.distance")
                                                        <span class="text-danger small">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- Weight --}}
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>
                                                        Weight
                                                        @if ($cargo_type === 'Solid')
                                                            <span class="text-danger">*</span>
                                                        @endif
                                                    </label>
                                                    <input type="number" step="any"
                                                           class="form-control"
                                                          wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.loaded_weight"
                                                           placeholder="Loading weight"
                                                           {{ $selectedStatus === 'Offloaded' ? 'disabled' : '' }}
                                                           {{ $cargo_type === 'Solid' ? 'required' : '' }}>
                                                    @error("deliveryNotes.$ttoId.loaded_weight")
                                                        <span class="text-danger small">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Solid: quantity + UOM --}}
                                        @if ($cargo_type === 'Solid')
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Quantity</label>
                                                        <input type="number" step="any"
                                                               class="form-control"
                                                              wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.loaded_quantity"
                                                               placeholder="Loaded quantity"
                                                               {{ $selectedStatus === 'Offloaded' ? 'disabled' : '' }}>
                                                        @error("deliveryNotes.$ttoId.loaded_quantity")
                                                            <span class="text-danger small">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Unit of Measure</label>
                                                        <select class="form-control"
                                                               wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.units_of_measure_id"
                                                                >
                                                            <option value="">Select UOM</option>
                                                            @foreach ($units_of_measures as $uom)
                                                                <option value="{{ $uom->id }}">{{ $uom->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                        {{-- Liquid: litreage fields --}}
                                        @elseif ($cargo_type === 'Liquid')
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Litreage @ Ambient</label>
                                                        <input type="number" step="any"
                                                               class="form-control"
                                                              wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.loaded_litreage"
                                                               placeholder="Litreage @ ambient"
                                                               {{ $selectedStatus === 'Offloaded' ? 'disabled' : '' }}>
                                                        @error("deliveryNotes.$ttoId.loaded_litreage")
                                                            <span class="text-danger small">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Litreage @ 20°C <span class="text-danger">*</span></label>
                                                        <input type="number" step="any"
                                                               class="form-control"
                                                              wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.loaded_litreage_at_20"
                                                               placeholder="Litreage @ 20°C"
                                                               {{ $selectedStatus === 'Offloaded' ? 'disabled' : '' }}
                                                               required>
                                                        @error("deliveryNotes.$ttoId.loaded_litreage_at_20")
                                                            <span class="text-danger small">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Unit of Measure</label>
                                                        <select class="form-control"
                                                               wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.units_of_measure_id"
                                                                >
                                                            <option value="">Select UOM</option>
                                                            @foreach ($units_of_measures as $uom)
                                                                <option value="{{ $uom->id }}">{{ $uom->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Loading rate/freight/currency --}}
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Currency <span class="text-danger">*</span></label>
                                                    <select class="form-control"
                                                           wire:model.debounce.300ms="currency_id"
                                                            disabled>
                                                        <option value="">Select Currency</option>
                                                        @foreach ($currencies ?? [] as $currency)
                                                            <option value="{{ $currency->id }}">
                                                                {{ $currency->name }}
                                                                ({{ $currency->symbol }})
                                                                {{ $currency->fullname }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('currency_id')
                                                        <span class="text-danger small">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Customer Rate <span class="text-danger">*</span></label>
                                                    <input type="number" step="any"
                                                           class="form-control"
                                                          wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.loaded_rate"
                                                           placeholder="Customer rate"
                                                           {{ ($selectedStatus === 'Offloaded' || !$canEditRates) ? 'disabled' : '' }}
                                                           required>
                                                    @error("deliveryNotes.$ttoId.loaded_rate")
                                                        <span class="text-danger small">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Customer Freight <span class="text-danger">*</span></label>
                                                    <input type="number" step="any"
                                                           class="form-control"
                                                          wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.loaded_freight"
                                                           placeholder="Customer freight"
                                                           {{ ($selectedStatus === 'Offloaded' || !$canEditRates) ? 'disabled' : '' }}
                                                           required>
                                                    @error("deliveryNotes.$ttoId.loaded_freight")
                                                        <span class="text-danger small">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Transporter loading rate/freight --}}
                                        @if ($hasTransporter)
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Transporter Rate <span class="text-danger">*</span></label>
                                                        <input type="number" step="any"
                                                               class="form-control"
                                                              wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.transporter_loaded_rate"
                                                               placeholder="Transporter rate"
                                                               {{ ($selectedStatus === 'Offloaded' || !$canEditRates) ? 'disabled' : '' }}
                                                               required>
                                                        @error("deliveryNotes.$ttoId.transporter_loaded_rate")
                                                            <span class="text-danger small">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Transporter Freight <span class="text-danger">*</span></label>
                                                        <input type="number" step="any"
                                                               class="form-control"
                                                              wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.transporter_loaded_freight"
                                                               placeholder="Transporter freight"
                                                               {{ ($selectedStatus === 'Offloaded' || !$canEditRates) ? 'disabled' : '' }}
                                                               required>
                                                        @error("deliveryNotes.$ttoId.transporter_loaded_freight")
                                                            <span class="text-danger small">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- ── Offloading Details (Offloaded status only) ── --}}
                                        @if ($selectedStatus === 'Offloaded')

                                            <h6 class="font-weight-bold mt-4 mb-2 border-bottom pb-1">
                                                Offloading Details
                                            </h6>

                                            <div class="row">
                                                {{-- Offloaded date --}}
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Date <span class="text-danger">*</span></label>
                                                        <input type="datetime-local"
                                                               class="form-control"
                                                              wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.offloaded_date"
                                                               required>
                                                        @error("deliveryNotes.$ttoId.offloaded_date")
                                                            <span class="text-danger small">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                {{-- Offloaded distance --}}
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>
                                                            Distance
                                                            @if (in_array($freight_calculation, ['rate_weight_distance', 'rate_distance']))
                                                                <span class="text-danger">*</span>
                                                            @endif
                                                        </label>
                                                        <input type="number" step="any"
                                                               class="form-control"
                                                              wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.offloaded_distance"
                                                               placeholder="Trip distance"
                                                               {{ in_array($freight_calculation, ['rate_weight_distance', 'rate_distance']) ? 'required' : '' }}>
                                                        @error("deliveryNotes.$ttoId.offloaded_distance")
                                                            <span class="text-danger small">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                {{-- Offloaded weight --}}
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>
                                                            Weight
                                                            @if ($cargo_type === 'Solid')
                                                                <span class="text-danger">*</span>
                                                            @endif
                                                        </label>
                                                        <input type="number" step="any"
                                                               class="form-control"
                                                              wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.offloaded_weight"
                                                               placeholder="Offloaded weight"
                                                               {{ $cargo_type === 'Solid' ? 'required' : '' }}>
                                                        @error("deliveryNotes.$ttoId.offloaded_weight")
                                                            <span class="text-danger small">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Solid: offloaded quantity + UOM --}}
                                            @if ($cargo_type === 'Solid')
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Quantity</label>
                                                            <input type="number" step="any"
                                                                   class="form-control"
                                                                  wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.offloaded_quantity"
                                                                   placeholder="Offloaded quantity">
                                                            @error("deliveryNotes.$ttoId.offloaded_quantity")
                                                                <span class="text-danger small">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Unit of Measure</label>
                                                            <select class="form-control"
                                                                   wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.units_of_measure_id"
                                                                    disabled>
                                                                <option value="">Select UOM</option>
                                                                @foreach ($units_of_measures as $uom)
                                                                    <option value="{{ $uom->id }}">{{ $uom->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                            {{-- Liquid: offloaded litreage --}}
                                            @elseif ($cargo_type === 'Liquid')
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Litreage @ Ambient</label>
                                                            <input type="number" step="any"
                                                                   class="form-control"
                                                                  wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.offloaded_litreage"
                                                                   placeholder="Litreage @ ambient">
                                                            @error("deliveryNotes.$ttoId.offloaded_litreage")
                                                                <span class="text-danger small">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Litreage @ 20°C <span class="text-danger">*</span></label>
                                                            <input type="number" step="any"
                                                                   class="form-control"
                                                                  wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.offloaded_litreage_at_20"
                                                                   placeholder="Litreage @ 20°C"
                                                                   required>
                                                            @error("deliveryNotes.$ttoId.offloaded_litreage_at_20")
                                                                <span class="text-danger small">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Unit of Measure</label>
                                                            <select class="form-control"
                                                                   wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.units_of_measure_id"
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

                                            {{-- Offloaded rate/freight/currency --}}
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Currency <span class="text-danger">*</span></label>
                                                        <select class="form-control"
                                                               wire:model.debounce.300ms="currency_id"
                                                                disabled>
                                                            <option value="">Select Currency</option>
                                                            @foreach ($currencies ?? [] as $currency)
                                                                <option value="{{ $currency->id }}">
                                                                    {{ $currency->name }}
                                                                    ({{ $currency->symbol }})
                                                                    {{ $currency->fullname }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Customer Rate <span class="text-danger">*</span></label>
                                                        <input type="number" step="any"
                                                               class="form-control"
                                                              wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.offloaded_rate"
                                                               placeholder="Offloading rate"
                                                               {{ !$canEditRates ? 'disabled' : '' }}
                                                               required>
                                                        @error("deliveryNotes.$ttoId.offloaded_rate")
                                                            <span class="text-danger small">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Customer Freight <span class="text-danger">*</span></label>
                                                        <input type="number" step="any"
                                                               class="form-control"
                                                              wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.offloaded_freight"
                                                               placeholder="Offloading freight"
                                                               {{ !$canEditRates ? 'disabled' : '' }}
                                                               required>
                                                        @error("deliveryNotes.$ttoId.offloaded_freight")
                                                            <span class="text-danger small">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Transporter offloaded rate/freight --}}
                                            @if ($hasTransporter)
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Transporter Rate <span class="text-danger">*</span></label>
                                                            <input type="number" step="any"
                                                                   class="form-control"
                                                                  wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.transporter_offloaded_rate"
                                                                   placeholder="Transporter offloading rate"
                                                                   {{ !$canEditRates ? 'disabled' : '' }}
                                                                   required>
                                                            @error("deliveryNotes.$ttoId.transporter_offloaded_rate")
                                                                <span class="text-danger small">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Transporter Freight <span class="text-danger">*</span></label>
                                                            <input type="number" step="any"
                                                                   class="form-control"
                                                                  wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.transporter_offloaded_freight"
                                                                   placeholder="Transporter offloading freight"
                                                                   {{ !$canEditRates ? 'disabled' : '' }}
                                                                   required>
                                                            @error("deliveryNotes.$ttoId.transporter_offloaded_freight")
                                                                <span class="text-danger small">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Comments --}}
                                            <div class="form-group mt-2">
                                                <label>Comments</label>
                                                <textarea class="form-control"
                                                         wire:model.debounce.300ms="deliveryNotes.{{ $ttoId }}.comments"
                                                          placeholder="Additional offloading notes..."
                                                          rows="3"></textarea>
                                                @error("deliveryNotes.$ttoId.comments")
                                                    <span class="text-danger small">{{ $message }}</span>
                                                @enderror
                                            </div>

                                        @endif {{-- /Offloaded --}}

                                    </div>{{-- /card-body --}}
                                </div>{{-- /card --}}

                            @endif {{-- /in_array Loaded|Offloaded --}}

                        @endforeach

                    @endif {{-- /trip_transport_orders --}}

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