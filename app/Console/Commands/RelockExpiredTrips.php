<?php

namespace App\Console\Commands;

use App\Models\Trip;
use Illuminate\Console\Command;

class RelockExpiredTrips extends Command
{
    protected $signature = 'trips:relock-expired';
    protected $description = 'Clear unlocked_until/unlocked_by on completed trips whose admin-granted temporary unlock window has passed.';

    public function handle(): int
    {
        $count = Trip::whereNotNull('unlocked_until')
            ->where('unlocked_until', '<=', now())
            ->update(['unlocked_until' => null, 'unlocked_by' => null]);

        $this->info("Re-locked {$count} expired trip(s).");

        return self::SUCCESS;
    }
}
