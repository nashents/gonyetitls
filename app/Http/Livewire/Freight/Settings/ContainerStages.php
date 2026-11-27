<?php

namespace App\Http\Livewire\Freight\Settings;

use App\Models\ChargeType;
use App\Models\ContainerStage;
use App\Models\FreightJob;
use App\Services\Freight\FreightJobStatusSync;
use App\Services\Freight\PortExposureService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use App\Http\Livewire\Freight\Concerns\FreightEmbeddedPagination;

/**
 * Freight > Master > Container Stages: the container lifecycle with an
 * explanation per stage and which free-time clocks it starts/stops.
 * System stages: name/description editable, code + position fixed.
 * Custom stages: added after any stage, can be moved, deactivated, or
 * deleted while nothing references them.
 */
class ContainerStages extends Component
{
    use FreightEmbeddedPagination;

    public $stage_id;
    public $name;
    public $description;
    public $after_code;
    public $job_status;
    public $is_active = true;
    public $editing_system = false;

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('container_stages', 'name')->ignore($this->stage_id)],
            'description' => 'nullable|string|max:1000',
            'job_status' => ['nullable', Rule::in(FreightJobStatusSync::FLOW)],
            'after_code' => [Rule::requiredIf(!$this->editing_system), 'nullable', Rule::exists('container_stages', 'code')],
        ];
    }

    protected $validationAttributes = ['after_code' => 'position', 'job_status' => 'job status'];

    public function edit($id)
    {
        $stage = ContainerStage::findOrFail($id);
        $this->stage_id = $stage->id;
        $this->name = $stage->name;
        $this->description = $stage->description;
        $this->job_status = $stage->job_status;
        $this->is_active = $stage->is_active;
        $this->editing_system = $stage->is_system;

        $stages = ContainerStage::allOrdered()->values();
        $index = $stages->search(fn ($s) => $s->id === $stage->id);
        $this->after_code = $index > 0 ? $stages[$index - 1]->code : null;

        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        $stage = $this->stage_id ? ContainerStage::findOrFail($this->stage_id) : null;

        if ($stage && $stage->is_system) {
            $stage->update(['name' => $this->name, 'description' => $this->description, 'job_status' => $this->job_status ?: null]);
            $this->resyncJobsAt($stage->code);
            $this->resetForm();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Stage saved.']);
            return;
        }

        if ($stage && $this->after_code === $stage->code) {
            $this->addError('after_code', 'A stage cannot come after itself.');
            return;
        }

        // New order with this stage placed straight after "after_code".
        $codes = ContainerStage::allOrdered()->pluck('code')
            ->reject(fn ($c) => $stage && $c === $stage->code)->values()->all();
        $code = $stage->code ?? $this->makeCode($this->name);
        array_splice($codes, array_search($this->after_code, $codes, true) + 1, 0, [$code]);

        if ($stage && ($error = $this->orderBreaksChargeTypes($stage->code, $codes))) {
            $this->addError('after_code', $error);
            return;
        }

        DB::transaction(function () use ($stage, $code, $codes) {
            if ($stage) {
                $stage->update(['name' => $this->name, 'description' => $this->description, 'job_status' => $this->job_status ?: null, 'is_active' => (bool) $this->is_active]);
            } else {
                ContainerStage::create([
                    'code' => $code,
                    'name' => $this->name,
                    'description' => $this->description,
                    'job_status' => $this->job_status ?: null,
                    'is_system' => false,
                    'is_active' => (bool) $this->is_active,
                    'sort_order' => 0,
                    'created_by' => Auth::id(),
                ]);
            }

            foreach ($codes as $i => $c) {
                ContainerStage::where('code', $c)->update(['sort_order' => ($i + 1) * 10]);
            }
        });

        ContainerStage::flush(); // sort_order was changed with query-builder updates
        $this->resyncJobsAt($code);
        $this->resetForm();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Stage saved.']);
    }

    public function toggleActive($id)
    {
        $stage = ContainerStage::findOrFail($id);

        if ($stage->is_system) {
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'System stages cannot be deactivated.']);
            return;
        }

        $stage->update(['is_active' => !$stage->is_active]);
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Stage ' . ($stage->is_active ? 'activated.' : 'deactivated.')]);
    }

    public function delete($id)
    {
        $stage = ContainerStage::findOrFail($id);

        if ($stage->is_system) {
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'System stages cannot be deleted.']);
            return;
        }

        if ($stage->inUse()) {
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'Containers or Charge Types already use this stage. Deactivate it instead.']);
            return;
        }

        $stage->delete();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Stage removed.']);
    }

    public function resetForm()
    {
        $this->reset(['stage_id', 'name', 'description', 'after_code', 'job_status', 'is_active', 'editing_system']);
        $this->resetValidation();
    }

    /** A Charge Type clock must still stop after it starts once this stage moves. */
    private function orderBreaksChargeTypes(string $code, array $codes): ?string
    {
        $types = ChargeType::where(fn ($q) => $q->where('start_milestone', $code)->orWhere('stop_milestone', $code))->get();

        foreach ($types as $type) {
            $start = array_search($type->start_milestone, $codes, true);
            $stop = array_search($type->stop_milestone, $codes, true);

            if ($start !== false && $stop !== false && $stop <= $start) {
                return "Charge Type \"{$type->name}\" would then stop its clock before it starts. Change its milestones first.";
            }
        }

        return null;
    }

    /** A changed stage -> job status link applies to jobs whose containers are on that stage now. */
    private function resyncJobsAt(string $code): void
    {
        ContainerStage::flush();
        $sync = app(FreightJobStatusSync::class);

        FreightJob::whereHas('shipments.containers', fn ($q) => $q->where('status', $code))
            ->get()->each(fn ($job) => $sync->sync($job));
    }

    private function makeCode(string $name): string
    {
        $base = Str::slug($name, '_') ?: 'stage';
        $code = $base;
        $i = 2;

        while (ContainerStage::where('code', $code)->exists()) {
            $code = $base . '_' . $i++;
        }

        return $code;
    }

    public function render()
    {
        return view('livewire.freight.settings.container-stages', [
            'stages' => $stages = ContainerStage::allOrdered(),
            'stageRows' => $this->paginateRows($stages),
            'effects' => PortExposureService::stageClockEffects(),
            'jobStatuses' => FreightJobStatusSync::FLOW,
        ]);
    }
}
