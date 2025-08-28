<?php

namespace App\Http\Livewire\Clusters;


use App\Models\Horse;
use App\Models\Cluster;
use App\Models\Trailer;
use Livewire\Component;
use App\Models\Category;
use Livewire\WithPagination;
use App\Models\CategoryValue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];

    private $clusters;
    public $cluster_id;
    public $trailers;
    public $trailer_id = [];
    public $horses;
    public $horse_id;
    public $status;
    public $name;
  

    public function mount(){
        $this->resetPage();
        $this->horses = Horse::orderBy('registration_number','asc')->get();
        $this->trailers = Trailer::orderBy('registration_number','asc')->get();
    }

   
    private function resetInputFields(){
        $this->name = '';
        $this->horse_id = '';
        $this->trailer_id = [];
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:clusters,name,NULL,id,deleted_at,NULL',
    ];

 

    public function store(){
        $this->validate();
        $cluster = new Cluster;
        $cluster->user_id = Auth::user()->id;
        $cluster->name = $this->name;
        $cluster->horse_id = $this->horse_id;
        $cluster->status = 1;
        $cluster->save();
        $cluster->trailers()->sync($this->trailer_id);
      
        $this->dispatchBrowserEvent('hide-clusterModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Cluster(s) Added Successfully!!"
        ]);
     
    }

    public function edit($id){
    $cluster = Cluster::find($id);
    $this->user_id = $cluster->user_id;
    $this->name = $cluster->name;
    $this->status = $cluster->status;
    $this->horse_id = $cluster->horse_id;
    $this->cluster_id = $cluster->id;
    $this->dispatchBrowserEvent('show-clusterEditModal');

    }

    public function update()
    {
        if ($this->cluster_id) {
            $cluster = Cluster::find($this->cluster_id);
            $cluster->update([
                'user_id' => Auth::user()->id,
                'name' => $this->name,
                'horse_id' => $this->horse_id,
                'status' => $this->status,
            ]);
             $cluster->trailers()->detach();
             $cluster->trailers()->sync($this->trailer_id);

        $this->dispatchBrowserEvent('hide-clusterEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Cluster Updated Successfully!!"
        ]);

        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
       
        if (isset($this->search)) {
            return view('livewire.clusters.index',[
                'clusters' => Cluster::query()->with('category','category_value')
                ->where('name','like', '%'.$this->search.'%')
                ->orderBy('name','asc')->paginate(10),
            ]);
        }else {
            return view('livewire.clusters.index',[
                'clusters' => Cluster::orderBy('name','asc')->paginate(10),
            ]);
        }

      
    }
}
