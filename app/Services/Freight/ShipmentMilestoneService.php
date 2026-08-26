<?php

namespace App\Services\Freight;

use App\Models\ShipmentMilestone;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ShipmentMilestoneService
{
    public function record(array $data): ShipmentMilestone
    {
        $data['status'] = $data['status'] ?? 'pending';
        $data['created_by'] = $data['created_by'] ?? Auth::id();

        return ShipmentMilestone::create($data);
    }

    public function complete(ShipmentMilestone $milestone, ?Carbon $actualAt = null, ?string $notes = null): ShipmentMilestone
    {
        $milestone->actual_at = $actualAt ?? now();
        $milestone->status = 'completed';

        if ($notes !== null) {
            $milestone->notes = $notes;
        }

        $milestone->save();

        return $milestone;
    }

    public function recordAndComplete(array $data): ShipmentMilestone
    {
        $data['actual_at'] = $data['actual_at'] ?? now();
        $data['status'] = 'completed';
        $data['created_by'] = $data['created_by'] ?? Auth::id();

        return ShipmentMilestone::create($data);
    }
}
