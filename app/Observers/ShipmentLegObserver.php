<?php

namespace App\Observers;

use App\Models\ShipmentLeg;
use App\Services\GonyetiLink\GonyetiLinkService;
use Throwable;

/**
 * Gonyeti Instance Link, freight side: keeps the transport instance in step
 * with road legs however they are created or changed (Transport tab, job
 * form leg 1, From/To re-routing). No-op unless the company has an active
 * link with role "freight". Runs after commit so a rolled-back save sends nothing.
 */
class ShipmentLegObserver
{
    public $afterCommit = true;

    /** Fields the transport side cares about. */
    const SHARED_FIELDS = [
        'transport_mode', 'origin_id', 'destination_id', 'planned_departure', 'planned_arrival',
        'estimated_departure', 'estimated_arrival', 'carrier_reference',
    ];

    public function created(ShipmentLeg $leg): void
    {
        $this->safely(fn ($link) => $link->requestLeg($leg));
    }

    public function updated(ShipmentLeg $leg): void
    {
        if (GonyetiLinkService::$applyingRemote) {
            return;
        }

        $this->safely(function (GonyetiLinkService $link) use ($leg) {
            if ($leg->wasChanged('status') && $leg->status === 'cancelled') {
                $link->legCancelled($leg);
                return;
            }

            if (!$leg->wasChanged(self::SHARED_FIELDS)) {
                return;
            }

            if ($leg->link_ref && !GonyetiLinkService::isRoadLeg($leg)) {
                $link->legCancelled($leg, 'Leg changed to ' . ($leg->transport_mode ?: 'another mode') . ' on the freight side');
                return;
            }

            $link->legUpdated($leg);
        });
    }

    public function deleted(ShipmentLeg $leg): void
    {
        $this->safely(fn ($link) => $link->legCancelled($leg, 'Leg removed on the freight side'));
    }

    /** The link must never break saving a leg; failures stay in the outbox for retry. */
    private function safely(callable $callback): void
    {
        // Demo data being loaded (Freight > Demo Data) must never reach the
        // client's transport instance as real Trip Requests.
        if (\App\Services\DemoData\DemoDataRecorder::$suppressExternal) {
            return;
        }

        try {
            $callback(app(GonyetiLinkService::class));
        } catch (Throwable $e) {
            report($e);
        }
    }
}
