<?php

namespace App\Console\Commands;

use App\Models\Bill;
use App\Models\Trip;
use App\Models\User;
use App\Services\Accounting\TransporterPaymentBillService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

/**
 * Transporter payment bills post when the trip is Offloaded (2026-10-16);
 * before that they posted at authorization. Aligns existing bills: posts
 * Offloaded trips' unposted bills, un-posts (reverses, dated like the original)
 * unpaid bills of trips not yet Offloaded. Paid bills are left posted.
 */
class AlignTransporterBillsToOffload extends Command
{
    protected $signature = 'transporter-bills:align-to-offload
                            {--dry-run : Report what would change without posting}
                            {--user= : User id recorded as poster (default: first user with an employee)}';

    protected $description = 'Post transporter payment bills of Offloaded trips and un-post unpaid ones whose trips are not Offloaded';

    public function handle(TransporterPaymentBillService $service): int
    {
        $user = $this->option('user') ? User::find($this->option('user')) : User::whereHas('employee')->orderBy('id')->first();
        if (!$user) {
            $this->error('No user to post as.');
            return self::FAILURE;
        }
        Auth::setUser($user);
        $dryRun = (bool) $this->option('dry-run');

        $rows = [];
        $counts = ['post' => 0, 'unpost' => 0, 'keep (paid)' => 0, 'ok' => 0];

        Trip::where('authorization', 'approved')
            ->whereHas('trip_expenses', fn ($q) => $q->whereNotNull('transporter_id'))
            ->orderBy('id')
            ->chunkById(100, function ($trips) use ($service, $dryRun, &$rows, &$counts) {
                foreach ($trips as $trip) {
                    $offloaded = $trip->trip_status === 'Offloaded';
                    $bills = Bill::where('trip_id', $trip->id)->where('category', TransporterPaymentBillService::CATEGORY)->get();
                    $action = 'ok';
                    foreach ($bills as $bill) {
                        $posted = $service->isPosted($bill);
                        $paid = $bill->bill_payments()->exists();
                        if ($offloaded && !$posted) {
                            $action = 'post';
                        } elseif (!$offloaded && $posted) {
                            $action = $paid ? 'keep (paid)' : 'unpost';
                        }
                        if ($action !== 'ok') {
                            $rows[] = [$trip->trip_number, $trip->trip_status, $bill->bill_number, (float) $bill->total, $action];
                        }
                    }
                    if ($bills->isEmpty() && $offloaded) {
                        $action = 'post';
                        $rows[] = [$trip->trip_number, $trip->trip_status, '(no bill yet)', null, 'post'];
                    }
                    $counts[$action]++;

                    if (!$dryRun && in_array($action, ['post', 'unpost'], true)) {
                        $warning = null;
                        $service->syncTrip($trip, $warning);
                        if ($warning) {
                            $this->warn($warning);
                        }
                    }
                }
            });

        if ($rows) {
            $this->table(['Trip', 'Status', 'Bill', 'Total', 'Action'], $rows);
        }
        foreach ($counts as $action => $n) {
            $this->line(($dryRun ? 'Would ' : '') . "{$action}: {$n} trip(s)");
        }

        return self::SUCCESS;
    }
}
