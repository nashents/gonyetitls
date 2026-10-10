<?php

namespace App\Http\Livewire\Tyres;

use App\Exports\TyreAssignmentsExport;
use App\Models\ChecklistResult;
use App\Models\Horse;
use App\Models\Trailer;
use App\Models\Tyre;
use App\Models\TyreAssignment;
use App\Models\Vehicle;
use App\Services\Fleet\TyreAssignmentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;

class TyreAssignments extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    // Search is not kept in the URL so it clears on refresh.

    private $tyre_assignments;
    public $horse_id;
    public $trailer_id;
    public $vehicle_id;
    public $type;
    public $equipmentId;

    // Bulk assign
    public $assigning = false;
    public $assignRows = [];
    public $fitting_odometer;
    public $date_fitted;
    public $assign_comments;
    public $tyreSearch;

    // Bulk unassign
    public $selected = [];
    public $ending_odometer;
    public $unassigned_date;
    public $unassignment_reason;

    public function mount($id, $type){
        $this->type = $type;
        $this->equipmentId = $id;
    }

    private function asset()
    {
        return match ($this->type) {
            'Horse' => Horse::find($this->equipmentId),
            'Trailer' => Trailer::find($this->equipmentId),
            'Vehicle' => Vehicle::find($this->equipmentId),
            default => null,
        };
    }

    private function assetColumn()
    {
        return app(TyreAssignmentService::class)->assetColumn($this->type);
    }

    private function newRow()
    {
        return ['key' => uniqid(), 'tyre_id' => '', 'axle' => '', 'position' => ''];
    }

    public function openAssign(){
        $this->resetErrorBag();
        $this->assigning = true;
        $this->assignRows = [$this->newRow()];
        $this->fitting_odometer = optional($this->asset())->mileage;
        $this->date_fitted = date('Y-m-d');
        $this->assign_comments = '';
        $this->tyreSearch = '';
        $this->dispatchBrowserEvent('show-assetTyreAssignModal');
    }

    public function addRow(){
        $this->assignRows[] = $this->newRow();
    }

    public function removeRow($index){
        unset($this->assignRows[$index]);
        $this->assignRows = array_values($this->assignRows);
        if (empty($this->assignRows)) {
            $this->assignRows = [$this->newRow()];
        }
    }

    public function saveAssignments(){
        $this->validate([
            'fitting_odometer' => 'required|numeric|min:0',
            'date_fitted' => 'required|date|before_or_equal:today',
            'assignRows' => 'required|array|min:1',
            'assignRows.*.tyre_id' => 'required|distinct|exists:tyres,id',
            'assignRows.*.axle' => 'required',
            'assignRows.*.position' => 'required',
            'assign_comments' => 'nullable|string',
        ], [
            'assignRows.*.tyre_id.required' => 'Select a tyre.',
            'assignRows.*.tyre_id.distinct' => 'This tyre is selected more than once.',
            'assignRows.*.axle.required' => 'Select an axle.',
            'assignRows.*.position.required' => 'Select a position.',
        ]);

        // One tyre per axle + position on the asset (spares excepted), within the batch and against what is already fitted.
        $errors = [];
        $seen = [];
        foreach ($this->assignRows as $i => $row) {
            if (TyreAssignment::isSpareSlot($row['axle'], $row['position'])) {
                continue;
            }
            $slot = $row['axle'].'|'.$row['position'];
            if (isset($seen[$slot])) {
                $errors["assignRows.$i.position"] = 'This axle and position is already used on another row.';
            } elseif ($taken = TyreAssignment::activeAtPosition($this->assetColumn(), $this->equipmentId, $row['axle'], $row['position'])) {
                $errors["assignRows.$i.position"] = TyreAssignment::positionTakenMessage($taken);
            }
            $seen[$slot] = true;
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        $service = app(TyreAssignmentService::class);
        // All or nothing: one unavailable tyre rolls back the whole batch.
        DB::transaction(function () use ($service) {
            foreach ($this->assignRows as $i => $row) {
                try {
                    $service->assign($this->type, $this->equipmentId, [
                        'tyre_id' => $row['tyre_id'],
                        'axle' => $row['axle'],
                        'position' => $row['position'],
                        'starting_odometer' => $this->fitting_odometer,
                        'date_fitted' => $this->date_fitted,
                        'description' => $this->assign_comments,
                    ]);
                } catch (ValidationException $e) {
                    $field = $e->validator->errors()->has('position') ? 'position' : 'tyre_id';
                    throw ValidationException::withMessages(["assignRows.$i.$field" => $e->validator->errors()->first()]);
                }
            }
        });

        $count = count($this->assignRows);
        $this->assigning = false;
        $this->assignRows = [];
        $this->dispatchBrowserEvent('hide-assetTyreAssignModal');
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => $count.' Tyre(s) Assigned Successfully!!'
        ]);
    }

    public function openUnassign($id = null){
        $this->resetErrorBag();
        if ($id) {
            $this->selected = [(string) $id];
        }
        if (empty($this->selected)) {
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Select the tyre(s) to unassign first.'
            ]);
            return;
        }
        $this->ending_odometer = optional($this->asset())->mileage;
        $this->unassigned_date = date('Y-m-d');
        $this->unassignment_reason = '';
        $this->dispatchBrowserEvent('show-assetTyreUnassignModal');
    }

    public function saveUnassignments(){
        $this->validate([
            'selected' => 'required|array|min:1',
            'ending_odometer' => 'required|numeric|min:0',
            'unassigned_date' => 'required|date|before_or_equal:today',
            'unassignment_reason' => 'required|string|max:1000',
        ]);

        $assignments = TyreAssignment::whereIn('id', $this->selected)
            ->where($this->assetColumn(), $this->equipmentId)
            ->where('status', 1)
            ->get();

        $highestFitting = $assignments->pluck('starting_odometer')->filter(fn ($v) => is_numeric($v))->max();
        if (!is_null($highestFitting) && (float) $this->ending_odometer < (float) $highestFitting) {
            throw ValidationException::withMessages([
                'ending_odometer' => 'Unassignment mileage cannot be less than the fitting mileage ('.$highestFitting.') of a selected tyre.',
            ]);
        }

        $service = app(TyreAssignmentService::class);
        $distance = DB::transaction(function () use ($assignments, $service) {
            $total = 0;
            foreach ($assignments as $assignment) {
                $total += $service->unassign($assignment, $this->ending_odometer, $this->unassigned_date, $this->unassignment_reason);
            }
            return $total;
        });

        $count = $assignments->count();
        $this->selected = [];
        $this->dispatchBrowserEvent('hide-assetTyreUnassignModal');
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => $count.' Tyre(s) Unassigned Successfully!! '.number_format($distance).'Kms added to tyre distance.'
        ]);
    }

    public function selectAll($ids){
        $this->selected = count($this->selected) === count($ids) ? [] : array_map('strval', $ids);
    }


    public function exportTyreAssignmentsCSV(Excel $excel){

        return $excel->download(new TyreAssignmentsExport($this->equipmentId, $this->type), $this->type.'_assigned_tyres.csv', Excel::CSV);
    }
    public function exportTyreAssignmentsPDF(Excel $excel){

        return $excel->download(new TyreAssignmentsExport($this->equipmentId, $this->type), $this->type.'_assigned_tyres.pdf', Excel::DOMPDF);
    }
    public function exportTyreAssignmentsExcel(Excel $excel){
        return $excel->download(new TyreAssignmentsExport($this->equipmentId, $this->type), $this->type.'_assigned_tyres.xlsx');
    }

     public function badge($id, $category){
        $checklist_result = ChecklistResult::where('tyre_id',$id)->latest()->first();
        $tyre = Tyre::find($id);
        $badge = "active";
        if ($checklist_result) {
                if ($category == "pressure") {
                    $standard = $tyre->pressure_psi;
                    $current = $checklist_result->pressure_psi;
                }elseif($category == "depth")
                {
                    $standard = $tyre->thread_depth ?? 0;
                    $current = $checklist_result->tread_depth_mm ?? 0;
                }
            
                if ($standard > 0) {
                    $pct = ($current / $standard) * 100;
                }else{
                    $pct = 0;
                }

                if ($pct >= 90) {
                    $badge = 'success';    // green
                } elseif ($pct >= 50) {
                    $badge = 'warning';    // yellow
                } else {
                    $badge = 'danger';     // red
                }
        }

        return $badge;

    }
    
    public function render()
    {
        $this->tyre_assignments = TyreAssignment::with('tyre.product.brand','horse','trailer','vehicle')
            ->where($this->assetColumn(), $this->equipmentId)
            ->where('status',1)
            ->orderBy('axle')
            ->orderBy('position')
            ->paginate(30);

        // Only tyres that are free to fit; tyres already picked on another row stay listed so the row keeps its value.
        $availableTyres = collect();
        if ($this->assigning) {
            $term = trim((string) $this->tyreSearch);
            $picked = collect($this->assignRows)->pluck('tyre_id')->filter()->all();
            $availableTyres = Tyre::query()
                ->with('product.brand')
                ->where('disposed', 0)
                ->where('retread', 0)
                ->whereDoesntHave('tyre_assignments', fn ($q) => $q->where('status', 1))
                ->when($term !== '', function ($q) use ($term, $picked) {
                    $like = "%{$term}%";
                    $q->where(function ($q) use ($like, $picked) {
                        $q->where('serial_number', 'like', $like)
                          ->orWhere('tyre_number', 'like', $like)
                          ->orWhereHas('product', fn ($p) => $p->where('name', 'like', $like)
                              ->orWhereHas('brand', fn ($b) => $b->where('name', 'like', $like)))
                          ->orWhereIn('id', $picked);
                    });
                })
                ->orderBy('serial_number')
                ->get();
        }

        return view('livewire.tyres.tyre-assignments',[
            'tyre_assignments' => $this->tyre_assignments,
            'availableTyres' => $availableTyres,
            'asset' => $this->asset(),
        ]);
    }
}
