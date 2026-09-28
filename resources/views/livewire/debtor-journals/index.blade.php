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
                                <a href="#" wire:click.prevent="showCreate()" class="btn btn-default pull-right"><i class="fa fa-plus-square-o"></i> New Debtors Journal</a>
                                <h5>Debtors Journal</h5>
                                <small class="text-muted">
                                    Manual adjustments to a customer's account: write-offs, discounts, opening balances, interest and corrections.
                                    A <strong>debit</strong> increases what the customer owes (DR Accounts Receivable, CR contra account).
                                    A <strong>credit</strong> reduces it (DR contra account, CR Accounts Receivable) and can be applied to the customer's open invoices.
                                    Every journal shows on the customer statement and in aged receivables. Posted journals can't be edited. Void one to reverse it, then capture a new one.
                                </small>
                            </div>
                        </div>
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                            <div class="row" style="margin-bottom: 10px">
                                <div class="col-md-3">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search journal#, ref, customer...">
                                </div>
                                <div class="col-md-3">
                                    <select wire:model="customer_filter" class="form-control">
                                        <option value="">All Customers</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select wire:model="type_filter" class="form-control">
                                        <option value="">Debits &amp; Credits</option>
                                        <option value="debit">Debits</option>
                                        <option value="credit">Credits</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select wire:model="status_filter" class="form-control">
                                        <option value="">Any status</option>
                                        <option value="posted">Posted</option>
                                        <option value="voided">Voided</option>
                                    </select>
                                </div>
                            </div>

                            <table class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>Journal#</th>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Type</th>
                                        <th>Contra Account</th>
                                        <th>Description</th>
                                        <th>Amount</th>
                                        <th>Applied</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($journals as $journal)
                                        @php
                                            $symbol = $journal->currency ? $journal->currency->symbol : '';
                                            $applied = (float) $journal->allocated_total;
                                            $open = round((float) $journal->amount - $applied, 2);
                                        @endphp
                                        <tr wire:key="dj-{{ $journal->id }}">
                                            <td>{{ $journal->journal_number }}</td>
                                            <td>{{ $journal->date ? $journal->date->format('Y-m-d') : '' }}</td>
                                            <td>{{ $journal->customer ? $journal->customer->name : '' }}</td>
                                            <td>
                                                @if ($journal->isCredit())
                                                    <span class="badge bg-info">Credit</span>
                                                @else
                                                    <span class="badge bg-primary">Debit</span>
                                                @endif
                                            </td>
                                            <td>{{ $journal->account ? $journal->account->name : '' }}</td>
                                            <td>
                                                {{ $journal->description }}
                                                @if ($journal->reference)
                                                    <br><small class="text-muted">Ref: {{ $journal->reference }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $symbol }}{{ number_format((float) $journal->amount, 2) }}</td>
                                            <td>
                                                @if ($journal->isCredit())
                                                    {{ $symbol }}{{ number_format($applied, 2) }}
                                                    @if (!$journal->isVoided() && $open > 0)
                                                        <br><span class="badge bg-warning">{{ $symbol }}{{ number_format($open, 2) }} open</span>
                                                    @endif
                                                @else
                                                    <small class="text-muted">n/a</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($journal->isVoided())
                                                    <span class="badge bg-danger">Voided</span>
                                                    <br><small class="text-muted">{{ $journal->void_reason }}</small>
                                                @else
                                                    <span class="badge bg-success">Posted</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if (!$journal->isVoided())
                                                    @if ($journal->isCredit())
                                                        <a href="#" wire:click.prevent="showAllocate({{ $journal->id }})" class="btn btn-default btn-sm"><i class="fa fa-link"></i> Apply</a>
                                                    @endif
                                                    <a href="#" wire:click.prevent="showVoid({{ $journal->id }})" class="btn btn-default btn-sm text-danger"><i class="fa fa-ban"></i> Void</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="10" class="text-center">No debtors journals found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                            {{ $journals->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Capture ─────────────────────────────────────────────────────── --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="djCreateModal" tabindex="-1" role="dialog" aria-labelledby="djCreateLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="djCreateLabel"><i class="fa fa-book"></i> New Debtors Journal
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    </h4>
                </div>
                <form wire:submit.prevent="store()">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Customer<span class="required" style="color: red">*</span></label>
                                    <select wire:model="customer_id" class="form-control" required>
                                        <option value="">Select Customer</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('customer_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Type<span class="required" style="color: red">*</span></label>
                                    <select wire:model="type" class="form-control" required>
                                        <option value="credit">Credit (reduce balance)</option>
                                        <option value="debit">Debit (increase balance)</option>
                                    </select>
                                    @error('type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Date<span class="required" style="color: red">*</span></label>
                                    <input type="date" wire:model.defer="date" class="form-control" required>
                                    @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Currency<span class="required" style="color: red">*</span></label>
                                    <select wire:model="currency_id" class="form-control" required>
                                        <option value="">Select Currency</option>
                                        @foreach ($currencies as $currency)
                                            <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                @if ($isForeignCurrency)
                                    <div class="form-group">
                                        <label>Exchange Rate<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" min="0" wire:model.defer="exchange_rate" class="form-control" placeholder="Rate to base currency">
                                        @error('exchange_rate') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Contra Account<span class="required" style="color: red">*</span></label>
                                    <select wire:model.defer="account_id" class="form-control" required>
                                        <option value="">Select Account</option>
                                        @foreach ($account_groups as $group)
                                            <optgroup label="{{ $group->name }}">
                                                @foreach ($group->account_types as $account_type)
                                                    @foreach ($account_type->accounts as $account)
                                                        <option value="{{ $account->id }}">{{ $account->name }}{{ $account->code ? ' (' . $account->code . ')' : '' }}</option>
                                                    @endforeach
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">
                                        {{ $type === 'credit' ? 'Debited' : 'Credited' }}. For example Bad Debts, Discount Allowed or Opening Balance Equity.
                                    </small>
                                    @error('account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Amount<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" min="0" wire:model.lazy="amount" class="form-control" required>
                                    @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Reference</label>
                                    <input type="text" wire:model.defer="reference" class="form-control" placeholder="Optional">
                                    @error('reference') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Description<span class="required" style="color: red">*</span></label>
                                    <input type="text" wire:model.defer="description" class="form-control" placeholder="Shown on the customer statement" required>
                                    @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        @if ($type === 'credit' && $customer_id && $currency_id)
                            <h5>Apply to open invoices <small class="text-muted">(optional, and can also be done later)</small></h5>
                            @error('capture_allocations') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            <table class="table table-sm table-bordered">
                                <thead><tr><th>Invoice#</th><th>Date</th><th>Total</th><th>Balance</th><th style="width: 25%">Apply</th></tr></thead>
                                <tbody>
                                    @forelse ($capture_invoices as $invoice)
                                        <tr wire:key="dj-cap-{{ $invoice['id'] }}">
                                            <td>{{ $invoice['invoice_number'] }}</td>
                                            <td>{{ $invoice['date'] }}</td>
                                            <td>{{ number_format((float) $invoice['total'], 2) }}</td>
                                            <td>{{ number_format((float) $invoice['balance'], 2) }}</td>
                                            <td><input type="number" step="any" min="0" max="{{ (float) $invoice['balance'] }}" wire:model.lazy="capture_allocations.{{ $invoice['id'] }}" class="form-control input-sm"></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5">This customer has no open approved invoices in this currency.</td></tr>
                                    @endforelse
                                </tbody>
                                @if (count($capture_invoices) > 0)
                                    <tfoot>
                                        <tr>
                                            <th colspan="4" class="text-right">Applied / unapplied</th>
                                            <th>{{ number_format($captureAllocated, 2) }} / {{ number_format(max(0, (float) $amount - $captureAllocated), 2) }}</th>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i> Post Journal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Allocate ────────────────────────────────────────────────────── --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="djAllocateModal" tabindex="-1" role="dialog" aria-labelledby="djAllocateLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="djAllocateLabel"><i class="fa fa-link"></i> Apply Journal Credit {{ $selected_journal ? $selected_journal->journal_number : '' }}
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    </h4>
                </div>
                <div class="modal-body">
                    @if ($selected_journal && $selected_journal->isCredit())
                        @php $symbol = $selected_journal->currency ? $selected_journal->currency->symbol : ''; @endphp
                        <p>
                            <strong>{{ $selected_journal->customer ? $selected_journal->customer->name : '' }}</strong>:
                            {{ $symbol }}{{ number_format((float) $selected_journal->amount, 2) }} credit,
                            {{ $symbol }}{{ number_format($selected_journal->unallocatedAmount(), 2) }} still unapplied.
                        </p>

                        <h5>Applied to</h5>
                        <table class="table table-sm table-bordered">
                            <thead><tr><th>Invoice#</th><th>Applied</th><th></th></tr></thead>
                            <tbody>
                                @forelse ($allocations as $allocation)
                                    <tr wire:key="dj-alloc-{{ $allocation->id }}">
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

                        @if ($selected_journal->unallocatedAmount() > 0)
                            <h5>Apply to invoice</h5>
                            <form wire:submit.prevent="allocate()">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label>Open invoices ({{ $selected_journal->currency ? $selected_journal->currency->name : '' }})<span class="required" style="color: red">*</span></label>
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

    {{-- ── Void ────────────────────────────────────────────────────────── --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="djVoidModal" tabindex="-1" role="dialog" aria-labelledby="djVoidLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="djVoidLabel"><i class="fa fa-ban"></i> Void {{ $selected_journal ? $selected_journal->journal_number : '' }}
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    </h4>
                </div>
                <form wire:submit.prevent="void()">
                    <div class="modal-body">
                        @if ($selected_journal)
                            <p>
                                This posts a reversing journal entry
                                @if ($selected_journal->invoice_payments->count() > 0)
                                    and releases its {{ $selected_journal->invoice_payments->count() }} invoice allocation(s) back onto those invoices' balances
                                @endif
                                . The journal is kept, marked as voided.
                            </p>
                        @endif
                        <div class="form-group">
                            <label>Reason<span class="required" style="color: red">*</span></label>
                            <textarea wire:model.defer="void_reason" class="form-control" rows="3" required></textarea>
                            @error('void_reason') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
                        <button type="submit" class="btn btn-danger btn-wide btn-rounded"><i class="fa fa-ban"></i> Void</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
