<?php

namespace App\Services\DemoData;

use App\Models\DemoDataBatch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Loads demo data on a live instance and rolls it back exactly.
 *
 * Load (record): every Eloquent row created inside the callback is written to
 * demo_data_records. While loading, outgoing email / notifications are
 * captured (nothing is sent) and $suppressExternal tells integrations (e.g.
 * the Gonyeti Instance Link leg observer) to stay quiet.
 *
 * Rollback (plan + rollback): deletes the recorded rows plus anything users
 * added to them afterwards in the module's own tables (e.g. a cost line added
 * to a demo job). It refuses - listing why - when data outside the module
 * points at demo rows or a demo row points at real finance records (an
 * invoice / bill / trip raised from demo data must be dealt with first).
 * Master rows the demo created (customers, vendors, destinations...) are kept
 * if anything real uses them. Rows are hard-deleted with raw queries, so no
 * observers fire (no ledger reversals, no integration messages).
 */
class DemoDataRecorder
{
    /** True while a demo batch is loading: integrations must not send anything. */
    public static bool $suppressExternal = false;

    /** Rows created during the current load: [table, id, class]. */
    private static ?array $capturing = null;

    private static bool $listening = false;

    /** Columns that point at finance / operational records outside the demo. */
    private const OUTBOUND_FINANCE_COLUMNS = [
        'invoice_id' => 'invoices', 'bill_id' => 'bills', 'trip_id' => 'trips',
        'journal_entry_id' => 'journal_entries', 'payment_id' => 'payments',
        'receipt_id' => 'receipts', 'purchase_id' => 'purchases', 'credit_note_id' => 'credit_notes',
        'debit_note_id' => 'debit_notes',
    ];

    /**
     * @param array $config ['cascade_prefixes' => [...], 'master_tables' => [...], 'aliases' => [table => [cols]], 'polymorphic' => [...]]
     */
    public function __construct(private array $config = [])
    {
    }

    // ------------------------------------------------------------------ load

    public function record(DemoDataBatch $batch, callable $callback): array
    {
        if (self::$capturing !== null) {
            throw new RuntimeException('A demo load is already running.');
        }

        $this->listen();

        $originalMail = Mail::getFacadeRoot();
        $originalNotifications = Notification::getFacadeRoot();
        Mail::fake();
        Notification::fake();
        self::$suppressExternal = true;
        self::$capturing = [];

        try {
            return DB::transaction(function () use ($batch, $callback) {
                $callback();

                $rows = self::$capturing;
                $now = now();
                foreach (array_chunk($rows, 500) as $chunk) {
                    DB::table('demo_data_records')->insert(array_map(fn ($r) => [
                        'demo_data_batch_id' => $batch->id,
                        'table_name' => $r[0],
                        'record_id' => $r[1],
                        'model_type' => $r[2],
                        'created_at' => $now,
                    ], $chunk));
                }

                $summary = collect($rows)->countBy(fn ($r) => $r[0])->sortKeys()->all();
                $batch->forceFill(['summary' => $summary])->save();

                return $summary;
            });
        } finally {
            self::$capturing = null;
            self::$suppressExternal = false;
            Mail::swap($originalMail);
            Notification::swap($originalNotifications);
        }
    }

    private function listen(): void
    {
        if (self::$listening) {
            return;
        }
        self::$listening = true;

        Event::listen('eloquent.created: *', function ($event, $payload) {
            if (self::$capturing === null) {
                return;
            }
            $model = $payload[0] ?? null;
            if ($model instanceof Model && $model->getTable() !== 'demo_data_batches' && is_numeric($model->getKey())) {
                self::$capturing[] = [$model->getTable(), (int) $model->getKey(), get_class($model)];
            }
        });
    }

    // -------------------------------------------------------------- rollback

    /**
     * What a rollback would do: ['delete' => [table => ids], 'keep' => [table => ids],
     * 'blockers' => [message, ...]]. Nothing is changed.
     */
    public function plan(DemoDataBatch $batch): array
    {
        $records = DB::table('demo_data_records')->where('demo_data_batch_id', $batch->id)->orderBy('id')->get();

        $masters = array_flip($this->config['master_tables'] ?? []);
        $delete = [];   // table => [id => true]
        $masterRows = [];
        foreach ($records as $r) {
            if (!$this->tableExists($r->table_name)) {
                continue;
            }
            if (isset($masters[$r->table_name])) {
                $masterRows[$r->table_name][$r->record_id] = true;
            } else {
                $delete[$r->table_name][$r->record_id] = true;
            }
        }

        // Only rows that still exist.
        $delete = $this->existing($delete);
        $masterRows = $this->existing($masterRows);

        $blockers = [];

        // Rows users added afterwards that hang off demo rows: module tables
        // are swept in; anything else blocks the rollback.
        for ($pass = 0; $pass < 6; $pass++) {
            $added = 0;
            foreach ($this->referencesTo($delete) as [$table, $column, $ids]) {
                if ($this->cascades($table)) {
                    foreach ($ids as $id) {
                        if (!isset($delete[$table][$id])) {
                            $delete[$table][$id] = true;
                            $added++;
                        }
                    }
                } else {
                    $blockers[$table . '.' . $column] = $this->blockerMessage($table, $column, $ids);
                }
            }
            if (!$added) {
                break;
            }
        }

        // Demo rows that point at real finance records (e.g. a demo charge invoiced).
        foreach ($delete as $table => $ids) {
            foreach (self::OUTBOUND_FINANCE_COLUMNS as $column => $target) {
                if ($table === $target || !$this->hasColumn($table, $column)) {
                    continue;
                }
                $targets = DB::table($table)->whereIn('id', array_keys($ids))->whereNotNull($column)->pluck($column)->unique()->all();
                $outside = array_values(array_filter($targets, fn ($id) => !isset($delete[$target][$id])));
                if ($outside) {
                    $blockers[$table . '->' . $column] = sprintf(
                        '%d demo %s are linked to real %s (id %s). Reverse or delete those first (e.g. credit the invoice / delete the bill or trip).',
                        count($outside), str_replace('_', ' ', $table), str_replace('_', ' ', $target), implode(', ', array_slice($outside, 0, 10))
                    );
                }
            }
        }

        // Master rows: delete unless something outside the delete set uses
        // them. Repeated, so demo masters that only use each other (a demo
        // shipping line's auto-created vendor) go together.
        $pending = $masterRows;
        for ($pass = 0; $pass < 6 && $pending; $pass++) {
            $freed = 0;
            foreach ($pending as $table => $ids) {
                // A master used by another still-pending demo master stays
                // pending until that one is freed on a later pass.
                $used = [];
                foreach ($this->referencesTo([$table => $ids], $delete) as [, , , $targetIds]) {
                    foreach ($targetIds as $tid) {
                        $used[$tid] = true;
                    }
                }
                foreach (array_keys($ids) as $id) {
                    if (!isset($used[$id])) {
                        $delete[$table][$id] = true;
                        unset($pending[$table][$id]);
                        $freed++;
                    }
                }
                if (empty($pending[$table])) {
                    unset($pending[$table]);
                }
            }
            if (!$freed) {
                break;
            }
        }
        $keep = array_map(fn ($ids) => array_keys($ids), $pending);

        return [
            'delete' => array_map(fn ($ids) => array_keys($ids), $delete),
            'keep' => $keep,
            'blockers' => array_values($blockers),
        ];
    }

    /** Hard-deletes the planned rows. Throws (nothing deleted) when blocked. */
    public function rollback(DemoDataBatch $batch, ?int $userId = null): array
    {
        $plan = $this->plan($batch);
        if ($plan['blockers']) {
            throw new DemoDataRollbackBlocked($plan['blockers']);
        }

        $order = $this->deleteOrder($batch, $plan['delete']);

        DB::transaction(function () use ($batch, $plan, $order, $userId) {
            $this->deleteAudits($plan['delete']);
            $this->deletePivots($plan['delete']);

            foreach ($order as [$table, $ids]) {
                foreach (array_chunk($ids, 500) as $chunk) {
                    DB::table($table)->whereIn('id', $chunk)->delete();
                }
            }

            $batch->forceFill([
                'status' => 'rolled_back',
                'rolled_back_at' => now(),
                'rolled_back_by' => $userId,
                'rollback_summary' => [
                    'deleted' => array_map('count', $plan['delete']),
                    'kept' => array_map('count', $plan['keep']),
                ],
            ])->save();
        });

        return $plan;
    }

    /**
     * Children before parents: rows added after the load first, then the
     * recorded rows newest-first, master rows last.
     */
    private function deleteOrder(DemoDataBatch $batch, array $delete): array
    {
        $recorded = DB::table('demo_data_records')->where('demo_data_batch_id', $batch->id)->orderByDesc('id')->get(['table_name', 'record_id']);
        $masters = array_flip($this->config['master_tables'] ?? []);

        $remaining = array_map(fn ($ids) => array_fill_keys($ids, true), $delete);
        $later = [];
        $recordedSet = [];
        foreach ($recorded as $r) {
            $recordedSet[$r->table_name][$r->record_id] = true;
        }
        foreach ($remaining as $table => $ids) {
            $extra = array_keys(array_diff_key($ids, $recordedSet[$table] ?? []));
            if ($extra) {
                $later[] = [$table, $extra];
            }
        }

        $order = $later;
        $masterSteps = [];
        foreach ($recorded as $r) {
            if (!isset($remaining[$r->table_name][$r->record_id])) {
                continue;
            }
            $step = [$r->table_name, [$r->record_id]];
            if (isset($masters[$r->table_name])) {
                $masterSteps[] = $step;
            } else {
                $order[] = $step;
            }
        }

        return array_merge($order, $masterSteps);
    }

    private function deleteAudits(array $delete): void
    {
        if (!$this->tableExists('audits')) {
            return;
        }

        $classesByTable = [];
        foreach (DB::table('audits')->distinct()->pluck('auditable_type') as $class) {
            try {
                if (class_exists($class) && is_subclass_of($class, Model::class)) {
                    $classesByTable[(new $class)->getTable()][] = $class;
                }
            } catch (\Throwable $e) {
                // ignore unknown types
            }
        }

        foreach ($delete as $table => $ids) {
            if ($table === 'audits' || empty($classesByTable[$table])) {
                continue;
            }
            foreach (array_chunk($ids, 500) as $chunk) {
                DB::table('audits')->whereIn('auditable_type', $classesByTable[$table])->whereIn('auditable_id', $chunk)->delete();
            }
        }
    }

    // --------------------------------------------------------------- helpers

    /**
     * Rows in other tables pointing at $targets ([table => [id => true]]),
     * excluding rows already in $exclude. Yields [table, column, ids, targetIds].
     */
    private function referencesTo(array $targets, ?array $exclude = null): array
    {
        $exclude = $exclude ?? $targets;
        $found = [];

        foreach ($targets as $targetTable => $ids) {
            if (!$ids) {
                continue;
            }
            $targetIds = array_keys($ids);

            foreach ($this->referencingColumns($targetTable) as [$table, $column, $typeColumn, $typeValue]) {
                if (in_array($table, ['demo_data_records', 'demo_data_batches', 'audits'], true)) {
                    continue;
                }
                $query = DB::table($table)->whereIn($column, $targetIds);
                if ($typeColumn) {
                    $query->where($typeColumn, $typeValue);
                }
                if ($this->hasColumn($table, 'id')) {
                    $rows = $query->get(['id', $column]);
                    $rows = $rows->reject(fn ($row) => isset($exclude[$table][$row->id]));
                    if ($rows->isNotEmpty()) {
                        $found[] = [$table, $column, $rows->pluck('id')->map(fn ($i) => (int) $i)->all(), $rows->pluck($column)->unique()->values()->all()];
                    }
                } elseif (!$this->cascades($table) && $query->exists()) {
                    // id-less pivot outside the module: blocks (module pivots go in deletePivots)
                    $found[] = [$table, $column, [], $query->pluck($column)->unique()->values()->all()];
                }
            }
        }

        return $found;
    }

    /** Id-less pivot rows in module tables pointing at deleted rows (e.g. container <-> cargo links). */
    private function deletePivots(array $delete): void
    {
        foreach ($delete as $targetTable => $ids) {
            foreach ($this->referencingColumns($targetTable) as [$table, $column, $typeColumn, $typeValue]) {
                if ($this->hasColumn($table, 'id') || !$this->cascades($table)) {
                    continue;
                }
                foreach (array_chunk($ids, 500) as $chunk) {
                    DB::table($table)->whereIn($column, $chunk)
                        ->when($typeColumn, fn ($q) => $q->where($typeColumn, $typeValue))
                        ->delete();
                }
            }
        }
    }

    /** Keeps only ids that still exist: [table => [id => true]]. */
    private function existing(array $set): array
    {
        $out = [];
        foreach ($set as $table => $ids) {
            $found = DB::table($table)->whereIn('id', array_keys($ids))->pluck('id')->all();
            if ($found) {
                $out[$table] = array_fill_keys(array_map('intval', $found), true);
            }
        }

        return $out;
    }

    /** [table, column, typeColumn|null, typeValue|null] that can hold an id of $targetTable. */
    private function referencingColumns(string $targetTable): array
    {
        static $columns = null;
        if ($columns === null) {
            $columns = collect(DB::select(
                'select table_name as t, column_name as c from information_schema.columns where table_schema = ?',
                [DB::getDatabaseName()]
            ))->groupBy('c')->map(fn ($rows) => $rows->pluck('t')->all())->all();
        }

        $names = array_unique(array_merge(
            [Str::singular($targetTable) . '_id'],
            $this->config['aliases'][$targetTable] ?? []
        ));

        $out = [];
        foreach ($names as $name) {
            foreach ($columns[$name] ?? [] as $table) {
                if ($table !== $targetTable || $name !== 'id') {
                    $out[] = [$table, $name, null, null];
                }
            }
        }

        foreach ($this->config['polymorphic'][$targetTable] ?? [] as [$table, $idColumn, $typeColumn, $typeValue]) {
            if ($this->tableExists($table)) {
                $out[] = [$table, $idColumn, $typeColumn, $typeValue];
            }
        }

        return $out;
    }

    private function cascades(string $table): bool
    {
        foreach ($this->config['cascade_prefixes'] ?? [] as $prefix) {
            if (Str::startsWith($table, $prefix)) {
                return true;
            }
        }

        return in_array($table, $this->config['cascade_tables'] ?? [], true);
    }

    private function blockerMessage(string $table, string $column, array $ids): string
    {
        $label = str_replace('_', ' ', $table);

        return sprintf(
            '%d %s record(s) outside the demo use demo data (%s%s). Delete or change them first, or they would be left pointing at nothing.',
            max(count($ids), 1), $label, $column, $ids ? ', id ' . implode(', ', array_slice($ids, 0, 10)) : ''
        );
    }

    private function tableExists(string $table): bool
    {
        static $tables = null;
        $tables ??= array_flip(array_map('strtolower', collect(DB::select('select table_name as t from information_schema.tables where table_schema = ?', [DB::getDatabaseName()]))->pluck('t')->all()));

        return isset($tables[strtolower($table)]);
    }

    private function hasColumn(string $table, string $column): bool
    {
        static $cache = [];

        return $cache[$table . '.' . $column] ??= \Illuminate\Support\Facades\Schema::hasColumn($table, $column);
    }
}
