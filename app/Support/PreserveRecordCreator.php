<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Stops an edit from re-stamping a record's author as the person who edited it.
 *
 * Many edit forms (Trips > Edit being the worst offender) used to do
 * `$model->user_id = Auth::id()` on save, so the "Created By" of a trip,
 * invoice, bill, ... silently became whoever touched it last. This listener
 * runs on every Eloquent `updating` event and, when a creator column that
 * already holds someone is being changed to the CURRENT user, puts the
 * original value back. Edits by that user are still recorded in the audit
 * trail (owen-it auditing), which is where "who changed it" belongs.
 *
 * Deliberately narrow: only a change *to the logged-in user* is reverted, so
 * any genuine reassignment to another person is untouched. Models where
 * user_id is a login link rather than an author are skipped.
 */
class PreserveRecordCreator
{
    /** Columns that hold "who created this record". */
    public const CREATOR_COLUMNS = ['user_id', 'created_by'];

    /** user_id here is the person's own login account, not an author. */
    public const SKIP_MODELS = [
        \App\Models\User::class,
        \App\Models\Employee::class,
        \App\Models\Driver::class,
    ];

    public static function handle(string $event, array $payload): void
    {
        $model = $payload[0] ?? null;

        if (! $model instanceof Model || ! $model->exists) {
            return;
        }

        if (in_array(get_class($model), self::SKIP_MODELS, true)) {
            return;
        }

        $editorId = Auth::id();
        if ($editorId === null) {
            return;
        }

        foreach (self::CREATOR_COLUMNS as $column) {
            if (! $model->isDirty($column)) {
                continue;
            }

            $original = $model->getOriginal($column);
            $new = $model->getAttribute($column);

            if ($original !== null && $original !== '' && (string) $new === (string) $editorId
                && (string) $original !== (string) $editorId) {
                $model->setAttribute($column, $original);
            }
        }
    }
}
