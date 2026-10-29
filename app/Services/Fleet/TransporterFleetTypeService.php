<?php

namespace App\Services\Fleet;

use App\Models\Booking;
use App\Models\Ticket;
use App\Models\Transporter;
use App\Services\FleetLimitService;
use Illuminate\Validation\ValidationException;

/**
 * Moves a transporter between managed and once-off. Own fleet is set only
 * through the default-transporter flow, never here.
 *
 * - Promoting to managed adds its assets to the licensed fleet count, so the
 *   company's plan band must have room for them.
 * - Demoting to once-off hides its assets from operational modules, so it is
 *   refused while any of them still has an open booking or job card.
 */
class TransporterFleetTypeService
{
    public function __construct(private FleetLimitService $fleetLimits)
    {
    }

    /** Reason the change is not allowed, or null when it is. */
    public function blockedReason(Transporter $transporter, string $fleetType): ?string
    {
        if (! in_array($fleetType, [Transporter::FLEET_MANAGED, Transporter::FLEET_ONCE_OFF], true)) {
            return 'Choose Managed or Once-off.';
        }

        if ($transporter->fleet_type === Transporter::FLEET_OWN) {
            return 'This is the own fleet (default transporter); make another transporter the default first.';
        }

        if ($transporter->fleet_type === $fleetType) {
            return null;
        }

        if ($fleetType === Transporter::FLEET_MANAGED) {
            $company = $transporter->company;
            $remaining = $company ? $this->fleetLimits->remainingCapacity($company) : null;
            $adding = $company ? $this->fleetLimits->transporterFleetCount($company, $transporter) : 0;

            if ($remaining !== null && $adding > $remaining) {
                return "Making {$transporter->name} managed adds {$adding} assets to the fleet count, but the plan only has room for {$remaining} more.";
            }

            return null;
        }

        $open = $this->openWorkCount($transporter);

        if ($open > 0) {
            return "{$transporter->name}'s assets still have {$open} open booking(s)/job card(s); close them before making it once-off.";
        }

        return null;
    }

    public function change(Transporter $transporter, string $fleetType): void
    {
        if ($reason = $this->blockedReason($transporter, $fleetType)) {
            throw ValidationException::withMessages(['fleet_type' => $reason]);
        }

        if ($transporter->fleet_type !== $fleetType) {
            $transporter->fleet_type = $fleetType;
            $transporter->save();
        }
    }

    private function openWorkCount(Transporter $transporter): int
    {
        $assets = function ($q) use ($transporter) {
            $q->whereIn('horse_id', $transporter->horses()->select('id'))
                ->orWhereIn('trailer_id', $transporter->trailers()->select('id'))
                ->orWhereIn('vehicle_id', $transporter->vehicles()->select('id'));
        };

        return Booking::where('status', 1)->where($assets)->count()
            + Ticket::where('status', 1)->where($assets)->count();
    }
}
