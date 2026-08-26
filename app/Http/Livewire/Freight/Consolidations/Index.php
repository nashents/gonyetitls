<?php

namespace App\Http\Livewire\Freight\Consolidations;

use App\Models\Consolidation;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public function paginationView()
    {
        return 'vendor.pagination.bootstrap-custom';
    }

    public $perPage = 15;
    public $search;

    protected $queryString = [
        'search',
        'perPage' => ['except' => 15],
        'page' => ['except' => 1],
    ];

    public function render()
    {
        $consolidations = Consolidation::query()
            ->with(['master_shipment.freight_job.customer', 'house_shipments']);

        if (filled($this->search)) {
            $search = trim($this->search);
            $consolidations->where(function ($q) use ($search) {
                $q->where('consolidation_number', 'like', "%{$search}%")
                    ->orWhereHas('master_shipment', fn ($q2) => $q2->where('shipment_number', 'like', "%{$search}%"));
            });
        }

        $consolidations->latest();

        return view('livewire.freight.consolidations.index', [
            'consolidations' => $consolidations->paginate($this->perPage),
        ]);
    }
}
