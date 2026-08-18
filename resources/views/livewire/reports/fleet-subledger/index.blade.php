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
                                            <h5>Fleet Control Accounts (Horses / Trailers / Vehicles / Drivers)</h5>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <p class="text-muted">
                                            Horses, trailers, vehicles, and drivers are tagged on every posted journal entry line
                                            rather than kept as separate Chart of Accounts entries, so nothing here duplicates
                                            an account you'd otherwise add manually - this report gives each one its own
                                            control-account-style total, with the real ledger accounts it's made up of underneath.
                                        </p>

                                        <div class="row hidden-print" style="margin-bottom: 15px;">
                                            <div class="col-md-12">
                                                <ul class="nav nav-tabs">
                                                    @foreach ($dimensions as $key => $config)
                                                        <li class="{{ $dimension === $key ? 'active' : '' }}">
                                                            <a href="#" wire:click.prevent="setDimension('{{ $key }}')">
                                                                {{ $config['label'] }}s
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>

                                        <div class="row hidden-print">
                                            <form wire:submit.prevent="generateStatement()">
                                                <div class="col-lg-2">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">From</span>
                                                        <input type="date" wire:model.debounce.500ms="date_from" class="form-control" aria-label="...">
                                                    </div>
                                                </div>
                                                <div class="col-lg-2" style="margin-left: 10px">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">To</span>
                                                        <input type="date" wire:model.debounce.500ms="date_to" class="form-control" aria-label="...">
                                                    </div>
                                                </div>
                                                <div class="col-lg-3" style="margin-left: 10px">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">Search</span>
                                                        <input type="text" wire:model.debounce.500ms="search" class="form-control" placeholder="Search {{ strtolower($dimensions[$dimension]['label']) }}..." aria-label="...">
                                                    </div>
                                                </div>
                                            </form>
                                        </div>

                                        <br>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-10 col-md-offset-1">
                                <div class="panel">
                                    <div class="panel-heading">
                                        <div class="panel-title">
                                            <div class="col-xs-5 p-n">
                                                <strong>{{ strtoupper($dimensions[$dimension]['label']) }}S</strong>
                                            </div>
                                            <div class="col-xs-6 p-n">
                                                <p style="font-size: 15px;"><strong><span style="float: right">From: {{ $date_from }}</span></strong></p>
                                                <p style="font-size: 15px;"><strong><span style="float: right">To: {{ $date_to }}</span></strong></p>
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <hr style="width:100%;" size="3" color="black">

                                    <div class="panel-body">
                                        @forelse ($rows as $row)
                                            <table class="table table-bordered" style="margin-bottom: 25px;">
                                                <thead>
                                                    <tr style="background-color: #D3D3D3;">
                                                        <th style="width: 55%; font-weight: bold;">{{ $row['label'] }}</th>
                                                        <th style="width: 15%" class="text-right">Debit</th>
                                                        <th style="width: 15%" class="text-right">Credit</th>
                                                        <th style="width: 15%" class="text-right">Balance</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($row['accounts'] as $account)
                                                        <tr>
                                                            <td class="text-muted">&nbsp;&nbsp;{{ $account['name'] }}</td>
                                                            <td class="text-right">
                                                                @if ($account['debit'] > 0)
                                                                    {{ number_format($account['debit'], 2) }}
                                                                @else
                                                                    <span class="text-muted">&mdash;</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-right">
                                                                @if ($account['credit'] > 0)
                                                                    {{ number_format($account['credit'], 2) }}
                                                                @else
                                                                    <span class="text-muted">&mdash;</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-right">
                                                                <span class="{{ $account['balance'] < 0 ? 'text-danger' : ($account['balance'] > 0 ? 'text-success' : 'text-muted') }}">
                                                                    {{ $account['balance'] < 0 ? '(' . number_format(abs($account['balance']), 2) . ')' : number_format($account['balance'], 2) }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    <tr style="background-color: #f1f1f1;">
                                                        <td style="font-weight: bold;">{{ $row['label'] }} Total</td>
                                                        <td class="text-right" style="font-weight: bold;">{{ number_format($row['total_debit'], 2) }}</td>
                                                        <td class="text-right" style="font-weight: bold;">{{ number_format($row['total_credit'], 2) }}</td>
                                                        <td class="text-right" style="font-weight: bold;">
                                                            @php $rowBalance = $row['total_debit'] - $row['total_credit']; @endphp
                                                            <span class="{{ $rowBalance < 0 ? 'text-danger' : ($rowBalance > 0 ? 'text-success' : 'text-muted') }}">
                                                                {{ $rowBalance < 0 ? '(' . number_format(abs($rowBalance), 2) . ')' : number_format($rowBalance, 2) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        @empty
                                            <p class="text-center text-muted">
                                                No journal entries tagged to a {{ strtolower($dimensions[$dimension]['label']) }} were found for the selected period.
                                            </p>
                                        @endforelse

                                        @if (count($rows) > 0)
                                            <table class="table table-bordered">
                                                <tfoot>
                                                    <tr style="border-top: 3px double #333333;">
                                                        <td style="font-weight: bold; text-transform: uppercase; width: 55%;">Grand Total</td>
                                                        <td class="text-right" style="font-weight: bold; width: 15%;">{{ number_format($totals['debit'], 2) }}</td>
                                                        <td class="text-right" style="font-weight: bold; width: 15%;">{{ number_format($totals['credit'], 2) }}</td>
                                                        <td class="text-right" style="font-weight: bold; width: 15%;">
                                                            @php $grandBalance = $totals['debit'] - $totals['credit']; @endphp
                                                            {{ $grandBalance < 0 ? '(' . number_format(abs($grandBalance), 2) . ')' : number_format($grandBalance, 2) }}
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
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
