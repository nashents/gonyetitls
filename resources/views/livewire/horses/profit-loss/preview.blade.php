<div>
    @section('title', ($selected_horse?->registration_number ?? 'Statement')
    .'_'.\Carbon\Carbon::parse($from)->format('Y-m-d')
    .'_'.\Carbon\Carbon::parse($to)->format('Y-m-d')
    .'_Profit_and_Loss'
)
    <div id="print-area">
        <div id="invoice">
            <x-loading/>

            {{-- Toolbar --}}
            <div class="toolbar hidden-print">
                <div class="text-end">
                    <button type="button" onclick="goBack()"
                            class="btn btn-default border-primary btn-wide btn-rounded">
                        <i class="fa fa-arrow-left" style="color:black"></i> Back
                    </button>

                    <a href="javascript:void(0)" onclick="printSection()"
                       class="btn btn-default border-primary btn-wide btn-rounded">
                        <i class="fa fa-print" style="color: black"></i> Print
                    </a>
                   
                </div>
                <hr>
            </div>

            <div class="invoice overflow-auto">
                <div style="min-width: 600px">

                    {{-- Header --}}
                    <header>
                        <div class="row">
                            <div class="col">
                                @if(!empty($company?->logo))
                                    <a href="javascript:;">
                                        <img src="{{ asset('images/uploads/'.$company->logo) }}" width="150" alt="Company Logo">
                                    </a>
                                @endif
                            </div>

                            <div class="col company-details">
                                <h4 class="name">
                                    <a href="javascript:;"
                                       style="color: {{ Auth::user()->employee->company?->color ?? Auth::user()->company?->color ?? '#000' }}">
                                        {{ $company?->name }}
                                    </a>
                                </h4>

                                <div>
                                    {{ $company?->street_address }} {{ $company?->suburb }}<br>
                                    {{ $company?->city }}, {{ $company?->country }}
                                </div>

                                <div>
                                    {{ $company?->phonenumber }}
                                    @if ($company?->second_phonenumber) | {{ $company->second_phonenumber }} @endif
                                    @if ($company?->third_phonenumber) | {{ $company->third_phonenumber }} @endif
                                </div>

                                <div>{{ $company?->email }}</div>
                                @if ($company?->second_email)
                                    <div>{{ $company->second_email }}</div>
                                @endif
                                @if ($company?->third_email)
                                    <div>{{ $company->third_email }}</div>
                                @endif

                                <br>
                                <h5 class="to">Statement of Comprehensive Income</h5>
                                <div><strong>{{ $selected_horse?->registration_number ?? '—' }}</strong></div>
                                <br>
                            </div>
                        </div>
                    </header>

                    <main>
                        {{-- Statement meta --}}
                        <div class="row contacts">
                            <div class="col invoice-to">
                                <div class="text-gray-light">Statement For:</div>

                                <h6 class="to">
                                    Horse:
                                    {{ $selected_horse?->registration_number ?? '—' }}
                                    {{ $selected_horse?->fleet_number ? '('.$selected_horse->fleet_number.')' : '' }}
                                    {{ $selected_horse?->horse_make?->name ? ' - '.$selected_horse->horse_make->name : '' }}
                                    {{ $selected_horse?->horse_model?->name ? ' '.$selected_horse->horse_model->name : '' }}
                                </h6>

                                <h6 class="to">
                                    Trailer(s): {{ $trailer_numbers ?? '—' }}
                                </h6>

                                <h6 class="to">
                                    Driver: {{ $driver_name ?? '—' }}
                                </h6>
                            </div>

                            <div class="col invoice-details">
                                <h5 class="to">
                                    {{ $default_currency?->fullname }}
                                    {{ $default_currency?->name }}
                                    {{ $default_currency?->symbol ? '(' . $default_currency->symbol . ')' : '' }}
                                </h5>

                                <div class="date" style="padding-bottom:3px;">
                                    <strong>From:</strong> {{ \Carbon\Carbon::parse($from)->format('d F Y') }}
                                </div>

                                <div class="date" style="padding-bottom:3px;">
                                    <strong>To:</strong> {{ \Carbon\Carbon::parse($to)->format('d F Y') }}
                                </div>
                            </div>
                        </div>

                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-12">
                                <div class="panel">

                                    {{-- Quick stats --}}
                                    <div class="panel-heading">
                                        <div class="panel-title">
                                            <div class="col-xs-12 p-n">
                                                <div class="text-gray-light">
                                                    Total Trip(s): <strong>{{ $total_trips ?? 0 }}</strong>
                                                </div>
                                                <div class="text-gray-light">
                                                    Total Fuel Order(s): <strong>{{ $total_fuel_orders ?? 0 }}</strong>
                                                </div>
                                                <div class="text-gray-light">
                                                    Total Fuel Usage:
                                                    <strong>{{ isset($total_fuel) ? number_format($total_fuel, 2) . ' Litres' : '0.00 Litres' }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="mb-3 mt-3" style="width:100%;" size="3" color="black">

                                    <div class="panel-body">

                                        {{-- ===================== INCOME ===================== --}}
                                        <div class="col-xs-12 p-n" style="background-color:#D3D3D3">
                                            <div class="col-xs-12 p-n">
                                                <strong><span style="margin-left:5px">Income</span></strong>
                                            </div>
                                        </div>

                                        <hr class="mb-3 mt-3" style="width:100%;" size="3" color="black">

                                        <div class="col-xs-12 p-n">
                                            <div class="col-xs-12 p-n">
                                                <span style="margin-left:5px">Sales</span>
                                                <span style="float:right; padding-right:5px;">
                                                    {{ $default_currency?->name }} {{ $default_currency?->symbol }}
                                                    {{ number_format((float)($total_income ?? 0), 2) }}
                                                </span>
                                            </div>
                                        </div>

                                        <hr style="width:100%;" size="3" color="black">

                                        <div class="col-xs-12 p-n">
                                            <div class="col-xs-12 p-n">
                                                <strong><span style="margin-left:5px">Total Income</span></strong>
                                                <span style="float:right; padding-right:5px;">
                                                    {{ $default_currency?->name }} {{ $default_currency?->symbol }}
                                                    {{ number_format((float)($total_income ?? 0), 2) }}
                                                </span>
                                            </div>
                                        </div>

                                        <hr style="width:100%;" size="3" color="black">

                                        {{-- ===================== COGS (SUMMARY BY ACCOUNT) ===================== --}}
                                        <div class="col-xs-12 p-n" style="background-color:#D3D3D3">
                                            <div class="col-xs-12 p-n">
                                                <strong><span style="margin-left:5px">Cost of Goods Sold</span></strong>
                                            </div>
                                        </div>

                                        <hr style="width:100%;" size="3" color="black">

                                        @if(!empty($cogs_lines))
                                            @foreach($cogs_lines as $line)
                                                <div class="col-xs-12 p-n">
                                                    <div class="col-xs-12 p-n">
                                                        <span style="margin-left:5px">{{ $line['name'] ?? '—' }}</span>
                                                        <span style="float:right; padding-right:5px;">
                                                            {{ $default_currency?->name }} {{ $default_currency?->symbol }}
                                                            {{ number_format((float)($line['amount'] ?? 0), 2) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <hr style="width:100%;" size="3" color="black">
                                            @endforeach
                                        @else
                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-12 p-n">
                                                    <span style="margin-left:5px">No COGS posted for this period</span>
                                                    <span style="float:right; padding-right:5px;">
                                                        {{ $default_currency?->name }} {{ $default_currency?->symbol }}0.00
                                                    </span>
                                                </div>
                                            </div>
                                            <hr style="width:100%;" size="3" color="black">
                                        @endif

                                        <div class="col-xs-12 p-n">
                                            <div class="col-xs-12 p-n">
                                                <strong><span style="margin-left:5px">Total Cost of Goods Sold</span></strong>
                                                <span style="float:right; padding-right:5px;">
                                                    {{ $default_currency?->name }} {{ $default_currency?->symbol }}
                                                    {{ number_format((float)($total_cost_of_goods_sold ?? 0), 2) }}
                                                </span>
                                            </div>
                                        </div>

                                        <hr style="width:100%;" size="3" color="black">

                                        {{-- ===================== GROSS PROFIT ===================== --}}
                                        <div class="col-xs-12 p-n" style="background-color:#D3D3D3">
                                            <div class="col-xs-6 p-n" style="margin-top:10px; margin-bottom:-10px;">
                                                <strong><span style="margin-left:5px">GROSS PROFIT</span></strong>
                                                <p style="margin-left:5px">As a percentage of Total Income</p>
                                            </div>

                                            <div class="col-xs-6 p-n">
                                                <strong>
                                                    <span style="float:right; margin-top:-55px; padding-right:5px;">
                                                        {{ $default_currency?->name }} {{ $default_currency?->symbol }}
                                                        {{ number_format((float)($gross_profit ?? 0), 2) }}
                                                    </span>
                                                </strong>
                                                <br>
                                                <p>
                                                    <span style="float:right; margin-top:-55px; padding-right:5px;">
                                                        {{ number_format((float)($gross_profit_percentage ?? 0), 2) }}%
                                                    </span>
                                                </p>
                                            </div>
                                        </div>

                                        <hr style="width:100%;" size="3" color="black">

                                        {{-- ===================== OPEX (SUMMARY BY ACCOUNT) ===================== --}}
                                        <div class="col-xs-12 p-n" style="background-color:#D3D3D3">
                                            <div class="col-xs-12 p-n">
                                                <strong><span style="margin-left:5px">Operating Expenses</span></strong>
                                            </div>
                                        </div>

                                        <hr style="width:100%;" size="3" color="black">

                                        @if(!empty($opex_lines))
                                            @foreach($opex_lines as $line)
                                                <div class="col-xs-12 p-n">
                                                    <div class="col-xs-12 p-n">
                                                        <span style="margin-left:5px">{{ $line['name'] ?? '—' }}</span>
                                                        <span style="float:right; padding-right:5px;">
                                                            {{ $default_currency?->name }} {{ $default_currency?->symbol }}
                                                            {{ number_format((float)($line['amount'] ?? 0), 2) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <hr style="width:100%;" size="3" color="black">
                                            @endforeach
                                        @else
                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-12 p-n">
                                                    <span style="margin-left:5px">No Operating Expenses posted for this period</span>
                                                    <span style="float:right; padding-right:5px;">
                                                        {{ $default_currency?->name }} {{ $default_currency?->symbol }}0.00
                                                    </span>
                                                </div>
                                            </div>
                                            <hr style="width:100%;" size="3" color="black">
                                        @endif

                                        <div class="col-xs-12 p-n">
                                            <div class="col-xs-12 p-n">
                                                <strong><span style="margin-left:5px">Total Operating Expenses</span></strong>
                                                <span style="float:right; padding-right:5px;">
                                                    {{ $default_currency?->name }} {{ $default_currency?->symbol }}
                                                    {{ number_format((float)($total_operating_expenses ?? 0), 2) }}
                                                </span>
                                            </div>
                                        </div>

                                        <hr style="width:100%;" size="3" color="black">

                                        {{-- ===================== NET PROFIT ===================== --}}
                                        <div class="col-xs-12 p-n" style="background-color:#D3D3D3">
                                            <div class="col-xs-6 p-n" style="margin-top:10px; margin-bottom:-10px;">
                                                <strong><span style="margin-left:5px">NET PROFIT</span></strong>
                                                <p style="margin-left:5px">As a percentage of Total Income</p>
                                            </div>

                                            <div class="col-xs-6 p-n">
                                                <strong>
                                                    <span style="float:right; margin-top:-55px; padding-right:5px;">
                                                        {{ $default_currency?->name }} {{ $default_currency?->symbol }}
                                                        {{ number_format((float)($net_profit ?? 0), 2) }}
                                                    </span>
                                                </strong>
                                                <br>
                                                <p>
                                                    <span style="float:right; margin-top:-55px; padding-right:5px;">
                                                        {{ number_format((float)($net_profit_percentage ?? 0), 2) }}%
                                                    </span>
                                                </p>
                                            </div>
                                        </div>

                                        {{-- ===================== OPTIONAL: DETAIL TABLES (VERY HELPFUL FOR RECON) ===================== --}}
                                        <br><br>
                                        <hr style="width:100%;" size="3" color="black">

                                        <h5 style="margin-left:5px;"><strong>COGS Line Items</strong></h5>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th style="width:110px;">Date</th>
                                                        <th style="width:140px;">Bill #</th>
                                                        <th style="width:140px;">Trip</th>
                                                        <th style="width:180px;">Account</th>
                                                        <th>Item</th>
                                                        <th style="width:170px;">Expense Currency</th>
                                                        <th style="width:190px;" class="text-right">Amount</th>
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
                                                            <td>{{ $it['expense_currency'] ?? '' }}</td>
                                                            <td class="text-right">{{ number_format((float)($it['amount'] ?? 0), 2) }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="7" class="text-center">No COGS line items for this period.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>

                                        <br>
                                        <h5 style="margin-left:5px;"><strong>Operating Expense Line Items</strong></h5>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th style="width:110px;">Date</th>
                                                        <th style="width:140px;">Bill #</th>
                                                        <th style="width:140px;">Trip</th>
                                                        <th style="width:180px;">Account</th>
                                                        <th>Item</th>
                                                        <th style="width:170px;">Expense Currency</th>
                                                        <th style="width:190px;" class="text-right">Amount</th>
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
                                                            <td>{{ $it['expense_currency'] ?? '' }}</td>
                                                            <td class="text-right">{{ number_format((float)($it['amount'] ?? 0), 2) }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="7" class="text-center">No Operating Expense line items for this period.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </main>

                </div>
                <div></div>
            </div>
        </div>
    </div>
</div>