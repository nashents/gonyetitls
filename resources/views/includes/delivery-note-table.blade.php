{{-- resources/views/includes/delivery-note-table.blade.php --}}
{{-- Variables expected: $dn, $cargo_type, $hasTransporter, $canSeeRates,
     $currencyName, $currencySymbol, $caption, $lossCaption, $trip          --}}

{{-- ── Loading / Offloading table ── --}}
<table class="table table-condensed mb-0 border-top table-striped">
    <caption><strong>{{ $caption }}</strong>
        @if ($cargo_type)
            &nbsp;<span class="label label-secondary">{{ $cargo_type }}</span>
        @endif
    </caption>
    <tbody>

        <tr>
            <th style="width:30%; padding-left:20px;">Loading Date</th>
            <td>{{ $dn->loaded_date ?? 'No Loading Date Recorded' }}</td>
        </tr>

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
        @elseif ($cargo_type === 'Liquid')
            @foreach ([
                'loaded_litreage'           => 'Loaded Litreage @ Ambient Meter Readings',
                'loaded_litreage_at_20'     => 'Loaded Litreage @ 20°C Meter Readings',
                'loaded_dip_litreage'       => 'Loaded Litreage @ Ambient Dips',
                'loaded_dip_litreage_at_20' => 'Loaded Litreage @ 20°C Dips',
            ] as $field => $label)
                <tr>
                    <th style="width:30%; padding-left:20px;">{{ $label }}</th>
                    <td>
                        @if (is_numeric($dn->$field))
                            {{ number_format($dn->$field, 2) }} {{ $dn->units_of_measure?->name }}
                        @else
                            No {{ $label }} Recorded
                        @endif
                    </td>
                </tr>
            @endforeach
        @endif

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

        <tr>
            <th style="width:30%; padding-left:20px;">Offloading Date</th>
            <td>{{ $dn->offloaded_date ?? 'No Offloading Date Recorded' }}</td>
        </tr>

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
        @elseif ($cargo_type === 'Liquid')
            @foreach ([
                'offloaded_litreage'           => 'Offloaded Litreage @ Ambient Meter Readings',
                'offloaded_litreage_at_20'     => 'Offloaded Litreage @ 20°C Meter Readings',
                'offloaded_dip_litreage'       => 'Offloaded Litreage @ Ambient Dips',
                'offloaded_dip_litreage_at_20' => 'Offloaded Litreage @ 20°C Dips',
                'shoretank_litreage'           => 'Shoretank Reading @ Ambient',
                'shoretank_litreage_at_20'     => 'Shoretank Reading @ 20°C',
            ] as $field => $label)
                <tr>
                    <th style="width:30%; padding-left:20px;">{{ $label }}</th>
                    <td>
                        @if (is_numeric($dn->$field))
                            {{ number_format($dn->$field, 2) }} {{ $dn->units_of_measure?->name }}
                        @else
                            No {{ $label }} Recorded
                        @endif
                    </td>
                </tr>
            @endforeach
        @endif

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

        <tr>
            <th style="width:30%; padding-left:20px;">Comments</th>
            <td>{{ $dn->comments ?? 'No Comment Recorded' }}</td>
        </tr>

    </tbody>
</table>

{{-- ── Loss Details table ── --}}
@php
    $uomName = $dn->units_of_measure?->name ?? '';

    $weight_loss         = (is_numeric($dn->loaded_weight) && is_numeric($dn->offloaded_weight))
                            ? $dn->loaded_weight - $dn->offloaded_weight : null;

    $quantity_loss       = (is_numeric($dn->loaded_quantity) && is_numeric($dn->offloaded_quantity))
                            ? $dn->loaded_quantity - $dn->offloaded_quantity : null;

    $allowableLossPct = $trip->allowableLossPercentage();
    $liquidLossRows   = $cargo_type === 'Liquid' ? $dn->liquidLossRows($allowableLossPct) : [];

    $freight_loss       = (is_numeric($dn->loaded_freight) && is_numeric($dn->offloaded_freight))
                            ? $dn->loaded_freight - $dn->offloaded_freight : null;

    $chargeable_weight_loss   = (is_numeric($weight_loss) && is_numeric($trip->allowable_loss_weight))
                                    ? max(0, $weight_loss - $trip->allowable_loss_weight) : null;

    $chargeable_quantity_loss = (is_numeric($quantity_loss) && is_numeric($trip->allowable_loss_quantity))
                                    ? max(0, $quantity_loss - $trip->allowable_loss_quantity) : null;

    $chargeableLossRate         = $trip->chargeableLossRate();
    $chargeable_weight_amount   = $trip->chargeableLossAmount($chargeable_weight_loss);
@endphp

<table class="table table-condensed mb-0 border-top table-striped">
    <caption><strong>{{ $lossCaption }}</strong></caption>
    <tbody>

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
        @if ($canSeeRates)
            <tr>
                <th style="width:30%; padding-left:20px;">Chargeable Loss Rate</th>
                <td>
                    @if (! is_null($chargeableLossRate))
                        {{ $currencyName }} {{ $currencySymbol }}{{ number_format($chargeableLossRate, 2) }} per ton / {{ $uomName ?: 'unit' }}
                        <small class="text-muted">({{ $trip->lossTerms()['rate']['source'] }})</small>
                    @else
                        <span class="text-muted">Not set for {{ $trip->lossTerms()['own_fleet'] ? 'own fleet' : 'subcontractor' }} trips (cargo or Business Settings)</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th style="width:30%; padding-left:20px;">Chargeable Weight Loss Amount</th>
                <td>
                    @if ($chargeable_weight_amount)
                        <span class="label label-danger">{{ $currencyName }} {{ $currencySymbol }}{{ number_format($chargeable_weight_amount, 2) }}</span>
                    @endif
                </td>
            </tr>
        @endif

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

        @elseif ($cargo_type === 'Liquid')
            <tr>
                <th style="width:30%; padding-left:20px;">Allowable Loss Percentage</th>
                <td>
                    @if ($allowableLossPct)
                        {{ \App\Services\LossRateService::formatPct($allowableLossPct) }} of loaded litreage
                        <small class="text-muted">({{ $trip->lossTerms()['allowance']['source'] }})</small>
                    @else
                        <span class="text-muted">Not set (customer, cargo or Business Settings)</span>
                    @endif
                </td>
            </tr>
            @foreach ($liquidLossRows as $row)
                <tr>
                    <th style="width:30%; padding-left:20px;">{{ $row['label'] }} Loss</th>
                    <td>
                        @if (is_numeric($row['loss']))
                            <span class="label {{ $row['loss'] > 0 ? 'label-danger' : ($row['loss'] == 0 ? 'label-default' : 'label-success') }}">
                                {{ number_format(abs($row['loss']), 2) }} {{ $uomName }}{{ $row['loss'] < 0 ? ' gain' : '' }}
                            </span>
                            @if (is_numeric($row['allowable']))
                                &nbsp;Allowable: <span class="label label-success">{{ number_format($row['allowable'], 2) }} {{ $uomName }}</span>
                                &nbsp;Chargeable:
                                <span class="label {{ $row['chargeable'] > 0 ? 'label-danger' : 'label-default' }}">{{ number_format($row['chargeable'], 2) }} {{ $uomName }}</span>
                                @php $rowAmount = $trip->chargeableLossAmount($row['chargeable']); @endphp
                                @if ($canSeeRates && ! is_null($rowAmount))
                                    &nbsp;Amount:
                                    <span class="label {{ $rowAmount > 0 ? 'label-danger' : 'label-default' }}">{{ $currencyName }} {{ $currencySymbol }}{{ number_format($rowAmount, 2) }}</span>
                                @endif
                            @endif
                        @else
                            No {{ $row['label'] }} Loss Recorded
                        @endif
                    </td>
                </tr>
            @endforeach
        @endif


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