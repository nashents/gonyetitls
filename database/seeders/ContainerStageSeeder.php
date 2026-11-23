<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * The system container lifecycle stages (spec section 23). Their codes are
 * fixed: PortExposureService::START_MAP / STOP_MAP and existing container
 * statuses / milestones refer to them. Idempotent - re-running only adds
 * missing stages and never overwrites a name/description edited in
 * Freight > Master > Container Stages.
 */
class ContainerStageSeeder extends Seeder
{
    public const STAGES = [
        'booked' => ['Booked', 'Container booked with the shipping line and a booking reference issued. Recorded automatically when the container is added to a shipment.'],
        'empty_released' => ['Empty Released', 'The shipping line has released the empty container to the shipper or haulier for packing (exports).'],
        'stuffed' => ['Stuffed / Loaded', 'Cargo has been packed into the container and the container sealed.'],
        'gate_in' => ['Gate-In', 'The packed container has been delivered through the port or terminal gate, ready for loading.'],
        'vessel_loaded' => ['Vessel Loaded', 'The container has been loaded onto the vessel at the port of loading.'],
        'discharged' => ['Discharged', 'The container has been offloaded from the vessel at the port of discharge and is now sitting at the port.'],
        'customs_cleared' => ['Customs Cleared', 'Customs has released the cargo (declaration accepted, duties and taxes settled).'],
        'port_released' => ['Port Released', 'The shipping line and terminal have released the container for collection (delivery order issued, port charges settled).'],
        'collected_from_port' => ['Collected from Port', 'The container has been picked up from the port or terminal by the haulier.'],
        'customer_delivery' => ['Customer Delivery', 'The container has been delivered to the consignee\'s premises for unpacking.'],
        'empty_returned' => ['Empty Returned', 'The empty container has been returned to the shipping line\'s depot.'],
    ];

    public function run()
    {
        $order = 10;

        foreach (self::STAGES as $code => [$name, $description]) {
            if (!DB::table('container_stages')->where('code', $code)->exists()) {
                DB::table('container_stages')->insert([
                    'code' => $code,
                    'name' => $name,
                    'description' => $description,
                    'sort_order' => $order,
                    'is_system' => true,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $order += 10;
        }
    }
}
