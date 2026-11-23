<?php

namespace App\Http\Livewire\Concerns;

use App\Services\Maps\MapLocationParser;

/**
 * Shared Google Places / location-link handling for the Destinations,
 * Loading Points, Offloading Points and Locations screens.
 *
 * The using component must declare public $location, $lat, $long and list
 * 'setLocationData' in $listeners. Pair with the
 * partials.google-places-autocomplete view.
 */
trait HandlesMapLocation
{
    /** Livewire property that holds the place name ('name' or 'city'). */
    protected function mapNameField(): string
    {
        return 'name';
    }

    /** Fired from the Places autocomplete when a suggestion is picked. */
    public function setLocationData($data)
    {
        $field = $this->mapNameField();
        $name = $data['name'] ?? $data['city'] ?? null;

        if (filled($name)) {
            $this->{$field} = $name;
        }
        $this->lat = $data['lat'] ?? $this->lat;
        $this->long = $data['long'] ?? $this->long;
        if (filled($data['location'] ?? null)) {
            $this->location = $data['location'];
        }
    }

    /** Re-parse coordinates every time the link changes. */
    public function updatedLocation($location)
    {
        if (!filled($location)) {
            return;
        }

        if ($pair = MapLocationParser::coordinates($location)) {
            [$this->lat, $this->long] = $pair;

            return;
        }

        // Unparseable link but coordinates already present (e.g. a ?cid= link
        // from the autocomplete): keep what we have.
        if (filled($this->lat) && filled($this->long)) {
            return;
        }

        $this->addError('location', 'Could not read coordinates from this link. Paste a Google Maps pin link, or type the latitude and longitude below.');
    }

    public function updatedLat()
    {
        $this->resetErrorBag('location');
    }

    public function updatedLong()
    {
        $this->resetErrorBag('location');
    }

    protected function mapCoordinateRules(): array
    {
        return [
            'lat' => 'nullable|numeric|between:-90,90',
            'long' => 'nullable|numeric|between:-180,180',
        ];
    }
}
