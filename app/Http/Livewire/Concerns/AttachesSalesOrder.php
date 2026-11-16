<?php

namespace App\Http\Livewire\Concerns;

use App\Models\Quotation;

/**
 * Sales order picker for the invoice Create/Edit forms: type an SO# into the search,
 * pick the matching sales order (a converted quotation) and it is attached to the invoice
 * as quotation_id + sales_order_number.
 *
 * The host component must expose $selectedCustomer.
 */
trait AttachesSalesOrder
{
    public $sales_order_search = '';
    public $quotation_id;
    public $sales_order_number;

    /** Sales orders matching the search, limited to the invoice's customer when one is selected. */
    public function getSalesOrderResultsProperty()
    {
        $search = trim((string) $this->sales_order_search);
        if ($search === '') {
            return collect();
        }

        $like = '%'.$search.'%';

        return Quotation::with('customer:id,name', 'currency:id,name')
            ->whereNotNull('sales_order_number')
            ->when(! empty($this->selectedCustomer), fn ($q) => $q->where('customer_id', $this->selectedCustomer))
            ->where(function ($q) use ($like) {
                $q->where('sales_order_number', 'like', $like)
                    ->orWhere('sales_order_ref', 'like', $like)
                    ->orWhere('quotation_number', 'like', $like);
            })
            ->orderBy('sales_order_seq', 'desc')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();
    }

    public function attachSalesOrder($id)
    {
        $salesOrder = Quotation::whereNotNull('sales_order_number')->find($id);
        if (! $salesOrder) {
            return;
        }

        $this->quotation_id = $salesOrder->id;
        $this->sales_order_number = $salesOrder->sales_order_number;
        $this->sales_order_search = '';
    }

    public function detachSalesOrder()
    {
        $this->quotation_id = null;
        $this->sales_order_number = null;
        $this->sales_order_search = '';
    }
}
