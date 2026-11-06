<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Transporters auto-created by the old trips import (before 2026-11-06) were
 * saved with no transporter_number. Give each one "<initials>T<own id>", the
 * same number the Transporters screen would have issued, falling back to the
 * next free number if that one is somehow taken. Idempotent.
 */
return new class extends Migration
{
    public function up(): void
    {
        $blank = DB::table('transporters')
            ->where(fn ($q) => $q->whereNull('transporter_number')->orWhere('transporter_number', ''))
            ->orderBy('id')
            ->get(['id', 'company_id']);

        if ($blank->isEmpty()) {
            return;
        }

        $companies = DB::table('companies')->pluck('name', 'id');
        $fallbackCompany = $companies->first();
        $next = (int) DB::table('transporters')->max('id');

        foreach ($blank as $row) {
            $words = preg_split('/\s+/', trim((string) ($companies[$row->company_id] ?? $fallbackCompany))) ?: [];
            $initials = strtoupper(($words[0][0] ?? '') . ($words[1][0] ?? ''));

            $number = $initials . 'T' . str_pad((string) $row->id, 5, '0', STR_PAD_LEFT);
            while (DB::table('transporters')->where('transporter_number', $number)->exists()) {
                $number = $initials . 'T' . str_pad((string) ++$next, 5, '0', STR_PAD_LEFT);
            }

            DB::table('transporters')->where('id', $row->id)->update(['transporter_number' => $number]);
        }
    }

    public function down(): void
    {
        // numbers may already be on documents/imports — leave them
    }
};
