<?php

namespace App\Http\Livewire\Accounts;

use App\Jobs\BulkCatchUpPaymentsJob;
use App\Services\Accounting\BulkCatchUpService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BulkCatchUp extends Component
{
    public $from;
    public $until;
    public $types = ['invoices', 'bills'];
    public $confirmed = false;

    public $preview;
    public $previewedFrom;
    public $previewedUntil;
    public $previewedTypes;

    public function mount()
    {
        abort_unless($this->userMayRun(), 403);
    }

    protected function userMayRun(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        $roleNames = $user->roles->pluck('name')->all();
        if (in_array('Super Admin', $roleNames)) {
            return true;
        }

        $departmentNames = $user->employee?->departments?->pluck('name')->all() ?? [];
        return in_array('Admin', $roleNames) && in_array('Finance', $departmentNames);
    }

    public function runPreview()
    {
        $this->validate([
            'until' => 'required|date',
            'from'  => 'nullable|date|before_or_equal:until',
            'types' => 'required|array|min:1',
        ]);

        $params = [
            'from'  => $this->from,
            'until' => $this->until,
            'types' => $this->types,
        ];

        $this->preview = app(BulkCatchUpService::class)->preview($params);

        $this->previewedFrom = $this->from;
        $this->previewedUntil = $this->until;
        $this->previewedTypes = $this->types;
        $this->confirmed = false;
    }

    public function showConfirm()
    {
        $this->dispatchBrowserEvent('show-bulkCatchUpConfirmModal');
    }

    public function run()
    {
        if (!$this->matchesPreviewedRange()) {
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'error',
                'message' => 'The range or scope has changed since the preview — please preview again before running.',
            ]);
            return;
        }

        if (!$this->confirmed) {
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'error',
                'message' => 'Please confirm you understand this action before running it.',
            ]);
            return;
        }

        BulkCatchUpPaymentsJob::dispatch([
            'from'  => $this->from,
            'until' => $this->until,
            'types' => $this->types,
        ], Auth::id());

        $this->dispatchBrowserEvent('hide-bulkCatchUpConfirmModal');
        $this->dispatchBrowserEvent('alert', [
            'type'    => 'success',
            'message' => 'Bulk catch-up started in the background. You will be notified when it finishes.',
        ]);

        $this->reset(['preview', 'previewedFrom', 'previewedUntil', 'previewedTypes', 'confirmed']);
    }

    protected function matchesPreviewedRange(): bool
    {
        if (!$this->preview) {
            return false;
        }
        if ($this->previewedFrom !== $this->from || $this->previewedUntil !== $this->until) {
            return false;
        }

        $current = $this->types;
        $previewed = $this->previewedTypes;
        sort($current);
        sort($previewed);

        return $current === $previewed;
    }

    public function render()
    {
        return view('livewire.accounts.bulk-catch-up');
    }
}
