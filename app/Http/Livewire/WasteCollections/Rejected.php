<?php

namespace App\Http\Livewire\WasteCollections;

use App\Models\WasteCollection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class Rejected extends Component
{
     use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    protected $waste_collections;
    public $waste_collection;
    public $waste_collection_id;
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
        $waste_collection = WasteCollection::find($id);
        $this->waste_collection_id = $waste_collection->id;
        $this->waste_collection = $waste_collection;
        $this->dispatchBrowserEvent('show-authorizationModal');
      }

    public function update(){

        
        DB::transaction(function () {
        
        $this->validate();

        $waste_collection = WasteCollection::find($this->waste_collection_id);
        $waste_collection->authorized_by_id = Auth::user()->id;
        $waste_collection->authorization = $this->authorize;
        $waste_collection->authorization_date = now();
        $waste_collection->authorization_comments = $this->comments;
        $waste_collection->update();

        if ($this->authorize == "approved") {

            $this->dispatchBrowserEvent('hide-authorizationModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Waste Collection Approved Successfully!!"
            ]);
             return redirect()->route('waste_collections.approved');

        }

         $this->dispatchBrowserEvent('hide-authorizationModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Waste Collection Rejected Successfully!!"
            ]);
             return redirect()->route('waste_collections.rejected');

       
        
        });
    }

    public function render()
    {
    $search = trim($this->search);

    $query = WasteCollection::query()
    ->where('authorization', 'rejected')
    ->when($search !== '', function ($q) use ($search) {
        $q->whereHas('waste_collection_items', function ($items) use ($search) {
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
        return view('livewire.waste-collections.rejected',[
            'waste_collections' => $query
        ]);
    }
}
