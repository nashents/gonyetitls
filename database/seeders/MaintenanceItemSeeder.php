<?php

namespace Database\Seeders;

use App\Models\MaintenanceItem;
use Illuminate\Database\Seeder;

/**
 * Standard tracked items from the workshop's Maintenance Planner workbook
 * (most common interval per item). Not part of DatabaseSeeder: loaded on
 * demand from Maintenance Schedules > Tracked Items > Load Standard Items.
 * Idempotent - an item that already exists by name is left as it is.
 */
class MaintenanceItemSeeder extends Seeder
{
    /** [name, unit, interval, applies_to, category, monitoring] */
    public const ITEMS = [
        ['SERVICE INTERVAL', 'hours', 250, 'horse,vehicle', 'Service', false],
        ['TOP END ADJUSTMENT', 'hours', 3000, 'horse,vehicle', 'Engine', false],
        ['THERMOSTATS', 'hours', 15000, 'horse,vehicle', 'Cooling', false],
        ['COOLANT', 'hours', 4000, 'horse,vehicle', 'Cooling', false],
        ['WATER PUMP', 'hours', 10000, 'horse,vehicle', 'Cooling', false],
        ['HYDRAULIC OIL CHANGE', 'hours', 2000, 'horse,vehicle', 'Hydraulics', false],
        ['ENGINE MOUNTINGS FRONT', 'hours', 25000, 'horse,vehicle', 'Engine', false],
        ['ENGINE MOUNTINGS REAR', 'hours', 18000, 'horse,vehicle', 'Engine', false],
        ['IDLER PULLEYS', 'hours', 10000, 'horse,vehicle', 'Engine', false],
        ['COMPRESSOR', 'hours', 12000, 'horse,vehicle', 'Air System', false],
        ['AIR DRIER', 'hours', 1000, 'horse,vehicle', 'Air System', false],
        ['DECOKE COMPRESSOR AIR PIPES', 'hours', 15000, 'horse,vehicle', 'Air System', false],
        ['CLUTCH', 'hours', 20000, 'horse,vehicle', 'Drivetrain', false],
        ['CLUTCH CYLINDER', 'hours', 10000, 'horse,vehicle', 'Drivetrain', false],
        ['CYLINDER HEAD', 'hours', 20000, 'horse,vehicle', 'Engine', false],
        ['GEARBOX REBUILD', 'hours', 20000, 'horse,vehicle', 'Drivetrain', false],
        ['TURBO', 'hours', 10000, 'horse,vehicle', 'Engine', false],
        ['EXHAUST MANIFOLD', 'hours', 12000, 'horse,vehicle', 'Engine', false],
        ['ALTERNATOR', 'hours', 10000, 'horse,vehicle', 'Electrical', false],
        ['ALTERNATOR BELT TENSIONER', 'hours', 15000, 'horse,vehicle', 'Electrical', false],
        ['ALTERNATOR BELTS', 'hours', 10000, 'horse,vehicle', 'Electrical', false],
        ['STARTER MOTOR', 'hours', 10000, 'horse,vehicle', 'Electrical', false],
        ['BASEPLATE', 'hours', 6000, 'horse,vehicle', 'Chassis', true],
        ['CAC HOSE', 'hours', 10000, 'horse,vehicle', 'Engine', false],
        ['FRONT RIGHT HUB BEARING', 'hours', 20000, 'horse,vehicle', 'Axles & Bearings', false],
        ['FRONT LEFT HUB BEARING', 'hours', 20000, 'horse,vehicle', 'Axles & Bearings', false],
        ['HELPER RIGHT HUB BEARING', 'hours', 40000, 'horse,vehicle', 'Axles & Bearings', false],
        ['HELPER LEFT HUB BEARING', 'hours', 40000, 'horse,vehicle', 'Axles & Bearings', false],
        ['FRONT RIGHT HUB DIFF BEARING', 'hours', 20000, 'horse,vehicle', 'Axles & Bearings', false],
        ['FRONT LEFT HUB DIFF BEARING', 'hours', 20000, 'horse,vehicle', 'Axles & Bearings', false],
        ['REAR RIGHT HUB DIFF BEARING', 'hours', 20000, 'horse,vehicle', 'Axles & Bearings', false],
        ['REAR LEFT HUB DIFF BEARING', 'hours', 20000, 'horse,vehicle', 'Axles & Bearings', false],
        ["PROPSHAFT FRONT 'U' JOINTS", 'hours', 25000, 'horse,vehicle', 'Drivetrain', false],
        ["PROPSHAFT REAR 'U' JOINTS", 'hours', 25000, 'horse,vehicle', 'Drivetrain', false],
        ['FAN HUB', 'hours', 15000, 'horse,vehicle', 'Cooling', false],
        ['FAN ASSEMBLY', 'hours', 15000, 'horse,vehicle', 'Cooling', false],
        ['FAN BELTS', 'hours', 10000, 'horse,vehicle', 'Cooling', false],
        ['FAN TENSIONER', 'hours', 15000, 'horse,vehicle', 'Cooling', false],
        ['FANHUB IDLER PULLEY BEARINGS', 'hours', 10000, 'horse,vehicle', 'Cooling', false],
        ['FRONT DIFF', 'hours', 30000, 'horse,vehicle', 'Drivetrain', true],
        ['MID DIFF', 'hours', 30000, 'horse,vehicle', 'Drivetrain', true],
        ['REAR DIFF', 'hours', 30000, 'horse,vehicle', 'Drivetrain', true],
        ['GEARBOX', 'hours', 30000, 'horse,vehicle', 'Drivetrain', true],
        ['GEARBOX OUTPUT BEARING', 'hours', 15000, 'horse,vehicle', 'Drivetrain', false],
        ['GEARBOX COOLER HOSE', 'hours', 30000, 'horse,vehicle', 'Drivetrain', false],
        ['GEARBOX TOWER RESEALING', 'hours', 15000, 'horse,vehicle', 'Drivetrain', false],
        ['COOLANT PIPES', 'hours', 18000, 'horse,vehicle', 'Cooling', false],
        ['NYLON PIPES', 'hours', 15000, 'horse,vehicle', 'Air System', false],
        ['STEERING CONTROL VALVE', 'hours', 25000, 'horse,vehicle', 'Steering', false],
        ['TRAILER CONTROL VALVE', 'hours', 18000, 'horse,vehicle', 'Air System', false],
        // Regreasing & Bearing Change sheet (trailers, by date)
        ['WHEEL BEARING REGREASE', 'days', 245, 'trailer', 'Axles & Bearings', false],
        ['WHEEL BEARINGS CHANGE', 'days', 1430, 'trailer', 'Axles & Bearings', false],
        ['WEEKLY GREASING & TYRE PRESSURE CHECK', 'days', 7, 'trailer', 'Axles & Bearings', false],
    ];

    public function run(): int
    {
        $created = 0;

        foreach (self::ITEMS as [$name, $unit, $interval, $appliesTo, $category, $monitoring]) {
            $item = MaintenanceItem::withTrashed()->firstOrCreate(['name' => $name], [
                'interval_unit' => $unit,
                "interval_{$unit}" => $interval,
                'applies_to' => $appliesTo,
                'category' => $category,
                'is_monitoring' => $monitoring,
                'status' => true,
            ]);

            if ($item->wasRecentlyCreated) {
                $created++;
            }
        }

        return $created;
    }
}
