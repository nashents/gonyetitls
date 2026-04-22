<div>
    <style>
        th {
            width: 30%;
            padding: 10px;
        }
    </style>
    <div class="col-md-12 p-n">
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h5>Offloading Details</h5>
                    </div>
                </div>

                {{-- ===== TRIP STATUS TABLE ===== --}}
                <table class="table table-condensed mb-0 border-top table-striped">
                    <caption>Trip Status</caption>
                    <tbody>
                        <tr>
                            <th style="width:30%; padding-left:20px;" scope="row">Trip Status</th>
                            @php
                                $statusMap = [
                                    'Offloaded'       => ['table-success',  'label-success'],
                                    'Scheduled'       => ['table-warning',  'label-warning'],
                                    'Loading Point'   => ['table-default',  'label-default'],
                                    'Started'         => ['table-default',  'label-primary'],
                                    'Loaded'          => ['table-info',     'label-info'],
                                    'InTransit'       => ['table-primary',  'label-primary'],
                                    'OnHold'          => ['table-danger',   'label-danger'],
                                    'Offloading Point'=> ['table-default',  'label-default'],
                                    'Cancelled'       => ['table-default',  'label-default'],
                                ];
                                [$tdClass, $labelClass] = $statusMap[$trip->trip_status] ?? ['table-default', 'label-default'];
                            @endphp
                            <td class="{{ $tdClass }}">
                                <span class="label {{ $labelClass }} label-wide" style="margin-right:5px;">
                                    {{ $trip->trip_status }}
                                    @if ($trip->authorization == 'approved')
                                        <a href="#" wire:click="status({{ $trip->id }})" style="margin-left:2px">
                                            <i class="fa fa-edit" style="color:black"></i>
                                        </a>
                                    @endif
                                </span>
                                @if ($trip->trip_status_date)
                                    @if (preg_match($pattern, $trip->trip_status_date))
                                        On {{ \Carbon\Carbon::parse($trip->trip_status_date)->format('d M Y g:i A') }}
                                    @else
                                        On {{ $trip->trip_status_date }}
                                    @endif
                                @endif
                            </td>
                        </tr>

                        @if ($trip->trip_status_description)
                            <tr>
                                <th style="width:30%; padding-left:20px;" scope="row">Trip Status Description</th>
                                <td>{{ $trip->trip_status_description }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>

                {{-- ===== PER-TTO LOADING / OFFLOADING TABLES ===== --}}
                @php
                    $user             = Auth::user();
                    $employee         = $user->employee;
                    $department_names = $employee?->departments?->pluck('name')->toArray() ?? [];
                    $role_names       = $user?->roles?->pluck('name')->toArray() ?? [];
                    $canSeeRates      = !(
                        $employee->company->rates_managed_by_finance == 1 &&
                        !(in_array('Finance', $department_names) || in_array('Super Admin', $role_names))
                    );
                    $currencyName   = $trip->currency?->name   ?? '';
                    $currencySymbol = $trip->currency?->symbol ?? '';
                @endphp

                @forelse ($trip->trip_transport_orders as $tto)
                    @php
                        $transport_order = $tto->transport_order;
                        $cargo_type      = $transport_order?->cargo?->type;

                        // Resolve delivery note — prefer trip_transport_order_id, fall back to transport_order_id
                        $dn = \App\Models\DeliveryNote::where('trip_id', $trip->id)
                            ->where(function ($q) use ($tto) {
                                $q->where('trip_transport_order_id', $tto->id)
                                  ->orWhere(function ($q2) use ($tto) {
                                      $q2->whereNull('trip_transport_order_id')
                                         ->where('transport_order_id', $tto->transport_order_id);
                                  });
                            })
                            ->latest()
                            ->first();

                        $hasTransporter = ($transport_order?->transporter_agreement == true)
                                       || ($trip->transporter_agreement == true);
                    @endphp

                    <table class="table table-condensed mb-0 border-top table-striped">
                        <caption>
                            Transport Order: <strong>{{ $transport_order?->reference ?? "Order #{$tto->id}" }}</strong>
                            @if ($cargo_type)
                                &nbsp;<span class="label label-secondary">{{ $cargo_type }}</span>
                            @endif
                            — Loading / Offloading Details
                        </caption>
                        <tbody>

                            {{-- Loading Date --}}
                            <tr>
                                <th style="width:30%; padding-left:20px;">Loading Date</th>
                                <td>
                                    {{ $dn?->loaded_date ?? 'No Loading Date Recorded' }}
                                </td>
                            </tr>

                            {{-- Solid: loaded quantity --}}
                            @if ($cargo_type === 'Solid')
                                <tr>
                                    <th style="width:30%; padding-left:20px;">Loaded Quantity</th>
                                    <td>
                                        @if (isset($dn->loaded_quantity))
                                            {{ $dn->loaded_quantity }} {{ $dn->units_of_measure?->name }}
                                        @else
                                            No Loaded Quantity Recorded
                                        @endif
                                    </td>
                                </tr>

                            {{-- Liquid: loaded litreage --}}
                            @elseif ($cargo_type === 'Liquid')
                                <tr>
                                    <th style="width:30%; padding-left:20px;">Loaded Litreage @ 20°C</th>
                                    <td>
                                        @if (is_numeric($dn?->loaded_litreage_at_20))
                                            {{ number_format($dn->loaded_litreage_at_20, 2) }} {{ $dn->units_of_measure?->name }}
                                        @else
                                            No Loaded Litreage @ 20°C Recorded
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th style="width:30%; padding-left:20px;">Loaded Litreage @ Ambient</th>
                                    <td>
                                        @if (is_numeric($dn?->loaded_litreage))
                                            {{ number_format($dn->loaded_litreage, 2) }} {{ $dn->units_of_measure?->name }}
                                        @else
                                            No Loaded Litreage @ Ambient Recorded
                                        @endif
                                    </td>
                                </tr>
                            @endif

                            {{-- Loaded weight --}}
                            <tr>
                                <th style="width:30%; padding-left:20px;">Loaded Weight</th>
                                <td>
                                    @if (isset($dn->loaded_weight))
                                        {{ $dn->loaded_weight }} tons
                                    @else
                                        No Loaded Weight Recorded
                                    @endif
                                </td>
                            </tr>

                            {{-- Loading rates — gated by finance permission --}}
                            @if ($canSeeRates)
                                <tr>
                                    <th style="width:30%; padding-left:20px;">Customer Rate @ Loading</th>
                                    <td>
                                        @if (isset($dn->loaded_rate))
                                            {{ $currencyName }} {{ $currencySymbol }}{{ $dn->loaded_rate }}
                                        @else
                                            No Customer Rate @ Loading
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th style="width:30%; padding-left:20px;">Customer Freight @ Loading</th>
                                    <td>
                                        @if (isset($dn->loaded_freight))
                                            {{ $currencyName }} {{ $currencySymbol }}{{ $dn->loaded_freight }}
                                        @else
                                            No Customer Freight @ Loading
                                        @endif
                                    </td>
                                </tr>

                                @if ($hasTransporter)
                                    <tr>
                                        <th style="width:30%; padding-left:20px;">Transporter Rate @ Loading</th>
                                        <td>
                                            @if (isset($dn->transporter_loaded_rate))
                                                {{ $currencyName }} {{ $currencySymbol }}{{ $dn->transporter_loaded_rate }}
                                            @else
                                                No Transporter Rate @ Loading
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width:30%; padding-left:20px;">Transporter Freight @ Loading</th>
                                        <td>
                                            @if (isset($dn->transporter_loaded_freight))
                                                {{ $currencyName }} {{ $currencySymbol }}{{ $dn->transporter_loaded_freight }}
                                            @else
                                                No Transporter Freight @ Loading
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endif

                            {{-- Offloading Date --}}
                            <tr>
                                <th style="width:30%; padding-left:20px;">Offloading Date</th>
                                <td>
                                    {{ $dn?->offloaded_date ?? 'No Offloading Date Recorded' }}
                                </td>
                            </tr>

                            {{-- Solid: offloaded quantity --}}
                            @if ($cargo_type === 'Solid')
                                <tr>
                                    <th style="width:30%; padding-left:20px;">Offloaded Quantity</th>
                                    <td>
                                        @if (isset($dn->offloaded_quantity))
                                            {{ $dn->offloaded_quantity }} {{ $dn->units_of_measure?->name }}
                                        @else
                                            No Offloaded Quantity Recorded
                                        @endif
                                    </td>
                                </tr>

                            {{-- Liquid: offloaded litreage --}}
                            @elseif ($cargo_type === 'Liquid')
                                <tr>
                                    <th style="width:30%; padding-left:20px;">Offloaded Litreage @ Ambient</th>
                                    <td>
                                        @if (isset($dn->offloaded_litreage))
                                            {{ $dn->offloaded_litreage }} {{ $dn->units_of_measure?->name }}
                                        @else
                                            No Offloaded Litreage @ Ambient Recorded
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th style="width:30%; padding-left:20px;">Offloaded Litreage @ 20°C</th>
                                    <td>
                                        @if (isset($dn->offloaded_litreage_at_20))
                                            {{ $dn->offloaded_litreage_at_20 }} {{ $dn->units_of_measure?->name }}
                                        @else
                                            No Offloaded Litreage @ 20°C Recorded
                                        @endif
                                    </td>
                                </tr>
                            @endif

                            {{-- Offloaded weight --}}
                            <tr>
                                <th style="width:30%; padding-left:20px;">Offloaded Weight</th>
                                <td>
                                    @if (isset($dn->offloaded_weight))
                                        {{ $dn->offloaded_weight }} tons
                                    @else
                                        No Offloaded Weight Recorded
                                    @endif
                                </td>
                            </tr>

                            {{-- Offloading rates — gated by finance permission --}}
                            @if ($canSeeRates)
                                <tr>
                                    <th style="width:30%; padding-left:20px;">Customer Rate @ Offloading</th>
                                    <td>
                                        @if (isset($dn->offloaded_rate))
                                            {{ $currencyName }} {{ $currencySymbol }}{{ $dn->offloaded_rate }}
                                        @else
                                            No Customer Rate @ Offloading
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th style="width:30%; padding-left:20px;">Customer Freight @ Offloading</th>
                                    <td>
                                        @if (isset($dn->offloaded_freight))
                                            {{ $currencyName }} {{ $currencySymbol }}{{ $dn->offloaded_freight }}
                                        @else
                                            No Customer Freight @ Offloading
                                        @endif
                                    </td>
                                </tr>

                                @if ($hasTransporter)
                                    <tr>
                                        <th style="width:30%; padding-left:20px;">Transporter Rate @ Offloading</th>
                                        <td>
                                            @if (isset($dn->transporter_offloaded_rate))
                                                {{ $currencyName }} {{ $currencySymbol }}{{ $dn->transporter_offloaded_rate }}
                                            @else
                                                No Transporter Rate @ Offloading
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width:30%; padding-left:20px;">Transporter Freight @ Offloading</th>
                                        <td>
                                            @if (isset($dn->transporter_offloaded_freight))
                                                {{ $currencyName }} {{ $currencySymbol }}{{ $dn->transporter_offloaded_freight }}
                                            @else
                                                No Transporter Freight @ Offloading
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endif

                            {{-- Comments --}}
                            <tr>
                                <th style="width:30%; padding-left:20px;">Comments</th>
                                <td>{{ $dn?->comments ?? 'No Comment Recorded' }}</td>
                            </tr>

                        </tbody>
                    </table>

                    {{-- ===== LOSS DETAILS TABLE (per TTO) ===== --}}
                    @php
                        $uomName = $dn?->units_of_measure?->name ?? '';

                        $weight_loss   = ($dn && is_numeric($dn->loaded_weight) && is_numeric($dn->offloaded_weight))
                                            ? $dn->loaded_weight - $dn->offloaded_weight
                                            : null;

                        $quantity_loss = ($dn && is_numeric($dn->loaded_quantity) && is_numeric($dn->offloaded_quantity))
                                            ? $dn->loaded_quantity - $dn->offloaded_quantity
                                            : null;

                        $litreage_loss = ($dn && is_numeric($dn->loaded_litreage) && is_numeric($dn->offloaded_litreage))
                                            ? $dn->loaded_litreage - $dn->offloaded_litreage
                                            : null;

                        $litreage_at_20_loss = ($dn && is_numeric($dn->loaded_litreage_at_20) && is_numeric($dn->offloaded_litreage_at_20))
                                            ? $dn->loaded_litreage_at_20 - $dn->offloaded_litreage_at_20
                                            : null;

                        $freight_loss = ($dn && is_numeric($dn->loaded_freight) && is_numeric($dn->offloaded_freight))
                                            ? $dn->loaded_freight - $dn->offloaded_freight
                                            : null;

                        $chargeable_weight_loss   = (is_numeric($weight_loss) && is_numeric($trip->allowable_loss_weight))
                                                        ? max(0, $weight_loss - $trip->allowable_loss_weight)
                                                        : null;

                        $chargeable_quantity_loss = (is_numeric($quantity_loss) && is_numeric($trip->allowable_loss_quantity))
                                                        ? max(0, $quantity_loss - $trip->allowable_loss_quantity)
                                                        : null;

                        $chargeable_litreage_loss = (is_numeric($litreage_at_20_loss) && is_numeric($trip->allowable_loss_litreage))
                                                        ? max(0, $litreage_at_20_loss - $trip->allowable_loss_litreage)
                                                        : null;
                    @endphp

                    <table class="table table-condensed mb-0 border-top table-striped">
                        <caption>
                            Transport Order: <strong>{{ $transport_order?->reference ?? "Order #{$tto->id}" }}</strong>
                            — Loss Details
                        </caption>
                        <tbody>

                            {{-- Weight loss --}}
                            <tr>
                                <th style="width:30%; padding-left:20px;">Weight Loss</th>
                                <td>
                                    @if (is_numeric($weight_loss))
                                        <span class="label {{ $weight_loss > 0 ? 'label-danger' : 'label-success' }}">
                                            {{ abs($weight_loss) }} Ton(s)
                                        </span>
                                    @else
                                        No Weight Loss Recorded
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th style="width:30%; padding-left:20px;">Allowable Weight Loss</th>
                                <td>
                                    @if ($trip->allowable_loss_weight)
                                        <span class="label label-success">{{ $trip->allowable_loss_weight }} Tons</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th style="width:30%; padding-left:20px;">Chargeable Weight Loss</th>
                                <td>
                                    @if ($chargeable_weight_loss)
                                        <span class="label label-danger">{{ $chargeable_weight_loss }} Tons</span>
                                    @endif
                                </td>
                            </tr>

                            {{-- Solid losses --}}
                            @if ($cargo_type === 'Solid')
                                <tr>
                                    <th style="width:30%; padding-left:20px;">Quantity Loss</th>
                                    <td>
                                        @if (is_numeric($quantity_loss))
                                            <span class="label {{ $quantity_loss > 0 ? 'label-danger' : ($quantity_loss == 0 ? 'label-default' : 'label-success') }}">
                                                {{ abs($quantity_loss) }} {{ $uomName }}
                                            </span>
                                        @else
                                            No Quantity Loss Recorded
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th style="width:30%; padding-left:20px;">Allowable Quantity Loss</th>
                                    <td>
                                        @if ($trip->allowable_loss_quantity)
                                            <span class="label label-success">{{ $trip->allowable_loss_quantity }} {{ $uomName }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th style="width:30%; padding-left:20px;">Chargeable Quantity Loss</th>
                                    <td>
                                        @if ($chargeable_quantity_loss)
                                            <span class="label label-danger">{{ $chargeable_quantity_loss }} {{ $uomName }}</span>
                                        @endif
                                    </td>
                                </tr>

                            {{-- Liquid losses --}}
                            @elseif ($cargo_type === 'Liquid')
                                <tr>
                                    <th style="width:30%; padding-left:20px;">Litreage Loss @ Ambient</th>
                                    <td>
                                        @if (is_numeric($litreage_loss))
                                            <span class="label {{ $litreage_loss > 0 ? 'label-danger' : ($litreage_loss == 0 ? 'label-default' : 'label-success') }}">
                                                {{ abs($litreage_loss) }} {{ $uomName }}
                                            </span>
                                        @else
                                            No Litreage @ Ambient Loss Recorded
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th style="width:30%; padding-left:20px;">Litreage Loss @ 20°C</th>
                                    <td>
                                        @if (is_numeric($litreage_at_20_loss))
                                            <span class="label {{ $litreage_at_20_loss > 0 ? 'label-danger' : ($litreage_at_20_loss == 0 ? 'label-default' : 'label-success') }}">
                                                {{ abs($litreage_at_20_loss) }} {{ $uomName }}
                                            </span>
                                        @else
                                            No Litreage @ 20°C Loss Recorded
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th style="width:30%; padding-left:20px;">Allowable Litreage Loss</th>
                                    <td>
                                        @if ($trip->allowable_loss_litreage)
                                            <span class="label label-success">{{ $trip->allowable_loss_litreage }} {{ $uomName }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th style="width:30%; padding-left:20px;">Chargeable Litreage Loss</th>
                                    <td>
                                        @if ($chargeable_litreage_loss)
                                            <span class="label label-danger">{{ $chargeable_litreage_loss }} {{ $uomName }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endif

                            {{-- Freight loss — gated by finance permission --}}
                            @if ($canSeeRates)
                                <tr>
                                    <th style="width:30%; padding-left:20px;">Freight Loss</th>
                                    <td>
                                        @if (is_numeric($freight_loss))
                                            <span class="label {{ $freight_loss > 0 ? 'label-danger' : ($freight_loss == 0 ? 'label-default' : 'label-success') }}">
                                                {{ $currencyName }} {{ $currencySymbol }}{{ number_format($freight_loss, 2) }}
                                            </span>
                                        @else
                                            <span class="label label-danger">No Freight Loss Recorded</span>
                                        @endif
                                    </td>
                                </tr>
                            @endif

                        </tbody>
                    </table>

                @empty
                    <div class="p-3">No transport orders linked to this trip.</div>
                @endforelse

            </div>
        </div>
    </div>

    @include('includes.trip_status')

</div>