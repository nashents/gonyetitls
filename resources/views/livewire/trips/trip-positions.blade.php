<div>
    <style id="trip-positions-style">
        .trip-positions-filter { padding: 15px; border-left: 3px solid #f0ad4e; background: #fff; margin-bottom: 15px; }
        .trip-positions-table td, .trip-positions-table th { vertical-align: middle !important; }

        .trip-details-accordion .card { border: 1px solid #e5e7eb; border-radius: 4px; margin-bottom: 10px; }
        .trip-details-accordion .card-header {
            background: #fff; padding: 12px 16px; cursor: pointer;
            display: flex; justify-content: space-between; align-items: center;
        }
        .trip-details-accordion .card-header .muted { color: #adb5bd; font-weight: 400; margin-left: 6px; font-size: 12px; letter-spacing: .5px; }
        .trip-details-accordion .card-header .toggle-icon { font-size: 16px; color: #333; }
        .trip-details-accordion .card-body { border-top: 1px solid #eee; padding: 0; }
        .trip-details-accordion .detail-row { display: flex; justify-content: space-between; padding: 10px 16px; border-bottom: 1px solid #f2f2f2; font-size: 13px; }
        .trip-details-accordion .detail-row:last-child { border-bottom: none; }
        .trip-details-accordion .detail-row .label { color: #333; font-weight: 600; }
        .trip-details-accordion .detail-row .value { text-align: right; color: #555; }
        .trip-details-accordion .detail-heading { text-align: center; font-weight: 600; padding: 8px 16px; background: #fafafa; font-size: 12px; text-transform: uppercase; color: #888; }
        .trip-details-accordion .empty-state { padding: 16px; text-align: center; color: #adb5bd; }
    </style>

    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                Truck Positions &mdash; {{ $trip->horse->registration_number ?? $trip->vehicle->registration_number ?? $trip->trip_number }}
                                <span class="badge badge-info">{{ $trip->trip_number }}</span>
                            </div>
                        </div>
                        <div class="panel-body p-20">

                            @php
                                $asset = $trip->horse ?? $trip->vehicle;
                                $truckDocuments = $trip->horse ? $trip->horse->horse_documents : ($trip->vehicle ? $trip->vehicle->vehicle_documents : collect());
                                $refNumbers = $trip->transport_orders->pluck('transport_order_number')->filter()->implode(' / ') ?: $trip->trip_ref;
                            @endphp

                            <div class="trip-details-accordion mb-15">
                                <div class="row">
                                    <div class="col-md-6">

                                        <div class="card">
                                            <div class="card-header" data-toggle="collapse" data-target="#tdDriver">
                                                <span>Driver <span class="muted">{{ $trip->driver?->employee ? strtoupper(trim($trip->driver->employee->name . ' ' . $trip->driver->employee->surname)) : 'NOT ASSIGNED' }}</span></span>
                                                <i class="fa fa-plus toggle-icon"></i>
                                            </div>
                                            <div class="collapse" id="tdDriver">
                                                <div class="card-body">
                                                    @if ($trip->driver)
                                                        <div class="detail-row"><span class="label">Driver Name</span><span class="value">{{ trim($trip->driver->employee?->name . ' ' . $trip->driver->employee?->surname) }}</span></div>
                                                        <div class="detail-row"><span class="label">Passport Number</span><span class="value">{{ $trip->driver->passport_number ?: '—' }}</span></div>
                                                        <div class="detail-heading">Driver Numbers</div>
                                                        <div class="detail-row"><span class="label">Mobile</span><span class="value">{{ $trip->driver->employee?->phonenumber ?: '—' }}</span></div>
                                                        <div class="detail-row"><span class="label">Reference Mobile</span><span class="value">{{ $trip->driver->reference_phonenumber ?: '—' }}</span></div>
                                                        <div class="detail-heading">Driver Ranking (distance travelled)</div>
                                                        @foreach ($driverRanking as $label => $km)
                                                            <div class="detail-row"><span class="label">{{ $label }}</span><span class="value">{{ number_format($km, 2) }}km</span></div>
                                                        @endforeach
                                                    @else
                                                        <div class="empty-state">No driver assigned to this trip.</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card">
                                            <div class="card-header" data-toggle="collapse" data-target="#tdTrailers">
                                                <span>Trailers <span class="muted">{{ $trip->trailers->pluck('registration_number')->implode(', ') ?: 'NONE' }}</span></span>
                                                <i class="fa fa-plus toggle-icon"></i>
                                            </div>
                                            <div class="collapse" id="tdTrailers">
                                                <div class="card-body">
                                                    @forelse ($trip->trailers as $trailer)
                                                        <div class="detail-row">
                                                            <span class="label">Trailer{{ !$loop->first ? ' #' . ($loop->index + 1) : '' }}</span>
                                                            <span class="value">{{ $trailer->identifier_label }} {{ $trailer->trailer_type?->name }}</span>
                                                        </div>
                                                    @empty
                                                        <div class="empty-state">No trailers on this trip.</div>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card">
                                            <div class="card-header" data-toggle="collapse" data-target="#tdTrip">
                                                <span>Trip <a href="{{ route('trips.show', $trip->id) }}" target="_blank" rel="noopener" onclick="event.stopPropagation()">{{ $refNumbers ?: $trip->trip_number }}</a></span>
                                                <i class="fa fa-plus toggle-icon"></i>
                                            </div>
                                            <div class="collapse" id="tdTrip">
                                                <div class="card-body">
                                                    <div class="detail-row"><span class="label">Trip Number</span><span class="value"><a href="{{ route('trips.show', $trip->id) }}" target="_blank" rel="noopener">{{ $trip->trip_number }}</a></span></div>
                                                    <div class="detail-row"><span class="label">Origin</span><span class="value">{{ $trip->loading_point?->name ?: '—' }}</span></div>
                                                    <div class="detail-row"><span class="label">Destination</span><span class="value">{{ $trip->offloading_point?->name ?: '—' }}</span></div>
                                                    <div class="detail-row"><span class="label">Customer</span><span class="value">{{ $trip->customer?->name ?? $trip->consignee?->name ?? '—' }}</span></div>
                                                    <div class="detail-row"><span class="label">Commodities</span><span class="value">{{ $trip->cargo?->name ?: '—' }}</span></div>
                                                    <div class="detail-row">
                                                        <span class="label">DN</span>
                                                        <span class="value">
                                                            @if ($trip->delivery_note)
                                                                {{ $trip->delivery_note->status ? 'Completed' : 'Draft' }}
                                                                @if ($trip->delivery_note->offloaded_quantity) &middot; {{ $trip->delivery_note->offloaded_quantity }} @endif
                                                            @else
                                                                No DN assigned
                                                            @endif
                                                        </span>
                                                    </div>
                                                    <div class="detail-row"><span class="label">Ref No</span><span class="value">{{ $refNumbers ?: '—' }}</span></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card">
                                            <div class="card-header" data-toggle="collapse" data-target="#tdTruckDocs">
                                                <span>Documents for this Truck</span>
                                                <i class="fa fa-plus toggle-icon"></i>
                                            </div>
                                            <div class="collapse" id="tdTruckDocs">
                                                <div class="card-body">
                                                    @forelse ($truckDocuments as $doc)
                                                        <div class="detail-row"><span class="label">{{ $doc->title ?: 'Document' }}</span><span class="value">{{ $doc->filename ?: '—' }}</span></div>
                                                    @empty
                                                        <div class="empty-state">No documents.</div>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="col-md-6">

                                        <div class="card">
                                            <div class="card-header" data-toggle="collapse" data-target="#tdSms">
                                                <span>Last SMS Sent</span>
                                                <i class="fa fa-plus toggle-icon"></i>
                                            </div>
                                            <div class="collapse" id="tdSms">
                                                <div class="card-body">
                                                    <div class="empty-state">No SMS system configured for this fleet.</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card">
                                            <div class="card-header" data-toggle="collapse" data-target="#tdGps">
                                                <span>GPS</span>
                                                <i class="fa fa-plus toggle-icon"></i>
                                            </div>
                                            <div class="collapse" id="tdGps">
                                                <div class="card-body">
                                                    @if ($gpsStatus['sat_report'])
                                                        <div class="detail-row"><span class="label">Sat. report</span><span class="value">{{ \Carbon\Carbon::parse($gpsStatus['sat_report'])->format('d/m/Y H:i') }}</span></div>
                                                        <div class="detail-row"><span class="label">Moved at</span><span class="value">{{ $gpsStatus['moved_at'] ? \Carbon\Carbon::parse($gpsStatus['moved_at'])->format('d/m/Y H:i') : '—' }}</span></div>
                                                    @else
                                                        <div class="empty-state">No GPS data logged for this truck yet.</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card">
                                            <div class="card-header" data-toggle="collapse" data-target="#tdDn">
                                                <span>DN Details</span>
                                                <i class="fa fa-plus toggle-icon"></i>
                                            </div>
                                            <div class="collapse" id="tdDn">
                                                <div class="card-body">
                                                    @if ($trip->delivery_note)
                                                        <div class="detail-row"><span class="label">Status</span><span class="value">{{ $trip->delivery_note->status ? 'Completed' : 'Draft' }}</span></div>
                                                        <div class="detail-row"><span class="label">Loaded</span><span class="value">{{ $trip->delivery_note->loaded_quantity ?: '—' }} on {{ $trip->delivery_note->loaded_date ?: '—' }}</span></div>
                                                        <div class="detail-row"><span class="label">Offloaded</span><span class="value">{{ $trip->delivery_note->offloaded_quantity ?: '—' }} on {{ $trip->delivery_note->offloaded_date ?: '—' }}</span></div>
                                                    @else
                                                        <div class="empty-state">No DN assigned</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card">
                                            <div class="card-header" data-toggle="collapse" data-target="#tdTripDocs">
                                                <span>Documents for this trip</span>
                                                <i class="fa fa-plus toggle-icon"></i>
                                            </div>
                                            <div class="collapse" id="tdTripDocs">
                                                <div class="card-body">
                                                    @forelse ($trip->tripDocuments as $doc)
                                                        <div class="detail-row"><span class="label">{{ $doc->title ?: 'Document' }}</span><span class="value">{{ $doc->filename ?: $doc->document_number ?: '—' }}</span></div>
                                                    @empty
                                                        <div class="empty-state">No documents.</div>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div wire:ignore id="trip-positions-map" style="width:100%; height:400px;" class="mb-15"></div>

                            <div class="trip-positions-filter">
                                <div class="form-row align-items-end">
                                    <div class="col-auto">
                                        <label class="small text-muted mb-1">From:</label>
                                        <input type="datetime-local" wire:model="from" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-auto">
                                        <label class="small text-muted mb-1">To:</label>
                                        <input type="datetime-local" wire:model="to" class="form-control form-control-sm">
                                    </div>
                                </div>
                            </div>

                            @if ($points->isEmpty())
                                <p class="text-muted text-center">
                                    No positions logged for this trip yet. Positions are logged automatically every ~10 minutes while the trip is in an active status (Started through Offloaded).
                                </p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered table-sm trip-positions-table">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Time</th>
                                                <th>Source</th>
                                                <th>Speed</th>
                                                <th>Position</th>
                                                <th>Odometer</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($points as $point)
                                                <tr>
                                                    <td>{{ $point->recorded_at->format('d-M-Y H:i:s') }}</td>
                                                    <td>{{ ucfirst($point->source) }}</td>
                                                    <td>{{ $point->speed !== null ? round($point->speed) . ' km/h' : '—' }}</td>
                                                    <td>
                                                        <a href="https://www.google.com/maps?q={{ $point->latitude }},{{ $point->longitude }}" target="_blank" rel="noopener">
                                                            {{ number_format($point->latitude, 5) }}, {{ number_format($point->longitude, 5) }}
                                                        </a>
                                                    </td>
                                                    <td>{{ $point->odometer !== null ? number_format($point->odometer, 1) . ' km' : '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        window.tripPositionsRoute = @json($mapRoute);

        function initTripPositionsMap() {
            const el = document.getElementById('trip-positions-map');
            if (!el || el.dataset.initialized) { return; }

            const route = window.tripPositionsRoute || [];
            const defaultCenter = { lat: -17.8216, lng: 31.0492 }; // Harare

            const map = new google.maps.Map(el, {
                center: route.length ? { lat: route[route.length - 1].lat, lng: route[route.length - 1].lng } : defaultCenter,
                zoom: route.length ? 9 : 6,
            });

            el.dataset.initialized = '1';

            if (!route.length) { return; }

            const path = route.map(function (p) { return { lat: p.lat, lng: p.lng }; });

            new google.maps.Polyline({
                path: path,
                geodesic: true,
                strokeColor: '#1c4e91',
                strokeOpacity: 0.8,
                strokeWeight: 3,
                map: map,
            });

            const bounds = new google.maps.LatLngBounds();

            route.forEach(function (p, i) {
                const isLatest = i === route.length - 1;
                const marker = new google.maps.Marker({
                    map: map,
                    position: { lat: p.lat, lng: p.lng },
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        fillColor: isLatest ? '#337ab7' : '#000',
                        fillOpacity: 1,
                        strokeColor: '#fff',
                        strokeWeight: 1,
                        scale: isLatest ? 8 : 5,
                    },
                });

                const info = new google.maps.InfoWindow({
                    content: '<small>' + p.recorded_at
                        + (p.speed !== null ? '<br>' + Math.round(p.speed) + ' km/h' : '')
                        + '</small>',
                });

                marker.addListener('click', function () { info.open(map, marker); });
                bounds.extend({ lat: p.lat, lng: p.lng });
            });

            if (route.length > 1) {
                map.fitBounds(bounds);
            }
        }

        function initTripPositionsMapWhenReady() {
            if (typeof google === 'undefined' || !google.maps) {
                return setTimeout(initTripPositionsMapWhenReady, 200);
            }
            initTripPositionsMap();
        }

        document.addEventListener('livewire:load', initTripPositionsMapWhenReady);
        document.addEventListener('livewire:update', initTripPositionsMapWhenReady);

        $(document).on('show.bs.collapse', '.trip-details-accordion .collapse', function () {
            $(this).prev('.card-header').find('.toggle-icon').removeClass('fa-plus').addClass('fa-minus');
        });
        $(document).on('hide.bs.collapse', '.trip-details-accordion .collapse', function () {
            $(this).prev('.card-header').find('.toggle-icon').removeClass('fa-minus').addClass('fa-plus');
        });
    </script>
</div>
