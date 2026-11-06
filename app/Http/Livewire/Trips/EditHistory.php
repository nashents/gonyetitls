<?php

namespace App\Http\Livewire\Trips;

use App\Models\Trip;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use OwenIt\Auditing\Models\Audit;

/**
 * Trips > View > "Edit History" tab: every audited change to the trip and to
 * its child records (loading/offloading points, expenses, documents, ...),
 * newest first, with who made it. Reads the owen-it `audits` table.
 */
class EditHistory extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public Trip $trip;

    public $perPage = 20;

    /** auditable class => label shown in the "Section" column. */
    public const SECTIONS = [
        \App\Models\Trip::class               => 'Trip',
        \App\Models\TripOrigin::class         => 'Loading Point',
        \App\Models\TripDestination::class    => 'Offloading Point',
        \App\Models\TripExpense::class        => 'Trip Expense',
        \App\Models\Commission::class         => 'Commission',
        \App\Models\TripDocument::class       => 'Trip Document',
        \App\Models\DeliveryNote::class       => 'Delivery Note',
        \App\Models\TripTransportOrder::class => 'Transport Order Link',
    ];

    /** Bookkeeping columns not worth showing. */
    protected const HIDDEN_FIELDS = ['created_at', 'updated_at', 'deleted_at', 'id', 'company_id'];

    /** *_id column => [model, display attributes tried in order]. */
    protected const LOOKUPS = [
        'user_id'             => [\App\Models\User::class, ['name', 'surname']],
        'authorized_by_id'    => [\App\Models\User::class, ['name', 'surname']],
        'customer_id'         => [\App\Models\Customer::class, ['name']],
        'transporter_id'      => [\App\Models\Transporter::class, ['name']],
        'horse_id'            => [\App\Models\Horse::class, ['registration_number']],
        'vehicle_id'          => [\App\Models\Vehicle::class, ['registration_number']],
        'currency_id'         => [\App\Models\Currency::class, ['name']],
        'cargo_id'            => [\App\Models\Cargo::class, ['name']],
        'route_id'            => [\App\Models\Route::class, ['name']],
        'destination_id'      => [\App\Models\Destination::class, ['city']],
        'loading_point_id'    => [\App\Models\LoadingPoint::class, ['name']],
        'offloading_point_id' => [\App\Models\OffloadingPoint::class, ['name']],
        'expense_id'          => [\App\Models\Expense::class, ['name']],
        'agent_id'            => [\App\Models\Agent::class, ['name']],
        'broker_id'           => [\App\Models\Broker::class, ['name']],
        'border_id'           => [\App\Models\Border::class, ['name']],
        'consignee_id'        => [\App\Models\Consignee::class, ['name']],
        'consignor_id'        => [\App\Models\Consignor::class, ['name']],
        'deal_id'             => [\App\Models\Deal::class, ['name']],
        'trip_group_id'       => [\App\Models\TripGroup::class, ['name']],
    ];

    protected $listeners = ['tripStatusUpdated' => '$refresh'];

    public function mount(Trip $trip): void
    {
        $this->trip = $trip;
    }

    public function render()
    {
        $audits = Audit::query()
            ->where(function ($q) {
                $q->where(function ($q) {
                    $q->where('auditable_type', Trip::class)
                        ->where('auditable_id', $this->trip->id);
                });

                foreach (self::SECTIONS as $class => $label) {
                    if ($class === Trip::class) {
                        continue;
                    }
                    $ids = $this->childIds($class);
                    if ($ids->isEmpty()) {
                        continue;
                    }
                    $q->orWhere(function ($q) use ($class, $ids) {
                        $q->where('auditable_type', $class)
                            ->whereIn('auditable_id', $ids);
                    });
                }
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($this->perPage, ['*'], 'historyPage');

        $users = \App\Models\User::withoutGlobalScopes()
            ->whereIn('id', $audits->getCollection()->pluck('user_id')->filter()->unique())
            ->get()
            ->keyBy('id');

        $rows = $audits->getCollection()->map(function (Audit $audit) use ($users) {
            $user = $users->get($audit->user_id);

            return [
                'id'      => $audit->id,
                'when'    => $audit->created_at,
                'user'    => $user ? trim($user->name . ' ' . $user->surname) : ($audit->user_id ? 'User #' . $audit->user_id : 'System'),
                'event'   => $audit->event,
                'section' => self::SECTIONS[$audit->auditable_type] ?? class_basename($audit->auditable_type),
                'changes' => $this->changes($audit),
            ];
        })->filter(fn ($row) => $row['event'] !== 'updated' || count($row['changes']));

        return view('livewire.trips.edit-history', [
            'audits' => $audits,
            'rows'   => $rows,
        ]);
    }

    /** IDs of this trip's child rows of $class, soft-deleted ones included. */
    protected function childIds(string $class): Collection
    {
        $query = $class::query()->where('trip_id', $this->trip->id);
        if (method_exists($class, 'bootSoftDeletes')) {
            $query->withTrashed();
        }

        return $query->pluck('id');
    }

    /** [['field' => label, 'old' => display, 'new' => display], ...] */
    protected function changes(Audit $audit): array
    {
        $old = (array) ($audit->old_values ?? []);
        $new = (array) ($audit->new_values ?? []);

        $out = [];
        foreach (array_unique(array_merge(array_keys($old), array_keys($new))) as $field) {
            if (in_array($field, self::HIDDEN_FIELDS, true)) {
                continue;
            }
            $before = $old[$field] ?? null;
            $after  = $new[$field] ?? null;

            // floats re-saved with more precision aren't real edits
            if (is_numeric($before) && is_numeric($after) && abs((float) $before - (float) $after) < 0.000001) {
                continue;
            }

            $out[] = [
                'field' => $this->label($field),
                'old'   => $this->display($field, $before),
                'new'   => $this->display($field, $after),
            ];
        }

        return $out;
    }

    protected function label(string $field): string
    {
        if ($field === 'user_id') {
            return 'Created By';
        }

        return Str::of($field)->replaceLast('_id', '')->replace('_', ' ')->title();
    }

    /** per-render memo of resolved ids */
    protected array $lookupCache = [];

    protected function display(string $field, $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }
        if (is_array($value)) {
            return json_encode($value);
        }
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        $key = $field . ':' . (is_scalar($value) ? $value : json_encode($value));
        if (array_key_exists($key, $this->lookupCache)) {
            return $this->lookupCache[$key];
        }

        return $this->lookupCache[$key] = $this->resolve($field, $value);
    }

    protected function resolve(string $field, $value): string
    {
        if ($field === 'driver_id') {
            $driver = \App\Models\Driver::withoutGlobalScopes()->with('employee')->find($value);
            $emp = $driver->employee ?? null;

            return $emp ? trim($emp->name . ' ' . $emp->surname) : (string) $value;
        }

        if (isset(self::LOOKUPS[$field])) {
            [$class, $attrs] = self::LOOKUPS[$field];
            $model = $class::withoutGlobalScopes()->find($value);
            if ($model) {
                $text = trim(collect($attrs)->map(fn ($a) => $model->{$a})->filter()->implode(' '));
                if ($text !== '') {
                    return $text;
                }
            }
        }

        if (is_numeric($value) && str_contains((string) $value, '.')) {
            return rtrim(rtrim(number_format((float) $value, 4, '.', ','), '0'), '.');
        }

        return (string) $value;
    }
}
