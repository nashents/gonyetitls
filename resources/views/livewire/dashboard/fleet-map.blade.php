<div wire:poll.{{ $pollSeconds }}s="$refresh">
    <div class="gd-panel-head">
        <h5 class="gd-panel-title"><i class="fa fa-map-marker"></i> Live Fleet Map</h5>
        <span class="gd-panel-note">
            <span class="badge badge-info">{{ count($markers) }} tracker(s) reporting</span>
            <a href="{{ route('fleet.live-map') }}" style="margin-left:8px;">Full map &rarr;</a>
        </span>
    </div>
    <div style="padding:12px 14px 16px 14px;">
        @if (! $this->cartrackEnabled && ! $this->ezyTrackEnabled && ! $this->fanTrackerEnabled && ! $this->pinpointEnabled)
            <p class="text-muted" style="margin:0;">
                No tracking provider is active for your company yet. Set up Cartrack, EzyTrack, FanTracker and/or Pinpoint under
                <a href="{{ route('company_integrations.index') }}">Integrations</a> first.
            </p>
        @else
            @if ($apiError)
                <p class="text-danger" style="margin:0 0 8px 0;">Tracking request failed: {{ $apiError }}</p>
            @endif
            <div id="dashboard-fleet-map" style="width:100%; height:420px; border-radius:8px; overflow:hidden;"></div>
        @endif
    </div>

    <script>
        window.dashboardFleetMapMarkers = @json($markers);

        function initDashboardFleetMap() {
            const el = document.getElementById('dashboard-fleet-map');
            if (!el) { return; }

            const defaultCenter = { lat: -17.8216, lng: 31.0492 }; // Harare
            const markers = window.dashboardFleetMapMarkers || [];

            const map = new google.maps.Map(el, {
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
            } else if (markers.length === 1) {
                map.setZoom(12);
            }
        }

        function initDashboardFleetMapWhenReady() {
            if (typeof google === 'undefined' || !google.maps) {
                return setTimeout(initDashboardFleetMapWhenReady, 200);
            }
            initDashboardFleetMap();
        }

        document.addEventListener('livewire:load', initDashboardFleetMapWhenReady);
        document.addEventListener('livewire:update', initDashboardFleetMapWhenReady);
    </script>
</div>
