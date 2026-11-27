<div>
    <div class="mb-10">
        <a href="#" wire:click.prevent="create" class="btn btn-xs btn-info">
            <i class="fa fa-plus"></i> Add Transport Document
        </a>
    </div>

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Type</th>
                <th>Document #</th>
                <th>Issue Date</th>
                <th>Carrier</th>
                <th># Originals</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($documentRows as $doc)
                <tr wire:key="transport-doc-{{ $doc->id }}">
                    <td>{{ $documentTypes[$doc->document_type] ?? ucwords(str_replace('_', ' ', $doc->document_type)) }}</td>
                    <td>{{ $doc->document_number }}</td>
                    <td>{{ $doc->issue_date?->format('d M Y') }}</td>
                    <td>{{ $doc->carrierLabel() }}</td>
                    <td>{{ $doc->number_of_originals }}</td>
                    <td><span class="label label-{{ $doc->statusBadge() }} label-wide">{{ $doc->statusLabel() }}</span></td>
                    <td style="white-space:nowrap">
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Update status">
                                <i class="fa fa-exchange"></i> Status <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                @foreach ($statuses as $code => $label)
                                    @if ($code !== $doc->status)
                                        <li>
                                            <a href="#" wire:click.prevent="setStatus({{ $doc->id }}, '{{ $code }}')">
                                                <span class="label label-{{ \App\Models\TransportDocument::STATUS_BADGES[$code] ?? 'info' }}">&nbsp;</span> Mark as {{ $label }}
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                        <a href="#" wire:click.prevent="edit({{ $doc->id }})" class="btn btn-xs btn-default" title="Edit"><i class="fa fa-edit"></i></a>
                        <a href="#" wire:click.prevent="delete({{ $doc->id }})" wire:confirm="Delete transport document {{ $doc->document_number }}?" class="btn btn-xs btn-danger" title="Delete"><i class="fa fa-trash"></i></a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No transport documents recorded for this shipment yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    @include('livewire.freight.partials.pagination', ['paginator' => $documentRows])

    <!-- Add Transport Document Modal -->
    <div wire:ignore.self class="modal fade" id="addTransportDocumentModal{{ $shipment->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="store">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        <h4 class="modal-title">{{ $document_id ? 'Edit Transport Document' : 'Add Transport Document' }}</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Type <span class="required text-danger">*</span></label>
                            <select class="form-control" wire:model="document_type">
                                <option value="">Select</option>
                                @foreach ($documentTypes as $code => $label)
                                    <option value="{{ $code }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('document_type') <span class="text-danger error">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Document Number <span class="required text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="document_number">
                            @error('document_number') <span class="text-danger error">{{ $message }}</span> @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Issue Date</label>
                                    <input type="date" class="form-control" wire:model="issue_date">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label># Originals</label>
                                    <input type="number" class="form-control" wire:model="number_of_originals">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            @if (\App\Models\TransportDocument::carrierIsShippingLine($document_type))
                                <label>Carrier (Shipping Line)</label>
                                <select class="form-control" wire:model="carrier_shipping_line_id">
                                    <option value="">Select Shipping Line</option>
                                    @foreach ($shippingLines as $shippingLine)
                                        <option value="{{ $shippingLine->id }}">{{ $shippingLine->name }}</option>
                                    @endforeach
                                </select>
                                <small><a href="{{ route('freight.settings.shipping-lines') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Shipping Line</a></small>
                                <a href="#" wire:click.prevent="refresh('shipping_lines')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                            @else
                                <label>Carrier{{ $document_type ? '' : ' (pick a document type first)' }}</label>
                                <select class="form-control" wire:model="carrier_vendor_id">
                                    <option value="">Select Vendor</option>
                                    @foreach ($vendors as $vendor)
                                        <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                    @endforeach
                                </select>
                                <small><a href="{{ route('vendors.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Vendor</a></small>
                                <a href="#" wire:click.prevent="refresh('vendors')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                            @endif
                            <input type="text" class="form-control mt-10" wire:model="carrier_name" placeholder="or type free text">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Place of Issue</label>
                                    <input type="text" class="form-control" wire:model="place_of_issue">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Freight Payable At</label>
                                    <input type="text" class="form-control" wire:model="freight_payable_at">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" wire:model="status">
                                @foreach ($statuses as $code => $label)
                                    <option value="{{ $code }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status') <span class="text-danger error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn bg-gray" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ $document_id ? 'Update Document' : 'Save Document' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        window.addEventListener('hide-addTransportDocumentModal-{{ $shipment->id }}', event => {
            $('#addTransportDocumentModal{{ $shipment->id }}').modal('hide');
        })
        window.addEventListener('show-addTransportDocumentModal-{{ $shipment->id }}', event => {
            $('#addTransportDocumentModal{{ $shipment->id }}').modal('show');
        })
    </script>
</div>
