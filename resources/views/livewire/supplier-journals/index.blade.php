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
                                <a href="#" wire:click.prevent="showCreate()" class="btn btn-default pull-right"><i class="fa fa-plus-square-o"></i> New Suppliers Journal</a>
                                <h5>Suppliers Journal</h5>
                                <small class="text-muted">
                                    Manual adjustments to a supplier's account: opening balances, discounts received, balances written back, interest charged and corrections.
                                    A <strong>credit</strong> increases what we owe the supplier (DR contra account, CR Accounts Payable).
                                    A <strong>debit</strong> reduces it (DR Accounts Payable, CR contra account) and can be applied to the supplier's open bills.
                                    Every journal shows on the supplier statement and in aged payables. Posted journals can't be edited. Void one to reverse it, then capture a new one.
                                </small>
                            </div>
                        </div>
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                            <div class="row" style="margin-bottom: 10px">
                                <div class="col-md-3">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search journal#, ref, supplier...">
                                </div>
                                <div class="col-md-3">
                                    <select wire:model="vendor_filter" class="form-control">
                                        <option value="">All Suppliers</option>
                                        @foreach ($vendors as $vendor)
                                            <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
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
                                        <th>Supplier</th>
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
                                        <tr wire:key="sj-{{ $journal->id }}">
                                            <td>{{ $journal->journal_number }}</td>
                                            <td>{{ $journal->date ? $journal->date->format('Y-m-d') : '' }}</td>
                                            <td>{{ $journal->vendor ? $journal->vendor->name : '' }}</td>
                                            <td>
                                                @if ($journal->isDebit())
                                                    <span class="badge bg-info">Debit</span>
                                                @else
                                                    <span class="badge bg-primary">Credit</span>
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
                                                @if ($journal->isDebit())
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
                                                    @if ($journal->isDebit())
                                                        <a href="#" wire:click.prevent="showAllocate({{ $journal->id }})" class="btn btn-default btn-sm"><i class="fa fa-link"></i> Apply</a>
                                                    @endif
                                                    <a href="#" wire:click.prevent="showVoid({{ $journal->id }})" class="btn btn-default btn-sm text-danger"><i class="fa fa-ban"></i> Void</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="10" class="text-center">No suppliers journals found.</td></tr>
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="sjCreateModal" tabindex="-1" role="dialog" aria-labelledby="sjCreateLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="sjCreateLabel"><i class="fa fa-book"></i> New Suppliers Journal
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    </h4>
                </div>
                <form wire:submit.prevent="store()">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Supplier<span class="required" style="color: red">*</span></label>
                                    <select wire:model="vendor_id" class="form-control" required>
                                        <option value="">Select Supplier</option>
                                        @foreach ($vendors as $vendor)
                                            <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('vendor_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Type<span class="required" style="color: red">*</span></label>
                                    <select wire:model="type" class="form-control" required>
                                        <option value="debit">Debit (reduce what we owe)</option>
                                        <option value="credit">Credit (increase what we owe)</option>
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
                                        {{ $type === 'credit' ? 'Debited' : 'Credited' }}. For example Discount Received, Opening Balance Equity or the expense being corrected.
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
                                    <input type="text" wire:model.defer="description" class="form-control" placeholder="Shown on the supplier statement" required>
                                    @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        @if ($type === 'debit' && $vendor_id && $currency_id)
                            <h5>Apply to open bills <small class="text-muted">(optional, and can also be done later)</small></h5>
                            @error('capture_allocations') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            <table class="table table-sm table-bordered">
                                <thead><tr><th>Bill#</th><th>Date</th><th>Total</th><th>Balance</th><th style="width: 25%">Apply</th></tr></thead>
                                <tbody>
                                    @forelse ($capture_bills as $bill)
                                        <tr wire:key="sj-cap-{{ $bill['id'] }}">
                                            <td>{{ $bill['bill_number'] }}</td>
                                            <td>{{ $bill['bill_date'] }}</td>
                                            <td>{{ number_format((float) $bill['total'], 2) }}</td>
                                            <td>{{ number_format((float) $bill['balance'], 2) }}</td>
                                            <td><input type="number" step="any" min="0" max="{{ (float) $bill['balance'] }}" wire:model.lazy="capture_allocations.{{ $bill['id'] }}" class="form-control input-sm"></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5">This supplier has no open approved bills in this currency.</td></tr>
                                    @endforelse
                                </tbody>
                                @if (count($capture_bills) > 0)
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="sjAllocateModal" tabindex="-1" role="dialog" aria-labelledby="sjAllocateLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="sjAllocateLabel"><i class="fa fa-link"></i> Apply Journal Debit {{ $selected_journal ? $selected_journal->journal_number : '' }}
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    </h4>
                </div>
                <div class="modal-body">
                    @if ($selected_journal && $selected_journal->isDebit())
                        @php $symbol = $selected_journal->currency ? $selected_journal->currency->symbol : ''; @endphp
                        <p>
                            <strong>{{ $selected_journal->vendor ? $selected_journal->vendor->name : '' }}</strong>:
                            {{ $symbol }}{{ number_format((float) $selected_journal->amount, 2) }} debit,
                            {{ $symbol }}{{ number_format($selected_journal->unallocatedAmount(), 2) }} still unapplied.
                        </p>

                        <h5>Applied to</h5>
                        <table class="table table-sm table-bordered">
                            <thead><tr><th>Bill#</th><th>Applied</th><th></th></tr></thead>
                            <tbody>
                                @forelse ($allocations as $allocation)
                                    <tr wire:key="sj-alloc-{{ $allocation->id }}">
                                        <td>
                                            @if ($allocation->bill)
                                                <a href="{{ route('bills.show', $allocation->bill->id) }}" target="_blank" style="color: blue">{{ $allocation->bill->bill_number }}</a>
                                            @endif
                                        </td>
                                        <td>{{ $symbol }}{{ number_format((float) $allocation->amount, 2) }}</td>
                                        <td><a href="#" wire:click.prevent="removeAllocation({{ $allocation->id }})" class="text-danger"><i class="fa fa-unlink"></i> Release</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3">Not applied to any bill yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>

                        @if ($selected_journal->unallocatedAmount() > 0)
                            <h5>Apply to bill</h5>
                            <form wire:submit.prevent="allocate()">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label>Open bills ({{ $selected_journal->currency ? $selected_journal->currency->name : '' }})<span class="required" style="color: red">*</span></label>
                                            <select wire:model="selectedBill" class="form-control">
                                                <option value="">Select Bill</option>
                                                @foreach ($open_bills as $bill)
                                                    <option value="{{ $bill->id }}">{{ $bill->bill_number }} | {{ $bill->bill_date }} | Total {{ $symbol }}{{ number_format((float) $bill->total, 2) }} | Balance {{ $symbol }}{{ number_format((float) $bill->balance, 2) }}</option>
                                                @endforeach
                                            </select>
                                            @error('selectedBill') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="sjVoidModal" tabindex="-1" role="dialog" aria-labelledby="sjVoidLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="sjVoidLabel"><i class="fa fa-ban"></i> Void {{ $selected_journal ? $selected_journal->journal_number : '' }}
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    </h4>
                </div>
                <form wire:submit.prevent="void()">
                    <div class="modal-body">
                        @if ($selected_journal)
                            <p>
                                This posts a reversing journal entry
                                @if ($selected_journal->bill_payments->count() > 0)
                                    and releases its {{ $selected_journal->bill_payments->count() }} bill allocation(s) back onto those bills' balances
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
