<?php

namespace App\Http\Livewire\TripEditAuthorizers;

use App\Models\TripEditAuthorizationRequest;
use App\Models\TripEditAuthorizer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Pending extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $request_id;
    public $decision;
    public $decision_comments;
    public $editAuthorizationRequest;

    public function mount()
    {
        $user = Auth::user();

        $isAuthorizer = TripEditAuthorizer::where('user_id', $user->id)->where('status', 1)->exists();

        abort_unless($isAuthorizer || $user->isSuperAdmin(), 403);
    }

    public function getRequestsProperty()
    {
        return TripEditAuthorizationRequest::with('trip', 'requester')
            ->where('status', 'pending')
            ->whereHas('trip', function ($q) {
                $q->where('user_id', '!=', Auth::user()->id);
            })
            ->latest()
            ->paginate(10);
    }

    public function decide($id)
    {
        $this->request_id = $id;
        $this->decision = null;
        $this->decision_comments = null;
        $this->editAuthorizationRequest = TripEditAuthorizationRequest::with('trip', 'requester')->find($id);
        $this->dispatchBrowserEvent('show-editAuthorizationDecisionModal');
    }

    public function update()
    {
        $this->validate([
            'decision' => 'required',
        ]);

        DB::transaction(function () {
            $request = TripEditAuthorizationRequest::find($this->request_id);

            abort_if(!$request || $request->trip->user_id === Auth::user()->id, 403, "You cannot authorize edits for your own trip.");

            $request->decided_by = Auth::user()->id;
            $request->status = $this->decision;
            $request->decided_at = now();
            $request->decision_comments = $this->decision_comments;
            $request->update();

            $this->dispatchBrowserEvent('hide-editAuthorizationDecisionModal');
            $this->dispatchBrowserEvent('alert', [
                'type' => 'success',
                'message' => "Request " . ucfirst($this->decision) . " Successfully!!",
            ]);
        });
    }

    public function render()
    {
        return view('livewire.trip-edit-authorizers.pending', [
            'requests' => $this->requests,
        ]);
    }
}
