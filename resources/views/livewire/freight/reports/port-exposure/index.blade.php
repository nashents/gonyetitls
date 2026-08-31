<div>

    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Port & Demurrage/Detention Exposure</h5>
                                <small style="color: green">All currently open (unstopped) container exposure, fleet-wide.</small>
                            </div>
                        </div>
                        <div class="panel-body">

                            <div class="row hidden-print">
                                <form wire:submit.prevent="generateStatement()">
                                <div class="col-lg-3">
                                    <div class="input-group">
                                        <span class="input-group-addon">Shipping Line</span>
                                        <select class="form-control" wire:model="shipping_line_vendor_id">
                                            <option value="">All</option>
                                            @foreach ($this->shippingLines as $vendor)
                                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                </form>
                            </div>
                            <br><br>

                            <div class="row hidden-print">
                                <div class="col-md-12 text-center">
                                    <div class="btn-group">
                                        <button wire:click.prevent="set_report('summary')" class="btn btn-default {{$summary == "summary" ? 'border-primary' : ""}} btn-wide btn-rounded" type="button">Summary</button>
                                        <button wire:click.prevent="set_report('details')" class="btn btn-default {{$details == "details" ? 'border-primary' : ""}} btn-wide btn-rounded" type="button">Details</button>
                                    </div>
                                    <div class="btn-group" style="margin-left: 25px">
                                        <a href="{{ route('freight.reports.port_exposure.print', ['shipping_line_vendor_id' => $shipping_line_vendor_id, 'view' => $this->viewMode()]) }}" target="_blank" class="btn btn-default btn-wide btn-rounded">
                                            <i class="fa fa-print"></i> Print
                                        </a>
                                        <a href="{{ route('freight.reports.port_exposure.pdf', ['shipping_line_vendor_id' => $shipping_line_vendor_id, 'view' => $this->viewMode()]) }}" target="_blank" class="btn btn-default btn-wide btn-rounded">
                                            <i class="fa fa-file-pdf-o"></i> Export PDF
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <br>

                            <div class="row">
                                <div class="col-md-10 col-md-offset-1">
                                    <div class="row text-center">
                                        @php
                                            $statusLabels = ['within_free_period' => 'Within Free Period', 'expiring_soon' => 'Expiring Soon', 'expiring_today' => 'Expiring Today', 'accruing' => 'Accruing'];
                                        @endphp
                                        @foreach ($statusLabels as $code => $label)
                                        <div class="col-md-3">
                                            <h5>{{ $label }}</h5><br>
                                            <h4 style="margin-top:-15px">{{ $status_breakdown[$code] ?? 0 }}</h4>
                                        </div>
                                        @endforeach
                                    </div>
                                    <div class="row text-center mt-20">
                                        <div class="col-md-6">
                                            <h5>Total Estimated Exposure</h5><br>
                                            <h4 style="margin-top:-15px">{{$default_currency?->name}} {{$default_currency?->symbol}}{{$this->fmt($grand_totals['total'] ?? 0)}}</h4>
                                        </div>
                                        <div class="col-md-6">
                                            <h5>Total Actual Charged</h5><br>
                                            <h4 style="margin-top:-15px">{{$default_currency?->name}} {{$default_currency?->symbol}}{{$this->fmt($grand_totals['actual_total'] ?? 0)}}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <div class="col-xs-5 p-n"><strong>SHIPPING LINES</strong></div>
                            </div>
                        </div>
                        <hr style="width:100%;" size="3" color="black">
                        <div class="panel-body">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Shipping Line</th>
                                        @foreach (\App\Models\ContainerChargeExposure::CHARGE_TYPES as $label)
                                        <th class="text-right">{{$label}}</th>
                                        @endforeach
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($details) && $summary == null)
                                        @foreach ($vendor_rows as $row)
                                        @if ($row['total'] != 0)
                                        <tr>
                                            <td>{{$row['label']}}</td>
                                            @foreach (\App\Models\ContainerChargeExposure::CHARGE_TYPES as $label)
                                            <td class="text-right">{{$default_currency?->name}} {{$default_currency?->symbol}}{{$this->fmt($row['buckets'][$label] ?? 0)}}</td>
                                            @endforeach
                                            <td class="text-right"><strong>{{$default_currency?->name}} {{$default_currency?->symbol}}{{$this->fmt($row['total'])}}</strong></td>
                                        </tr>
                                        @endif
                                        @endforeach
                                    @endif
                                    <tr style="background-color: #D3D3D3">
                                        <td><strong>Total</strong></td>
                                        @foreach (\App\Models\ContainerChargeExposure::CHARGE_TYPES as $label)
                                        <td class="text-right"><strong>{{$default_currency?->name}} {{$default_currency?->symbol}}{{$this->fmt($grand_totals['buckets'][$label] ?? 0)}}</strong></td>
                                        @endforeach
                                        <td class="text-right"><strong>{{$default_currency?->name}} {{$default_currency?->symbol}}{{$this->fmt($grand_totals['total'] ?? 0)}}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
