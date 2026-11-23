{{--
    Google Places autocomplete for the name field of a location modal.
    Attaches to #autocomplete (create) and #autocomplete-edit (edit) and sends
    the picked place to the component's setLocationData listener.
    Pair with App\Http\Livewire\Concerns\HandlesMapLocation.
--}}
<style>
    /* Suggestions dropdown must sit above Bootstrap modals (z-index 1050). */
    .pac-container { z-index: 20000 !important; }
</style>

<script>
    (function () {
        function attach(input) {
            if (!input || input.dataset.placesAttached) return;
            input.dataset.placesAttached = '1';

            var autocomplete = new google.maps.places.Autocomplete(input, {
                fields: ['name', 'geometry', 'url', 'formatted_address']
            });

            // Enter picks a suggestion instead of submitting the modal form.
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && document.querySelector('.pac-container .pac-item')) {
                    var visible = Array.prototype.some.call(document.querySelectorAll('.pac-container'), function (el) {
                        return el.offsetParent !== null;
                    });
                    if (visible) e.preventDefault();
                }
            });

            autocomplete.addListener('place_changed', function () {
                var place = autocomplete.getPlace();
                if (!place || !place.geometry) return;

                // Keep the input in step with the name Livewire will store.
                input.value = place.name;

                Livewire.emit('setLocationData', {
                    name: place.name,
                    lat: place.geometry.location.lat(),
                    long: place.geometry.location.lng(),
                    location: place.url || ''
                });
            });
        }

        function attachAll() {
            if (!(window.google && google.maps && google.maps.places)) return;
            attach(document.getElementById('autocomplete'));
            attach(document.getElementById('autocomplete-edit'));
        }

        window.gonyetiPlacesReady = function () {
            attachAll();
        };

        window.addEventListener('initializeAutocomplete', function (event) {
            var input = document.getElementById('autocomplete-edit');
            if (input && event.detail && event.detail.name !== undefined) input.value = event.detail.name;
            attachAll();
        });

        // Livewire can re-render the modal inputs; re-attach to any new element.
        document.addEventListener('livewire:load', function () {
            Livewire.hook('message.processed', attachAll);
        });

        if (window.google && google.maps && google.maps.places) {
            attachAll();
        } else if (!document.getElementById('google-maps-places-js')) {
            var s = document.createElement('script');
            s.id = 'google-maps-places-js';
            s.async = true;
            s.src = 'https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_key') }}&libraries=places&callback=gonyetiPlacesReady';
            document.head.appendChild(s);
        }
    })();
</script>
