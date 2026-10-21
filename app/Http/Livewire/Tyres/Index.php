<?php

namespace App\Http\Livewire\Tyres;

use App\Exports\TyresExport;
use App\Models\ChecklistResult;
use App\Models\Dispose;
use App\Models\Horse;
use App\Models\Product;
use App\Models\Trailer;
use App\Models\Tyre;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;

class Index extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    public $location;
    public $equipment_type;
    public $equipment_id;
    public $equipments = [];
    protected $queryString = ['search', 'location', 'equipment_type', 'equipment_id'];

    private $tyres;
    public $tyre_id;
    public $tyre;
    public $tyre_number;
    public $serial_number;
    public $product_id;
    public $products;
    public $width;
    public $aspect_ratio;
    public $diameter;
    public $qty;
    public $rate;
    public $user_id;
    public $dispose;
    public $comments;
    public $date;
    public $tread_depth_mm;
    public $pressure_psi;
    public $rim_condition;
    public $wear_pattern;
    public $sidewall_damage;
    public $valve_ok;
    public $axle_match;
    public $wheel_nuts_torqued;
    public $notes;
    public $rating;
    public $action_required;
    public $checklist_result;
    public $usage;
    public $balance;

    public function mount(){
        $this->products = Product::where('department','tyre')->get();
        $this->loadEquipments();
    }

    // Filters shared by the list and its exports (see Tyre::scopeListFilter).
    protected function filters(){
        return [
            'search' => $this->search,
            'location' => $this->location,
            'equipment_type' => $this->equipment_type,
            'equipment_id' => $this->equipment_id,
        ];
    }

    protected function loadEquipments(){
        $model = ['Horse' => Horse::class, 'Trailer' => Trailer::class, 'Vehicle' => Vehicle::class][$this->equipment_type] ?? null;
        $this->equipments = $model ? $model::orderByIdentifier('asc')->get() : [];
    }

    public function updatingLocation(){
        $this->resetPage();
    }

    public function updatedEquipmentType(){
        $this->equipment_id = null;
        $this->loadEquipments();
        $this->resetPage();
    }

    public function updatingEquipmentId(){
        $this->resetPage();
    }

    public function clearFilters(){
        $this->reset(['search', 'location', 'equipment_type', 'equipment_id', 'equipments']);
        $this->resetPage();
    }

    public function exportTyresCSV(Excel $excel){
        return $excel->download(new TyresExport($this->search, $this->filters()), 'tyres_'.now()->format('Y-m-d').'.csv', Excel::CSV);
    }
    public function exportTyresPDF(Excel $excel){
        return $excel->download(new TyresExport($this->search, $this->filters()), 'tyres_'.now()->format('Y-m-d').'.pdf', Excel::DOMPDF);
    }
    public function exportTyresExcel(Excel $excel){
        return $excel->download(new TyresExport($this->search, $this->filters()), 'tyres_'.now()->format('Y-m-d').'.xlsx');
    }
    
    public function updated($value){
        $this->validateOnly($value);
    }

    protected $rules = [
        'product_id' => 'required',
        'rate' => 'required',
        'tyre_number' => 'required',
        'serial_number' => 'required',
        'width' => 'required',
        'aspect_ratio' => 'required',
        'diameter' => 'required',

        'date' => 'required',
        'comments' => 'required',
        'dispose' => 'required',
    ];


   

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


        public function update(){
            $tyre = Tyre::find($this->tyre_id);
            $tyre->tyre_id = $this->tyre_id;
            $tyre->tyre_number = $this->tyre_number;
            $tyre->serial_number = $this->serial_number;
            $tyre->product_id = $this->product_id;
            $tyre->width = $this->width;
            $tyre->aspect_ratio = $this->aspect_ratio;
            $tyre->diameter = $this->diameter;
            $tyre->rate = $this->rate;
            $tyre->update();

            $this->dispatchBrowserEvent('hide-tyreEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Tyre Updated Successfully!!"
            ]);
        }

        


   
    
        private function resetInputFields(){
            $this->dispose = '';
            $this->comments = '';
            $this->date = '';
    
        }
    
        public function showDispose($id){
            $this->tyre_id = $id;
            $this->tyre = Tyre::find($id);
            $this->dispatchBrowserEvent('show-disposeModal');
    
        }
    
        public function dispose(){

           $dispose = Dispose::firstOrCreate(
            [
                'tyre_id' => $this->tyre_id, // Unique condition to check
                'user_id' => Auth::id(),     // Optional: use Auth::id() shorthand
            ],
            [
                'comments' => $this->comments,
                'date'     => $this->date,
            ]
            );
    
            $tyre = Tyre::find($this->tyre_id);
            $tyre->disposed = $this->dispose;
            $tyre->status = 0;
            $tyre->update();

            $assignments = $tyre->tyre_assignments;
            if ($assignments) {
                foreach ($assignments as $assignment) {
                    $assignment->status = 0;
                    $assignment->update();
                }
            }
    
            $this->dispatchBrowserEvent('hide-disposeModal');
    
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Tyre Disposed Successfully!!"
            ]);

            return redirect()->route('disposes.index');
        }


        public function updatingSearch()
        {
            $this->resetPage();
        }

    public function render()
    {

        $tyres = Tyre::query()
            ->with([
                'product.brand',
                'user',
                'goods_received',
                'tyre_assignments' => function ($q) {
                    $q->where('status', 1)
                    ->latest()
                    ->with(['horse', 'vehicle', 'trailer']);
                },
            ])
            ->listFilter($this->filters())
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('livewire.tyres.index', compact('tyres'));
      
    }
}
