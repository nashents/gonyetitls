{{--
    "Funded By: Company / Customer" columns for a trip fuel order - see CustomerFuelSupplyService.
    Outputs bare .col-md-4 columns so the caller can put them in its own row
    (Funded By first, then Category, then the funding customer).
    Params: $part ('funding' | 'customer'), $flag / $customerField (wire:model names),
            $customers, $selectable (false for Bulk Buy stations / truck-to-truck fuel),
            $col (optional column class, default col-md-4).
--}}
@php
    $customerFunded = (string) data_get($this, $flag) === '1';
    $col = $col ?? 'col-md-4';
@endphp
@if ($part === 'funding')
    <div class="{{ $col }}">
        <div class="form-group">
            <label>Funded By<span class="required" style="color: red">*</span></label>
            @if ($selectable)
                <select wire:model="{{ $flag }}" class="form-control">
                    <option value="0">Company</option>
                    <option value="1">Customer (supplied as part-payment)</option>
                </select>
                @if ($customerFunded)
                    <small class="text-muted">No supplier bill - credited to the customer and applied against the trip's invoice (invoice stays at full amount).</small>
                @endif
            @else
                <input type="text" class="form-control" value="Company" disabled>
                <small class="text-muted">Bulk Buy / truck fuel is our own stock. Customer fuel delivered into a tank is recorded on the top-up.</small>
            @endif
        </div>
    </div>
@elseif ($part === 'customer' && $selectable && $customerFunded)
    <div class="{{ $col }}">
        <div class="form-group">
            <label>Funding Customer<span class="required" style="color: red">*</span></label>
            <select wire:model.debounce.300ms="{{ $customerField }}" class="form-control" required>
                <option value="">Select Customer</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                @endforeach
            </select>
            @error($customerField) <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    {{-- Supplier bill already settled - see ConfirmsFuelFundingReversal. --}}
    @php $fundingReversal = data_get($this, 'fundingReversal'); @endphp
    @if ($selectable && $customerFunded && !empty($fundingReversal))
        <div class="col-md-12">
            <div class="alert alert-warning">
                <strong><i class="fa fa-exclamation-triangle"></i> This fuel's supplier bill is already settled.</strong>
                To change it to customer funded, the following will be reversed first:
                <ul style="margin-top:8px;">
                    @foreach ($fundingReversal['items'] as $item)
                        <li>
                            <strong>{{ $item['title'] }}</strong><br>
                            {{ $item['action'] }}
                            @if (!empty($item['others']))
                                <br><span style="color:#a94442">It also settles {{ implode(', ', $item['others']) }} - {{ count($item['others']) > 1 ? 'those bills' : 'that bill' }} will reopen as unpaid.</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
                @if (!empty($fundingReversal['blocked']))
                    <p style="color:#a94442"><strong>{{ $fundingReversal['blocked'] }}</strong></p>
                    <button type="button" class="btn btn-default btn-sm" wire:click="cancelFundingReversal">Close</button>
                @else
                    <p>The bill is then settled by the customer and applied against the trip's invoice.</p>
                    <button type="button" class="btn btn-danger btn-sm" wire:click="confirmFundingReversal" wire:loading.attr="disabled"><i class="fa fa-undo"></i> Reverse and switch to Customer</button>
                    <button type="button" class="btn btn-default btn-sm" wire:click="cancelFundingReversal">Cancel</button>
                @endif
            </div>
        </div>
    @endif
@endif
