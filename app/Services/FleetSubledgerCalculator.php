<?php

namespace App\Services;

use Illuminate\Support\Collection;
use App\Models\JournalEntryLine;

/**
 * Fleet sub-ledger: per-horse/trailer/vehicle/driver debit/credit totals for
 * a date range, drawn from the same posted journal_entry_lines population as
 * TrialBalanceCalculator. Horses, trailers, vehicles, and drivers are not
 * chart-of-accounts entries and are never posted to directly - they are
 * dimension tags carried on lines that hit real accounts (Fuel, Repairs &
 * Maintenance, Insurance, etc.), copied over from Bills/Payments by
 * BillJournalService/PaymentJournalService. This groups the existing tags
 * into a control-account-style statement (unit total, with the real-account
 * breakdown beneath it) without altering the underlying postings, so the
 * Trial Balance itself stays untouched and still balances.
 */
class FleetSubledgerCalculator
{
    public const DIMENSIONS = [
        'horse'   => ['column' => 'horse_id',   'table' => 'horses',   'label' => 'Horse'],
        'trailer' => ['column' => 'trailer_id',  'table' => 'trailers', 'label' => 'Trailer'],
        'vehicle' => ['column' => 'vehicle_id',  'table' => 'vehicles', 'label' => 'Vehicle'],
        'driver'  => ['column' => 'driver_id',   'table' => 'drivers',  'label' => 'Driver'],
    ];

    public function __construct(
        private int $companyId,
        private string $dateFrom,
        private string $dateTo,
        private string $dimension,
        private string $search = ''
    ) {
        if (!array_key_exists($this->dimension, self::DIMENSIONS)) {
            throw new \InvalidArgumentException("Unknown fleet dimension [{$this->dimension}]");
        }
    }

    private function config(): array
    {
        return self::DIMENSIONS[$this->dimension];
    }

    /**
     * Raw lines: one row per unit + account combination, so the view can
     * show each unit's control-account total with its real-account
     * breakdown underneath.
     */
    private function lines(): Collection
    {
        $column = $this->config()['column'];

        return JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->join('accounts', 'accounts.id', '=', 'journal_entry_lines.account_id')
            ->where('journal_entries.company_id', $this->companyId)
            ->where('journal_entries.status', '!=', 'draft')
            ->whereNotNull("journal_entry_lines.{$column}")
            ->whereBetween('journal_entries.date', [$this->dateFrom, $this->dateTo])
            ->selectRaw("
                journal_entry_lines.{$column} as unit_id,
                accounts.id as account_id,
                accounts.name as account_name,
                SUM(journal_entry_lines.debit) as total_debit,
                SUM(journal_entry_lines.credit) as total_credit,
                SUM(journal_entry_lines.debit) - SUM(journal_entry_lines.credit) as balance
            ")
            ->groupBy("journal_entry_lines.{$column}", 'accounts.id', 'accounts.name')
            ->get();
    }

    /**
     * @return array{
     *     0: array<int, array{unit_id: int, label: string, accounts: array<int, array{name: string, debit: float, credit: float, balance: float}>, total_debit: float, total_credit: float, balance: float}>,
     *     1: array{debit: float, credit: float}
     * } [rows keyed by unit_id, grand totals]
     */
    public function byUnit(): array
    {
        $lines = $this->lines();
        $labels = $this->unitLabels($lines->pluck('unit_id')->unique());

        $rows = [];
        $grandTotal = ['debit' => 0.0, 'credit' => 0.0];

        foreach ($lines as $line) {
            $unitId = (int) $line->unit_id;
            $label = $labels[$unitId] ?? "#{$unitId}";

            if ($this->search && stripos($label, $this->search) === false) {
                continue;
            }

            $rows[$unitId] ??= [
                'unit_id'     => $unitId,
                'label'       => $label,
                'accounts'    => [],
                'total_debit' => 0.0,
                'total_credit'=> 0.0,
                'balance'     => 0.0,
            ];

            $rows[$unitId]['accounts'][] = [
                'name'    => $line->account_name,
                'debit'   => (float) $line->total_debit,
                'credit'  => (float) $line->total_credit,
                'balance' => (float) $line->balance,
            ];

            $rows[$unitId]['total_debit'] += (float) $line->total_debit;
            $rows[$unitId]['total_credit'] += (float) $line->total_credit;
            $rows[$unitId]['balance'] += (float) $line->balance;

            $grandTotal['debit'] += (float) $line->total_debit;
            $grandTotal['credit'] += (float) $line->total_credit;
        }

        usort($rows, fn ($a, $b) => $b['total_debit'] <=> $a['total_debit']);

        return [array_values($rows), $grandTotal];
    }

    /**
     * The dimension tables (horses/trailers/vehicles/drivers) live outside
     * the accounting schema, so their display labels are fetched separately
     * rather than joined in SQL - keeps this calculator from having to know
     * each table's full column set, and each table already differs (drivers
     * have no own name column, borrowing it from their linked user).
     */
    private function unitLabels(Collection $unitIds): array
    {
        if ($unitIds->isEmpty()) {
            return [];
        }

        return match ($this->dimension) {
            'horse' => \App\Models\Horse::whereIn('id', $unitIds)->get(['id', 'horse_number', 'fleet_number'])
                ->mapWithKeys(fn ($h) => [$h->id => $h->fleet_number ?: ($h->horse_number ?: "Horse #{$h->id}")])->all(),
            'trailer' => \App\Models\Trailer::whereIn('id', $unitIds)->get(['id', 'trailer_number', 'fleet_number'])
                ->mapWithKeys(fn ($t) => [$t->id => $t->fleet_number ?: ($t->trailer_number ?: "Trailer #{$t->id}")])->all(),
            'vehicle' => \App\Models\Vehicle::whereIn('id', $unitIds)->get(['id', 'vehicle_number', 'fleet_number'])
                ->mapWithKeys(fn ($v) => [$v->id => $v->fleet_number ?: ($v->vehicle_number ?: "Vehicle #{$v->id}")])->all(),
            'driver' => \App\Models\Driver::whereIn('id', $unitIds)->with('user:id,name')->get(['id', 'user_id', 'driver_number'])
                ->mapWithKeys(fn ($d) => [$d->id => $d->user->name ?? ($d->driver_number ?: "Driver #{$d->id}")])->all(),
            default => [],
        };
    }
}
