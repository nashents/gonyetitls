<?php

namespace App\Models;

use Database\Seeders\ContainerStageSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Container lifecycle stages (Freight > Master > Container Stages), in
 * sort_order. `code` is what shipping_containers.status, container
 * milestones' milestone_code and charge_types.start_/stop_milestone store.
 * System stages (ContainerStageSeeder::STAGES) keep their code and position
 * because PortExposureService::START_MAP / STOP_MAP rely on them.
 */
class ContainerStage extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['code', 'name', 'description', 'job_status', 'sort_order', 'is_system', 'is_active', 'created_by'];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    /** Per-request memo of every stage, ordered. */
    private static $all = null;

    protected static function booted()
    {
        static::saved(fn () => static::flush());
        static::deleted(fn () => static::flush());
    }

    /** Drop the memo (also needed after query-builder updates, which fire no model events). */
    public static function flush(): void
    {
        static::$all = null;
    }

    public static function allOrdered()
    {
        if (static::$all === null) {
            static::$all = static::tableExists()
                ? static::orderBy('sort_order')->orderBy('id')->get()
                : collect(ContainerStageSeeder::STAGES)->map(fn ($s, $code) => new static([
                    'code' => $code, 'name' => $s[0], 'description' => $s[1], 'is_system' => true, 'is_active' => true,
                ]))->values();
        }

        return static::$all;
    }

    /** code => name, in lifecycle order. Inactive stages only when asked (labels for old records). */
    public static function options(bool $includeInactive = false): array
    {
        return static::allOrdered()
            ->when(!$includeInactive, fn ($c) => $c->where('is_active', true))
            ->pluck('name', 'code')
            ->all();
    }

    public static function label(?string $code): ?string
    {
        if ($code === null) {
            return null;
        }

        return static::options(true)[$code] ?? ucwords(str_replace('_', ' ', $code));
    }

    /** Whether containers, milestones or charge types reference this stage. */
    public function inUse(): bool
    {
        return ShippingContainer::withTrashed()->where('status', $this->code)->exists()
            || ShipmentMilestone::whereNotNull('shipping_container_id')->where('milestone_code', $this->code)->exists()
            || ChargeType::withTrashed()->where(fn ($q) => $q->where('start_milestone', $this->code)->orWhere('stop_milestone', $this->code))->exists();
    }

    private static function tableExists(): bool
    {
        static $exists = null;

        return $exists ??= Schema::hasTable('container_stages');
    }
}
