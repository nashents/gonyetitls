<div wire:poll.{{ $pollSeconds }}s="$refresh">
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                Live Fleet Locations
                                <span class="badge badge-info">{{ count($markers) }} vehicle(s) reporting</span>
                            </div>
                        </div>
        <div class="panel-body p-20">
                            @if (! $this->cartrackEnabled && ! $this->ezyTrackEnabled && ! $this->fanTrackerEnabled && ! $this->pinpointEnabled)
                                <p class="text-muted">
                                    No tracking provider is active for your company yet. Set up Cartrack, EzyTrack, FanTracker and/or Pinpoint under
                                    <a href="{{ route('company_integrations.index') }}">Integrations</a> first.
                                </p>
                            @else
                                @if ($apiError)
                                    <p class="text-danger">Tracking request failed: {{ $apiError }}</p>
                                @endif
                                @if ($this->cartrackEnabled && empty($markers))
                                    <p class="text-muted">
                                        No live positions yet. Run <code>php artisan cartrack:match-vehicles</code>
                                        to link your fleet, then check back shortly.
                                    </p>
                                @endif
                                @if ($this->ezyTrackEnabled)
                                    <p class="text-muted">
                                        EzyTrack devices show up once linked under
                                        <a href="{{ route('fleet.ezytrack-device-mappings') }}">EzyTrack Device Mapping</a>.
                                    </p>
                                @endif
                                @if ($this->fanTrackerEnabled)
                                    <p class="text-muted">
                                        FanTracker trackers show up once linked under
                                        <a href="{{ route('fleet.fantracker-device-mappings') }}">FanTracker Device Mapping</a>.
                                    </p>
                                @endif
                                @if ($this->pinpointEnabled)
                                    <p class="text-muted">
                                        Pinpoint trackers show up once linked under
                                        <a href="{{ route('fleet.pinpoint-device-mappings') }}">Pinpoint Device Mapping</a>.
                                    </p>
                                @endif
                            @endif
                            <div id="cartrack-live-map" style="width:100%; height:600px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        window.cartrackMarkers = @json($markers);

        function initCartrackLiveMap() {
            const defaultCenter = { lat: -17.8216, lng: 31.0492 }; // Harare
            const markers = window.cartrackMarkers || [];

            const map = new google.maps.Map(document.getElementById('cartrack-live-map'), {
                center: markers.length ? { lat: markers[0].latitude, lng: markers[0].longitude } : defaultCenter,
                zoom: markers.length ? 10 : 6,
            });

            const bounds = new google.maps.LatLngBounds();

            markers.forEach(function (marker) {
                const position = { lat: marker.latitude, lng: marker.longitude };
                const mapMarker = new google.maps.Marker({
                    map: map,
                    position: position,
                    label: marker.label ? String(marker.label).slice(0, 2) : undefined,
                    title: marker.label + (marker.type ? ' (' + marker.type + ')' : ''),
                });

                const info = new google.maps.InfoWindow({
                    content: '<strong>' + marker.label + '</strong>'
                        + (marker.type ? '<br>' + marker.type : '')
                        + (marker.source ? '<br><small>' + marker.source + '</small>' : '')
                        + (marker.last_update ? '<br><small>' + marker.last_update + '</small>' : ''),
                });

                mapMarker.addListener('click', function () { info.open(map, mapMarker); });
                bounds.extend(position);
            });

            if (markers.length > 1) {
                map.fitBounds(bounds);
            }
        }

        function initCartrackLiveMapWhenReady() {
            if (typeof google === 'undefined' || !google.maps) {
                return setTimeout(initCartrackLiveMapWhenReady, 200);
            }
            initCartrackLiveMap();
        }

        document.addEventListener('livewire:load', initCartrackLiveMapWhenReady);
        document.addEventListener('livewire:update', initCartrackLiveMapWhenReady);
    </script>
</div>
