<?php

namespace App\Http\Livewire\Tyres;

use App\Exports\TyreAssignmentsExport;
use App\Models\ChecklistResult;
use App\Models\Tyre;
use App\Models\TyreAssignment;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;

class TyreAssignments extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];

    private $tyre_assignments;
    public $horse_id;
    public $trailer_id;
    public $vehicle_id;
    public $type;
    public $equipmentId;

    public function mount($id, $type){
        $this->type = $type;
        $this->equipmentId = $id;
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
        if ($this->type == "Horse") {
           $this->tyre_assignments = TyreAssignment::where('horse_id',$this->equipmentId)->where('status',1)->paginate(15);
        }elseif ($this->type == "Trailer") {
            $this->tyre_assignments = TyreAssignment::where('trailer_id',$this->equipmentId)->where('status',1)->paginate(15);
        }elseif ($this->type == "Vehicle") {
            $this->tyre_assignments = TyreAssignment::where('vehicle_id',$this->equipmentId)->where('status',1)->paginate(15);
        }
        return view('livewire.tyres.tyre-assignments',[
            'tyre_assignments' => $this->tyre_assignments
        ]);
    }
}
