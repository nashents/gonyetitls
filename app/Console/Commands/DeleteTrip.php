<?php

namespace App\Console\Commands;

use App\Models\Trip;
use App\Models\User;
use App\Services\Accounting\TripDeletionService;
use Illuminate\Console\Command;

class DeleteTrip extends Command
{
    protected $signature = 'trips:delete
        {trip_number* : Trip number(s) to delete}
        {--reason= : Reason recorded on the reversed invoices/bills}
        {--as= : Email of the user the deletion is attributed to}
        {--force : Skip the confirmation prompt}';

    protected $description = 'Delete a trip and reverse the invoice(s)/bill(s) raised against it via TripDeletionService, refusing rather than guessing on anything it cannot safely reverse.';

    public function handle(TripDeletionService $tripDeletion): int
    {
        $tripNumbers = $this->argument('trip_number');

        $trips = Trip::whereIn('trip_number', $tripNumbers)->get();
        $missing = collect($tripNumbers)->diff($trips->pluck('trip_number'));

        if ($missing->isNotEmpty()) {
            $this->error('Trip number(s) not found: ' . $missing->implode(', '));
            return self::FAILURE;
        }

        $deletedById = null;
        if ($this->option('as')) {
            $user = User::where('email', $this->option('as'))->first();
            if (! $user) {
                $this->error("No user found with email {$this->option('as')}");
                return self::FAILURE;
            }
            $deletedById = $user->id;
        }

        $this->table(
            ['Trip', 'Status', 'Customer', 'Invoiced?'],
            $trips->map(fn (Trip $trip) => [
                $trip->trip_number,
                $trip->trip_status,
                $trip->customer?->name,
                $trip->is_invoiced ? 'Yes' : 'No',
            ])
        );

        if (! $this->option('force') && ! $this->confirm('Delete ' . $trips->count() . ' trip(s) and reverse everything posted against them?')) {
            $this->info('Aborted.');
            return self::SUCCESS;
        }

        $failures = 0;

        foreach ($trips as $trip) {
            try {
                $warnings = $tripDeletion->delete($trip, $deletedById, $this->option('reason'));

                $this->info("Deleted trip {$trip->trip_number}.");
                foreach ($warnings as $warning) {
                    $this->warn("  - {$warning}");
                }
            } catch (\Throwable $e) {
                $failures++;
                $this->error("Failed to delete trip {$trip->trip_number}: {$e->getMessage()}");
            }
        }

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }
}
