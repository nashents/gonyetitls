<div>
    <section class="section">
        <x-loading/>

        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">

                        <div>
                            @include('includes.messages')
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel">
                                    <div class="panel-heading">
                                        <div class="panel-title">
                                            <h5>Profit &amp; Loss</h5>
                                        </div>
                                    </div>

                                    <div class="panel-body">

                                        {{-- Filters --}}
                                        <div class="row">
                                            <form wire:submit.prevent="generateStatement()">
                                                <div class="col-lg-4">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">Select Transporter</span>
                                                        <select wire:model.debounce.300ms="selectedTransporter" class="form-control">
                                                            <option value="">Select Transporter</option>
                                                            @foreach ($transporters as $transporter)
                                                                <option value="{{ $transporter->id }}">
                                                                    {{ $transporter->name }}
                                                                    {{ $transporter->transport_number ? '(' . $transporter->transport_number . ')' : '' }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-2">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">From</span>
                                                        <input type="date" wire:model.debounce.300ms="from" class="form-control">
                                                    </div>
                                                </div>

                                                <div class="col-lg-2" style="margin-left: 10px">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">To</span>
                                                        <input type="date" wire:model.debounce.300ms="to" class="form-control">
                                                    </div>
                                                </div>
                                            </form>

                                            @if (!empty($selectedTransporter) && !empty($from) && !empty($to))
                                                <div class="col-lg-2">
                                                    <a href="{{ route('transporters.statement.preview', ['selectedTransporter' => $selectedTransporter, 'from' => $from, 'to' => $to]) }}"
                                                       class="btn btn-default border-primary btn-rounded btn-wide">
                                                        <i class="fa fa-eye"></i> Preview Report (PDF)
                                                    </a>
                                                </div>
                                            @endif
                                        </div>

                                        <br><br>

                                        {{-- Toggle --}}
                                        <div class="row">
                                            <div class="col-md-4"></div>

                                            <div class="col-md-6">
                                                <div class="col-lg-2">
                                                    <div class="btn-group">
                                                        <button type="button"
                                                                wire:click.prevent="set_report('summary')"
                                                                class="btn btn-default {{ ($summary === 'summary') ? 'border-primary' : '' }} btn-wide btn-rounded">
                                                            Summary
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="col-lg-2" style="margin-left: 25px">
                                                    <div class="btn-group">
                                                        <button type="button"
                                                                wire:click.prevent="set_report('details')"
                                                                class="btn btn-default {{ ($details === 'details') ? 'border-primary' : '' }} btn-wide btn-rounded">
                                                            Details
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-2"></div>
                                        </div>

                                        {{-- KPI Row --}}
                                        <div class="row">
                                            <center>
                                                <div class="col-md-2"></div>

                                                <div class="col-md-2">
                                                    <h5>Income</h5>
                                                    <br>
                                                    <h4 style="margin-top:-15px">
                                                        {{ $default_currency->name }} {{ $default_currency->symbol }}
                                                        {{ number_format((float)($total_income ?? 0), 2) }} -
                                                    </h4>
                                                </div>

                                                <div class="col-md-2">
                                                    <h5>Cost Of Goods Sold</h5>
                                                    <br>
                                                    <h4 style="margin-top:-15px">
                                                        {{ $default_currency->name }} {{ $default_currency->symbol }}
                                                        {{ number_format((float)($total_cost_of_goods_sold ?? 0), 2) }} -
                                                    </h4>
                                                </div>

                                                <div class="col-md-2">
                                                    <h5>Operating Expenses</h5>
                                                    <br>
                                                    <h4 style="margin-top:-15px">
                                                        {{ $default_currency->name }} {{ $default_currency->symbol }}
                                                        {{ number_format((float)($total_operating_expenses ?? 0), 2) }} =
                                                    </h4>
                                                </div>

                                                <div class="col-md-2">
                                                    <h5>Net Profit</h5>
                                                    <br>
                                                    <h4 style="margin-top:-15px">
                                                        {{ $default_currency->name }} {{ $default_currency->symbol }}
                                                        {{ number_format((float)($net_profit ?? 0), 2) }}
                                                    </h4>
                                                </div>

                                                <div class="col-md-2"></div>
                                            </center>
                                        </div>

                                        {{-- Fleet stats --}}
                                        @if (!empty($selected_transporter))
                                            <div class="row">
                                                <center>
                                                    <div class="col-md-2"></div>
                                                    <div class="col-md-2"><small>Trucks</small><br><strong>{{ $total_trucks ?? 0 }}</strong></div>
                                                    <div class="col-md-2"><small>Trips</small><br><strong>{{ $total_trips ?? 0 }}</strong></div>
                                                    <div class="col-md-2"><small>Fuel Orders</small><br><strong>{{ $total_fuel_orders ?? 0 }}</strong></div>
                                                    <div class="col-md-2"><small>Fuel (L)</small><br><strong>{{ number_format((float)($total_fuel ?? 0), 2) }}</strong></div>
                                                    <div class="col-md-2"></div>
                                                </center>
                                            </div>
                                        @endif

                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Body --}}
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-10 col-md-offset-1">
                                <div class="panel">

                                    <div class="panel-heading">
                                        <div class="panel-title">
                                            <div class="col-xs-5 p-n">
                                                @if (!empty($selected_transporter))
                                                    <strong>
                                                        {{ $selected_transporter->name }}
                                                        {{ $selected_transporter->transport_number ? '('.$selected_transporter->transport_number.')' : '' }}
                                                    </strong>
                                                @endif
                                            </div>

                                            <div class="col-xs-6 p-n">
                                                <p style="font-size: 15px;">
                                                    <strong><span style="float:right">From: {{ $from }}</span></strong>
                                                </p>
                                                <p style="font-size: 15px;">
                                                    <strong><span style="float:right">To: {{ $to }}</span></strong>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <br><br>
                                    <hr style="width:100%;" size="3" color="black">

                                    <div class="panel-body">

                                        {{-- SUMMARY --}}
                                        @if (($summary === 'summary') && empty($details))

                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n"><span style="margin-left:5px">Income</span></div>
                                                <div class="col-xs-6 p-n">
                                                    <span style="float:right">
                                                        {{ $default_currency->name }} {{ $default_currency->symbol }}
                                                        {{ number_format((float)($total_income ?? 0), 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <hr style="width:100%;" size="3" color="black">

                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n"><span style="margin-left:5px">Cost of Goods Sold</span></div>
                                                <div class="col-xs-6 p-n">
                                                    <span style="float:right">
                                                        {{ $default_currency->name }} {{ $default_currency->symbol }}
                                                        {{ number_format((float)($total_cost_of_goods_sold ?? 0), 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <hr style="width:100%;" size="3" color="black">

                                            <div class="col-xs-12 p-n" style="background-color:#D3D3D3">
                                                <div class="col-xs-5 p-n" style="margin-top:10px; margin-bottom:-10px;">
                                                    <strong><span style="margin-left:5px">GROSS PROFIT</span></strong>
                                                    <p style="margin-left:5px">As a percentage of Total Income</p>
                                                </div>
                                                <div class="col-xs-6 p-n" style="margin-top:10px; margin-bottom:-10px;">
                                                    <strong>
                                                        <span style="float:right">
                                                            {{ $default_currency->name }} {{ $default_currency->symbol }}
                                                            {{ number_format((float)($gross_profit ?? 0), 2) }}
                                                        </span>
                                                    </strong>
                                                    <br>
                                                    <p><span style="float:right">{{ number_format((float)($gross_profit_percentage ?? 0), 2) }}%</span></p>
                                                </div>
                                            </div>

                                            <br>
                                            <hr style="width:100%;" size="3" color="black">

                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n"><span style="margin-left:5px">Operating Expenses</span></div>
                                                <div class="col-xs-6 p-n">
                                                    <span style="float:right">
                                                        {{ $default_currency->name }} {{ $default_currency->symbol }}
                                                        {{ number_format((float)($total_operating_expenses ?? 0), 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <hr style="width:100%;" size="3" color="black">

                                            <div class="col-xs-12 p-n" style="background-color:#D3D3D3">
                                                <div class="col-xs-5 p-n" style="margin-top:10px; margin-bottom:-10px;">
                                                    <strong><span style="margin-left:5px">NET PROFIT</span></strong>
                                                    <p style="margin-left:5px">As a percentage of Total Income</p>
                                                </div>
                                                <div class="col-xs-6 p-n" style="margin-top:10px; margin-bottom:-10px;">
                                                    <strong>
                                                        <span style="float:right">
                                                            {{ $default_currency->name }} {{ $default_currency->symbol }}
                                                            {{ number_format((float)($net_profit ?? 0), 2) }}
                                                        </span>
                                                    </strong>
                                                    <br>
                                                    <p><span style="float:right">{{ number_format((float)($net_profit_percentage ?? 0), 2) }}%</span></p>
                                                </div>
                                            </div>

                                            <br>
                                            <hr style="width:100%;" size="3" color="black">

                                            {{-- Expenses by resource --}}
                                            <div class="col-xs-12 p-n" style="background-color:#D3D3D3">
                                                <div class="col-xs-12 p-n">
                                                    <strong><span style="margin-left:5px">Expenses By Resource</span></strong>
                                                </div>
                                            </div>
                                            <hr style="width:100%;" size="3" color="black">

                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n"><span style="margin-left:5px">Trucks</span></div>
                                                <div class="col-xs-6 p-n">
                                                    <span style="float:right">
                                                        {{ $default_currency->name }} {{ $default_currency->symbol }}
                                                        {{ number_format((float)($total_truck_expenses ?? 0), 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <hr style="width:100%;" size="1" color="#eee">

                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n"><span style="margin-left:5px">Trailers</span></div>
                                                <div class="col-xs-6 p-n">
                                                    <span style="float:right">
                                                        {{ $default_currency->name }} {{ $default_currency->symbol }}
                                                        {{ number_format((float)($total_trailer_expenses ?? 0), 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <hr style="width:100%;" size="1" color="#eee">

                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n"><span style="margin-left:5px">Drivers</span></div>
                                                <div class="col-xs-6 p-n">
                                                    <span style="float:right">
                                                        {{ $default_currency->name }} {{ $default_currency->symbol }}
                                                        {{ number_format((float)($total_driver_expenses ?? 0), 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <hr style="width:100%;" size="1" color="#eee">

                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n"><span style="margin-left:5px">Other</span></div>
                                                <div class="col-xs-6 p-n">
                                                    <span style="float:right">
                                                        {{ $default_currency->name }} {{ $default_currency->symbol }}
                                                        {{ number_format((float)($total_other_expenses ?? 0), 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <hr style="width:100%;" size="3" color="black">

                                        {{-- DETAILS: LINE ITEMS --}}
                                        @elseif (($details === 'details') && empty($summary))

                                            {{-- Income --}}
                                            <div class="col-xs-12 p-n" style="background-color:#D3D3D3">
                                                <div class="col-xs-5 p-n">
                                                    <strong><span style="margin-left:5px">Income</span></strong>
                                                </div>
                                            </div>
                                            <hr style="width:100%;" size="3" color="black">

                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n"><span style="margin-left:5px">Sales</span></div>
                                                <div class="col-xs-6 p-n">
                                                    <span style="float:right">
                                                        {{ $default_currency->name }} {{ $default_currency->symbol }}
                                                        {{ number_format((float)($total_income ?? 0), 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <hr style="width:100%;" size="3" color="black">

                                            {{-- COGS line items --}}
                                            <div class="col-xs-12 p-n" style="background-color:#D3D3D3">
                                                <div class="col-xs-12 p-n">
                                                    <strong><span style="margin-left:5px">Cost of Goods Sold (Line Items)</span></strong>
                                                </div>
                                            </div>
                                            <hr style="width:100%;" size="3" color="black">

                                            <div class="table-responsive">
                                                <table class="table table-bordered table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th style="width:110px;">Date</th>
                                                            <th style="width:130px;">Bill #</th>
                                                            <th style="width:130px;">Trip</th>
                                                            <th style="width:160px;">Account</th>
                                                            <th>Item</th>
                                                            <th style="width:150px;">Resource</th>
                                                            <th style="width:160px;">Expense Currency</th>
                                                            <th style="width:200px;" class="text-right">Amount ({{ $default_currency->name }} {{ $default_currency->symbol }})</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse(($cogs_items ?? []) as $it)
                                                            <tr>
                                                                <td>{{ $it['date'] ?? '' }}</td>
                                                                <td>{{ $it['bill_number'] ?? '' }}</td>
                                                                <td>{{ $it['trip_ref'] ?? '' }}</td>
                                                                <td>{{ $it['account_name'] ?? '—' }}</td>
                                                                <td>{{ $it['item_name'] ?? '—' }}</td>
                                                                <td>{{ $it['resource_type'] ?? 'Other' }}{{ !empty($it['resource_name']) ? ' - '.$it['resource_name'] : '' }}</td>
                                                                <td>{{ $it['expense_currency'] ?? '' }}</td>
                                                                <td class="text-right">{{ number_format((float)($it['amount'] ?? 0), 2) }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="8" class="text-center">No COGS line items for this period.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <th colspan="7" class="text-right">Total COGS</th>
                                                            <th class="text-right">{{ number_format((float)($total_cost_of_goods_sold ?? 0), 2) }}</th>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>

                                            {{-- Gross Profit --}}
                                            <div class="col-xs-12 p-n" style="background-color:#D3D3D3">
                                                <div class="col-xs-5 p-n" style="margin-top:10px; margin-bottom:-10px;">
                                                    <strong><span style="margin-left:5px">GROSS PROFIT</span></strong>
                                                    <p style="margin-left:5px">As a percentage of Total Income</p>
                                                </div>

                                                <div class="col-xs-6 p-n" style="margin-top:10px; margin-bottom:-10px;">
                                                    <strong><span style="float:right">{{ number_format((float)($gross_profit ?? 0), 2) }}</span></strong>
                                                    <br>
                                                    <p><span style="float:right">{{ number_format((float)($gross_profit_percentage ?? 0), 2) }}%</span></p>
                                                </div>
                                            </div>

                                            <br>
                                            <hr style="width:100%;" size="3" color="black">

                                            {{-- OPEX line items --}}
                                            <div class="col-xs-12 p-n" style="background-color:#D3D3D3">
                                                <div class="col-xs-12 p-n">
                                                    <strong><span style="margin-left:5px">Operating Expenses (Line Items)</span></strong>
                                                </div>
                                            </div>
                                            <hr style="width:100%;" size="3" color="black">

                                            <div class="table-responsive">
                                                <table class="table table-bordered table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th style="width:110px;">Date</th>
                                                            <th style="width:130px;">Bill #</th>
                                                            <th style="width:130px;">Trip</th>
                                                            <th style="width:160px;">Account</th>
                                                            <th>Item</th>
                                                            <th style="width:150px;">Resource</th>
                                                            <th style="width:160px;">Expense Currency</th>
                                                            <th style="width:200px;" class="text-right">Amount ({{ $default_currency->name }} {{ $default_currency->symbol }})</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse(($opex_items ?? []) as $it)
                                                            <tr>
                                                                <td>{{ $it['date'] ?? '' }}</td>
                                                                <td>{{ $it['bill_number'] ?? '' }}</td>
                                                                <td>{{ $it['trip_ref'] ?? '' }}</td>
                                                                <td>{{ $it['account_name'] ?? '—' }}</td>
                                                                <td>{{ $it['item_name'] ?? '—' }}</td>
                                                                <td>{{ $it['resource_type'] ?? 'Other' }}{{ !empty($it['resource_name']) ? ' - '.$it['resource_name'] : '' }}</td>
                                                                <td>{{ $it['expense_currency'] ?? '' }}</td>
                                                                <td class="text-right">{{ number_format((float)($it['amount'] ?? 0), 2) }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="8" class="text-center">No Operating Expense line items for this period.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <th colspan="7" class="text-right">Total Operating Expenses</th>
                                                            <th class="text-right">{{ number_format((float)($total_operating_expenses ?? 0), 2) }}</th>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>

                                            {{-- Net Profit --}}
                                            <div class="col-xs-12 p-n" style="background-color:#D3D3D3">
                                                <div class="col-xs-5 p-n" style="margin-top:10px; margin-bottom:-10px;">
                                                    <strong><span style="margin-left:5px">NET PROFIT</span></strong>
                                                    <p style="margin-left:5px">As a percentage of Total Income</p>
                                                </div>

                                                <div class="col-xs-6 p-n" style="margin-top:10px; margin-bottom:-10px;">
                                                    <strong><span style="float:right">{{ number_format((float)($net_profit ?? 0), 2) }}</span></strong>
                                                    <br>
                                                    <p><span style="float:right">{{ number_format((float)($net_profit_percentage ?? 0), 2) }}%</span></p>
                                                </div>
                                            </div>

                                            <br>
                                            <hr style="width:100%;" size="3" color="black">

                                            {{-- Expenses by resource --}}
                                            <div class="col-xs-12 p-n" style="background-color:#D3D3D3">
                                                <div class="col-xs-12 p-n">
                                                    <strong><span style="margin-left:5px">Expenses By Resource</span></strong>
                                                </div>
                                            </div>
                                            <hr style="width:100%;" size="3" color="black">

                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n"><span style="margin-left:5px">Trucks</span></div>
                                                <div class="col-xs-6 p-n">
                                                    <span style="float:right">
                                                        {{ $default_currency->name }} {{ $default_currency->symbol }}
                                                        {{ number_format((float)($total_truck_expenses ?? 0), 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <hr style="width:100%;" size="1" color="#eee">

                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n"><span style="margin-left:5px">Trailers</span></div>
                                                <div class="col-xs-6 p-n">
                                                    <span style="float:right">
                                                        {{ $default_currency->name }} {{ $default_currency->symbol }}
                                                        {{ number_format((float)($total_trailer_expenses ?? 0), 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <hr style="width:100%;" size="1" color="#eee">

                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n"><span style="margin-left:5px">Drivers</span></div>
                                                <div class="col-xs-6 p-n">
                                                    <span style="float:right">
                                                        {{ $default_currency->name }} {{ $default_currency->symbol }}
                                                        {{ number_format((float)($total_driver_expenses ?? 0), 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <hr style="width:100%;" size="1" color="#eee">

                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n"><span style="margin-left:5px">Other</span></div>
                                                <div class="col-xs-6 p-n">
                                                    <span style="float:right">
                                                        {{ $default_currency->name }} {{ $default_currency->symbol }}
                                                        {{ number_format((float)($total_other_expenses ?? 0), 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <hr style="width:100%;" size="3" color="black">

                                        @endif

                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
