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
                                                <h5>Trial Balance</h5>
                                            </div>
                                        </div>
                                        <div class="panel-body">

                                            <div class="row hidden-print">
                                                <form wire:submit.prevent="generateStatement()">
                                                <div class="col-lg-2">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                  From
                                                  </span>
                                                  <input type="date" wire:model.debounce.500ms="date_from" class="form-control" aria-label="...">
                                                    </div>
                                                    <!-- /input-group -->
                                                </div>
                                                <div class="col-lg-2" style="margin-left: 10px">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                  To
                                                  </span>
                                                  <input type="date" wire:model.debounce.500ms="date_to" class="form-control" aria-label="...">
                                                    </div>
                                                    <!-- /input-group -->
                                                </div>
                                                <div class="col-lg-3" style="margin-left: 10px">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                  Search
                                                  </span>
                                                  <input type="text" wire:model.debounce.500ms="search" class="form-control" placeholder="Search account..." aria-label="...">
                                                    </div>
                                                    <!-- /input-group -->
                                                </div>
                                                <div class="col-lg-3" style="margin-left: 10px; margin-top: 8px;">
                                                    <label style="font-weight: normal;">
                                                        <input type="checkbox" wire:model="hide_zero_balances"> Hide zero balances
                                                    </label>
                                                </div>
                                                </form>
                                            </div>
                                            <!-- /.row -->
                                            <br>
                                            <br>

                                            <div class="row hidden-print">
                                                <div class="col-md-12 text-center">
                                                    <a href="{{ route('reports.trial_balance.print', ['date_from' => $date_from, 'date_to' => $date_to, 'search' => $search, 'hide_zero' => $hide_zero_balances ? '1' : '0']) }}" target="_blank" class="btn btn-default btn-wide btn-rounded">
                                                        <i class="fa fa-print"></i> Print
                                                    </a>
                                                    <a href="{{ route('reports.trial_balance.pdf', ['date_from' => $date_from, 'date_to' => $date_to, 'search' => $search, 'hide_zero' => $hide_zero_balances ? '1' : '0']) }}" target="_blank" class="btn btn-default btn-wide btn-rounded">
                                                        <i class="fa fa-file-pdf-o"></i> Export PDF
                                                    </a>
                                                </div>
                                            </div>
                                            <br>
                                            <div class="row">
                                                <div class="col-md-12 text-center">
                                                    @if ($isBalanced)
                                                        <span class="badge badge-success px-3 py-2">
                                                            <i class="fas fa-check-circle"></i> Trial Balance is Balanced
                                                        </span>
                                                    @else
                                                        <span class="badge badge-danger px-3 py-2">
                                                            <i class="fas fa-exclamation-triangle"></i>
                                                            Out of Balance by {{ number_format(abs($totals['debit'] - $totals['credit']), 2) }}
                                                        </span>
                                                        <br><br>
                                                        <button type="button" class="btn btn-sm btn-default hidden-print" wire:click="diagnoseImbalance" wire:loading.attr="disabled">
                                                            <i class="fa fa-search"></i> Diagnose Why
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>

                                            @if ($imbalanceDiagnosis !== null)
                                                <div class="row hidden-print" style="margin-top: 15px;">
                                                    <div class="col-md-10 col-md-offset-1">
                                                        <div class="panel" style="border: 1px solid #ddd;">
                                                            <div class="panel-heading">
                                                                <div class="panel-title">Imbalance Diagnosis</div>
                                                            </div>
                                                            <div class="panel-body">
                                                                @if (count($imbalanceDiagnosis) === 0)
                                                                    <p class="text-muted mb-0">No self-imbalanced journal entries found in this date range. The gap may come from a reversal pair split across a date boundary rather than a broken posting - try widening the date range.</p>
                                                                @else
                                                                    <table class="table table-sm table-bordered">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Journal #</th>
                                                                                <th>Reference</th>
                                                                                <th>Date</th>
                                                                                <th>Source</th>
                                                                                <th class="text-right">Off by</th>
                                                                                <th>Status</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach ($imbalanceDiagnosis as $row)
                                                                                <tr>
                                                                                    <td>{{ $row['journal_number'] }}</td>
                                                                                    <td>{{ $row['reference'] }}</td>
                                                                                    <td>{{ $row['date'] }}</td>
                                                                                    <td>{{ ucfirst(str_replace('_', ' ', $row['source_type'])) }}</td>
                                                                                    <td class="text-right">{{ number_format(abs($row['diff']), 2) }}</td>
                                                                                    <td>
                                                                                        @if ($row['fixable'])
                                                                                            <span class="badge badge-warning">Auto-fixable</span>
                                                                                        @else
                                                                                            <span class="badge badge-secondary">Needs manual review</span>
                                                                                        @endif
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>

                                                                    @if (collect($imbalanceDiagnosis)->contains('fixable', true))
                                                                        <button type="button" class="btn btn-sm bg-danger" wire:click="repairImbalance" wire:loading.attr="disabled"
                                                                            onclick="return confirm('Reverse and repost every auto-fixable entry listed above, using each document\'s current figures? This posts real correcting journal entries.')">
                                                                            <i class="fa fa-wrench"></i> Fix Auto-Fixable Entries
                                                                        </button>
                                                                    @endif
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            @if ($repairSummary !== null)
                                                <div class="row hidden-print" style="margin-top: 15px;">
                                                    <div class="col-md-10 col-md-offset-1">
                                                        <div class="panel" style="border: 1px solid #ddd;">
                                                            <div class="panel-heading">
                                                                <div class="panel-title">Repair Summary</div>
                                                            </div>
                                                            <div class="panel-body">
                                                                <p>
                                                                    Before: debit {{ number_format($repairSummary['before']['debit'], 2) }} / credit {{ number_format($repairSummary['before']['credit'], 2) }}
                                                                    (off by {{ number_format(abs($repairSummary['before']['diff']), 2) }})<br>
                                                                    After: debit {{ number_format($repairSummary['after']['debit'], 2) }} / credit {{ number_format($repairSummary['after']['credit'], 2) }}
                                                                    (off by {{ number_format(abs($repairSummary['after']['diff']), 2) }})
                                                                </p>

                                                                @if (count($repairSummary['fixed']) > 0)
                                                                    <strong>Fixed ({{ count($repairSummary['fixed']) }}):</strong>
                                                                    <table class="table table-sm table-bordered">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Type</th>
                                                                                <th>Reference</th>
                                                                                <th>Old Journal #</th>
                                                                                <th>New Journal #</th>
                                                                                <th class="text-right">Amount corrected</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach ($repairSummary['fixed'] as $row)
                                                                                <tr>
                                                                                    <td>{{ ucfirst(str_replace('_', ' ', $row['document_type'])) }}</td>
                                                                                    <td>{{ $row['reference'] }}</td>
                                                                                    <td>{{ $row['old_journal_number'] }}</td>
                                                                                    <td>{{ $row['new_journal_number'] }}</td>
                                                                                    <td class="text-right">{{ number_format($row['corrected_amount'], 2) }}</td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                @endif

                                                                @if (count($repairSummary['skipped']) > 0)
                                                                    <strong>Needs manual review ({{ count($repairSummary['skipped']) }}):</strong>
                                                                    <table class="table table-sm table-bordered">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Journal #</th>
                                                                                <th>Reference</th>
                                                                                <th class="text-right">Off by</th>
                                                                                <th>Why it wasn't auto-fixed</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach ($repairSummary['skipped'] as $row)
                                                                                <tr>
                                                                                    <td>{{ $row['journal_number'] }}</td>
                                                                                    <td>{{ $row['reference'] }}</td>
                                                                                    <td class="text-right">{{ number_format(abs($row['diff']), 2) }}</td>
                                                                                    <td>{{ $row['reason'] }}</td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- /.col-md-12 -->
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                                <div class="col-md-10 col-md-offset-1">
                                    <div class="panel">
                                        <div class="panel-heading">
                                            <div class="panel-title" >
                                                <div class="col-xs-5 p-n">
                                                   <strong>ACCOUNTS</strong>
                                                </div>
                                                <div class="col-xs-6 p-n">
                                                    <p style="font-size: 15px;"><strong><span style="float: right" >From: {{$date_from}}</span></strong></p>
                                                    <p style="font-size: 15px;"><strong><span style="float: right" >To: {{$date_to}}</span></strong></p>
                                                </div>

                                            </div>

                                        </div>
                                        <br>
                                        <br>
                                        <hr style="width:100%;", size="3", color=black>


                                        <div class="panel-body ">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 40%">Account</th>
                                                        <th style="width: 20%">Type</th>
                                                        <th style="width: 15%" class="text-right">Debit</th>
                                                        <th style="width: 15%" class="text-right">Credit</th>
                                                        <th style="width: 10%" class="text-right">Balance</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($groupedLines as $groupName => $lines)
                                                        {{-- Group Header --}}
                                                        <tr style="background-color: #D3D3D3;">
                                                            <td colspan="5" style="font-weight: bold; text-transform: uppercase;">
                                                                {{ $groupName }}
                                                            </td>
                                                        </tr>

                                                        {{-- Account Lines --}}
                                                        @foreach ($lines as $line)
                                                        <tr>
                                                            <td>{{ $line->account_name }}</td>
                                                            <td class="text-muted"><small>{{ $line->account_type_name }}</small></td>
                                                            <td class="text-right">
                                                                @if ($line->total_debit > 0)
                                                                    {{ number_format($line->total_debit, 2) }}
                                                                @else
                                                                    <span class="text-muted">&mdash;</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-right">
                                                                @if ($line->total_credit > 0)
                                                                    {{ number_format($line->total_credit, 2) }}
                                                                @else
                                                                    <span class="text-muted">&mdash;</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-right" style="font-weight: bold;">
                                                                @php $balance = $line->total_debit - $line->total_credit; @endphp
                                                                <span class="{{ $balance < 0 ? 'text-danger' : ($balance > 0 ? 'text-success' : 'text-muted') }}">
                                                                    {{ $balance < 0 ? '(' . number_format(abs($balance), 2) . ')' : number_format($balance, 2) }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        @endforeach

                                                        {{-- Group Subtotal --}}
                                                        <tr style="background-color: #f1f1f1;">
                                                            <td colspan="2" class="text-right" style="font-weight: bold;">
                                                                {{ $groupName }} Total
                                                            </td>
                                                            <td class="text-right" style="font-weight: bold;">
                                                                {{ number_format($lines->sum('total_debit'), 2) }}
                                                            </td>
                                                            <td class="text-right" style="font-weight: bold;">
                                                                {{ number_format($lines->sum('total_credit'), 2) }}
                                                            </td>
                                                            <td class="text-right" style="font-weight: bold;">
                                                                @php $groupBalance = $lines->sum('total_debit') - $lines->sum('total_credit'); @endphp
                                                                <span class="{{ $groupBalance < 0 ? 'text-danger' : 'text-success' }}">
                                                                    {{ $groupBalance < 0 ? '(' . number_format(abs($groupBalance), 2) . ')' : number_format($groupBalance, 2) }}
                                                                </span>
                                                            </td>
                                                        </tr>

                                                    @empty
                                                        <tr>
                                                            <td colspan="5" class="text-center text-muted">
                                                                No journal entries found for the selected period.
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>

                                                {{-- Grand Totals --}}
                                                <tfoot>
                                                    <tr style="border-top: 3px double #333333;">
                                                        <td colspan="2" style="font-weight: bold; text-transform: uppercase;">Grand Total</td>
                                                        <td class="text-right" style="font-weight: bold;">
                                                            {{ number_format($totals['debit'], 2) }}
                                                        </td>
                                                        <td class="text-right" style="font-weight: bold;">
                                                            {{ number_format($totals['credit'], 2) }}
                                                        </td>
                                                        <td class="text-right" style="font-weight: bold;">
                                                            @if ($isBalanced)
                                                                <span class="text-success">0.00</span>
                                                            @else
                                                                <span class="text-danger">
                                                                    {{ number_format(abs($totals['debit'] - $totals['credit']), 2) }}
                                                                </span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>

                                    </div>
                                </div>

                            </div>
                    </div>
                </div>


            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>

      <!-- Modal -->


</div>
