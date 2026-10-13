<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\FreightServiceType;

return new class extends Migration
{
    private array $types = [
        ['name' => 'Freight Forwarding', 'description' => 'End-to-end management of a shipment\'s transport, without handling customs clearance.'],
        ['name' => 'Customs Clearing', 'description' => 'Customs declaration and clearance services only, no transport arranged.'],
        ['name' => 'Freight Forwarding & Customs Clearing', 'description' => 'Combined transport management and customs clearance for a shipment.'],
        ['name' => 'Door-to-Door', 'description' => 'Full door-to-door service from the shipper\'s premises to the consignee\'s premises.'],
        ['name' => 'Port-to-Port', 'description' => 'Transport arranged between origin and destination ports/terminals only.'],
        ['name' => 'Consolidation (LCL/Groupage)', 'description' => 'Grouping multiple customers\' cargo into shared containers or loads.'],
        ['name' => 'Project Cargo / Breakbulk', 'description' => 'Oversized, heavy-lift, or non-containerized cargo requiring special handling.'],
        ['name' => 'Warehousing & Distribution', 'description' => 'Storage, cross-docking, and local distribution of goods.'],
        ['name' => 'Cross-Border Transport', 'description' => 'Overland transport across international borders.'],
        ['name' => 'Freight Brokerage', 'description' => 'Arranging carriers/transporters on behalf of the customer without providing own transport.'],
        ['name' => 'Courier / Express Parcel', 'description' => 'Time-critical, small-parcel express delivery services.'],
    ];

    public function up(): void
    {
        foreach ($this->types as $type) {
            FreightServiceType::firstOrCreate(
                ['name' => $type['name']],
                ['description' => $type['description'], 'is_locked' => false]
            );
        }
    }

    public function down(): void
    {
        FreightServiceType::whereIn('name', array_column($this->types, 'name'))->delete();
    }
};
