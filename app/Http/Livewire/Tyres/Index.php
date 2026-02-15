<?php

namespace App\Http\Livewire\Tyres;

use App\Exports\TyresExport;
use App\Models\ChecklistResult;
use App\Models\Dispose;
use App\Models\Product;
use App\Models\Tyre;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;

class Index extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];

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
        $this->resetPage();
    }

    public function exportTyresCSV(Excel $excel){
        return $excel->download(new TyresExport($this->search), 'tyres_'.now()->format('Y-m-d').'.csv', Excel::CSV);
    }
    public function exportTyresPDF(Excel $excel){
        return $excel->download(new TyresExport($this->search), 'tyres_'.now()->format('Y-m-d').'.pdf', Excel::DOMPDF);
    }
    public function exportTyresExcel(Excel $excel){
        return $excel->download(new TyresExport($this->search), 'tyres_'.now()->format('Y-m-d').'.xlsx');
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

        $search = trim((string) $this->search);
        $tyres = Tyre::query()
            ->with([
                'product.brand',
                // load active assignment + its asset relations if you show them in the list
                'tyre_assignments' => function ($q) {
                    $q->where('status', 1)
                    ->latest()
                    ->with(['horse', 'vehicle', 'trailer']);
                },
            ])
            ->where('disposed', 0)
            ->where('retread', 0)
            ->when($search !== '', function ($q) use ($search) {
                $like = "%{$search}%";

                $q->where(function ($qq) use ($like) {

                    // Tyre fields
                    $qq->where('tyre_number', 'like', $like)
                    ->orWhere('serial_number', 'like', $like)
                    ->orWhere('purchase_date', 'like', $like)
                    ->orWhere('type', 'like', $like);

                    // Related models
                    $qq->orWhereHas('product', fn ($p) => $p->where('name', 'like', $like))
                    ->orWhereHas('product.brand', fn ($b) => $b->where('name', 'like', $like))
                    ->orWhereHas('currency', fn ($c) => $c->where('name', 'like', $like))
                    ->orWhereHas('store', fn ($s) => $s->where('name', 'like', $like))
                    ->orWhereHas('vendor', fn ($v) => $v->where('name', 'like', $like));

                    // ✅ Search in active assignments by asset registration_number
                    $qq->orWhereHas('tyre_assignments', function ($ta) use ($like) {
                        $ta->where('status', 1)
                        ->where(function ($x) use ($like) {
                            $x->whereHas('horse', fn ($h) => $h->where('registration_number', 'like', $like))
                                ->orWhereHas('vehicle', fn ($v) => $v->where('registration_number', 'like', $like))
                                ->orWhereHas('trailer', fn ($t) => $t->where('registration_number', 'like', $like));
                        });
                    });
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('livewire.tyres.index', compact('tyres'));
      
    }
}
