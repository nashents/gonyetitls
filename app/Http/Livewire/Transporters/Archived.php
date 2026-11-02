<?php

namespace App\Http\Livewire\Transporters;

use App\Models\Transporter;
use Livewire\Component;
use Livewire\WithPagination;

class Archived extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];

    /** Restore a transporter back to the active list. */
    public function restore($id)
    {
        $transporter = Transporter::find($id);
        if ($transporter) {
            $transporter->archive = 0;
            $transporter->status  = 1;
            $transporter->save();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Transporter restored.']);
        }
    }

    public function render()
    {
        $query = Transporter::where('archive', 1)->orderBy('name', 'asc');

        if (filled($this->search)) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('transporter_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return view('livewire.transporters.archived', [
            'transporters' => $query->paginate(10),
        ]);
    }
}
