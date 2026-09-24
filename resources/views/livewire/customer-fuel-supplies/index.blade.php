<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div>
                                @include('includes.messages')
                            </div>
                            <div class="panel-title">
                                <h5>Customer Supplied Fuel &amp; Expenses</h5>
                                <small class="text-muted">
                                    Fuel and trip expenses customers supplied or paid as part-payment of their trips. Each one is credited to the customer's account
                                    (DR Fuel - COGS / Fuel - Ops for Once Off Buy, DR Fuel Inventory for a Bulk Buy top-up, DR Trip Expense for an expense line, CR Accounts Receivable)
                                    and applied against the trip's invoice, which stays at the full amount. Supplies with no trip, or whose trip
                                    isn't invoiced yet, stay open until applied here or the invoice is approved.
                                </small>
                            </div>
                        </div>
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                            <div class="row" style="margin-bottom: 10px">
                                <div class="col-md-4">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search supply#, customer, trip...">
                                </div>
                                <div class="col-md-4">
                                    <select wire:model="customer_filter" class="form-control">
                                        <option value="">All Customers</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select wire:model="status_filter" class="form-control">
                                        <option value="">All</option>
                                        <option value="open">Not fully applied</option>
                                        <option value="applied">Fully applied</option>
                                    </select>
                                </div>
                            </div>

                            <table class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>Supply#</th>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Source</th>
                                        <th>Trip</th>
                                        <th>Qty (L)</th>
                                        <th>Amount</th>
                                        <th>Applied</th>
                                        <th>Open</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($supplies as $supply)
                                        @php
                                            $symbol = $supply->currency ? $supply->currency->symbol : '';
                                            $applied = (float) $supply->allocated_total;
                                            $open = round((float) $supply->amount - $applied, 2);
                                        @endphp
                                        <tr>
                                            <td>{{ $supply->supply_number }}</td>
                                            <td>{{ $supply->date ? $supply->date->format('Y-m-d') : '' }}</td>
                                            <td>{{ $supply->customer ? $supply->customer->name : '' }}</td>
                                            <td>
                                                @if ($supply->fuel)
                                                    Fuel order {{ $supply->fuel->order_number }}
                                                @elseif ($supply->top_up)
                                                    Top up {{ $supply->top_up->order_number }}
                                                @elseif ($supply->trip_expense_id)
                                                    Expense line: {{ $supply->description }}
                                                @endif
                                                <br><small class="text-muted">{{ $supply->purchase_type }}{{ $supply->container ? ' - ' . $supply->container->name : '' }}</small>
                                            </td>
                                            <td>
                                                @if ($supply->trip)
                                                    <a href="{{ route('trips.show', $supply->trip->id) }}" target="_blank" style="color: blue">{{ $supply->trip->trip_number }}</a>
                                                @endif
                                            </td>
                                            <td>{{ $supply->quantity ? number_format((float) $supply->quantity, 2) : '' }}</td>
                                            <td>{{ $symbol }}{{ number_format((float) $supply->amount, 2) }}</td>
                                            <td>{{ $symbol }}{{ number_format($applied, 2) }}</td>
                                            <td>
                                                @if ($open > 0)
                                                    <span class="badge bg-warning">{{ $symbol }}{{ number_format($open, 2) }}</span>
                                                @else
                                                    <span class="badge bg-success">Applied</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="#" wire:click.prevent="showAllocate({{ $supply->id }})" class="btn btn-default btn-sm"><i class="fa fa-link"></i> Apply / Allocations</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="10" class="text-center">No customer supplied fuel recorded yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                            {{ $supplies->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="cfsAllocateModal" tabindex="-1" role="dialog" aria-labelledby="cfsAllocateLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="cfsAllocateLabel"><i class="fa fa-gas-pump"></i> Apply Fuel Supply {{ $selected_supply ? $selected_supply->supply_number : '' }}
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    </h4>
                </div>
                <div class="modal-body">
                    @if ($selected_supply)
                        @php $symbol = $selected_supply->currency ? $selected_supply->currency->symbol : ''; @endphp
                        <p>
                            <strong>{{ $selected_supply->customer ? $selected_supply->customer->name : '' }}</strong> -
                            {{ $symbol }}{{ number_format((float) $selected_supply->amount, 2) }} supplied,
                            {{ $symbol }}{{ number_format($selected_supply->unallocatedAmount(), 2) }} still open.
                        </p>

                        <h5>Applied to</h5>
                        <table class="table table-sm table-bordered">
                            <thead><tr><th>Invoice#</th><th>Applied</th><th></th></tr></thead>
                            <tbody>
                                @forelse ($allocations as $allocation)
                                    <tr>
                                        <td>
                                            @if ($allocation->invoice)
                                                <a href="{{ route('invoices.show', $allocation->invoice->id) }}" target="_blank" style="color: blue">{{ $allocation->invoice->invoice_number }}</a>
                                            @endif
                                        </td>
                                        <td>{{ $symbol }}{{ number_format((float) $allocation->amount, 2) }}</td>
                                        <td><a href="#" wire:click.prevent="removeAllocation({{ $allocation->id }})" class="text-danger"><i class="fa fa-unlink"></i> Release</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3">Not applied to any invoice yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>

                        @if ($selected_supply->unallocatedAmount() > 0)
                            <h5>Apply to invoice</h5>
                            <form wire:submit.prevent="allocate()">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label>Open invoices ({{ $selected_supply->currency ? $selected_supply->currency->name : '' }})<span class="required" style="color: red">*</span></label>
                                            <select wire:model="selectedInvoice" class="form-control">
                                                <option value="">Select Invoice</option>
                                                @foreach ($open_invoices as $invoice)
                                                    <option value="{{ $invoice->id }}">{{ $invoice->invoice_number }} | {{ $invoice->date }} | Total {{ $symbol }}{{ number_format((float) $invoice->total, 2) }} | Balance {{ $symbol }}{{ number_format((float) $invoice->balance, 2) }}</option>
                                                @endforeach
                                            </select>
                                            @error('selectedInvoice') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Amount<span class="required" style="color: red">*</span></label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.defer="allocate_amount">
                                            @error('allocate_amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn bg-success btn-rounded"><i class="fa fa-save"></i> Apply</button>
                            </form>
                        @endif
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
