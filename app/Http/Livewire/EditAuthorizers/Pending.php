<?php

namespace App\Http\Livewire\EditAuthorizers;

use App\Models\EditAuthorizationRequest;
use App\Services\EditAuthorizationService;
use Illuminate\Support\Facades\Auth;
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

        abort_unless(app(EditAuthorizationService::class)->isAuthorizer($user) || $user->isSuperAdmin(), 403);
    }

    public function getRequestsProperty()
    {
        $user = Auth::user();

        $query = EditAuthorizationRequest::with('editable', 'requester')
            ->pending()
            ->where(function ($q) use ($user) {
                $q->whereNull('owner_id')->orWhere('owner_id', '!=', $user->id);
            });

        if (! $user->isSuperAdmin()) {
            $modules = app(EditAuthorizationService::class)->authorizedModules($user);
            $query->whereIn('module', $modules ?: ['__none__']);
        }

        return $query->latest()->paginate(10);
    }

    public function decide($id)
    {
        $this->request_id = $id;
        $this->decision = null;
        $this->decision_comments = null;
        $this->editAuthorizationRequest = EditAuthorizationRequest::with('editable', 'requester')->find($id);
        $this->dispatchBrowserEvent('show-editAuthorizationDecisionModal');
    }

    public function update()
    {
        $this->validate([
            'decision' => 'required',
        ]);

        $request = EditAuthorizationRequest::find($this->request_id);

        app(EditAuthorizationService::class)->decide($request, Auth::user(), $this->decision, $this->decision_comments);

        $this->dispatchBrowserEvent('hide-editAuthorizationDecisionModal');
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => "Request " . ucfirst($this->decision) . " Successfully!!",
        ]);
    }

    public function render()
    {
        return view('livewire.edit-authorizers.pending', [
            'requests' => $this->requests,
        ]);
    }
}
