<?php

namespace App\Http\Livewire\Values;

use App\Models\Value;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{


    public $values;
    public  $value_id;
    public $status;
    public $name;
    public $user_id;
    public $attribute_id;


    public function mount(){
        $this->values = Value::all();
    }

    public function updated($value){
        $this->validateOnly($value);
    }

    protected $rules = [
        'name' => 'required|unique:values,name,NULL,id,deleted_at,NULL|string',
    ];


    public function edit($id){
    $value = Value::find($id);
    $this->value_id = $value->id;
    $this->user_id = Auth::user()->id;
    $this->attribute_id = $value->attribute_id;
    $this->name = $value->name;
    $this->dispatchBrowserEvent('show-valueEditModal');

    }

    public function update()
    {
        if ($this->value_id) {
            $value = Value::find($this->value_id);
            $value->update([
                'user_id' => Auth::user()->id,
                'attribute_id' => $this->attribute_id,
                'name' => $this->name,
            ]);

            Session::flash('success','Value updated successfully');
            $this->dispatchBrowserEvent('hide-valueEditModal',['message',"Value successfully updated"]);
            return redirect()->route('values.index');

        }
    }
    public function render()
    {
        return view('livewire.values.index');
    }
}
