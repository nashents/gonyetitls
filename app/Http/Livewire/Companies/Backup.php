<?php

namespace App\Http\Livewire\Companies;

use App\Jobs\GenerateDataBackupJob;
use App\Models\DataBackup;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Backup extends Component
{
    public function mount()
    {
        abort_unless(DataBackup::userCanManage(Auth::user()), 403);
    }

    public function generate()
    {
        abort_unless(DataBackup::userCanManage(Auth::user()), 403);

        $pending = DataBackup::latest()->get()->first(fn (DataBackup $b) => $b->isPending());
        if ($pending) {
            $this->dispatchBrowserEvent('alert', [
                'type' => 'warning',
                'message' => 'A backup is already being prepared. Please wait for it to finish.',
            ]);
            return;
        }

        $backup = DataBackup::create(['user_id' => Auth::id(), 'status' => 'queued']);
        GenerateDataBackupJob::dispatch($backup->id);

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Backup queued. This can take a while for large datasets — you can leave this page and come back.',
        ]);
    }

    public function delete($id)
    {
        abort_unless(DataBackup::userCanManage(Auth::user()), 403);

        $backup = DataBackup::findOrFail($id);
        if ($backup->isPending()) {
            return;
        }

        $backup->deleteFile();
        $backup->delete();
    }

    public function render()
    {
        $backups = DataBackup::with('user')->latest()->limit(20)->get();

        return view('livewire.companies.backup', [
            'backups' => $backups,
            'hasPending' => $backups->contains(fn (DataBackup $b) => $b->isPending()),
        ]);
    }
}
