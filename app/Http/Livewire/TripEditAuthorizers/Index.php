<?php

namespace App\Http\Livewire\TripEditAuthorizers;

use App\Models\TripEditAuthorizer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Index extends Component
{
    public $trip_edit_authorizers;
    public $users;
    public $user_id;
    public $status;
    public $trip_edit_authorizer_id;

    public function mount()
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $this->status = 1;
        $this->users = User::where('category', 'employee')->where('active', 1)->orderBy('name', 'asc')->get();
    }

    protected $rules = [
        'user_id' => 'required',
    ];

    private function resetInputFields()
    {
        $this->user_id = null;
        $this->status = 1;
        $this->trip_edit_authorizer_id = null;
    }

    public function store()
    {
        $this->validate();

        DB::transaction(function () {
            $authorizer = new TripEditAuthorizer;
            $authorizer->user_id = $this->user_id;
            $authorizer->created_by = Auth::user()->id;
            $authorizer->status = $this->status;
            $authorizer->save();

            $this->dispatchBrowserEvent('hide-tripEditAuthorizerModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert', [
                'type' => 'success',
                'message' => "Trip Edit Authorizer Added Successfully!!",
            ]);
        });
    }

    public function edit($id)
    {
        $authorizer = TripEditAuthorizer::find($id);
        $this->trip_edit_authorizer_id = $authorizer->id;
        $this->user_id = $authorizer->user_id;
        $this->status = $authorizer->status;
        $this->dispatchBrowserEvent('show-tripEditAuthorizerEditModal');
    }

    public function update()
    {
        $this->validate();

        DB::transaction(function () {
            if ($this->trip_edit_authorizer_id) {
                $authorizer = TripEditAuthorizer::find($this->trip_edit_authorizer_id);
                $authorizer->user_id = $this->user_id;
                $authorizer->status = $this->status;
                $authorizer->update();

                $this->dispatchBrowserEvent('hide-tripEditAuthorizerEditModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert', [
                    'type' => 'success',
                    'message' => "Trip Edit Authorizer Updated Successfully!!",
                ]);
            }
        });
    }

    public function delete($id)
    {
        $authorizer = TripEditAuthorizer::find($id);
        if ($authorizer) {
            $authorizer->delete();
            $this->dispatchBrowserEvent('alert', [
                'type' => 'success',
                'message' => "Trip Edit Authorizer Removed Successfully!!",
            ]);
        }
    }

    public function render()
    {
        $this->trip_edit_authorizers = TripEditAuthorizer::with('user', 'creator')->latest()->get();

        return view('livewire.trip-edit-authorizers.index', [
            'trip_edit_authorizers' => $this->trip_edit_authorizers,
        ]);
    }
}
