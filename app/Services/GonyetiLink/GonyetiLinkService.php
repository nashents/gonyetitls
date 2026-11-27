<?php

namespace App\Services\GonyetiLink;

use App\Integrations\GonyetiLink\GonyetiLinkDriver;
use App\Models\CompanyIntegration;
use App\Models\Destination;
use App\Models\GonyetiLinkMessage;
use App\Models\ShipmentLeg;
use App\Models\Trip;
use App\Models\TripRequest;
use App\Services\Freight\ShipmentLegService;
use App\Services\Integrations\IntegrationGate;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Throwable;

/**
 * Gonyeti Instance Link: a client's freight forwarding instance and transport
 * instance working as one.
 *
 * freight side  -> leg.requested / leg.updated / leg.cancelled
 * transport side -> trip.created / trip.status / request.rejected
 *
 * Outbound messages go through gonyeti_link_messages and are delivered in
 * order. A message that can't be delivered (other side down) stays "failed"
 * and blocks later ones until a retry gets through, so nothing is applied out
 * of order. Rejected messages (4xx) are logged and skipped.
 */
class GonyetiLinkService
{
    const PROVIDER = 'gonyeti_link';
    const ROLE_FREIGHT = 'freight';
    const ROLE_TRANSPORT = 'transport';

    /** True while applying a message from the other instance: don't echo it back. */
    public static bool $applyingRemote = false;

    public function __construct(protected ShipmentLegService $legs)
    {
    }

    // ---------------------------------------------------------------
    // Integration lookup
    // ---------------------------------------------------------------

    public function integrationFor(?int $companyId, string $role): ?CompanyIntegration
    {
        if (!$companyId || !IntegrationGate::activeForCompany(self::PROVIDER, $companyId)) {
            return null;
        }

        // Explicit company filter; skip any tenant scope (inbound messages have no signed-in user).
        return CompanyIntegration::withoutGlobalScope('company')->where('company_id', $companyId)
            ->where('status', 'active')
            ->whereHas('integration_provider', fn ($q) => $q->where('key', self::PROVIDER))
            ->get()
            ->first(fn ($i) => ($i->config['role'] ?? self::ROLE_FREIGHT) === $role);
    }

    public function enabledForUser(string $role): bool
    {
        return (bool) $this->integrationFor(IntegrationGate::companyIdForUser(), $role);
    }

    /** Active link integrations of every company (inbound signature check). */
    public function activeIntegrations()
    {
        return CompanyIntegration::withoutGlobalScope('company')->where('status', 'active')
            ->whereHas('integration_provider', fn ($q) => $q->where('key', self::PROVIDER)->where('is_active', true))
            ->get();
    }

    // ---------------------------------------------------------------
    // Outbox
    // ---------------------------------------------------------------

    public function queue(CompanyIntegration $integration, string $event, ?string $ref, array $data): GonyetiLinkMessage
    {
        $message = GonyetiLinkMessage::create([
            'company_integration_id' => $integration->id,
            'direction' => 'out',
            'event' => $event,
            'ref' => $ref,
            'payload' => $data,
            'status' => 'pending',
        ]);

        $this->deliver($integration);

        return $message->fresh();
    }

    /** Sends waiting messages oldest first; stops at the first one that can't get through. */
    public function deliver(CompanyIntegration $integration, int $limit = 50): array
    {
        $driver = new GonyetiLinkDriver($integration);
        $sent = 0;
        $failed = null;

        $waiting = GonyetiLinkMessage::where('company_integration_id', $integration->id)
            ->where('direction', 'out')
            ->whereIn('status', ['pending', 'failed'])
            ->orderBy('id')
            ->limit($limit)
            ->get();

        foreach ($waiting as $message) {
            $result = $driver->post($message->event, $message->ref, (array) $message->payload);
            $message->attempts++;

            if ($result['success']) {
                $message->forceFill(['status' => 'sent', 'sent_at' => now(), 'last_error' => null])->save();
                $sent++;
                $this->afterSent($message);
                continue;
            }

            $message->forceFill([
                'status' => $result['permanent'] ? 'rejected' : 'failed',
                'last_error' => $result['error'],
            ])->save();
            $this->afterFailed($message, $result['error'], $result['permanent']);

            if (!$result['permanent']) {
                $failed = $result['error'];
                break;
            }
        }

        $integration->forceFill($failed
            ? ['last_error' => $failed]
            : ['last_success_at' => $sent ? now() : $integration->last_success_at, 'last_error' => null, 'last_sync_at' => now()])->save();

        return ['sent' => $sent, 'error' => $failed];
    }

    /** Waiting (undelivered) outbound messages for a ref. */
    public function waitingFor(?string $ref): int
    {
        return $ref ? GonyetiLinkMessage::where('ref', $ref)->where('direction', 'out')->whereIn('status', ['pending', 'failed'])->count() : 0;
    }

    protected function afterSent(GonyetiLinkMessage $message): void
    {
        if ($message->event === 'leg.requested' && ($leg = ShipmentLeg::where('link_ref', $message->ref)->first())
            && in_array($leg->link_status, [null, 'failed'], true)) {
            $leg->forceFill(['link_status' => 'requested', 'link_message' => null, 'link_synced_at' => now()])->save();
        }
    }

    protected function afterFailed(GonyetiLinkMessage $message, ?string $error, bool $permanent): void
    {
        if (str_starts_with($message->event, 'leg.') && ($leg = ShipmentLeg::where('link_ref', $message->ref)->first())
            && in_array($leg->link_status, [null, 'failed', 'requested'], true)) {
            $leg->forceFill([
                'link_status' => $message->event === 'leg.requested' && $leg->link_status !== 'requested' ? 'failed' : $leg->link_status,
                'link_message' => ($permanent ? 'Rejected by the transport instance: ' : 'Not delivered yet: ') . $error,
            ])->save();
        }
    }

    // ---------------------------------------------------------------
    // Freight side: road legs out
    // ---------------------------------------------------------------

    public static function isRoadLeg(ShipmentLeg $leg): bool
    {
        return strtolower((string) $leg->transport_mode) === 'road';
    }

    protected function freightIntegrationForLeg(ShipmentLeg $leg): ?CompanyIntegration
    {
        $companyId = optional(optional($leg->shipment)->freight_job)->company_id ?? IntegrationGate::companyIdForUser();

        return $this->integrationFor($companyId, self::ROLE_FREIGHT);
    }

    /** New road leg (or a manual "Send to Transport"): ask the transport instance for a trip. */
    public function requestLeg(ShipmentLeg $leg): ?GonyetiLinkMessage
    {
        if (!self::isRoadLeg($leg) || !($integration = $this->freightIntegrationForLeg($leg))) {
            return null;
        }

        if (!$leg->link_ref) {
            $leg->forceFill(['link_ref' => (string) Str::uuid()])->save();
        }
        // A resend after a rejection/cancellation reopens the request.
        $leg->forceFill(['link_status' => 'failed', 'link_message' => 'Sending...'])->save();

        return $this->queue($integration, 'leg.requested', $leg->link_ref, $this->legPayload($leg));
    }

    /** Route/dates/cargo edited after sending. A road leg that was never sent is sent now. */
    public function legUpdated(ShipmentLeg $leg): ?GonyetiLinkMessage
    {
        if (!$leg->link_ref) {
            return self::isRoadLeg($leg) ? $this->requestLeg($leg) : null;
        }
        if (in_array($leg->link_status, ['cancelled', 'rejected'], true) || !($integration = $this->freightIntegrationForLeg($leg))) {
            return null;
        }

        return $this->queue($integration, 'leg.updated', $leg->link_ref, $this->legPayload($leg));
    }

    public function legCancelled(ShipmentLeg $leg, string $reason = 'Leg cancelled on the freight side'): ?GonyetiLinkMessage
    {
        if (!$leg->link_ref || $leg->link_status === 'cancelled' || !($integration = $this->freightIntegrationForLeg($leg))) {
            return null;
        }

        $leg->forceFill(['link_status' => 'cancelled', 'link_message' => $reason])->save();

        return $this->queue($integration, 'leg.cancelled', $leg->link_ref, ['reason' => $reason]);
    }

    public function legPayload(ShipmentLeg $leg): array
    {
        $leg->loadMissing('shipment.freight_job.customer', 'shipment.containers', 'origin.country', 'destination.country');
        $shipment = $leg->shipment;
        $job = $shipment?->freight_job;

        $place = fn ($d) => $d ? [
            'city' => $d->city,
            'country' => optional($d->country)->name,
            'description' => $d->description,
        ] : null;

        return [
            'job_number' => $job?->job_number,
            'job_customer' => optional($job?->customer)->name,
            'customer_reference' => $job?->customer_reference,
            'shipment_number' => $shipment?->shipment_number,
            'cargo_description' => $shipment?->cargo_description,
            'gross_weight' => $shipment?->gross_weight,
            'volume_cbm' => $shipment?->volume_cbm,
            'package_count' => $shipment?->package_count,
            'containers' => $shipment ? $shipment->containers->map(fn ($c) => trim($c->container_number . ($c->container_type ? " ({$c->container_type})" : '')))->filter()->values()->all() : [],
            'leg_sequence' => $leg->sequence,
            'origin' => $place($leg->origin),
            'destination' => $place($leg->destination),
            'planned_departure' => optional($leg->planned_departure ?? $leg->estimated_departure)->toIso8601String(),
            'planned_arrival' => optional($leg->planned_arrival ?? $leg->estimated_arrival)->toIso8601String(),
            'carrier_reference' => $leg->carrier_reference,
        ];
    }

    // ---------------------------------------------------------------
    // Transport side: trip progress out
    // ---------------------------------------------------------------

    protected function transportIntegration(TripRequest $request): ?CompanyIntegration
    {
        $integration = $request->integration;

        return $integration && $integration->status === 'active' ? $integration : $this->integrationFor($request->company_id, self::ROLE_TRANSPORT);
    }

    public function tripLinked(TripRequest $request, Trip $trip): void
    {
        $request->forceFill([
            'trip_id' => $trip->id,
            'status' => TripRequest::TRIP_CREATED,
            'changed_since_trip' => false,
            'responded_by' => auth()->id(),
            'responded_at' => now(),
        ])->save();

        if ($integration = $this->transportIntegration($request)) {
            $this->queue($integration, 'trip.created', $request->remote_ref, $this->tripPayload($trip));
        }
    }

    public function rejectRequest(TripRequest $request, string $reason): void
    {
        $request->forceFill([
            'status' => TripRequest::REJECTED,
            'rejection_reason' => $reason,
            'responded_by' => auth()->id(),
            'responded_at' => now(),
        ])->save();

        if ($integration = $this->transportIntegration($request)) {
            $this->queue($integration, 'request.rejected', $request->remote_ref, ['reason' => $reason]);
        }
    }

    /** Called by TripObserver when a trip's status changes. */
    public function tripStatusChanged(Trip $trip): void
    {
        $requests = TripRequest::where('trip_id', $trip->id)->where('status', TripRequest::TRIP_CREATED)->get();
        foreach ($requests as $request) {
            if ($integration = $this->transportIntegration($request)) {
                $this->queue($integration, 'trip.status', $request->remote_ref, $this->tripPayload($trip));
            }
        }
    }

    public function tripPayload(Trip $trip): array
    {
        return [
            'trip_number' => $trip->trip_number,
            'trip_status' => $trip->trip_status,
            'start_date' => $trip->start_date,
            'end_date' => $trip->end_date,
            'horse' => optional($trip->horse)->registration_number,
            'driver' => optional(optional($trip->driver)->employee, fn ($e) => trim($e->name . ' ' . $e->surname)),
        ];
    }

    // ---------------------------------------------------------------
    // Inbound
    // ---------------------------------------------------------------

    /** @return array response body for the sender */
    public function handleInbound(CompanyIntegration $integration, string $event, ?string $ref, array $data, array $source = []): array
    {
        $role = $integration->config['role'] ?? self::ROLE_FREIGHT;

        GonyetiLinkMessage::create([
            'company_integration_id' => $integration->id,
            'direction' => 'in',
            'event' => $event,
            'ref' => $ref,
            'payload' => $data,
            'status' => 'received',
        ]);

        $integration->forceFill(['last_success_at' => now()])->save();

        if ($event === 'ping') {
            return ['ok' => true, 'role' => $role, 'company' => optional($integration->company)->name];
        }

        if (!$ref) {
            abort(422, 'Missing ref.');
        }

        if ($role === self::ROLE_TRANSPORT && in_array($event, ['leg.requested', 'leg.updated', 'leg.cancelled'], true)) {
            $request = $this->receiveLeg($integration, $event, $ref, $data, $source);

            return ['ok' => true, 'request_number' => $request->request_number, 'status' => $request->status];
        }

        if ($role === self::ROLE_FREIGHT && in_array($event, ['trip.created', 'trip.status', 'request.rejected'], true)) {
            self::$applyingRemote = true;
            try {
                $this->receiveTripUpdate($event, $ref, $data);
            } finally {
                self::$applyingRemote = false;
            }

            return ['ok' => true];
        }

        abort(422, "Event {$event} is not accepted by a {$role} instance.");
    }

    protected function receiveLeg(CompanyIntegration $integration, string $event, string $ref, array $data, array $source): TripRequest
    {
        $request = TripRequest::withTrashed()->where('remote_ref', $ref)->first();

        if ($event === 'leg.cancelled') {
            if (!$request) {
                abort(422, 'Unknown request.');
            }
            $request->forceFill(['status' => TripRequest::CANCELLED, 'rejection_reason' => $data['reason'] ?? null])->save();

            return $request;
        }

        $place = fn ($p) => $p ? trim(implode(', ', array_filter([$p['city'] ?? null, $p['country'] ?? null]))) : null;

        $fields = [
            'company_id' => $integration->company_id,
            'company_integration_id' => $integration->id,
            'source_company' => $source['company'] ?? null,
            'job_number' => $data['job_number'] ?? null,
            'shipment_number' => $data['shipment_number'] ?? null,
            'job_customer' => $data['job_customer'] ?? null,
            'cargo_description' => $data['cargo_description'] ?? null,
            'gross_weight' => is_numeric($data['gross_weight'] ?? null) ? $data['gross_weight'] : null,
            'volume_cbm' => is_numeric($data['volume_cbm'] ?? null) ? $data['volume_cbm'] : null,
            'package_count' => is_numeric($data['package_count'] ?? null) ? (int) $data['package_count'] : null,
            'containers' => implode(', ', (array) ($data['containers'] ?? [])) ?: null,
            'origin_text' => $place($data['origin'] ?? null),
            'destination_text' => $place($data['destination'] ?? null),
            'origin_id' => $this->matchDestination($data['origin'] ?? null),
            'destination_id' => $this->matchDestination($data['destination'] ?? null),
            'planned_departure' => !empty($data['planned_departure']) ? Carbon::parse($data['planned_departure']) : null,
            'planned_arrival' => !empty($data['planned_arrival']) ? Carbon::parse($data['planned_arrival']) : null,
            'carrier_reference' => $data['carrier_reference'] ?? null,
            'payload' => $data,
        ];

        if (!$request) {
            $request = new TripRequest(['remote_ref' => $ref]);
            $request->forceFill($fields + ['status' => TripRequest::PENDING])->save();
            $request->forceFill(['request_number' => 'TRQ' . str_pad((string) $request->id, 5, '0', STR_PAD_LEFT)])->save();

            return $request;
        }

        if ($request->trashed()) {
            $request->restore();
        }

        // Keep the transport team's own destination picks on an update.
        if ($request->origin_id) {
            unset($fields['origin_id']);
        }
        if ($request->destination_id) {
            unset($fields['destination_id']);
        }

        if ($request->status === TripRequest::TRIP_CREATED) {
            $fields['changed_since_trip'] = true;
        } elseif ($event === 'leg.requested' || $request->status === TripRequest::CANCELLED) {
            // Re-sent after a rejection/cancellation: back in the queue.
            $fields['status'] = TripRequest::PENDING;
            $fields['rejection_reason'] = null;
        }

        $request->forceFill($fields)->save();

        return $request;
    }

    /** Local Destination with the same city (and country, when given). */
    public function matchDestination(?array $place): ?int
    {
        $city = trim((string) ($place['city'] ?? ''));
        if ($city === '') {
            return null;
        }

        $matches = Destination::with('country')->whereRaw('LOWER(TRIM(city)) = ?', [strtolower($city)])->get();
        if ($matches->count() > 1 && !empty($place['country'])) {
            $byCountry = $matches->filter(fn ($d) => strcasecmp((string) optional($d->country)->name, (string) $place['country']) === 0);
            $matches = $byCountry->isNotEmpty() ? $byCountry : $matches;
        }

        return $matches->count() === 1 ? $matches->first()->id : null;
    }

    protected function receiveTripUpdate(string $event, string $ref, array $data): void
    {
        $leg = ShipmentLeg::where('link_ref', $ref)->first();
        if (!$leg) {
            abort(422, 'Unknown leg.');
        }

        if ($event === 'request.rejected') {
            $leg->forceFill(['link_status' => 'rejected', 'link_message' => $data['reason'] ?? 'Rejected', 'link_synced_at' => now()])->save();

            return;
        }

        $leg->forceFill([
            'link_status' => 'trip_created',
            'link_trip_number' => $data['trip_number'] ?? $leg->link_trip_number,
            'link_trip_status' => $data['trip_status'] ?? $leg->link_trip_status,
            'link_message' => trim(implode(' · ', array_filter([$data['horse'] ?? null, $data['driver'] ?? null]))) ?: null,
            'link_synced_at' => now(),
        ])->save();

        // Same mapping as an own-fleet trip; never moves a cancelled leg.
        $stage = ShipmentLegService::legStageForTripStatus($data['trip_status'] ?? null);
        if ($stage && $stage !== $leg->status && $leg->status !== 'cancelled') {
            $milestone = [];
            if ($stage === 'completed' && !empty($data['end_date'])) {
                try {
                    $milestone['actual_at'] = Carbon::parse($data['end_date']);
                } catch (Throwable $e) {
                    // keep now()
                }
            }
            $this->legs->transitionStatus($leg, $stage, $milestone);
        }
    }
}
