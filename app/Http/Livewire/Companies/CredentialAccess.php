<?php

namespace App\Http\Livewire\Companies;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Business Settings > Credentials Access: picks the accounts (besides is_admin
 * users, who always can) allowed to see employee PINs and send login details.
 */
class CredentialAccess extends Component
{
    public $company_id;
    public $sharer_ids = [];
    public $search;

    public function mount($id)
    {
        abort_unless(Auth::user()->is_admin(), 403);

        $company = Company::find($id);
        $this->company_id = $company->id;
        $this->sharer_ids = array_map('strval', (array) ($company->credential_sharer_ids ?? []));
    }

    public function update()
    {
        abort_unless(Auth::user()->is_admin(), 403);

        $ids = collect($this->sharer_ids)
            ->filter(fn ($v) => filled($v))
            ->map(fn ($v) => (int) $v)
            ->unique()
            ->values()
            ->all();

        $company = Company::find($this->company_id);
        $company->credential_sharer_ids = $ids;
        $company->update();

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Credentials Access Updated Successfully!!',
        ]);
    }

    public function removeSharer($id)
    {
        $this->sharer_ids = array_values(array_filter($this->sharer_ids, fn ($v) => (int) $v !== (int) $id));
    }

    public function render()
    {
        $search = trim($this->search ?? '');

        $users = User::query()
            ->where(fn ($q) => $q->whereNull('is_admin')->orWhere('is_admin', false))
            ->whereHas('employee', fn ($q) => $q->where('archive', false))
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('surname', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")))
            ->orderBy('name')
            ->orderBy('surname')
            ->get(['id', 'name', 'surname', 'username']);

        $selected_users = User::whereIn('id', array_map('intval', array_filter($this->sharer_ids)))
            ->orderBy('name')
            ->orderBy('surname')
            ->get(['id', 'name', 'surname']);

        return view('livewire.companies.credential-access', [
            'users' => $users,
            'selected_users' => $selected_users,
        ]);
    }
}
