<?php

namespace App\Http\Livewire\Tyres;

use App\Models\Tyre;
use App\Models\Dispose;
use App\Models\Product;
use Livewire\Component;
use App\Models\TyreDetail;
use Livewire\WithPagination;
use App\Models\TyreAssignment;
use App\Models\ChecklistResult;
use Illuminate\Support\Facades\Auth;

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
        if(filled($this->search)){
            return view('livewire.tyres.index',[
                'tyres' => Tyre::with('product.brand')
                ->where('disposed', 0)
                ->where('retread', 0)
                ->where('tyre_number','like', '%'.$this->search.'%')
                ->orWhere('serial_number','like', '%'.$this->search.'%')
                ->orWhere('purchase_date','like', '%'.$this->search.'%')
                ->orWhere('type','like', '%'.$this->search.'%')
                ->orWhereHas('product', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('product.brand', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('currency', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('store', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('vendor', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orderBy('created_at','desc')
                ->paginate(10)
            ]);
        }else{
            return view('livewire.tyres.index',[
                'tyres' => Tyre::with('product.brand')
                ->where('disposed',0)
                ->where('retread',0)
                ->orderBy('created_at','desc')
                ->paginate(10)
            ]);
        }
      
    }
}
