{{-- Trip expenses for Bulk Schedule: each line goes on every trip, or only on the ticked trucks. --}}
<div class="panel panel-default" style="padding: 10px; margin-top: 10px;">
    <div style="margin-bottom: 8px;">
        <strong><i class="fa fa-money"></i> Trip Expenses</strong>
        <small class="text-muted">Each line is added to every trip, or untick "All trips" to choose the trucks. Same as ticking an expense on Trips &gt; Create.</small>
        <a href="#" wire:click.prevent="addExpenseLine" class="btn btn-default border-primary btn-rounded btn-xs" style="float: right;"><i class="fa fa-plus"></i> Add Expense</a>
    </div>

    @if (count($expenseLines) === 0)
        <small class="text-muted">No expenses. Click <strong>Add Expense</strong> to add tolls, permits, allowances etc. to the trips.</small>
    @else
        <div style="overflow-x: auto;">
            <table class="table table-bordered table-sm" style="margin-bottom: 0;">
                <thead class="thead-light">
                    <tr>
                        <th style="min-width: 170px">Expense<span style="color: red">*</span></th>
                        <th style="min-width: 130px">Funded By</th>
                        <th style="min-width: 120px">Category<span style="color: red">*</span></th>
                        <th style="min-width: 130px">Payment Method</th>
                        <th style="min-width: 150px">Currency<span style="color: red">*</span></th>
                        <th style="min-width: 160px">Vendor<span style="color: red">*</span></th>
                        <th style="width: 110px">Amount<span style="color: red">*</span></th>
                        <th style="min-width: 200px">Apply To</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($expenseLines as $k => $line)
                        <tr wire:key="bulk-trip-expense-{{ $line['key'] }}">
                            <td>
                                <select wire:model="expenseLines.{{ $k }}.expense_id" class="form-control input-sm">
                                    <option value="">Select expense</option>
                                    @foreach ($expenses as $expense)
                                        <option value="{{ $expense->id }}">{{ $expense->name }}</option>
                                    @endforeach
                                </select>
                                @error('expenseLines.'.$k.'.expense_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </td>
                            <td>
                                <select wire:model="expenseLines.{{ $k }}.funded_by" class="form-control input-sm">
                                    <option value="0">Company</option>
                                    <option value="1">Customer</option>
                                    <option value="2">Transporter (record only)</option>
                                </select>
                                @if ($line['funded_by'] === '1')
                                    <small class="text-muted">No supplier bill - credited to each trip's customer.</small>
                                @elseif ($line['funded_by'] === '2')
                                    <small class="text-muted">Recorded on the trip only.</small>
                                @endif
                            </td>
                            <td>
                                <select wire:model.defer="expenseLines.{{ $k }}.category" class="form-control input-sm" @if ($line['funded_by'] === '2') disabled @endif>
                                    <option value="Self">Self</option>
                                    <option value="Customer">Customer</option>
                                    <option value="Transporter">Transporter</option>
                                </select>
                                @error('expenseLines.'.$k.'.category') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </td>
                            <td>
                                <select wire:model.defer="expenseLines.{{ $k }}.payment_method_id" class="form-control input-sm">
                                    <option value="">None</option>
                                    @foreach ($paymentMethods as $method)
                                        <option value="{{ $method->id }}">{{ $method->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select wire:model="expenseLines.{{ $k }}.currency_id" class="form-control input-sm">
                                    <option value="">Select</option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                    @endforeach
                                </select>
                                @error('expenseLines.'.$k.'.currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                @if ($this->isForeign($line['currency_id']))
                                    <input type="number" step="any" min="0" wire:model.lazy="expenseLines.{{ $k }}.exchange_rate" class="form-control input-sm" placeholder="Conversion rate" style="margin-top: 4px;">
                                    @error('expenseLines.'.$k.'.exchange_rate') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                @endif
                            </td>
                            <td>
                                <select wire:model.defer="expenseLines.{{ $k }}.vendor_id" class="form-control input-sm">
                                    <option value="">Select vendor</option>
                                    @foreach ($vendors as $vendor)
                                        <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                    @endforeach
                                </select>
                                @error('expenseLines.'.$k.'.vendor_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </td>
                            <td>
                                <input type="number" step="any" min="0" wire:model.lazy="expenseLines.{{ $k }}.amount" class="form-control input-sm">
                                @error('expenseLines.'.$k.'.amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </td>
                            <td>
                                <label style="font-weight: normal; margin-bottom: 2px;"><input type="checkbox" wire:model="expenseLines.{{ $k }}.all_trips"> All trips ({{ count($rows) }})</label>
                                @if (! $line['all_trips'])
                                    <div style="max-height: 90px; overflow-y: auto;">
                                        @forelse ($rows as $row)
                                            <label wire:key="bulk-trip-expense-{{ $line['key'] }}-{{ $row['horse_id'] }}" style="display: block; font-weight: normal; margin: 0;">
                                                <input type="checkbox" wire:model.defer="expenseLines.{{ $k }}.horse_ids" value="{{ $row['horse_id'] }}"> {{ $row['label'] }}
                                            </label>
                                        @empty
                                            <small class="text-muted">Add trucks first.</small>
                                        @endforelse
                                    </div>
                                    @error('expenseLines.'.$k.'.horse_ids') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                @endif
                            </td>
                            <td>
                                <a href="#" wire:click.prevent="removeExpenseLine({{ $k }})" class="text-danger" title="Remove expense"><i class="fa fa-times"></i></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
