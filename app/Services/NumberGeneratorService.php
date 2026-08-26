<?php

namespace App\Services;

use App\Models\NumberSequence;
use Illuminate\Support\Facades\DB;

class NumberGeneratorService
{
    /**
     * Generate a year-segmented, race-safe sequential number like FF-2026-000001.
     */
    public function generate(string $type, string $prefix, int $padLength = 6): string
    {
        return DB::transaction(function () use ($type, $prefix, $padLength) {
            $year = now()->year;

            $sequence = NumberSequence::where('type', $type)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if (!$sequence) {
                $sequence = NumberSequence::create([
                    'type' => $type,
                    'year' => $year,
                    'last_number' => 0,
                ]);
            }

            $sequence->increment('last_number');

            return sprintf('%s-%d-%s', $prefix, $year, str_pad((string) $sequence->last_number, $padLength, '0', STR_PAD_LEFT));
        });
    }
}
