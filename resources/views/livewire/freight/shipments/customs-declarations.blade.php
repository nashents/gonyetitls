<div>
    <div class="mb-10">
        <a href="#" class="btn btn-xs btn-info" data-toggle="modal" data-target="#addDeclarationModal{{ $shipment->id }}">
            <i class="fa fa-plus"></i> Add Customs Declaration
        </a>
    </div>

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Declaration #</th>
                <th>Type</th>
                <th>Entry #</th>
                <th>Clearing Agent</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($declarationRows as $declaration)
                <tr wire:key="{{ 'b7446c-20-' . ($loop->parent?->index ?? 'r') . '-' . $loop->index . '-' . ($declaration->id ?? '') }}">
                    <td>{{ $declaration->declaration_number }}</td>
                    <td>{{ ucfirst($declaration->declaration_type ?? '') }}</td>
                    <td>{{ $declaration->entry_number }}</td>
                    <td>{{ $declaration->clearing_agent?->name }}</td>
                    <td>
                        <span class="label label-info label-wide">{{ $workflowStages[$declaration->status] ?? $declaration->status }}</span>
                        @php
                            // Every status change records a completed milestone (CustomsDeclarationService::create /
                            // transitionStatus), so these rows are the status trail. Newest update first.
                            $statusTrail = $declaration->milestones->sortByDesc('id');
                        @endphp
                        @if ($statusTrail->isNotEmpty())
                            <ul class="list-unstyled" style="margin:6px 0 0; max-height:110px; overflow-y:auto; font-size:11px; line-height:1.5">
                                @foreach ($statusTrail as $change)
                                    <li class="{{ $loop->first ? '' : 'text-muted' }}" wire:key="declaration-{{ $declaration->id }}-trail-{{ $change->id }}">
                                        <strong>{{ $change->milestone_name }}</strong>
                                        &middot; {{ ($change->actual_at ?? $change->created_at)?->format('d M Y H:i') }}
                                        &middot; {{ $change->creator ? trim($change->creator->name . ' ' . $change->creator->surname) : 'System' }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </td>
                    <td>
                        <a href="#" wire:click.prevent="toggleExpand({{ $declaration->id }})" class="btn btn-xs btn-default"><i class="fa fa-eye"></i> Details</a>
                        @if ($declaration->nextWorkflowStage())
                            <div style="margin-top:6px; white-space:nowrap;">
                                <input type="datetime-local" class="form-control input-sm" style="display:inline-block; width:auto;"
                                       wire:model.defer="stage_dates.{{ $declaration->id }}" max="{{ now()->format('Y-m-d\TH:i') }}"
                                       title="When this stage actually happened - change it if you're updating the status late.">
                                <a href="#" wire:click.prevent="advanceStage({{ $declaration->id }})" wire:confirm="Mark this declaration as '{{ $workflowStages[$declaration->nextWorkflowStage()] }}'?" class="btn btn-xs btn-success">
                                    <i class="fa fa-arrow-right"></i> {{ $workflowStages[$declaration->nextWorkflowStage()] }}
                                </a>
                            </div>
                            @error('stage_dates.' . $declaration->id) <span class="text-danger error">{{ $message }}</span> @enderror
                        @endif
                    </td>
                </tr>
                @if ($expanded_declaration_id == $declaration->id)
                    <tr>
                        <td colspan="6" style="background-color:#f9f9f9;">
                            <div class="row mb-10">
                                <div class="col-md-3"><strong>Customs Value:</strong> {{ $declaration->currency?->symbol }}{{ number_format($declaration->total_customs_value, 2) }}</div>
                                <div class="col-md-2"><strong>Duty:</strong> {{ number_format($declaration->total_duty, 2) }}</div>
                                <div class="col-md-2"><strong>VAT:</strong> {{ number_format($declaration->total_vat, 2) }}</div>
                                <div class="col-md-2"><strong>Excise:</strong> {{ number_format($declaration->total_excise, 2) }}</div>
                                <div class="col-md-3"><strong>Levies:</strong> {{ number_format($declaration->total_levies, 2) }}</div>
                            </div>
                            <div class="row mb-10">
                                <div class="col-md-6">
                                    <strong>Declarant:</strong>
                                    {{ $declaration->declarantName() ?? '—' }}
                                    @if (! $declaration->declarant_id && $declaration->declarant_name)
                                        <small class="text-muted">(not in system)</small>
                                    @endif
                                    @if ($declaration->declarant_code)
                                        &middot; <strong>Code:</strong> {{ $declaration->declarant_code }}
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    @if ($declaration->customs_procedure)
                                        <strong>Customs Procedure:</strong> {{ $declaration->customsProcedureLabel() }}<br>
                                    @endif
                                    <strong>Clearing Officer:</strong>
                                    {{ $declaration->clearing_officer ? trim($declaration->clearing_officer->name . ' ' . $declaration->clearing_officer->surname) : '—' }}
                                </div>
                            </div>

                            <h6 class="underline mt-10 mb-10"><strong>Declaration Lines</strong></h6>
                            <table class="table table-condensed table-bordered">
                                <thead>
                                    <tr>
                                        <th>HS Code</th><th>Description</th><th>Qty</th><th>Customs Value</th><th>Duty</th><th>VAT</th><th>Excise</th><th>Levies</th><th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($declaration->lines as $line)
                                        <tr wire:key="{{ 'b7446c-55-' . ($loop->parent?->index ?? 'r') . '-' . $loop->index . '-' . ($line->id ?? '') }}">
                                            <td>{{ $line->hs_code }}</td>
                                            <td>{{ $line->description }}</td>
                                            <td>{{ $line->quantity }} {{ $line->uom }}</td>
                                            <td>{{ number_format($line->customs_value ?? 0, 2) }}</td>
                                            <td>{{ number_format($line->duty_amount, 2) }}</td>
                                            <td>{{ number_format($line->vat_amount, 2) }}</td>
                                            <td>{{ number_format($line->excise_amount, 2) }}</td>
                                            <td>{{ number_format($line->levies_amount, 2) }}</td>
                                            <td>
                                                <a href="#" wire:click.prevent="editLine({{ $line->id }})" class="btn btn-xs btn-default"><i class="fa fa-edit"></i></a>
                                                <a href="#" wire:click.prevent="deleteLine({{ $line->id }})" wire:confirm="Remove this declaration line?" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
    @include('livewire.freight.partials.pagination', ['paginator' => $declarationRows])
                            <a href="#" wire:click.prevent="showAddLine({{ $declaration->id }})" class="btn btn-xs btn-info mb-20"><i class="fa fa-plus"></i> Add Line</a>

                            <h6 class="underline mt-20 mb-10"><strong>Milestone History</strong></h6>
                            <table class="table table-condensed">
                                <thead>
                                    <tr><th>Stage</th><th>Status</th><th>Actual</th></tr>
                                </thead>
                                <tbody>
                                    @foreach ($declaration->milestones as $milestone)
                                        <tr>
                                            <td>{{ $milestone->milestone_name }}</td>
                                            <td>{{ ucfirst($milestone->status) }}</td>
                                            <td>{{ $milestone->actual_at?->format('d M Y H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="row mb-20">
                                <div class="col-md-4">
                                    <select class="form-control" wire:model="ad_hoc_milestone_code">
                                        <option value="">Record ad-hoc milestone / stage...</option>
                                        @foreach ($workflowStages as $code => $label)
                                            <option value="{{ $code }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="datetime-local" class="form-control" wire:model.defer="ad_hoc_milestone_date" max="{{ now()->format('Y-m-d\TH:i') }}"
                                           title="When this stage actually happened">
                                    @error('ad_hoc_milestone_date') <span class="text-danger error">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-2">
                                    <a href="#" wire:click.prevent="recordAdHocMilestone({{ $declaration->id }})" class="btn btn-xs btn-warning">Record</a>
                                </div>
                            </div>

                            <h6 class="underline mt-20 mb-10"><strong>Documents</strong></h6>
                            @livewire('documents.index', ['id' => $declaration->id, 'category' => 'customs_declaration'], key('declaration-docs-'.$declaration->id))
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="6" class="text-center">No customs declarations recorded for this shipment yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Add Declaration Modal -->
    <div wire:ignore.self class="modal fade" id="addDeclarationModal{{ $shipment->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="store">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        <h4 class="modal-title">Add Customs Declaration</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Declaration Type</label>
                                    <select class="form-control" wire:model="declaration_type">
                                        <option value="">Select</option>
                                        <option value="import">Import</option>
                                        <option value="export">Export</option>
                                        <option value="transit">Transit</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Customs Office</label>
                                    <select class="form-control" wire:model="customs_office">
                                        <option value="">Select Customs Office</option>
                                        @foreach ($customsOffices as $office)
                                            <option value="{{ $office->name }}">{{ $office->name }}</option>
                                        @endforeach
                                        <option value="{{ \App\Http\Livewire\Freight\Shipments\CustomsDeclarations::NEW_OFFICE }}">+ Add new customs office</option>
                                    </select>
                                    @if ($customs_office === \App\Http\Livewire\Freight\Shipments\CustomsDeclarations::NEW_OFFICE)
                                        <input type="text" class="form-control mt-10" wire:model.defer="new_customs_office" placeholder="Type the new customs office name">
                                        <div style="margin-top:4px; font-size:12px; line-height:1.4; height:auto; white-space:normal; color:#31708f;"><i class="fa fa-info-circle"></i> Saved to the list for next time.</div>
                                    @endif
                                    @error('new_customs_office') <span class="text-danger error">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Entry Number</label>
                                    <input type="text" class="form-control" wire:model="entry_number">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Declaration Reference</label>
                                    <input type="text" class="form-control" wire:model="declaration_reference">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Customs Procedure (CPC)</label>
                                    <select class="form-control" wire:model="customs_procedure">
                                        <option value="">Select</option>
                                        @foreach (\App\Models\CustomsDeclaration::CUSTOMS_PROCEDURES as $cpc => $cpcLabel)
                                            <option value="{{ $cpc }}">{{ $cpc }} - {{ $cpcLabel }}</option>
                                        @endforeach
                                        <option value="{{ \App\Http\Livewire\Freight\Shipments\CustomsDeclarations::OTHER_PROCEDURE }}">Other (type the code)</option>
                                    </select>
                                    @if ($customs_procedure === \App\Http\Livewire\Freight\Shipments\CustomsDeclarations::OTHER_PROCEDURE)
                                        <input type="text" class="form-control mt-10" wire:model.defer="other_customs_procedure" placeholder="e.g. 4000 412 or 7100">
                                        @error('other_customs_procedure') <span class="text-danger error">{{ $message }}</span> @enderror
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Declaration Date</label>
                                    <input type="date" class="form-control" wire:model="declaration_date">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Country</label>
                                    <select class="form-control" wire:model="country_id">
                                        <option value="">Select</option>
                                        @foreach ($countries as $country)
                                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                                        @endforeach
                                    </select>
                                    <small><a href="{{ route('countries.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Country</a></small>
                                    <a href="#" wire:click.prevent="refresh('countries')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Currency</label>
                                    <select class="form-control" wire:model="currency_id">
                                        <option value="">Select</option>
                                        @foreach ($currencies as $currency)
                                            <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                        @endforeach
                                    </select>
                                    <small><a href="{{ route('currencies.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Currency</a></small>
                                    <a href="#" wire:click.prevent="refresh('currencies')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Clearing Agent</label>
                                    <select class="form-control" wire:model="clearing_agent_id">
                                        <option value="">Select</option>
                                        @foreach ($clearingAgents as $agent)
                                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                        @endforeach
                                    </select>
                                    <small><a href="{{ route('clearing_agents.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New</a></small>
                                    <a href="#" wire:click.prevent="refresh('clearing_agents')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Declarant</label>
                                    <select class="form-control" wire:model="declarant_id">
                                        <option value="">Not one of our staff / none</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->user_id }}">{{ $user->name }} {{ $user->surname }}</option>
                                        @endforeach
                                    </select>
                                    @unless ($declarant_id)
                                        <input type="text" class="form-control mt-10" wire:model.defer="declarant_name" placeholder="or type the declarant's name (e.g. the clearing agent's declarant)">
                                        @error('declarant_name') <span class="text-danger error">{{ $message }}</span> @enderror
                                    @endunless
                                </div>
                                @if ($declarant_id)
                                    {{-- Staff declarant: code lives on their employee record. Shown read-only;
                                         typed here only when missing or on "Update", and written back to the
                                         employee record when the declaration is saved. --}}
                                    @php $staffDeclarant = $users->firstWhere('user_id', $declarant_id); @endphp
                                    @if ($staffDeclarant?->customs_code && ! $editing_staff_code)
                                        <p class="mb-10">
                                            <strong>Customs Code:</strong> {{ $staffDeclarant->customs_code }}
                                            <a href="#" wire:click.prevent="$set('editing_staff_code', true)" class="btn btn-xs btn-default" style="margin-left:6px"><i class="fa fa-pencil"></i> Update</a>
                                        </p>
                                    @else
                                        <div class="form-group">
                                            <label>Declarant / Customs Code</label>
                                            <input type="text" class="form-control" wire:model.defer="staff_code_input" placeholder="{{ $staffDeclarant?->customs_code ? 'New customs code' : 'No code on record yet - enter it here' }}">
                                            <small class="text-muted">Saved to {{ $staffDeclarant?->name }}'s record when you save this declaration, and reused next time.</small>
                                            @if ($staffDeclarant?->customs_code)
                                                <a href="#" wire:click.prevent="cancelStaffCodeUpdate" style="float:right"><small>Cancel - keep {{ $staffDeclarant->customs_code }}</small></a>
                                            @endif
                                            @error('staff_code_input') <span class="text-danger error">{{ $message }}</span> @enderror
                                        </div>
                                    @endif
                                @else
                                    <div class="form-group">
                                        <label>Declarant / Customs Code</label>
                                        <input type="text" class="form-control" wire:model.defer="declarant_code" placeholder="Customs registration / declarant code">
                                        @error('declarant_code') <span class="text-danger error">{{ $message }}</span> @enderror
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Clearing Officer</label>
                                    <select class="form-control" wire:model="clearing_officer_id">
                                        <option value="">Select</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->user_id }}">{{ $user->name }} {{ $user->surname }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn bg-gray" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Declaration</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add/Edit Line Modal -->
    <div wire:ignore.self class="modal fade" id="lineModal{{ $shipment->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="saveLine">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        <h4 class="modal-title">{{ $editing_line_id ? 'Edit' : 'Add' }} Declaration Line</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Cargo Line</label>
                                    <select class="form-control" wire:model="line_shipment_cargo_id">
                                        <option value="">None</option>
                                        @foreach ($shipment->cargo_items as $cargoItem)
                                            <option value="{{ $cargoItem->id }}">{{ $cargoItem->commodity ?? $cargoItem->cargo?->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>HS Code</label>
                                    <input type="text" class="form-control" wire:model="line_hs_code">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Country of Origin</label>
                                    <select class="form-control" wire:model="line_country_of_origin_id">
                                        <option value="">Select</option>
                                        @foreach ($countries as $country)
                                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                                        @endforeach
                                    </select>
                                    <small><a href="{{ route('countries.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New</a></small>
                                    <a href="#" wire:click.prevent="refresh('countries')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <input type="text" class="form-control" wire:model="line_description">
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Quantity</label>
                                    <input type="number" step="any" class="form-control" wire:model="line_quantity">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>UOM</label>
                                    <select class="form-control" wire:model="line_uom">
                                        <option value="">Select</option>
                                        @foreach ($uoms as $uom)
                                            <option value="{{ $uom->abbreviation ?: $uom->name }}">{{ $uom->name }}{{ $uom->abbreviation ? ' ('.$uom->abbreviation.')' : '' }}</option>
                                        @endforeach
                                    </select>
                                    <small><a href="{{ route('units_of_measure.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New</a></small>
                                    <a href="#" wire:click.prevent="refresh('uoms')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Customs Value</label>
                                    <input type="number" step="any" class="form-control" wire:model="line_customs_value">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Currency</label>
                                    <select class="form-control" wire:model="line_currency_id">
                                        <option value="">Select</option>
                                        @foreach ($currencies as $currency)
                                            <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                        @endforeach
                                    </select>
                                    <small><a href="{{ route('currencies.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New</a></small>
                                    <a href="#" wire:click.prevent="refresh('currencies')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Exch. Rate</label>
                                    <input type="number" step="any" class="form-control" wire:model="line_exchange_rate">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Duty Rate %</label>
                                    <input type="number" step="any" class="form-control" wire:model="line_duty_rate">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Duty Amt</label>
                                    <input type="number" step="any" class="form-control" wire:model="line_duty_amount">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>VAT Rate %</label>
                                    <input type="number" step="any" class="form-control" wire:model="line_vat_rate">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>VAT Amt</label>
                                    <input type="number" step="any" class="form-control" wire:model="line_vat_amount">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Excise Rate %</label>
                                    <input type="number" step="any" class="form-control" wire:model="line_excise_rate">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Excise Amt</label>
                                    <input type="number" step="any" class="form-control" wire:model="line_excise_amount">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Levies</label>
                                    <input type="number" step="any" class="form-control" wire:model="line_levies_amount">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Trade Agreement</label>
                                    <input type="text" class="form-control" wire:model="line_trade_agreement">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Permit Reference</label>
                                    <input type="text" class="form-control" wire:model="line_permit_reference">
                                </div>
                            </div>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" wire:model="line_is_preferential"> Preferential Treatment
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn bg-gray" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Line</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        window.addEventListener('hide-addDeclarationModal-{{ $shipment->id }}', event => {
            $('#addDeclarationModal{{ $shipment->id }}').modal('hide');
        })
        window.addEventListener('show-lineModal-{{ $shipment->id }}', event => {
            $('#lineModal{{ $shipment->id }}').modal('show');
        })
        window.addEventListener('hide-lineModal-{{ $shipment->id }}', event => {
            $('#lineModal{{ $shipment->id }}').modal('hide');
        })
    </script>
</div>
