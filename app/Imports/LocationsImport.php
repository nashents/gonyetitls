<?php

namespace App\Imports;

use App\Imports\Concerns\SkipsTemplateExampleRows;
use App\Models\Location;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithLimit;

/**
 * Worksites (locations) import. Columns: name, location_pin, latitude,
 * longitude, description. A row whose name matches an existing worksite
 * updates it; otherwise a new one is created. When latitude/longitude are
 * blank they are read from a full Google Maps link in location_pin
 * (short maps.app.goo.gl links are stored but not expanded here).
 */
class LocationsImport implements ToCollection, SkipsEmptyRows, WithHeadingRow, WithLimit
{
    use SkipsTemplateExampleRows;
    use Importable;

    public int $created = 0;
    public int $updated = 0;
    public array $skipped = [];

    public function limit(): int
    {
        return 2500;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $name = trim((string) $row->get('name'));
            if ($name === '') {
                $this->skipped[] = 'a row with no name';
                continue;
            }

            $pin  = trim((string) $row->get('location_pin')) ?: null;
            $lat  = $row->get('latitude');
            $long = $row->get('longitude');
            if ((!filled($lat) || !filled($long)) && $pin) {
                [$lat, $long] = $this->coordinatesFromPin($pin) ?? [$lat, $long];
            }

            $location = Location::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)])->first();
            $isNew = !$location;
            if ($isNew) {
                $location = new Location;
                $location->user_id = Auth::id();
                $location->status = 1;
            }

            $location->name = $name;
            if ($pin) {
                $location->location_pin = $pin;
            }
            if (filled($lat)) {
                $location->lat = (string) $lat;
            }
            if (filled($long)) {
                $location->long = (string) $long;
            }
            if (filled($row->get('description'))) {
                $location->description = $row->get('description');
            }
            $location->save();

            $isNew ? $this->created++ : $this->updated++;
        }
    }

    /** Same URL patterns the Worksites form understands. */
    private function coordinatesFromPin(string $url): ?array
    {
        foreach (['/\/search\/([-0-9.]+),\+?([-0-9.]+)/', '/@([-0-9.]+),([-0-9.]+),[0-9.]+z/', '/!3d([-0-9.]+)!4d([-0-9.]+)/'] as $pattern) {
            if (preg_match($pattern, $url, $m)) {
                return [$m[1], $m[2]];
            }
        }

        return null;
    }
}
