<div>

    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Freight Job Profitability</h5>
                                <small style="color: green">Revenue, cost and margin across freight jobs opened in the selected date range.</small>
                            </div>
                        </div>
                        <div class="panel-body">

                            <div class="row hidden-print">
                                <form wire:submit.prevent="generateStatement()">
                                    <div class="col-lg-2">
                                        <div class="input-group">
                                            <span class="input-group-addon">From</span>
                                            <input type="date" wire:model.debounce.300ms="from" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <div class="input-group">
                                            <span class="input-group-addon">To</span>
                                            <input type="date" wire:model.debounce.300ms="to" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <select class="form-control" wire:model="customer_id">
                                            <option value="">All Customers</option>
                                            @foreach ($this->customers as $customer)
                                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-2">
                                        <select class="form-control" wire:model="salesperson_id">
                                            <option value="">All Salespersons</option>
                                            @foreach ($this->salespersons as $salesperson)
                                                <option value="{{ $salesperson->id }}">{{ $salesperson->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-2">
                                        <select class="form-control" wire:model="freight_service_type_id">
                                            <option value="">All Service Types</option>
                                            @foreach ($this->freightServiceTypes as $type)
                                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </form>
                            </div>
                            <br>
                            <div class="row hidden-print">
                                <div class="col-lg-2">
                                    <select class="form-control" wire:model="primary_transport_mode">
                                        <option value="">All Modes</option>
                                        @foreach (\App\Services\Freight\JobProfitabilityCalculator::TRANSPORT_MODES as $mode)
                                            <option value="{{ $mode }}">{{ ucfirst($mode) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2">
                                    <select class="form-control" wire:model="status">
                                        <option value="">All Statuses</option>
                                        @foreach (\App\Services\Freight\JobProfitabilityCalculator::STATUSES as $statusOption)
                                            <option value="{{ $statusOption }}">{{ ucwords(str_replace('_', ' ', $statusOption)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <br><br>

                            <div class="row hidden-print">
                                <div class="col-md-12 text-center">
                                    <div class="btn-group">
                                        <button wire:click.prevent="set_report('summary')" class="btn btn-default {{$summary == "summary" ? 'border-primary' : ""}} btn-wide btn-rounded" type="button">Summary</button>
                                        <button wire:click.prevent="set_report('details')" class="btn btn-default {{$details == "details" ? 'border-primary' : ""}} btn-wide btn-rounded" type="button">Details</button>
                                    </div>
                                    <div class="btn-group" style="margin-left: 25px">
                                        <a href="{{ route('freight.reports.job_profitability.print', ['from' => $from, 'to' => $to, 'customer_id' => $customer_id, 'salesperson_id' => $salesperson_id, 'freight_service_type_id' => $freight_service_type_id, 'primary_transport_mode' => $primary_transport_mode, 'status' => $status, 'view' => $this->viewMode()]) }}" target="_blank" class="btn btn-default btn-wide btn-rounded">
                                            <i class="fa fa-print"></i> Print
                                        </a>
                                        <a href="{{ route('freight.reports.job_profitability.pdf', ['from' => $from, 'to' => $to, 'customer_id' => $customer_id, 'salesperson_id' => $salesperson_id, 'freight_service_type_id' => $freight_service_type_id, 'primary_transport_mode' => $primary_transport_mode, 'status' => $status, 'view' => $this->viewMode()]) }}" target="_blank" class="btn btn-default btn-wide btn-rounded">
                                            <i class="fa fa-file-pdf-o"></i> Export PDF
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <br>

                            <div class="row">
                                <div class="col-md-10 col-md-offset-1">
                                    <div class="row text-center">
                                        <div class="col-md-3">
                                            <h5>Jobs</h5><br>
                                            <h4 style="margin-top:-15px">{{ $grand_totals['jobCount'] ?? 0 }}</h4>
                                        </div>
                                        <div class="col-md-3">
                                            <h5>Total Revenue</h5><br>
                                            <h4 style="margin-top:-15px">{{$default_currency?->name}} {{$default_currency?->symbol}}{{$this->fmt($grand_totals['revenue'] ?? 0)}}</h4>
                                        </div>
                                        <div class="col-md-3">
                                            <h5>Total Cost</h5><br>
                                            <h4 style="margin-top:-15px">{{$default_currency?->name}} {{$default_currency?->symbol}}{{$this->fmt($grand_totals['cost'] ?? 0)}}</h4>
                                        </div>
                                        <div class="col-md-3">
                                            <h5>Gross Margin</h5><br>
                                            <h4 style="margin-top:-15px">{{$default_currency?->name}} {{$default_currency?->symbol}}{{$this->fmt($grand_totals['margin'] ?? 0)}} ({{ $grand_totals['marginPct'] ?? 0 }}%)</h4>
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
                                <div class="col-xs-5 p-n"><strong>{{ $details ? 'JOBS' : 'CUSTOMERS' }}</strong></div>
                                <div class="col-xs-6 p-n"><p style="font-size: 15px;"><strong><span style="float: right">{{ $from }} to {{ $to }}</span></strong></p></div>
                            </div>
                        </div>
                        <hr style="width:100%;" size="3" color="black">
                        <div class="panel-body">
                            <table class="table table-bordered table-striped">
                                @if ($details)
                                <thead>
                                    <tr>
                                        <th>Job #</th><th>Customer</th><th>Status</th><th>Opened</th>
                                        <th class="text-right">Revenue</th><th class="text-right">Cost</th><th class="text-right">Margin</th><th class="text-right">Margin %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($rows as $row)
                                    <tr>
                                        <td>{{ $row['job_number'] }}</td>
                                        <td>{{ $row['customer'] }}</td>
                                        <td>{{ ucwords(str_replace('_', ' ', $row['status'])) }}</td>
                                        <td>{{ $row['opened_at'] }}</td>
                                        <td class="text-right">{{$this->fmt($row['revenue'])}}</td>
                                        <td class="text-right">{{$this->fmt($row['cost'])}}</td>
                                        <td class="text-right">{{$this->fmt($row['margin'])}}</td>
                                        <td class="text-right">{{ $row['marginPct'] }}%</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="8" class="text-center">No jobs found for this period/filter combination.</td></tr>
                                    @endforelse
                                    <tr style="background-color: #D3D3D3">
                                        <td colspan="4"><strong>Total ({{ $grand_totals['jobCount'] ?? 0 }} jobs)</strong></td>
                                        <td class="text-right"><strong>{{$this->fmt($grand_totals['revenue'] ?? 0)}}</strong></td>
                                        <td class="text-right"><strong>{{$this->fmt($grand_totals['cost'] ?? 0)}}</strong></td>
                                        <td class="text-right"><strong>{{$this->fmt($grand_totals['margin'] ?? 0)}}</strong></td>
                                        <td class="text-right"><strong>{{ $grand_totals['marginPct'] ?? 0 }}%</strong></td>
                                    </tr>
                                </tbody>
                                @else
                                <thead>
                                    <tr>
                                        <th>Customer</th><th class="text-right">Jobs</th>
                                        <th class="text-right">Revenue</th><th class="text-right">Cost</th><th class="text-right">Margin</th><th class="text-right">Margin %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($rows as $row)
                                    <tr>
                                        <td>{{ $row['label'] }}</td>
                                        <td class="text-right">{{ $row['jobCount'] }}</td>
                                        <td class="text-right">{{$this->fmt($row['revenue'])}}</td>
                                        <td class="text-right">{{$this->fmt($row['cost'])}}</td>
                                        <td class="text-right">{{$this->fmt($row['margin'])}}</td>
                                        <td class="text-right">{{ $row['marginPct'] }}%</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="6" class="text-center">No jobs found for this period/filter combination.</td></tr>
                                    @endforelse
                                    <tr style="background-color: #D3D3D3">
                                        <td><strong>Total</strong></td>
                                        <td class="text-right"><strong>{{ $grand_totals['jobCount'] ?? 0 }}</strong></td>
                                        <td class="text-right"><strong>{{$this->fmt($grand_totals['revenue'] ?? 0)}}</strong></td>
                                        <td class="text-right"><strong>{{$this->fmt($grand_totals['cost'] ?? 0)}}</strong></td>
                                        <td class="text-right"><strong>{{$this->fmt($grand_totals['margin'] ?? 0)}}</strong></td>
                                        <td class="text-right"><strong>{{ $grand_totals['marginPct'] ?? 0 }}%</strong></td>
                                    </tr>
                                </tbody>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
