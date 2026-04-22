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
                                    'Offloaded'        => ['table-success',  'label-success'],
                                    'Scheduled'        => ['table-warning',  'label-warning'],
                                    'Loading Point'    => ['table-default',  'label-default'],
                                    'Started'          => ['table-default',  'label-primary'],
                                    'Loaded'           => ['table-info',     'label-info'],
                                    'InTransit'        => ['table-primary',  'label-primary'],
                                    'OnHold'           => ['table-danger',   'label-danger'],
                                    'Offloading Point' => ['table-default',  'label-default'],
                                    'Cancelled'        => ['table-default',  'label-default'],
                                ];
                                [$tdClass, $labelClass] = $statusMap[$trip->trip_status] ?? ['table-default', 'label-default'];
                            @endphp
                            <td class="{{ $tdClass }}">
                                <span class="label {{ $labelClass }} label-wide" style="margin-right:5px;">
                                    {{ $trip->trip_status }}
                                    @if ($trip->authorization == 'approved')
                                        <a href="#" wire:click="$emit('openTripStatusModal', {{ $trip->id }})" style="margin-left:2px">
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

                {{-- ===== SHARED PHP CONTEXT ===== --}}
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

                    // Decide which rendering path to use:
                    // - useTtoPath: trip has TTOs and at least one has a delivery note
                    // - useLegacyPath: no TTOs at all, or no TTOs have DNs → fall back to trip->delivery_note
                    $ttos = $trip->trip_transport_orders ?? collect();

                    $ttoHasAnyDn = $ttos->count() > 0 && $ttos->contains(function ($tto) use ($trip) {
                        return \App\Models\DeliveryNote::where('trip_id', $trip->id)
                            ->where(function ($q) use ($tto) {
                                $q->where('trip_transport_order_id', $tto->id)
                                  ->orWhere(function ($q2) use ($tto) {
                                      $q2->whereNull('trip_transport_order_id')
                                         ->where('transport_order_id', $tto->transport_order_id);
                                  });
                            })
                            ->exists();
                    });

                    $useTtoPath    = $ttos->count() > 0 && $ttoHasAnyDn;
                    $useLegacyPath = ! $useTtoPath;

                    // Legacy single delivery note
                    $legacy_dn        = $useLegacyPath ? $trip->delivery_note : null;
                    $legacy_cargo_type = $useLegacyPath ? ($trip->cargo?->type ?? null) : null;
                    $legacy_has_transporter = $useLegacyPath && ($trip->transporter_agreement == true);
                @endphp

                {{-- ===================================================== --}}
                {{-- PATH A — NEW: per-TTO delivery notes                   --}}
                {{-- ===================================================== --}}
                @if ($useTtoPath)

                    @foreach ($ttos as $tto)
                        @php
                            $transport_order = $tto->transport_order;
                            $cargo_type      = $transport_order?->cargo?->type;

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

                        @if ($dn)
                            @include('includes.delivery-note-table', [
                                'dn'              => $dn,
                                'cargo_type'      => $cargo_type,
                                'hasTransporter'  => $hasTransporter,
                                'canSeeRates'     => $canSeeRates,
                                'currencyName'    => $currencyName,
                                'currencySymbol'  => $currencySymbol,
                                'caption'         => 'Transport Order: '.($transport_order?->reference ?? "Order #{$tto->id}"),
                                'lossCaption'     => 'Transport Order: '.($transport_order?->reference ?? "Order #{$tto->id}").' — Loss Details',
                                'trip'            => $trip,
                            ])
                        @endif

                    @endforeach

                @endif

                {{-- ===================================================== --}}
                {{-- PATH B — LEGACY: single trip->delivery_note            --}}
                {{-- ===================================================== --}}
                @if ($useLegacyPath)

                    @php
                        $dn             = $legacy_dn;
                        $cargo_type     = $legacy_cargo_type;
                        $hasTransporter = $legacy_has_transporter;
                    @endphp

                    @if ($dn)
                        @include('includes.delivery-note-table', [
                            'dn'             => $dn,
                            'cargo_type'     => $cargo_type,
                            'hasTransporter' => $hasTransporter,
                            'canSeeRates'    => $canSeeRates,
                            'currencyName'   => $currencyName,
                            'currencySymbol' => $currencySymbol,
                            'caption'        => 'Loading / Offloading Details',
                            'lossCaption'    => 'Loss Details',
                            'trip'           => $trip,
                        ])
                    @else
                        <div class="p-3 text-muted">No loading / offloading details recorded yet.</div>
                    @endif

                @endif

            </div>
        </div>
    </div>

    @include('includes.trip_status')

</div>