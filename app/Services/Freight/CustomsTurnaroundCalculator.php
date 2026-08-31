<?php

namespace App\Services\Freight;

use App\Models\CustomsDeclaration;
use Carbon\Carbon;

/**
 * Customs Turnaround Time Report: for declarations with both a
 * submission_date and a clearance_date, computes days elapsed between
 * them, grouped by clearing officer. No turnaround-time calculation
 * exists anywhere else in this codebase - this is the first.
 */
class CustomsTurnaroundCalculator
{
    public function __construct(
        private string $from,
        private string $to,
        private ?int $clearingOfficerId = null,
    ) {
    }

    private function turnedAroundDeclarations()
    {
        return CustomsDeclaration::whereNotNull('submission_date')
            ->whereNotNull('clearance_date')
            ->whereBetween('submission_date', [$this->from, $this->to])
            ->when($this->clearingOfficerId, fn ($q) => $q->where('clearing_officer_id', $this->clearingOfficerId))
            ->with('clearing_officer:id,name');
    }

    private function daysElapsed(CustomsDeclaration $declaration): int
    {
        return Carbon::parse($declaration->submission_date)->startOfDay()
            ->diffInDays(Carbon::parse($declaration->clearance_date)->startOfDay());
    }

    private function median(array $values): float
    {
        if (empty($values)) {
            return 0.0;
        }

        sort($values);
        $count = count($values);
        $middle = (int) floor($count / 2);

        if ($count % 2 === 0) {
            return ($values[$middle - 1] + $values[$middle]) / 2;
        }

        return (float) $values[$middle];
    }

    /**
     * @return array{
     *     0: array<int, array{label: string, count: int, avgDays: float, medianDays: float}>,
     *     1: array{count: int, avgDays: float, medianDays: float}
     * } [rows keyed by clearing_officer_id, overall totals]
     */
    public function byClearingOfficer(): array
    {
        $daysByOfficer = [];
        $labels = [];
        $allDays = [];

        foreach ($this->turnedAroundDeclarations()->get() as $declaration) {
            $officerId = $declaration->clearing_officer_id ?? 0;
            $days = $this->daysElapsed($declaration);

            $daysByOfficer[$officerId] ??= [];
            $daysByOfficer[$officerId][] = $days;
            $labels[$officerId] ??= $declaration->clearing_officer->name ?? 'Unassigned Officer';
            $allDays[] = $days;
        }

        $rows = [];
        foreach ($daysByOfficer as $officerId => $days) {
            $rows[$officerId] = [
                'label' => $labels[$officerId],
                'count' => count($days),
                'avgDays' => round(array_sum($days) / count($days), 1),
                'medianDays' => $this->median($days),
            ];
        }

        usort($rows, fn ($a, $b) => $b['count'] <=> $a['count']);

        $overall = [
            'count' => count($allDays),
            'avgDays' => $allDays ? round(array_sum($allDays) / count($allDays), 1) : 0.0,
            'medianDays' => $this->median($allDays),
        ];

        return [$rows, $overall];
    }

    /**
     * @return list<array{declaration_number: string, clearing_officer: string, submission_date: ?string, clearance_date: ?string, days: int}>
     */
    public function details(): array
    {
        return $this->turnedAroundDeclarations()->get()
            ->map(fn (CustomsDeclaration $declaration) => [
                'declaration_number' => $declaration->declaration_number,
                'clearing_officer' => $declaration->clearing_officer->name ?? 'Unassigned Officer',
                'submission_date' => optional($declaration->submission_date)->format('Y-m-d'),
                'clearance_date' => optional($declaration->clearance_date)->format('Y-m-d'),
                'days' => $this->daysElapsed($declaration),
            ])
            ->sortByDesc('days')
            ->values()
            ->all();
    }
}
