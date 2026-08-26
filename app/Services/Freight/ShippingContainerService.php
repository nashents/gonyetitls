<?php

namespace App\Services\Freight;

use App\Models\ShippingContainer;
use Illuminate\Support\Facades\DB;

class ShippingContainerService
{
    public function __construct(
        private ShipmentMilestoneService $milestones,
        private PortExposureService $exposures
    ) {
    }

    /**
     * $cargoLinks: list of ['shipment_cargo_id' => int, 'quantity' => ?int, 'weight' => ?float, 'notes' => ?string]
     */
    public function create(array $data, array $cargoLinks = []): ShippingContainer
    {
        return DB::transaction(function () use ($data, $cargoLinks) {
            $data['status'] = $data['status'] ?? 'booked';
            $container = ShippingContainer::create($data);

            foreach ($cargoLinks as $link) {
                $container->cargo_items()->attach($link['shipment_cargo_id'], [
                    'quantity' => $link['quantity'] ?? null,
                    'weight' => $link['weight'] ?? null,
                    'notes' => $link['notes'] ?? null,
                ]);
            }

            $this->milestones->recordAndComplete([
                'shipment_id' => $container->shipment_id,
                'shipping_container_id' => $container->id,
                'milestone_code' => 'booked',
                'milestone_name' => ShippingContainer::LIFECYCLE_STAGES['booked'],
            ]);

            return $container;
        });
    }

    public function transitionStatus(ShippingContainer $container, string $milestoneCode, array $milestoneData = []): ShippingContainer
    {
        return DB::transaction(function () use ($container, $milestoneCode, $milestoneData) {
            $container->status = $milestoneCode;
            $container->save();

            $this->milestones->recordAndComplete(array_merge([
                'shipment_id' => $container->shipment_id,
                'shipping_container_id' => $container->id,
                'milestone_code' => $milestoneCode,
                'milestone_name' => ShippingContainer::LIFECYCLE_STAGES[$milestoneCode] ?? ucwords(str_replace('_', ' ', $milestoneCode)),
            ], $milestoneData));

            $this->exposures->handleContainerTransition($container, $milestoneCode, $milestoneData['actual_at'] ?? now());

            return $container;
        });
    }
}
