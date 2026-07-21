<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Generic link between a local Gonyeti record and its Sage record.
 *
 * Statuses: not_synced | pending | synced | failed | requires_attention.
 * The external_id (Sage CLASSID/PROJECTID) drives idempotency — present means
 * "update", absent means "create".
 */
class IntegrationMapping extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'last_synced_at'    => 'datetime',
        'last_attempted_at' => 'datetime',
    ];

    public const STATUS_NOT_SYNCED         = 'not_synced';
    public const STATUS_PENDING            = 'pending';
    public const STATUS_SYNCED             = 'synced';
    public const STATUS_FAILED             = 'failed';
    public const STATUS_REQUIRES_ATTENTION = 'requires_attention';

    public function company_integration()
    {
        return $this->belongsTo(CompanyIntegration::class);
    }

    /** Find the mapping row for a given entity type + local id. */
    public function scopeFor($query, string $entityType, int $localId)
    {
        return $query->where('entity_type', $entityType)->where('local_id', $localId);
    }

    /** True if the record already exists in Sage (⇒ update, never create). */
    public function existsInSage(): bool
    {
        return ! empty($this->external_id);
    }

    // ─────────────────────────────────────────────────────────────
    // STATE HELPERS  (payloads are redacted upstream — never credentials)
    // ─────────────────────────────────────────────────────────────

    public function markSynced(string $externalId, ?string $externalReference = null, ?string $request = null, ?string $response = null): void
    {
        $this->forceFill([
            'external_id'        => $externalId,
            'external_reference' => $externalReference ?? $this->external_reference,
            'sync_status'        => self::STATUS_SYNCED,
            'last_synced_at'     => now(),
            'last_attempted_at'  => now(),
            'last_error'         => null,
            'request_payload'    => $request,
            'response_payload'   => $response,
        ])->save();
    }

    public function markFailed(string $error, ?string $request = null, ?string $response = null): void
    {
        $this->forceFill([
            'sync_status'       => self::STATUS_FAILED,
            'last_attempted_at' => now(),
            'last_error'        => mb_substr($error, 0, 2000),
            'request_payload'   => $request,
            'response_payload'  => $response,
        ])->save();
    }

    public function markRequiresAttention(string $error, ?string $request = null, ?string $response = null): void
    {
        $this->forceFill([
            'sync_status'       => self::STATUS_REQUIRES_ATTENTION,
            'last_attempted_at' => now(),
            'last_error'        => mb_substr($error, 0, 2000),
            'request_payload'   => $request,
            'response_payload'  => $response,
        ])->save();
    }
}
