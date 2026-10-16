<?php

namespace App\Imports;

use App\Imports\Concerns\SkipsTemplateExampleRows;

use App\Models\Border;
use App\Models\Country;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

/**
 * Columns: name, country_a, country_b (country names). A row whose name
 * matches an existing border updates it, otherwise a new border is created.
 */
class BordersImport implements  ToCollection, SkipsEmptyRows, WithLimit,
WithHeadingRow,
SkipsOnError,
WithValidation,
WithChunkReading,
WithBatchInserts
{
    use SkipsTemplateExampleRows;

    use Importable, SkipsErrors;

    public function limit(): int
    {
        return 2500;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if ($row->filter()->isNotEmpty()) {
                $name = trim((string) $row['name']);
                // "#" marks the template's example row
                if ($name == "" || str_starts_with($name, '#')) {
                    continue;
                }

                $country_a = $this->countryId($row['country_a'] ?? null);
                $country_b = $this->countryId($row['country_b'] ?? null);

                $border = self::findBorder($name);

                if (isset($border)) {
                    $border->name = $name;
                    if ($country_a) {
                        $border->country_a = $country_a;
                    }
                    if ($country_b) {
                        $border->country_b = $country_b;
                    }
                    $border->update();
                } else {
                    $border = new Border;
                    $border->user_id = Auth::user()->id;
                    $border->name = $name;
                    $border->country_a = $country_a;
                    $border->country_b = $country_b;
                    $border->save();
                }
            }
        }
    }

    public static function findBorder($name)
    {
        return Border::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($name))])->first();
    }

    private function countryId($name)
    {
        $name = trim((string) $name);
        if ($name == "") {
            return null;
        }
        $country = Country::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)])->first();

        return $country ? $country->id : null;
    }

    public function rules(): array{
        return[
            '*.name' => ['required'],
        ];
    }

    public function batchSize(): int
    {
        return 10;
    }

    public function chunkSize(): int
    {
        return 10;
    }
}
