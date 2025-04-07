<?php

namespace App\Http\Livewire;

use App\Models\Notice;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Notices extends Component
{
    use WithPagination;

    public $user_id;
    public $title;
    public $body;
    public $notices;

    private function resetInputFields(){
        $this->title = '';
        $this->body = '';
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'title' => 'required',
        'body' => 'required',
    ];
    
    public function mount(){
        $this->notices = Notice::orderBy('created_at','desc')->get();
    }
    public function store(){
        try{
        $notice = new Notice;
        $notice->user_id = Auth::user()->id;
        $notice->title = $this->title;
        $notice->description = $this->body;
        $notice->save();

        $this->resetInputFields();
        $this->dispatchBrowserEvent('hide-noticeModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Notice Created Successfully!!"
        ]);
        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something went wrong while creating notice!!"
        ]);
    }
    }

    public function edit($id){
        $notice = Notice::find($id);
        $this->title = $notice->title;
        $this->body = $notice->description;
        $this->notice_id = $notice->id;
        $this->dispatchBrowserEvent('show-noticeEditModal');
    }

    public function update(){
        try{
        if ($this->notice_id) {
       
        $notice = Notice::find($this->notice_id);
        $notice->user_id = Auth::user()->id;
        $notice->title = $this->title;
        $notice->description = $this->body;
        $notice->update();
       
        $this->resetInputFields();
        $this->dispatchBrowserEvent('hide-noticeEditModal');
      
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Notice Created Successfully!!"
        ]);
     }
        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something went wrong while creating notice!!"
        ]);
    }

    }

    public function render()
    {
        $this->notices = Notice::orderBy('created_at','desc')->get();
        return view('livewire.notices',[
            'notices' => $this->notices,
        ]);
    }
}
