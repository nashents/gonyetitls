<?php

namespace App\Http\Livewire\Customers;

use App\Models\Customer;
use Livewire\Component;
use Livewire\WithPagination;

class Archived extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];

    /** Restore a customer back to the active list. */
    public function restore($id)
    {
        $customer = Customer::find($id);
        if ($customer) {
            $customer->archive = 0;
            $customer->status  = 1;
            $customer->save();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Customer restored.']);
        }
    }

    public function render()
    {
        $query = Customer::where('archive', 1)->orderBy('name', 'asc');

        if (filled($this->search)) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return view('livewire.customers.archived', [
            'customers' => $query->paginate(10),
        ]);
    }
}
