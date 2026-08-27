<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Freight Rate Cards</h5>
                                <small style="color: green">Buying rates (paid to suppliers) and Selling rates (billed to customers), by lane/mode/commodity/container.</small>
                            </div>
                        </div>
                        <div class="panel-body">
                            <form wire:submit.prevent="save" class="mb-20">
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Direction</label>
                                            <select class="form-control" wire:model="direction">
                                                @foreach ($directions as $code => $label)
                                                    <option value="{{ $code }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    @if ($direction === 'buy')
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Vendor</label>
                                                <select class="form-control" wire:model="vendor_id">
                                                    <option value="">Any</option>
                                                    @foreach ($vendors as $vendor)
                                                        <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    @else
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Customer</label>
                                                <select class="form-control" wire:model="customer_id">
                                                    <option value="">Any</option>
                                                    @foreach ($customers as $customer)
                                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Charge Type</label>
                                            <select class="form-control" wire:model="charge_type_id">
                                                <option value="">Any</option>
                                                @foreach ($chargeTypes as $type)
                                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Mode</label>
                                            <select class="form-control" wire:model="mode">
                                                <option value="">Any</option>
                                                <option value="sea">Sea</option>
                                                <option value="air">Air</option>
                                                <option value="road">Road</option>
                                                <option value="rail">Rail</option>
                                                <option value="courier">Courier</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Container Type</label>
                                            <input type="text" class="form-control" wire:model="container_type">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Origin</label>
                                            <select class="form-control" wire:model="origin_location_id">
                                                <option value="">Any</option>
                                                @foreach ($locations as $location)
                                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Destination</label>
                                            <select class="form-control" wire:model="destination_location_id">
                                                <option value="">Any</option>
                                                @foreach ($locations as $location)
                                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Commodity</label>
                                            <select class="form-control" wire:model="cargo_id">
                                                <option value="">Any</option>
                                                @foreach ($cargos as $cargo)
                                                    <option value="{{ $cargo->id }}">{{ $cargo->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Currency</label>
                                            <select class="form-control" wire:model="currency_id">
                                                <option value="">Select</option>
                                                @foreach ($currencies as $currency)
                                                    <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Rate Basis</label>
                                            <select class="form-control" wire:model="rate_basis">
                                                <option value="">Select</option>
                                                @foreach ($rateBases as $code => $label)
                                                    <option value="{{ $code }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Rate</label>
                                            <input type="number" step="any" class="form-control" wire:model="rate">
                                        </div>
                                    </div>
                                    @if ($direction === 'sell')
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Markup Type</label>
                                                <select class="form-control" wire:model="markup_type">
                                                    <option value="">None</option>
                                                    @foreach ($markupTypes as $code => $label)
                                                        <option value="{{ $code }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Markup Value</label>
                                                <input type="number" step="any" class="form-control" wire:model="markup_value">
                                            </div>
                                        </div>
                                    @endif
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Effective From</label>
                                            <input type="date" class="form-control" wire:model="effective_from">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Effective To</label>
                                            <input type="date" class="form-control" wire:model="effective_to">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-10">
                                        <div class="form-group">
                                            <label>Notes</label>
                                            <input type="text" class="form-control" wire:model="notes">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label>&nbsp;</label>
                                        <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-save"></i> Save</button>
                                    </div>
                                </div>
                            </form>

                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Direction</th><th>Party</th><th>Charge Type</th><th>Mode</th><th>Rate</th><th>Active</th><th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($rateCards as $card)
                                        <tr>
                                            <td>{{ $directions[$card->direction] ?? $card->direction }}</td>
                                            <td>{{ $card->vendor?->name ?? $card->customer?->name ?? 'Any' }}</td>
                                            <td>{{ $card->charge_type?->name ?? 'Any' }}</td>
                                            <td>{{ ucfirst($card->mode ?? 'Any') }}</td>
                                            <td>{{ $card->currency?->symbol }}{{ $card->rate !== null ? number_format($card->rate, 2) : '—' }} {{ $card->rate_basis }}</td>
                                            <td>{{ $card->is_active ? 'Yes' : 'No' }}</td>
                                            <td>
                                                <a href="#" wire:click.prevent="edit({{ $card->id }})" class="btn btn-xs btn-default"><i class="fa fa-edit"></i></a>
                                                <a href="#" wire:click.prevent="delete({{ $card->id }})" wire:confirm="Remove this rate card?" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7" class="text-center">No rate cards configured yet.</td></tr>
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
