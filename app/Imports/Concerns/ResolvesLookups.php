<?php

namespace App\Imports\Concerns;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Row-level lookup helpers for freight bulk imports, shared between
 * FreightRateCardsImport and FreightJobsImport. Mirrors TripsImport's
 * proven fuzzy-match + in-memory-cache approach, split into two variants:
 * resolveOrCreate() (benign master data, safe to auto-create) and
 * resolveOnly() (a missing match is a real data problem - the row is
 * skipped and reported, never silently papered over with a fake record).
 */
trait ResolvesLookups
{
    protected array $lookupCache = [];

    protected function resolveOrCreate(string $modelClass, $name, array $extra = [])
    {
        $name = is_string($name) ? trim($name) : $name;

        if (!$name) {
            return null;
        }

        $cacheKey = 'create:' . $modelClass . '|' . strtolower($name);

        if (isset($this->lookupCache[$cacheKey])) {
            return $this->lookupCache[$cacheKey];
        }

        $record = $modelClass::where('name', 'LIKE', '%' . $name . '%')->first();

        if (!$record) {
            $record = $modelClass::create(array_merge([
                'name' => $name,
                'user_id' => Auth::id(),
            ], $extra));
        }

        return $this->lookupCache[$cacheKey] = $record;
    }

    protected function resolveOnly(string $modelClass, $name)
    {
        $name = is_string($name) ? trim($name) : $name;

        if (!$name) {
            return null;
        }

        $cacheKey = 'only:' . $modelClass . '|' . strtolower($name);

        if (array_key_exists($cacheKey, $this->lookupCache)) {
            return $this->lookupCache[$cacheKey];
        }

        return $this->lookupCache[$cacheKey] = $modelClass::where('name', 'LIKE', '%' . $name . '%')->first();
    }

    protected function parseExcelDate($value): ?Carbon
    {
        if (!$value) {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject($value));
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
