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
            @forelse ($shipment->customs_declarations as $declaration)
                <tr>
                    <td>{{ $declaration->declaration_number }}</td>
                    <td>{{ ucfirst($declaration->declaration_type ?? '') }}</td>
                    <td>{{ $declaration->entry_number }}</td>
                    <td>{{ $declaration->clearing_agent?->name }}</td>
                    <td><span class="label label-info label-wide">{{ $workflowStages[$declaration->status] ?? $declaration->status }}</span></td>
                    <td>
                        <a href="#" wire:click.prevent="toggleExpand({{ $declaration->id }})" class="btn btn-xs btn-default"><i class="fa fa-eye"></i> Details</a>
                        @if ($declaration->nextWorkflowStage())
                            <a href="#" wire:click.prevent="advanceStage({{ $declaration->id }})" wire:confirm="Mark this declaration as '{{ $workflowStages[$declaration->nextWorkflowStage()] }}'?" class="btn btn-xs btn-success">
                                <i class="fa fa-arrow-right"></i> {{ $workflowStages[$declaration->nextWorkflowStage()] }}
                            </a>
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

                            <h6 class="underline mt-10 mb-10"><strong>Declaration Lines</strong></h6>
                            <table class="table table-condensed table-bordered">
                                <thead>
                                    <tr>
                                        <th>HS Code</th><th>Description</th><th>Qty</th><th>Customs Value</th><th>Duty</th><th>VAT</th><th>Excise</th><th>Levies</th><th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($declaration->lines as $line)
                                        <tr>
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
    <div class="modal fade" id="addDeclarationModal{{ $shipment->id }}" tabindex="-1" role="dialog">
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
                                    <input type="text" class="form-control" wire:model="customs_office">
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
                                    <label>Customs Procedure</label>
                                    <input type="text" class="form-control" wire:model="customs_procedure">
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
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Declarant</label>
                                    <select class="form-control" wire:model="declarant_id">
                                        <option value="">Select</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }} {{ $user->surname }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Clearing Officer</label>
                                    <select class="form-control" wire:model="clearing_officer_id">
                                        <option value="">Select</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }} {{ $user->surname }}</option>
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
    <div class="modal fade" id="lineModal{{ $shipment->id }}" tabindex="-1" role="dialog">
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
                                    <input type="text" class="form-control" wire:model="line_uom">
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
