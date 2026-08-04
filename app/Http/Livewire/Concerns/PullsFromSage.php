<?php

namespace App\Http\Livewire\Concerns;

use App\Jobs\Sage\PullFromSageJob;

/**
 * Shared "Pull from Sage" dispatch for the entity index components. The host
 * component provides the `sageEnabled` computed property (the gate).
 */
trait PullsFromSage
{
    protected function dispatchSagePull(string $entity, string $label, array $options = []): void
    {
        if (! $this->sageEnabled) {
            return;
        }

        $user      = auth()->user();
        $companyId  = optional(optional($user)->employee)->company_id
            ?? optional($user)->company_id
            ?? optional(optional($user)->company)->id;

        if (! $companyId) {
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'warning',
                'message' => 'No company context for the Sage import.',
            ]);
            return;
        }

        PullFromSageJob::dispatch($entity, (int) $companyId, (int) $user->id, $options);

        $this->dispatchBrowserEvent('alert', [
            'type'    => 'success',
            'message' => ucfirst($label) . ' import from Sage queued — records will appear shortly.',
        ]);
    }
}
