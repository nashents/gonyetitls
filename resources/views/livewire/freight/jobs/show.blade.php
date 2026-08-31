<div>
    <div class="row mt-30">
        <div class="col-md-3">
            <div class="panel border-primary no-border border-3-top">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h5>{{ $job->job_number }}</h5>
                    </div>
                </div>
                <div class="panel-body">
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <th>Customer</th>
                                <td>{{ $job->customer?->name }}</td>
                            </tr>
                            <tr>
                                <th>Service Type</th>
                                <td>{{ $job->freight_service_type?->name }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td><span class="badge bg-info">{{ ucwords(str_replace('_', ' ', $job->status)) }}</span></td>
                            </tr>
                            <tr>
                                <th>Mode</th>
                                <td>{{ ucfirst($job->primary_transport_mode ?? '') }}</td>
                            </tr>
                            <tr>
                                <th>Route</th>
                                <td>{{ $job->origin }} &rarr; {{ $job->destination }}</td>
                            </tr>
                            <tr>
                                <th>Opened</th>
                                <td>{{ $job->opened_at?->format('d M Y') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- /.col-md-3 -->

        <div class="col-md-9">
            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#overview" aria-controls="overview" role="tab" data-toggle="tab">Overview</a></li>
                <li role="presentation"><a href="#shipment" aria-controls="shipment" role="tab" data-toggle="tab">Shipment</a></li>
                <li role="presentation"><a href="#cargo" aria-controls="cargo" role="tab" data-toggle="tab">Cargo</a></li>
                <li role="presentation"><a href="#parties" aria-controls="parties" role="tab" data-toggle="tab">Parties</a></li>
                <li role="presentation"><a href="#containers" aria-controls="containers" role="tab" data-toggle="tab">Containers</a></li>
                <li role="presentation"><a href="#transport" aria-controls="transport" role="tab" data-toggle="tab">Transport</a></li>
                <li role="presentation"><a href="#transport-documents" aria-controls="transport-documents" role="tab" data-toggle="tab">Transport Documents</a></li>
                <li role="presentation"><a href="#customs" aria-controls="customs" role="tab" data-toggle="tab">Customs</a></li>
                <li role="presentation"><a href="#costing" aria-controls="costing" role="tab" data-toggle="tab">Costing</a></li>
                <li role="presentation"><a href="#timeline" aria-controls="timeline" role="tab" data-toggle="tab">Timeline</a></li>
                <li role="presentation"><a href="#documents" aria-controls="documents" role="tab" data-toggle="tab">Documents</a></li>
            </ul>
            <div class="tab-content bg-white p-15">

                <div role="tabpanel" class="tab-pane active" id="overview">
                    <table class="table table-striped">
                        <tbody class="line-height-35">
                            <tr>
                                <th class="w-20">Customer Reference</th>
                                <td>{{ $job->customer_reference }}</td>
                            </tr>
                            <tr>
                                <th>Incoterm</th>
                                <td>{{ $job->incoterm }}</td>
                            </tr>
                            <tr>
                                <th>Currency</th>
                                <td>{{ $job->currency?->name }}</td>
                            </tr>
                            <tr>
                                <th>Import/Export</th>
                                <td>{{ ucfirst($job->import_export_type ?? '') }}</td>
                            </tr>
                            <tr>
                                <th>Origin Country</th>
                                <td>{{ $job->origin_country?->name }}</td>
                            </tr>
                            <tr>
                                <th>Destination Country</th>
                                <td>{{ $job->destination_country?->name }}</td>
                            </tr>
                            <tr>
                                <th>Salesperson</th>
                                <td>{{ $job->salesperson?->name }} {{ $job->salesperson?->surname }}</td>
                            </tr>
                            <tr>
                                <th>Operations Officer</th>
                                <td>{{ $job->operations_officer?->name }} {{ $job->operations_officer?->surname }}</td>
                            </tr>
                            <tr>
                                <th>Clearing Officer</th>
                                <td>{{ $job->clearing_officer?->name }} {{ $job->clearing_officer?->surname }}</td>
                            </tr>
                            <tr>
                                <th>Quotation</th>
                                <td>{{ $job->quotation?->quotation_number }}</td>
                            </tr>
                            <tr>
                                <th>Estimated Revenue</th>
                                <td>{{ $job->currency?->symbol }}{{ number_format($job->estimated_revenue ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Estimated Cost</th>
                                <td>{{ $job->currency?->symbol }}{{ number_format($job->estimated_cost ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Notes</th>
                                <td>{{ $job->notes }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div role="tabpanel" class="tab-pane" id="shipment">
                    <div class="mb-10 text-right">
                        @livewire('freight.shipments.add-shipment', ['jobId' => $job->id], key('add-shipment-'.$job->id))
                    </div>
                    @forelse ($job->shipments as $shipment)
                        <div class="panel border-primary no-border border-3-top mb-20">
                            <div class="panel-heading">
                                <div class="panel-title">
                                    <h6>{{ $shipment->shipment_number }} &mdash; {{ ucfirst($shipment->mode ?? '') }} / {{ $shipment->shipment_type }}</h6>
                                </div>
                            </div>
                            <div class="panel-body">
                                <table class="table table-striped">
                                    <tbody>
                                        <tr>
                                            <th class="w-20">Port/Place of Loading</th>
                                            <td>{{ $shipment->port_of_loading?->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Port/Place of Discharge</th>
                                            <td>{{ $shipment->port_of_discharge?->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>ETD</th>
                                            <td>{{ $shipment->etd?->format('d M Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th>ETA</th>
                                            <td>{{ $shipment->eta?->format('d M Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Booking Reference</th>
                                            <td>{{ $shipment->booking_reference }}</td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td><span class="label label-info label-wide">{{ ucwords(str_replace('_', ' ', $shipment->status)) }}</span></td>
                                        </tr>
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    @empty
                        <p class="text-center">No shipments recorded for this job yet.</p>
                    @endforelse
                </div>

                <div role="tabpanel" class="tab-pane" id="cargo">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Shipment</th>
                                <th>Commodity</th>
                                <th>HS Code</th>
                                <th>Qty</th>
                                <th>Packages</th>
                                <th>Gross Wt</th>
                                <th>DG</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($job->shipments as $shipment)
                                @forelse ($shipment->cargo_items as $cargoItem)
                                    <tr>
                                        <td>{{ $shipment->shipment_number }}</td>
                                        <td>{{ $cargoItem->commodity ?? $cargoItem->cargo?->name }}</td>
                                        <td>{{ $cargoItem->hs_code }}</td>
                                        <td>{{ $cargoItem->quantity }} {{ $cargoItem->uom }}</td>
                                        <td>{{ $cargoItem->packages }}</td>
                                        <td>{{ $cargoItem->gross_weight }}</td>
                                        <td>{{ $cargoItem->is_dangerous_goods ? 'Yes' : 'No' }}</td>
                                    </tr>
                                @empty
                                @endforelse
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div role="tabpanel" class="tab-pane" id="parties">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Shipment</th>
                                <th>Role</th>
                                <th>Party Type</th>
                                <th>Party</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($job->shipments as $shipment)
                                @forelse ($shipment->parties as $party)
                                    <tr>
                                        <td>{{ $shipment->shipment_number }}</td>
                                        <td>{{ ucwords(str_replace('_', ' ', $party->role)) }}</td>
                                        <td>{{ ucwords(str_replace('_', ' ', $party->party_type)) }}</td>
                                        <td>{{ $party->party()?->name }}</td>
                                    </tr>
                                @empty
                                @endforelse
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div role="tabpanel" class="tab-pane" id="containers">
                    @forelse ($job->shipments as $shipment)
                        <h6 class="underline mt-10 mb-10"><strong>{{ $shipment->shipment_number }}</strong></h6>
                        @livewire('freight.shipments.containers', ['shipmentId' => $shipment->id], key('containers-'.$shipment->id))
                    @empty
                        <p class="text-center">No shipments recorded for this job yet.</p>
                    @endforelse
                </div>

                <div role="tabpanel" class="tab-pane" id="transport">
                    @forelse ($job->shipments as $shipment)
                        <h6 class="underline mt-10 mb-10"><strong>{{ $shipment->shipment_number }}</strong></h6>
                        @livewire('freight.shipments.legs', ['shipmentId' => $shipment->id], key('legs-'.$shipment->id))
                    @empty
                        <p class="text-center">No shipments recorded for this job yet.</p>
                    @endforelse
                </div>

                <div role="tabpanel" class="tab-pane" id="transport-documents">
                    @forelse ($job->shipments as $shipment)
                        <h6 class="underline mt-10 mb-10"><strong>{{ $shipment->shipment_number }}</strong></h6>
                        @livewire('freight.shipments.transport-documents', ['shipmentId' => $shipment->id], key('transport-documents-'.$shipment->id))
                    @empty
                        <p class="text-center">No shipments recorded for this job yet.</p>
                    @endforelse
                </div>

                <div role="tabpanel" class="tab-pane" id="customs">
                    @forelse ($job->shipments as $shipment)
                        <h6 class="underline mt-10 mb-10"><strong>{{ $shipment->shipment_number }}</strong></h6>
                        @livewire('freight.shipments.customs-declarations', ['shipmentId' => $shipment->id], key('customs-declarations-'.$shipment->id))
                    @empty
                        <p class="text-center">No shipments recorded for this job yet.</p>
                    @endforelse
                </div>

                <div role="tabpanel" class="tab-pane" id="costing">
                    @livewire('freight.jobs.costing', ['jobId' => $job->id], key('costing-'.$job->id))
                </div>

                <div role="tabpanel" class="tab-pane" id="timeline">
                    @livewire('freight.shipments.milestones', ['jobId' => $job->id], key('milestones-'.$job->id))
                </div>

                <div role="tabpanel" class="tab-pane" id="documents">
                    @livewire('documents.index', ['id' => $job->id, 'category' => 'freight_job'])
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="btn-group pull-right mt-10">
                            <a href="{{ route('freight.jobs.index') }}" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i> Back</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- /.col-md-9 -->
    </div>
</div>
