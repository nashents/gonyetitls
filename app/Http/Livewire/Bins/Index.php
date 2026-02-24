<?php

namespace App\Http\Livewire\Bins;
use App\Exports\BinsExport;
use App\Models\Bin;
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
    private $bins;
    public $name;
    public $bin_number;
    public $description;
    public $bin_id;
    public $user_id;

    public function mount(){
        
    }
    public function exportBinsCSV(Excel $excel){

        return $excel->download(new BinsExport, 'bins.csv', Excel::CSV);
    }
    public function exportBinsPDF(Excel $excel){

        return $excel->download(new BinsExport, 'bins.pdf', Excel::DOMPDF);
    }
    public function exportBinsExcel(Excel $excel){

        return $excel->download(new BinsExport, 'bins.xlsx');
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    private function resetInputFields(){
        $this->name = "";
        $this->bin_number = "";
        $this->description = "";
    }
    protected $rules = [
        'name' => 'required|unique:bins,name,NULL,id,deleted_at,NULL|string|min:2',
        'bin_number' => 'required|unique:bins,bin_number,NULL,id,deleted_at,NULL|string|min:2',
    ];

    public function store(){
        try{
        
        $this->validate();
        
        $bin = new Bin;
        $bin->user_id = Auth::user()->id;
        $bin->name = $this->name;
        $bin->bin_number = $this->bin_number;
        $bin->description = $this->description;
        $bin->save();

        $this->dispatchBrowserEvent('hide-binModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Bin Created Successfully!!"
        ]);

    }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating bin!!"
        ]);
         }
    }

    public function edit($id){
    $bin = Bin::find($id);
  
    $this->user_id = $bin->user_id;
    $this->name = $bin->name;
    $this->bin_id = $bin->id;
    $this->dispatchBrowserEvent('show-binEditModal');

    }


    public function update()
    {
        if ($this->bin_id) {
            try{

            $bin = Bin::find($this->bin_id);
            $bin->name = $this->name;
            $bin->bin_number = $this->bin_number;
            $bin->description = $this->description;
            $bin->update();

            $this->dispatchBrowserEvent('hide-binEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Bin Updated Successfully!!"
            ]);
            // return redirect()->route('bins.index');
        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while updating bin!!"
        ]);
    }

        }
    }


    public function render()
    {
        $search = trim($this->search);

        $query = Bin::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('name', 'like', "%{$search}%")
                    ->orWhere('part_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('unit_of_measure', 'like', "%{$search}%");
                });
            })
        ->orderBy('name', 'asc')
        ->paginate(10);

        return view('livewire.bins.index',[
            'bins' => $query
        ]);
    }
}
