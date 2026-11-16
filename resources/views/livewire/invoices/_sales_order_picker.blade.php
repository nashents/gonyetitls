{{-- Sales order picker (AttachesSalesOrder trait): search by SO#, pick the row to attach. --}}
<div class="form-group">
    <label for="sales_order_search">S.O Number</label>
    @if ($sales_order_number)
        <div class="input-group">
            <input type="text" class="form-control" value="{{ $sales_order_number }}" readonly>
            <span class="input-group-btn">
                <button type="button" class="btn btn-default" wire:click="detachSalesOrder" title="Remove sales order">
                    <i class="fa fa-times"></i>
                </button>
            </span>
        </div>
    @else
        <input type="text" id="sales_order_search" class="form-control" autocomplete="off"
               wire:model.debounce.300ms="sales_order_search" placeholder="Search S.O number...">
        @if (trim((string) $sales_order_search) !== '')
            <div class="list-group" style="margin-bottom:0; max-height:220px; overflow-y:auto;">
                @forelse ($this->salesOrderResults as $salesOrder)
                    <a href="#" class="list-group-item" wire:key="so-result-{{ $salesOrder->id }}"
                       wire:click.prevent="attachSalesOrder({{ $salesOrder->id }})">
                        <strong>{{ $salesOrder->sales_order_number }}</strong>
                        @if ($salesOrder->sales_order_ref)
                            <small>| Ref: {{ $salesOrder->sales_order_ref }}</small>
                        @endif
                        <br>
                        <small>
                            {{ $salesOrder->customer?->name }}
                            | Quote: {{ $salesOrder->quotation_number }}
                            @if ($salesOrder->sales_order_date)
                                | {{ $salesOrder->sales_order_date }}
                            @endif
                            @if ($salesOrder->currency)
                                | {{ $salesOrder->currency->name }}
                            @endif
                        </small>
                    </a>
                @empty
                    <span class="list-group-item text-muted">
                        No sales order found{{ !empty($selectedCustomer) ? ' for the selected customer' : '' }}.
                    </span>
                @endforelse
            </div>
        @endif
    @endif
    @error('sales_order_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
</div>
