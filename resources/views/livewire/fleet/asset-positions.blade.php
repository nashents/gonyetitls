<div>
    <style id="asset-positions-ui-polish">
        .asset-positions-table { table-layout: fixed; }
        .asset-positions-table th { white-space: nowrap; }
        .asset-positions-table td { vertical-align: top !important; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .asset-positions-table td.wrap { white-space: normal; overflow: visible; text-overflow: clip; }

        /* Freeze #, Truck and Fleet so you can scroll everything from Trip
           rightward while still knowing which truck/fleet a row belongs to. */
        .asset-positions-table th:nth-child(-n+3),
        .asset-positions-table td:nth-child(-n+3) {
            position: sticky;
            background: #fff;
            z-index: 2;
        }
        .asset-positions-table thead th:nth-child(-n+3) { background: #f8f9fa; z-index: 3; }

        /* Striping — the frozen columns need the same stripe/hover colour
           explicitly, since they otherwise sit on their own opaque #fff. */
        .asset-positions-table tbody tr:nth-child(even) td { background: #f8f9fa; }
        .asset-positions-table tbody tr:nth-child(even) td:nth-child(-n+3) { background: #f8f9fa; }
        .asset-positions-table tbody tr:hover td { background: #f5f5f5; }
        .asset-positions-table tbody tr:hover td:nth-child(-n+3) { background: #f5f5f5; }
        .asset-positions-table th:nth-child(1), .asset-positions-table td:nth-child(1) { left: 0; }
        .asset-positions-table th:nth-child(2), .asset-positions-table td:nth-child(2) { left: 40px; }
        .asset-positions-table th:nth-child(3), .asset-positions-table td:nth-child(3) {
            left: 140px;
            box-shadow: 2px 0 4px rgba(0,0,0,.08);
        }
        .asset-positions-meta { display: block; color: #6c757d; line-height: 1.35; }
        .asset-positions-legend { background: #fff; padding: 10px 14px; margin: 10px; border-radius: 4px; box-shadow: 0 1px 4px rgba(0,0,0,.3); font-size: 13px; max-height: 320px; overflow-y: auto; }
        .asset-positions-legend strong { display: block; margin-bottom: 6px; font-size: 13px; }
        .asset-positions-legend .legend-row { display: flex; align-items: center; margin-bottom: 4px; white-space: nowrap; }
        .asset-positions-legend .legend-swatch { width: 12px; height: 12px; border-radius: 2px; margin-right: 6px; flex: 0 0 auto; }
        .asset-positions-legend .legend-toggle { display: block; margin-top: 6px; cursor: pointer; color: #337ab7; }
    </style>
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
                        </div>
                        <div class="panel-body p-20">
                            <div class="panel-title">
                                <h5 class="mb-0">Asset Positions</h5>

                                @unless ($this->trackingEnabled)
                                    <div class="alert alert-warning mt-10 mb-0">
                                        No live tracking integration is active for this company — the map and Position/In area/24h/48h columns will be blank until one is configured.
                                    </div>
                                @endunless
                            </div>

                            <div wire:ignore id="asset-positions-map" style="width:100%; height:400px;" class="mb-15"></div>

                            <div class="row align-items-center mb-15">
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search truck, fleet #, trip, driver, customer, destination...">
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive" style="overflow-x:auto; width:100%;">
                                <table class="table table-striped table-hover table-bordered table-sm align-middle asset-positions-table" cellspacing="0" width="100%">
                                    <colgroup>
                                        <col style="width:40px">
                                        <col style="width:100px">
                                        <col style="width:90px">
                                        <col style="width:110px">
                                        <col style="width:100px">
                                        <col style="width:100px">
                                        <col style="width:130px">
                                        <col style="width:110px">
                                        <col style="width:140px">
                                        <col style="width:160px">
                                        <col style="width:160px">
                                        <col style="width:160px">
                                        <col style="width:220px">
                                        <col style="width:130px">
                                        <col style="width:180px">
                                        <col style="width:120px">
                                        <col style="width:100px">
                                        <col style="width:70px">
                                        <col style="width:70px">
                                        <col style="width:220px">
                                        <col style="width:110px">
                                        <col style="width:110px">
                                        <col style="width:120px">
                                        <col style="width:110px">
                                        <col style="width:100px">
                                        <col style="width:100px">
                                        <col style="width:100px">
                                    </colgroup>
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th>
                                            <th wire:click="sortBy('registration_number')" class="sortable" style="cursor:pointer;">
                                                Asset
                                                @if ($sortField === 'registration_number')
                                                    <i class="fa fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                                @else
                                                    <i class="fa fa-sort text-muted"></i>
                                                @endif
                                            </th>
                                            <th wire:click="sortBy('fleet_number')" class="sortable" style="cursor:pointer;">
                                                Fleet
                                                @if ($sortField === 'fleet_number')
                                                    <i class="fa fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                                @else
                                                    <i class="fa fa-sort text-muted"></i>
                                                @endif
                                            </th>
                                            <th>Trip/Trip Ref</th>
                                            <th>Trailer</th>
                                            <th>Trailer #2</th>
                                            <th>Trailer Type</th>
                                            <th>Owner</th>
                                            <th>Driver</th>
                                            <th>Origin</th>
                                            <th>Destination</th>
                                            <th>Customer/Consignee</th>
                                            <th>Cargo</th>
                                            <th>TO Ref</th>
                                            <th>Position</th>
                                            <th>In area since</th>
                                            <th>In area for</th>
                                            <th>24h</th>
                                            <th>48h</th>
                                            <th>Notes</th>
                                            <th>FMS Status</th>
                                            <th>POD</th>
                                            <th>Route</th>
                                            <th>Trip Status</th>
                                            <th>Trip Date</th>
                                            <th>Since Load</th>
                                            <th>To Dest (km)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($horses as $horse)
                                            @php
                                                $assetKey = $horse->asset_type . ':' . $horse->id;
                                                $trip = $horse->latestTrip;
                                                $position = $positions->get($assetKey);
                                                $stat = $analytics->get($assetKey);

                                                $trailer1 = $trip?->trailers->get(0);
                                                $trailer2 = $trip?->trailers->get(1);
                                                $driver = $trip?->driver ?? ($horse->asset_type === 'horse' ? $horse->currentAssignment?->driver : null);
                                                $transportOrder = $trip?->transport_orders->first();
                                                $latestStatus = $trip?->latestStatus;

                                                $sinceLoad = null;
                                                if ($trip?->loading_time) {
                                                    try {
                                                        $sinceLoad = \Carbon\Carbon::parse($trip->loading_time)->diffForHumans(null, true);
                                                    } catch (\Exception $e) {
                                                        $sinceLoad = null;
                                                    }
                                                }

                                                $toDest = null;
                                                if ($position && $trip?->offloading_point?->lat && $trip?->offloading_point?->long) {
                                                    $toDest = \App\Models\AssetPositionLog::haversineKm(
                                                        $position['lat'], $position['lng'],
                                                        (float) $trip->offloading_point->lat, (float) $trip->offloading_point->long
                                                    );
                                                }

                                                $cargoDetails = $transportOrder?->cargo_details
                                                    ?: collect([$trip?->customer?->name, $trip?->cargo?->type, $trip?->haulage_type])->filter()->implode(' / ');
                                            @endphp
                                            <tr>
                                                <td>{{ $loop->iteration + ($horses->currentPage() - 1) * $horses->perPage() }}</td>
                                                <td>
                                                    <a href="{{ route($horse->asset_type === 'horse' ? 'horses.show' : 'vehicles.show', $horse->id) }}">{{ $horse->registration_number }}</a>
                                                    <div class="asset-positions-meta"><small>{{ ucfirst($horse->asset_type) }}</small></div>
                                                </td>
                                                <td>{{ $horse->fleet_number }}</td>
                                                <td>
                                                    @if ($trip)
                                                        <a href="{{ route('trips.show', $trip->id) }}">{{ $trip->trip_number }}</a>
                                                        @if ($trip->trip_ref)
                                                            <div class="asset-positions-meta"><small>{{ $trip->trip_ref }}</small></div>
                                                        @endif
                                                    @else
                                                        &mdash;
                                                    @endif
                                                </td>
                                                <td>{{ $trailer1->registration_number ?? '—' }}</td>
                                                <td>{{ $trailer2->registration_number ?? '—' }}</td>
                                                <td>{{ collect([$trailer1?->trailer_type?->name, $trailer2?->trailer_type?->name])->filter()->unique()->implode(' / ') ?: '—' }}</td>
                                                <td>{{ $horse->transporter->name ?? '—' }}</td>
                                                <td>{{ $driver?->employee ? trim($driver->employee->name . ' ' . $driver->employee->surname) : '—' }}</td>
                                                <td class="wrap">{{ $trip?->loading_point?->name ?? '—' }}</td>
                                                <td class="wrap">{{ $trip?->offloading_point?->name ?? '—' }}</td>
                                                <td class="wrap">{{ $trip?->customer?->name ?? $trip?->consignee?->name ?? '—' }}</td>
                                                <td class="wrap">
                                                    @if ($trip?->cargo || $trip?->weight || $trip?->quantity)
                                                        <strong>{{ $trip?->cargo?->name ?? '—' }}</strong>
                                                        @if ($trip?->cargo?->type)
                                                            <span class="badge badge-secondary">{{ $trip->cargo->type }}</span>
                                                        @endif
                                                        <div class="asset-positions-meta">
                                                            @if ($trip?->weight || $trip?->quantity)
                                                                <small>
                                                                    @if ($trip?->weight) Weight: {{ number_format((float) $trip->weight, 2) }} @endif
                                                                    @if ($trip?->quantity) {{ $trip?->weight ? '&middot;' : '' }} Qty: {{ number_format((float) $trip->quantity, 2) }} {{ $trip?->units_of_measure?->abbreviation ?? $trip?->units_of_measure?->name }} @endif
                                                                </small>
                                                            @endif
                                                            @if ($cargoDetails)
                                                                <small>{{ $cargoDetails }}</small>
                                                            @endif
                                                        </div>
                                                    @else
                                                        &mdash;
                                                    @endif
                                                </td>
                                                <td class="wrap">{{ $trip?->transport_orders->pluck('transport_order_number')->filter()->implode(' / ') ?: '—' }}</td>
                                                <td>
                                                    @if ($position)
                                                        <a href="https://www.google.com/maps?q={{ $position['lat'] }},{{ $position['lng'] }}" target="_blank" rel="noopener">
                                                            {{ number_format($position['lat'], 4) }}, {{ number_format($position['lng'], 4) }}
                                                        </a>
                                                        <div class="asset-positions-meta">
                                                            <small>
                                                                @if ($position['speed'] !== null){{ round((float) $position['speed']) }} km/h,@endif
                                                                @if ($position['observed_at'])
                                                                    {{ \Illuminate\Support\Carbon::parse($position['observed_at'])->format('d M H:i') }}
                                                                @endif
                                                            </small>
                                                        </div>
                                                    @else
                                                        &mdash;
                                                    @endif
                                                </td>
                                                <td>{{ $stat && $stat['in_area_since'] ? $stat['in_area_since']->format('d M H:i') : '—' }}</td>
                                                <td>{{ $stat && $stat['in_area_since'] ? $stat['in_area_since']->diffForHumans(null, true) : '—' }}</td>
                                                <td>{{ $stat ? $stat['distance_24h'] . ' km' : '—' }}</td>
                                                <td>{{ $stat ? $stat['distance_48h'] . ' km' : '—' }}</td>
                                                <td class="wrap">
                                                    @if ($latestStatus?->description)
                                                        {{ $latestStatus->description }}
                                                        <div class="asset-positions-meta">
                                                            <small>{{ optional($latestStatus->user)->name }} {{ optional($latestStatus->user)->surname }} &middot; {{ \Carbon\Carbon::parse($latestStatus->date ?? $latestStatus->created_at)->format('d M H:i') }}</small>
                                                        </div>
                                                    @else
                                                        &mdash;
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($latestStatus?->status)
                                                        <span class="badge badge-info">{{ $latestStatus->status }}</span>
                                                    @else
                                                        &mdash;
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($trip?->pod)
                                                        <span class="badge badge-success" title="POD uploaded{{ $trip->pod->date ? ' - ' . $trip->pod->date : '' }}"><i class="fa fa-file-alt"></i> Uploaded</span>
                                                    @elseif ($trip)
                                                        <span class="badge badge-secondary" title="No POD uploaded yet"><i class="fa fa-file-alt"></i> Missing</span>
                                                    @else
                                                        &mdash;
                                                    @endif
                                                </td>
                                                <td>{{ $trip?->route?->name ?? '—' }}</td>
                                                <td>{{ $trip?->trip_status ?? '—' }}</td>
                                                <td>{{ $trip?->start_date ? \Carbon\Carbon::parse($trip->start_date)->format('d M Y') : ($trip?->created_at?->format('d M Y') ?? '—') }}</td>
                                                <td>{{ $sinceLoad ?? '—' }}</td>
                                                <td>{{ $toDest !== null ? $toDest . ' km' : '—' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="27" class="text-center">
                                                    <img src="{{ asset('images/nodata.png') }}" alt="No data" style="max-width: 300px; padding: 2rem 0;">
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="clearfix"></div>
                            {{ $horses->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        window.assetPositionsMarkers = @json($mapMarkers);

        // Stable colour per Owner group, so the same owner always gets the
        // same legend swatch/marker colour across refreshes.
        window.assetPositionsColourFor = window.assetPositionsColourFor || function (group) {
            const palette = ['#d9534f', '#5b2c6f', '#f0ad4e', '#337ab7', '#5bc0de', '#5cb85c', '#8e44ad', '#e67e22', '#2c3e50', '#c0392b'];
            let hash = 0;
            for (let i = 0; i < group.length; i++) { hash = (hash * 31 + group.charCodeAt(i)) >>> 0; }
            return palette[hash % palette.length];
        };

        function initAssetPositionsMap() {
            const el = document.getElementById('asset-positions-map');
            if (!el || el.dataset.initialized) { return; }

            const defaultCenter = { lat: -17.8216, lng: 31.0492 }; // Harare
            const markers = window.assetPositionsMarkers || [];

            const map = new google.maps.Map(el, {
                center: markers.length ? { lat: markers[0].latitude, lng: markers[0].longitude } : defaultCenter,
                zoom: markers.length ? 6 : 6,
                mapTypeControl: true,
                mapTypeControlOptions: { style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR },
            });

            el.dataset.initialized = '1';
            el._apMap = map;
            renderAssetPositionsMarkers(map, markers);
        }

        function renderAssetPositionsMarkers(map, markers) {
            const el = document.getElementById('asset-positions-map');

            if (el._apClusterer) {
                el._apClusterer.clearMarkers();
            }

            const bounds = new google.maps.LatLngBounds();
            const groups = [];

            const gMarkers = markers.map(function (marker) {
                const position = { lat: marker.latitude, lng: marker.longitude };
                const colour = window.assetPositionsColourFor(marker.group || 'Other');

                if (groups.indexOf(marker.group) === -1) { groups.push(marker.group); }

                const gMarker = new google.maps.Marker({
                    position: position,
                    label: marker.label ? String(marker.label).slice(0, 2) : undefined,
                    title: marker.label + (marker.group ? ' (' + marker.group + ')' : ''),
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        fillColor: colour,
                        fillOpacity: 1,
                        strokeColor: '#fff',
                        strokeWeight: 1,
                        scale: 9,
                    },
                });

                const info = new google.maps.InfoWindow({
                    content: '<strong>' + marker.label + '</strong>'
                        + (marker.group ? '<br>' + marker.group : '')
                        + (marker.source ? '<br><small>' + marker.source + '</small>' : '')
                        + (marker.last_update ? '<br><small>' + marker.last_update + '</small>' : ''),
                });

                gMarker.addListener('click', function () { info.open(map, gMarker); });
                bounds.extend(position);

                return gMarker;
            });

            if (window.markerClusterer && gMarkers.length) {
                el._apClusterer = new markerClusterer.MarkerClusterer({ map: map, markers: gMarkers });
            } else {
                gMarkers.forEach(function (m) { m.setMap(map); });
            }

            if (gMarkers.length > 1) {
                map.fitBounds(bounds);
            }

            if (groups.length) {
                renderAssetPositionsLegend(map, groups);
            }
        }

        function renderAssetPositionsLegend(map, groups) {
            if (map._apLegendControl) { return; } // legend only needs building once per map instance

            const legend = document.createElement('div');
            legend.className = 'asset-positions-legend';

            const title = document.createElement('strong');
            title.textContent = 'Legend';
            legend.appendChild(title);

            const list = document.createElement('div');
            groups.sort().forEach(function (group) {
                const row = document.createElement('div');
                row.className = 'legend-row';

                const swatch = document.createElement('span');
                swatch.className = 'legend-swatch';
                swatch.style.background = window.assetPositionsColourFor(group);
                row.appendChild(swatch);

                const label = document.createElement('span');
                label.textContent = group;
                row.appendChild(label);

                list.appendChild(row);
            });
            legend.appendChild(list);

            const toggle = document.createElement('a');
            toggle.className = 'legend-toggle';
            toggle.textContent = 'Hide legend';
            toggle.href = '#';
            toggle.addEventListener('click', function (e) {
                e.preventDefault();
                const hidden = list.style.display === 'none';
                list.style.display = hidden ? '' : 'none';
                toggle.textContent = hidden ? 'Hide legend' : 'Show legend';
            });
            legend.appendChild(toggle);

            map._apLegendControl = legend;
            map.controls[google.maps.ControlPosition.LEFT_TOP].push(legend);
        }

        function initAssetPositionsMapWhenReady() {
            if (typeof google === 'undefined' || !google.maps) {
                return setTimeout(initAssetPositionsMapWhenReady, 200);
            }
            initAssetPositionsMap();
        }

        function refreshAssetPositionsMap() {
            const el = document.getElementById('asset-positions-map');
            if (el && el._apMap) {
                renderAssetPositionsMarkers(el._apMap, window.assetPositionsMarkers || []);
            } else {
                initAssetPositionsMapWhenReady();
            }
        }

        document.addEventListener('livewire:load', initAssetPositionsMapWhenReady);
        document.addEventListener('livewire:update', refreshAssetPositionsMap);
    </script>
</div>
