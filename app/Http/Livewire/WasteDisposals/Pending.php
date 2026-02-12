<?php

namespace App\Http\Livewire\WasteDisposals;

use App\Models\WasteDisposal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Pending extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    protected $waste_disposals;
    public $waste_disposal;
    public $waste_disposal_id;
    public $authorize;
    public $comments;


    public function mount(){
       
    }

    public function updated($value){
        $this->validateOnly($value);
    }

    private function resetInputFields(){
        $this->authorize = "";
        $this->comments = "";
    }


    protected $rules = [
        'authorize' => 'required',
        'comments' => 'nullable',
    ];

    

     public function authorize($id){
        $waste_disposal = WasteDisposal::find($id);
        $this->waste_disposal_id = $waste_disposal->id;
        $this->waste_disposal = $waste_disposal;
        $this->dispatchBrowserEvent('show-authorizationModal');
      }

    public function update(){

        DB::transaction(function () {
        
        $this->validate();

        $waste_disposal = WasteDisposal::find($this->waste_disposal_id);
        $waste_disposal->authorized_by_id = Auth::user()->id;
        $waste_disposal->authorization = $this->authorize;
        $waste_disposal->authorization_date = now();
        $waste_disposal->authorization_comments = $this->comments;
        $waste_disposal->update();

        if ($this->authorize == "approved") {

            $this->dispatchBrowserEvent('hide-authorizationModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Waste disposal Approved Successfully!!"
            ]);
             return redirect()->route('waste_disposals.approved');

        }

         $this->dispatchBrowserEvent('hide-authorizationModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Waste disposal Rejected Successfully!!"
            ]);
             return redirect()->route('waste_disposals.rejected');

       
        
        });
    }

    public function render()
    {
       $search = trim($this->search);

    $query = WasteDisposal::query()
    ->where('authorization', 'pending')
    ->when($search !== '', function ($q) use ($search) {
        $q->whereHas('waste_disposal_items', function ($items) use ($search) {
            $items->whereHas('waste_type', function ($wt) use ($search) {
                $wt->where(function ($w) use ($search) {
                    $w->where('name', 'like', "%{$search}%")
                      ->orWhere('category', 'like', "%{$search}%")
                      ->orWhere('composition', 'like', "%{$search}%")
                      ->orWhere('generation_area', 'like', "%{$search}%")
                      ->orWhere('control_methods', 'like', "%{$search}%");
                });
            });
        });
    })
    ->orderBy('created_at', 'desc')
    ->paginate(10);
        return view('livewire.waste-disposals.pending',[
            'waste_disposals' => $query
        ]);
    }
}
