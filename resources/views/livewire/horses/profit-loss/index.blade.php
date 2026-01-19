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

                        {{-- Header --}}
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
                                                        <span class="input-group-addon">Select Horse</span>
                                                        <select wire:model.debounce.300ms="selectedHorse" class="form-control">
                                                            <option value="">Select Horse</option>
                                                            @foreach ($horses as $horse)
                                                                <option value="{{ $horse->id }}">
                                                                    {{ $horse->registration_number }}
                                                                    {{ $horse->fleet_number ? '(' . $horse->fleet_number . ')' : '' }}
                                                                    {{ $horse->horse_make ? $horse->horse_make->name : '' }}
                                                                    {{ $horse->horse_model ? $horse->horse_model->name : '' }}
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

                                            @if (!empty($selectedHorse) && !empty($from) && !empty($to))
                                                <div class="col-lg-2">
                                                    <a href="{{ route('horses.statement.preview', ['selectedHorse' => $selectedHorse, 'from' => $from, 'to' => $to]) }}"
                                                       class="btn btn-default border-primary btn-rounded btn-wide">
                                                        <i class="fa fa-eye"></i> Preview Report (PDF)
                                                    </a>
                                                </div>
                                            @endif
                                        </div>

                                        <br><br>

                                        {{-- Summary/Details Toggle --}}
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

                                        {{-- Top KPI Row --}}
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
                                                @if (isset($selected_horse))
                                                    <strong>
                                                        {{ $selected_horse->registration_number }}
                                                        {{ $selected_horse->fleet_number ? $selected_horse->fleet_number : '' }}
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

                                        {{-- ===================== SUMMARY VIEW ===================== --}}
                                        @if (($summary === 'summary') && empty($details))

                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n">
                                                    <span style="margin-left:5px">Income</span>
                                                </div>
                                                <div class="col-xs-6 p-n">
                                                    <span style="float:right">
                                                        {{ $default_currency->name }} {{ $default_currency->symbol }}
                                                        {{ number_format((float)($total_income ?? 0), 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <hr style="width:100%;" size="3" color="black">

                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n">
                                                    <span style="margin-left:5px">Cost of Goods Sold</span>
                                                </div>
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
                                                    <p>
                                                        <span style="float:right">
                                                            {{ number_format((float)($gross_profit_percentage ?? 0), 2) }}%
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>

                                            <br>
                                            <hr style="width:100%;" size="3" color="black">

                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n">
                                                    <span style="margin-left:5px">Operating Expenses</span>
                                                </div>
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
                                                    <p>
                                                        <span style="float:right">
                                                            {{ number_format((float)($net_profit_percentage ?? 0), 2) }}%
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>

                                        {{-- ===================== DETAILS VIEW ===================== --}}
                                        @elseif (($details === 'details') && empty($summary))

                                            {{-- Income --}}
                                            <div class="col-xs-12 p-n" style="background-color:#D3D3D3">
                                                <div class="col-xs-5 p-n">
                                                    <strong><span style="margin-left:5px">Income</span></strong>
                                                </div>
                                            </div>
                                            <hr style="width:100%;" size="3" color="black">

                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n">
                                                    <span style="margin-left:5px">Sales</span>
                                                </div>
                                                <div class="col-xs-6 p-n">
                                                    <span style="float:right">
                                                        {{ $default_currency->name }} {{ $default_currency->symbol }}
                                                        {{ number_format((float)($total_income ?? 0), 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <hr style="width:100%;" size="3" color="black">

                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n">
                                                    <strong><span style="margin-left:5px">Total Income</span></strong>
                                                </div>
                                                <div class="col-xs-6 p-n">
                                                    <span style="float:right">
                                                        {{ $default_currency->name }} {{ $default_currency->symbol }}
                                                        {{ number_format((float)($total_income ?? 0), 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <hr style="width:100%;" size="3" color="black">

                                            {{-- COGS --}}
                                            <div class="col-xs-12 p-n" style="background-color:#D3D3D3">
                                                <div class="col-xs-5 p-n">
                                                    <strong><span style="margin-left:5px">Cost of Goods Sold</span></strong>
                                                </div>
                                            </div>
                                            <hr style="width:100%;" size="3" color="black">

                                            @php $cogsLines = $cogs_lines ?? []; @endphp

                                            @if(!empty($cogsLines))
                                                @foreach($cogsLines as $line)
                                                    <div class="col-xs-12 p-n">
                                                        <div class="col-xs-5 p-n">
                                                            <span style="margin-left:5px">{{ $line['name'] ?? '—' }}</span>
                                                        </div>
                                                        <div class="col-xs-6 p-n">
                                                            <span style="float:right">
                                                                {{ $default_currency->name }} {{ $default_currency->symbol }}
                                                                {{ number_format((float)($line['amount'] ?? 0), 2) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <hr style="width:100%;" size="3" color="black">
                                                @endforeach
                                            @else
                                                <div class="col-xs-12 p-n">
                                                    <div class="col-xs-5 p-n">
                                                        <span style="margin-left:5px">No COGS posted for this period</span>
                                                    </div>
                                                    <div class="col-xs-6 p-n">
                                                        <span style="float:right">
                                                            {{ $default_currency->name }} {{ $default_currency->symbol }}0.00
                                                        </span>
                                                    </div>
                                                </div>
                                                <hr style="width:100%;" size="3" color="black">
                                            @endif

                                            {{-- Gross Profit --}}
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
                                                    <p>
                                                        <span style="float:right">
                                                            {{ number_format((float)($gross_profit_percentage ?? 0), 2) }}%
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>

                                            <br>
                                            <hr style="width:100%;" size="3" color="black">

                                            {{-- Operating Expenses --}}
                                            <div class="col-xs-12 p-n" style="background-color:#D3D3D3">
                                                <div class="col-xs-5 p-n">
                                                    <strong><span style="margin-left:5px">Operating Expenses</span></strong>
                                                </div>
                                            </div>
                                            <hr style="width:100%;" size="3" color="black">

                                            @php $opexLines = $opex_lines ?? []; @endphp

                                            @if(!empty($opexLines))
                                                @foreach($opexLines as $line)
                                                    <div class="col-xs-12 p-n">
                                                        <div class="col-xs-5 p-n">
                                                            <span style="margin-left:5px">{{ $line['name'] ?? '—' }}</span>
                                                        </div>
                                                        <div class="col-xs-6 p-n">
                                                            <span style="float:right">
                                                                {{ $default_currency->name }} {{ $default_currency->symbol }}
                                                                {{ number_format((float)($line['amount'] ?? 0), 2) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <hr style="width:100%;" size="3" color="black">
                                                @endforeach
                                            @else
                                                <div class="col-xs-12 p-n">
                                                    <div class="col-xs-5 p-n">
                                                        <span style="margin-left:5px">No Operating Expenses posted for this period</span>
                                                    </div>
                                                    <div class="col-xs-6 p-n">
                                                        <span style="float:right">
                                                            {{ $default_currency->name }} {{ $default_currency->symbol }}0.00
                                                        </span>
                                                    </div>
                                                </div>
                                                <hr style="width:100%;" size="3" color="black">
                                            @endif

                                            {{-- Net Profit --}}
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
                                                    <p>
                                                        <span style="float:right">
                                                            {{ number_format((float)($net_profit_percentage ?? 0), 2) }}%
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>

                                        @endif

                                    </div>{{-- panel-body --}}
                                </div>{{-- panel --}}
                            </div>{{-- col --}}
                        </div>{{-- panel-body --}}
                    </div>{{-- panel --}}
                </div>
            </div>
        </div>
    </section>
</div>