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
                                                    @endif
                                                </div>
                                            </div>

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
