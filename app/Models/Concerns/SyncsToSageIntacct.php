<?php

namespace App\Models\Concerns;

/**
 * Shared Sage Intacct sync-state behaviour for Customer and Vendor.
 *
 * Adds the four sage_* columns and small helpers so the sync service and the
 * Livewire components stay thin. Apply with `use SyncsToSageIntacct;` and make
 * sure the four columns are present in the model's $fillable.
 */
trait SyncsToSageIntacct
{
    /**
     * Cast the synced-at timestamp. Merged into the model's existing casts.
     */
    public function initializeSyncsToSageIntacct(): void
    {
        $this->casts['sage_last_synced_at'] = 'datetime';
    }

    // ─────────────────────────────────────────────────────────────
    // SCOPES
    // ─────────────────────────────────────────────────────────────

    /** Records whose last sync attempt failed (retry candidates). */
    public function scopeSageFailed($query)
    {
        return $query->where('sage_sync_status', 'failed');
    }

    /** Records never successfully pushed to Sage. */
    public function scopeSageUnsynced($query)
    {
        return $query->whereNull('sage_intacct_id');
    }

    // ─────────────────────────────────────────────────────────────
    // STATE HELPERS
    // ─────────────────────────────────────────────────────────────

    /**
     * The Sage Intacct record id for this model, if known.
     *
     * Records imported from Sage carry their id in `custom_ref`; brand-new
     * records get it in `sage_intacct_id` after a successful create. Either
     * being present means the record already exists in Sage — never re-create.
     */
    public function sageId(): ?string
    {
        return $this->sage_intacct_id ?: $this->custom_ref ?: null;
    }

    /** True if the record already exists in Sage (must update, not create). */
    public function existsInSage(): bool
    {
        return ! empty($this->sageId());
    }

    /**
     * Mark this record as successfully synced and store the Sage id.
     * The id is mirrored into custom_ref to keep the import convention symmetric.
     */
    public function markSageSynced(string $sageId): void
    {
        $this->forceFill([
            'sage_intacct_id'     => $sageId,
            // ADJUST: remove this line if you prefer custom_ref to remain the
            // import-only source and sage_intacct_id to be the canonical field.
            'custom_ref'          => $this->custom_ref ?: $sageId,
            'sage_sync_status'    => 'synced',
            'sage_last_synced_at' => now(),
            'sage_sync_error'     => null,
        ])->saveQuietly();
    }

    /**
     * Mark this record as failed with a readable, secret-free error message.
     */
    public function markSageFailed(string $error): void
    {
        $this->forceFill([
            'sage_sync_status' => 'failed',
            'sage_sync_error'  => mb_substr($error, 0, 2000),
        ])->saveQuietly();
    }
}
