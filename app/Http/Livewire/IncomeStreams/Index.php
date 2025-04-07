<?php

namespace App\Http\Livewire\IncomeStreams;

use App\Models\IncomeStream;
use Livewire\Component;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{



    public $income_streams;
    public $description;
    public $name;
    public $type;

    public $expense_id;
    public $user_id;

    public function mount(){
        $this->income_streams = IncomeStream::latest()->get();
    }
    private function resetInputFields(){
        $this->type = '';
        $this->name = '';
        $this->description = '';
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:income_streams,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    public function store(){
        try{
        $income_stream = new IncomeStream;
        $income_stream->user_id = Auth::user()->id;
        $income_stream->name = $this->name;
        $income_stream->type = $this->type;
        $income_stream->description = $this->description;
        $income_stream->save();
        $this->dispatchBrowserEvent('hide-income_streamModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Income Stream Created Successfully!!"
        ]);
        }
        catch(\Exception $e){
        $this->dispatchBrowserEvent('hide-income_streamModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating Income Stream!!"
        ]);
    }

    }

    public function edit($id){
    $income_stream = IncomeStream::find($id);
    $this->user_id = $income_stream->user_id;
    $this->name = $income_stream->name;
    $this->type = $income_stream->type;
    $this->description = $income_stream->description;
    $this->income_stream_id = $income_stream->id;
    $this->dispatchBrowserEvent('show-income_streamEditModal');

    }

    public function update()
    {
        if ($this->income_stream_id) {
            try{
            $income_stream = IncomeStream::find($this->income_stream_id);
            $income_stream->user_id = Auth::user()->id;
            $income_stream->name = $this->name;
            $income_stream->type = $this->type;
            $income_stream->description = $this->description;
            $income_stream->update();

            $this->dispatchBrowserEvent('hide-income_streamEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Income Stream Updated Successfully!!"
            ]);
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-income_streamEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while updating income_streams!!"
            ]);
        }
        }
    }


    public function render()
    {
        $this->income_streams = IncomeStream::latest()->get();
        return view('livewire.income-streams.index',[
            'income_streams' => $this->income_streams
        ]);
    }
}
