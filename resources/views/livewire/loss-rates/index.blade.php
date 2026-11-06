<div>
    <style>
        .lr-matrix { border-collapse: separate; border-spacing: 0; }
        .lr-matrix th, .lr-matrix td { padding: 4px 6px !important; vertical-align: middle !important; white-space: nowrap; }
        .lr-matrix .lr-sticky { position: sticky; left: 0; z-index: 2; background: #fff; min-width: 180px; }
        .lr-matrix thead th { position: sticky; top: 0; z-index: 3; background: #f5f5f5; }
        .lr-matrix thead th.lr-sticky { z-index: 4; }
        .lr-matrix input.form-control { width: 90px; height: 28px; padding: 2px 6px; }
        .lr-matrix .lr-set input { background: #e8f4fd; border-color: #5bc0de; }
        .lr-matrix .lr-gap input { border-color: #d9534f; }
        .lr-matrix .lr-defaults td, .lr-matrix .lr-defaults th { background: #fcf8e3; }
        .lr-legend { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 10px;
                     background: #f9f9f9; border: 1px solid #e5e5e5; border-radius: 4px; padding: 8px 12px; margin-bottom: 10px; font-size: 13px; color: #333; }
        .lr-legend .lr-order { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; }
        .lr-legend .lr-step { background: #fff; border: 1px solid #ccc; border-radius: 3px; padding: 2px 8px; font-weight: 600; }
        .lr-legend .lr-arrow { color: #888; }
        .lr-legend .lr-keys { display: flex; flex-wrap: wrap; gap: 14px; align-items: center; }
        .lr-swatch { display: inline-block; width: 16px; height: 16px; vertical-align: middle; border: 2px solid #ccc; border-radius: 3px; margin-right: 4px; background: #fff; }
        .lr-status { display: inline-block; padding: 4px 10px; border-radius: 3px; font-weight: 600; font-size: 13px; color: #fff; }
        .lr-status.ok { background: #2e7d32; }
        .lr-status.bad { background: #c62828; }
    </style>
    @php
        $fmtPct = fn ($v) => is_numeric($v) ? ($unit === 'fraction' ? rtrim(rtrim(number_format($v / 100, 8, '.', ''), '0'), '.') : ($v + 0) . '%') : null;
        $fmtRate = fn ($v) => is_numeric($v) ? number_format($v, 2) : null;
    @endphp
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div>@include('includes.messages')</div>

                            {{-- Business Settings defaults --}}
                            <div class="row" style="margin-bottom:10px;">
                                @foreach ([
                                    ['Default Allowable Loss', $fmtPct($company?->allowable_loss_percentage)],
                                    ["Default Rate – Own Fleet ({$ownFleetName})", $fmtRate($company?->own_fleet_chargeable_loss_rate)],
                                    ['Default Rate – Subcontractors', $fmtRate($company?->subcontractor_chargeable_loss_rate)],
                                ] as [$label, $value])
                                    <div class="col-md-3 col-sm-6">
                                        <div style="border:1px solid #e5e5e5; border-radius:4px; padding:8px 12px;">
                                            <small class="text-muted">{{ $label }}</small><br>
                                            <strong>{!! $value ? e($value) : '<span class="text-danger">not set</span>' !!}</strong>
                                        </div>
                                    </div>
                                @endforeach
                                <div class="col-md-3 col-sm-6" style="padding-top:12px;">
                                    @if ($company)
                                        <a href="{{ route('company-profile', $company->id) }}" class="btn btn-default btn-sm btn-rounded"><i class="fa fa-cog"></i> Edit Defaults</a>
                                    @endif
                                    <button type="button" wire:click="export" class="btn btn-default btn-sm btn-rounded"><i class="fa fa-file-excel-o"></i> Export Excel</button>
                                </div>
                            </div>

                            <ul class="nav nav-tabs" style="margin-bottom:10px;">
                                <li class="{{ $tab === 'allowances' ? 'active' : '' }}"><a href="#" wire:click.prevent="setTab('allowances')"><i class="fas fa-percentage"></i> Allowable Loss (Customer &times; Cargo)</a></li>
                                <li class="{{ $tab === 'rates' ? 'active' : '' }}"><a href="#" wire:click.prevent="setTab('rates')"><i class="fa fa-money"></i> Chargeable Rates (Cargo &times; Own Fleet / Subbie)</a></li>
                            </ul>

                            <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
                                <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                                    <input type="text" class="form-control" style="max-width:220px;" wire:model.debounce.300ms="search"
                                        placeholder="{{ $tab === 'rates' ? 'Search cargos...' : 'Search customers...' }}" />
                                    <select class="form-control" style="max-width:160px;" wire:model="cargoType">
                                        <option value="">All cargo types</option>
                                        @foreach ($cargoTypes as $type)
                                            <option value="{{ $type }}">{{ $type }}</option>
                                        @endforeach
                                    </select>
                                    @if ($tab === 'allowances')
                                        <input type="text" class="form-control" style="max-width:180px;" wire:model.debounce.300ms="cargoSearch" placeholder="Filter cargo columns..." />
                                    @endif
                                    <label style="margin:0; font-weight:normal;">
                                        <input type="checkbox" wire:model="gapsOnly"> Only rows with gaps
                                    </label>
                                </div>
                                @if ($tab === 'allowances')
                                    <div>
                                        <small class="text-muted">Enter as:</small>
                                        <label style="font-weight:normal; margin:0 6px;"><input type="radio" wire:model="unit" value="percent"> % (0.3)</label>
                                        <label style="font-weight:normal; margin:0;"><input type="radio" wire:model="unit" value="fraction"> fraction (0.003)</label>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="panel-body p-20">
                            @if ($tab === 'allowances')
                                <div class="lr-legend">
                                    <div>
                                        <div class="lr-order">
                                            <span>A trip uses the first value set:</span>
                                            <span class="lr-step">Customer + Cargo cell</span><span class="lr-arrow">&rarr;</span>
                                            <span class="lr-step">Customer's All cargos</span><span class="lr-arrow">&rarr;</span>
                                            <span class="lr-step">Cargo default row</span><span class="lr-arrow">&rarr;</span>
                                            <span class="lr-step">Business default</span>
                                        </div>
                                        <div class="lr-keys" style="margin-top:6px;">
                                            <span><span class="lr-swatch"></span>Blank: shows the inherited value in grey</span>
                                            <span><span class="lr-swatch" style="background:#e8f4fd; border-color:#5bc0de;"></span>Set for this customer + cargo</span>
                                            <span><span class="lr-swatch" style="border-color:#d9534f;"></span>Nothing applies (loss can't be measured)</span>
                                        </div>
                                    </div>
                                    @if ($totalGaps)
                                        <span class="lr-status bad"><i class="fa fa-exclamation-triangle"></i> {{ $totalGaps }} gap(s) across {{ $customersWithGaps }} customer(s)</span>
                                    @else
                                        <span class="lr-status ok"><i class="fa fa-check"></i> No gaps</span>
                                    @endif
                                </div>
                                <div style="overflow:auto; max-height:70vh;">
                                    <table class="table table-bordered table-sm lr-matrix">
                                        <thead>
                                            <tr>
                                                <th class="lr-sticky">Customer</th>
                                                <th title="The customer's % for every cargo without its own agreement">All cargos</th>
                                                @foreach ($cargos as $cargo)
                                                    <th title="{{ $cargo->type }}">{{ $cargo->name }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="lr-defaults" wire:key="lr-defaults">
                                                <th class="lr-sticky">Cargo default <small class="text-muted">(any customer)</small></th>
                                                <td><small class="text-muted">Business: {{ $fmtPct($businessDefault) ?? 'not set' }}</small></td>
                                                @foreach ($cargos as $cargo)
                                                    <td wire:key="lr-default-{{ $cargo->id }}">
                                                        <input type="number" step="any" min="0" class="form-control"
                                                            wire:model.defer="cargoDefaults.{{ $cargo->id }}"
                                                            placeholder="{{ $fmtPct($businessDefault) ?? '' }}">
                                                    </td>
                                                @endforeach
                                            </tr>
                                            @forelse ($customers as $customer)
                                                @php $m = $matrix[$customer->id]; @endphp
                                                <tr wire:key="lr-customer-{{ $customer->id }}">
                                                    <th class="lr-sticky">
                                                        {{ $customer->name }}
                                                        @if ($m['gaps'])
                                                            <span class="label label-danger" title="Cargos with no allowable loss">{{ $m['gaps'] }}</span>
                                                        @endif
                                                    </th>
                                                    <td class="{{ is_numeric($customer->allowable_loss_percentage) ? 'lr-set' : '' }}">
                                                        <input type="number" step="any" min="0" class="form-control"
                                                            wire:model.defer="allowances.{{ $customer->id }}.all" placeholder="-">
                                                    </td>
                                                    @foreach ($cargos as $cargo)
                                                        @php $cell = $m['cells'][$cargo->id]; $own = $cell['source'] === 'Customer + Cargo'; @endphp
                                                        <td class="{{ $own ? 'lr-set' : (is_null($cell['value']) ? 'lr-gap' : '') }}"
                                                            title="{{ $customer->name }} / {{ $cargo->name }}: {{ $cell['value'] !== null ? \App\Services\LossRateService::formatPct($cell['value']) . ' from ' . $cell['source'] : 'not set' }}">
                                                            <input type="number" step="any" min="0" class="form-control"
                                                                wire:model.defer="allowances.{{ $customer->id }}.{{ $cargo->id }}"
                                                                placeholder="{{ $own ? '' : ($fmtPct($cell['value']) ?? '') }}">
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @empty
                                                <tr><td colspan="{{ $cargos->count() + 2 }}" class="text-center text-muted">No customers to show.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                @foreach ($errors->getMessages() as $key => $messages)
                                    @if (str_starts_with($key, 'allowances.') || str_starts_with($key, 'cargoDefaults.'))
                                        <div class="text-danger"><small>{{ $messages[0] }}</small></div>
                                        @break
                                    @endif
                                @endforeach
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-top:10px;">
                                    {{ $customers->links() }}
                                    <button type="button" class="btn btn-success btn-rounded" wire:click="saveAllowances"><i class="fa fa-save"></i> Save Allowances</button>
                                </div>
                            @else
                                <p class="text-muted"><small>
                                    The rate is per cargo and depends only on who carried it: <strong>Own Fleet</strong> = {{ $ownFleetName }} (the Default Transporter); every other transporter is a <strong>Subcontractor</strong> and they all share one rate.
                                    Blank = Business default (greyed). It is not customer-specific.
                                    @if ($rateGaps)
                                        <br><span class="label label-danger">{{ $rateGaps }} cargo(s) without a rate</span>
                                    @endif
                                </small></p>
                                <table class="table table-bordered table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Cargo</th>
                                            <th>Type</th>
                                            <th>Own Fleet ({{ $ownFleetName }})</th>
                                            <th>Subcontractors</th>
                                            <th>Subbie &minus; Own</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($rateRows as $row)
                                            @php $cargo = $row['cargo']; @endphp
                                            <tr wire:key="lr-rate-{{ $cargo->id }}">
                                                <td>{{ $cargo->name }}</td>
                                                <td>{{ $cargo->type }}</td>
                                                @foreach (['own', 'sub'] as $key)
                                                    @php $r = $row[$key]; $isOwn = str_starts_with((string) $r['source'], 'Cargo'); @endphp
                                                    <td>
                                                        <input type="number" step="any" min="0" class="form-control input-sm"
                                                            style="max-width:140px; {{ is_null($r['value']) ? 'border-color:#d9534f;' : ($isOwn ? 'background:#e8f4fd;' : '') }}"
                                                            wire:model.defer="rates.{{ $cargo->id }}.{{ $key }}"
                                                            placeholder="{{ $isOwn ? '' : ($fmtRate($r['value']) ?? 'not set') }}">
                                                        @error("rates.{$cargo->id}.{$key}") <span class="text-danger error">{{ $message }}</span> @enderror
                                                    </td>
                                                @endforeach
                                                <td>
                                                    @if (! is_null($row['own']['value']) && ! is_null($row['sub']['value']))
                                                        {{ number_format($row['sub']['value'] - $row['own']['value'], 2) }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center text-muted">No cargos to show.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                <div style="text-align:right;">
                                    <button type="button" class="btn btn-success btn-rounded" wire:click="saveRates"><i class="fa fa-save"></i> Save Rates</button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
