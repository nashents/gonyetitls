<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Free Day Policies</h5>
                                <small style="color: green">Leave "Shipping Line" blank for a generic default that applies when no line-specific policy exists.</small>
                            </div>
                        </div>
                        <div class="panel-body">
                            <form wire:submit.prevent="savePolicy" class="mb-20">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Charge Type</label>
                                            <select class="form-control" wire:model="policy_charge_type">
                                                <option value="">Select</option>
                                                @foreach ($chargeTypes as $code => $label)
                                                    <option value="{{ $code }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            @error('policy_charge_type') <span class="text-danger error">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Shipping Line</label>
                                            <select class="form-control" wire:model="policy_vendor_id">
                                                <option value="">Generic Default</option>
                                                @foreach ($vendors as $vendor)
                                                    <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Free Days</label>
                                            <input type="number" class="form-control" wire:model="policy_free_days">
                                            @error('policy_free_days') <span class="text-danger error">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <label>&nbsp;</label>
                                        <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-save"></i></button>
                                    </div>
                                </div>
                            </form>

                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr><th>Charge Type</th><th>Shipping Line</th><th>Free Days</th><th></th></tr>
                                </thead>
                                <tbody>
                                    @forelse ($policies as $policy)
                                        <tr>
                                            <td>{{ $chargeTypes[$policy->charge_type] ?? $policy->charge_type }}</td>
                                            <td>{{ $policy->shipping_line_vendor?->name ?? 'Generic Default' }}</td>
                                            <td>{{ $policy->free_days }}</td>
                                            <td>
                                                <a href="#" wire:click.prevent="editPolicy({{ $policy->id }})" class="btn btn-xs btn-default"><i class="fa fa-edit"></i></a>
                                                <a href="#" wire:click.prevent="deletePolicy({{ $policy->id }})" wire:confirm="Remove this free day policy?" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center">No free day policies configured yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Rate Tiers</h5>
                                <small style="color: green">Leave "Day To" blank for the final, open-ended tier.</small>
                            </div>
                        </div>
                        <div class="panel-body">
                            <form wire:submit.prevent="saveTier" class="mb-20">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Charge Type</label>
                                            <select class="form-control" wire:model="tier_charge_type">
                                                <option value="">Select</option>
                                                @foreach ($chargeTypes as $code => $label)
                                                    <option value="{{ $code }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            @error('tier_charge_type') <span class="text-danger error">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Shipping Line</label>
                                            <select class="form-control" wire:model="tier_vendor_id">
                                                <option value="">Generic Default</option>
                                                @foreach ($vendors as $vendor)
                                                    <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Day From</label>
                                            <input type="number" class="form-control" wire:model="tier_day_from">
                                            @error('tier_day_from') <span class="text-danger error">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Day To</label>
                                            <input type="number" class="form-control" wire:model="tier_day_to">
                                            @error('tier_day_to') <span class="text-danger error">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Rate/Day</label>
                                            <input type="number" step="any" class="form-control" wire:model="tier_rate">
                                            @error('tier_rate') <span class="text-danger error">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Currency</label>
                                            <select class="form-control" wire:model="tier_currency_id">
                                                <option value="">Select</option>
                                                @foreach ($currencies as $currency)
                                                    <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Tier</button>
                            </form>

                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr><th>Charge Type</th><th>Shipping Line</th><th>Days</th><th>Rate</th><th></th></tr>
                                </thead>
                                <tbody>
                                    @forelse ($tiers as $tier)
                                        <tr>
                                            <td>{{ $chargeTypes[$tier->charge_type] ?? $tier->charge_type }}</td>
                                            <td>{{ $tier->shipping_line_vendor?->name ?? 'Generic Default' }}</td>
                                            <td>{{ $tier->day_from }}{{ $tier->day_to ? ' - '.$tier->day_to : '+' }}</td>
                                            <td>{{ $tier->currency?->symbol }}{{ number_format($tier->rate, 2) }}</td>
                                            <td>
                                                <a href="#" wire:click.prevent="editTier({{ $tier->id }})" class="btn btn-xs btn-default"><i class="fa fa-edit"></i></a>
                                                <a href="#" wire:click.prevent="deleteTier({{ $tier->id }})" wire:confirm="Remove this rate tier?" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center">No rate tiers configured yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
