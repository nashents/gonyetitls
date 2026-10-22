<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Edit Freight Job — {{ $job->job_number }}</h5>
                                <small style="color: green">Asterisk <span style="color: red">(*)</span> sign indicates all mandatory fields.</small>
                            </div>
                        </div>
                        <div class="panel-body">
                            <form wire:submit.prevent="update()" class="p-20">

                                <h6 class="underline mt-20 mb-20"><strong>Customer &amp; Service</strong></h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Customer <span class="required text-danger">*</span></label>
                                            <select class="form-control" wire:model="customer_id">
                                                <option value="">Select Customer</option>
                                                @foreach ($customers as $customer)
                                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('customer_id') <span class="text-danger error">{{ $message }}</span> @enderror
                                            <small><a href="{{ route('customers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Customer</a></small>
                                            <a href="#" wire:click.prevent="refresh('customers')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Customer Reference</label>
                                            <input type="text" class="form-control" wire:model="customer_reference">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Freight Service Type</label>
                                            <select class="form-control" wire:model="freight_service_type_id">
                                                <option value="">Select Service Type</option>
                                                @foreach ($freight_service_types as $type)
                                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                @endforeach
                                            </select>
                                            <small><a href="{{ route('freight.settings.service-types') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Service Type</a></small>
                                            <a href="#" wire:click.prevent="refresh('freight_service_types')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Import/Export</label>
                                            <select class="form-control" wire:model="import_export_type">
                                                <option value="">Select</option>
                                                <option value="import">Import</option>
                                                <option value="export">Export</option>
                                                <option value="cross_trade">Cross Trade</option>
                                                <option value="domestic">Domestic</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Primary Transport Mode <span class="required text-danger">*</span></label>
                                            <select class="form-control" wire:model="primary_transport_mode">
                                                <option value="">Select Mode</option>
                                                <option value="sea">Sea</option>
                                                <option value="air">Air</option>
                                                <option value="road">Road</option>
                                                <option value="rail">Rail</option>
                                                <option value="courier">Courier</option>
                                                <option value="multimodal">Multimodal</option>
                                            </select>
                                            @error('primary_transport_mode') <span class="text-danger error">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Incoterm</label>
                                            <select class="form-control" wire:model="incoterm">
                                                <option value="">Select</option>
                                                @foreach ($incoterms as $code => $label)
                                                    <option value="{{ $code }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Currency</label>
                                            <select class="form-control" wire:model="currency_id">
                                                <option value="">Select Currency</option>
                                                @foreach ($currencies as $currency)
                                                    <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                                @endforeach
                                            </select>
                                            <small><a href="{{ route('currencies.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Currency</a></small>
                                            <a href="#" wire:click.prevent="refresh('currencies')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Quotation</label>
                                            <select class="form-control" wire:model="quotation_id">
                                                <option value="">None</option>
                                                @foreach ($quotations as $quotation)
                                                    <option value="{{ $quotation->id }}">{{ $quotation->quotation_number }} - {{ $quotation->customer?->name }}</option>
                                                @endforeach
                                            </select>
                                            <small><a href="{{ route('quotations.create') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Quotation</a></small>
                                            <a href="#" wire:click.prevent="refresh('quotations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Origin</label>
                                            <select class="form-control" wire:model="from">
                                                <option value="">Select Origin</option>
                                                @foreach ($destinations as $destination)
                                                    <option value="{{ $destination->id }}">{{ $destination->country ? $destination->country->name : '' }} {{ $destination->city }}</option>
                                                @endforeach
                                            </select>
                                            <small><a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Destination</a></small>
                                            <a href="#" wire:click.prevent="refresh('destinations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Destination</label>
                                            <select class="form-control" wire:model="to">
                                                <option value="">Select Destination</option>
                                                @foreach ($destinations as $destination)
                                                    <option value="{{ $destination->id }}">{{ $destination->country ? $destination->country->name : '' }} {{ $destination->city }}</option>
                                                @endforeach
                                            </select>
                                            <small><a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Destination</a></small>
                                            <a href="#" wire:click.prevent="refresh('destinations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Salesperson</label>
                                            <select class="form-control" wire:model="salesperson_id">
                                                <option value="">None</option>
                                                @foreach ($officers as $officer)
                                                    <option value="{{ $officer->user_id }}">{{ $officer->name }} {{ $officer->surname }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Operations Officer</label>
                                            <select class="form-control" wire:model="operations_officer_id">
                                                <option value="">None</option>
                                                @foreach ($officers as $officer)
                                                    <option value="{{ $officer->user_id }}">{{ $officer->name }} {{ $officer->surname }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Clearing Officer</label>
                                            <select class="form-control" wire:model="clearing_officer_id">
                                                <option value="">None</option>
                                                @foreach ($officers as $officer)
                                                    <option value="{{ $officer->user_id }}">{{ $officer->name }} {{ $officer->surname }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="underline mt-20 mb-20"><strong>Status</strong></h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Status <span class="required text-danger">*</span></label>
                                            <select class="form-control" wire:model="status">
                                                @foreach ($statuses as $statusOption)
                                                    <option value="{{ $statusOption }}">{{ ucwords(str_replace('_', ' ', $statusOption)) }}</option>
                                                @endforeach
                                            </select>
                                            @error('status') <span class="text-danger error">{{ $message }}</span> @enderror
                                            @if (! $job->isApproved())
                                                <small class="text-muted">This job is {{ $job->authorization }} authorization - it can only be Draft or Cancelled until approved.{{ $job->authorization === 'rejected' ? ' Saving sends it for authorization again.' : '' }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <h6 class="underline mt-20 mb-20"><strong>Notes</strong></h6>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <textarea class="form-control" rows="3" wire:model="notes"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-20">
                                    <div class="col-md-12">
                                        <a href="{{ route('freight.jobs.show', $job->id) }}" class="btn bg-gray">Cancel</a>
                                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Changes</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
