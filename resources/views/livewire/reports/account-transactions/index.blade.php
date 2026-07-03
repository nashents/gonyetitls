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
                                                <h5>Account Transactions (General Ledger)</h5>
                                            </div>
                                        </div>
                                        <div class="panel-body">

                                            <div class="row hidden-print">
                                                <form wire:submit.prevent="generateStatement()">
                                                <div class="col-lg-4">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                    Account
                                                    </span>
                                                    <select wire:model.debounce.300ms="account_id" class="form-control" aria-label="...">
                                                        @foreach ($accounts as $account)
                                                        <option value="{{$account->id}}">{{$account->name}}</option>
                                                        @endforeach
                                                    </select>
                                                    </div>
                                                    <!-- /input-group -->
                                                </div>
                                                <div class="col-lg-2">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                  From
                                                  </span>
                                                  <input type="date" wire:model.debounce.300ms="from" class="form-control" aria-label="...">
                                                    </div>
                                                    <!-- /input-group -->
                                                </div>
                                                <div class="col-lg-2" style="margin-left: 10px">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                  To
                                                  </span>
                                                  <input type="date" wire:model.debounce.300ms="to" class="form-control" aria-label="...">
                                                    </div>
                                                    <!-- /input-group -->
                                                </div>
                                                </form>
                                            </div>
                                            <!-- /.row -->
                                            <br>
                                            <br>

                                            <div class="row hidden-print">
                                                <div class="col-md-12 text-center">
                                                    <div class="btn-group">
                                                        <button wire:click.prevent="set_report('summary')" class="btn btn-default {{$summary == "summary" ? 'border-primary' : ""}} btn-wide btn-rounded" type="button" aria-haspopup="true" aria-expanded="true">
                                                            Summary
                                                        </button>
                                                        <button wire:click.prevent="set_report('details')" class="btn btn-default {{$details == "details" ? 'border-primary' : ""}} btn-wide btn-rounded" type="button" aria-haspopup="true" aria-expanded="true">
                                                            Transactions
                                                        </button>
                                                    </div>
                                                    <div class="btn-group" style="margin-left: 25px">
                                                        <a href="{{ route('reports.account_transactions.print', ['account_id' => $account_id, 'from' => $from, 'to' => $to, 'view' => $this->viewMode()]) }}" target="_blank" class="btn btn-default btn-wide btn-rounded">
                                                            <i class="fa fa-print"></i> Print
                                                        </a>
                                                        <a href="{{ route('reports.account_transactions.pdf', ['account_id' => $account_id, 'from' => $from, 'to' => $to, 'view' => $this->viewMode()]) }}" target="_blank" class="btn btn-default btn-wide btn-rounded">
                                                            <i class="fa fa-file-pdf-o"></i> Export PDF
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <br>
                                            <div class="row">
                                                <div class="col-md-8 col-md-offset-2">
                                                    <div class="row text-center">
                                                        <div class="col-md-4">
                                                            <h5>Opening Balance</h5>
                                                            <br>
                                                            <h4 style="margin-top:-15px">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($opening_balance)}}</h4>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <h5>Net Movement</h5>
                                                            <br>
                                                            <h4 style="margin-top:-15px">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($closing_balance - $opening_balance)}}</h4>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <h5>Closing Balance</h5>
                                                            <br>
                                                            <h4 style="margin-top:-15px">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($closing_balance)}}</h4>
                                                        </div>
                                                    </div>
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
                                                   <strong>{{ collect($accounts)->firstWhere('id', $account_id)->name ?? 'Account' }}</strong>
                                                </div>
                                                <div class="col-xs-6 p-n">
                                                    <p style="font-size: 15px;"><strong><span style="float: right" >From: {{$from}}</span></strong></p>
                                                    <p style="font-size: 15px;"><strong><span style="float: right" >To: {{$to}}</span></strong></p>
                                                </div>

                                            </div>

                                        </div>
                                        <br>
                                        <br>
                                        <hr style="width:100%;", size="3", color=black>


                                        <div class="panel-body ">

                                            @if (isset($details) && $summary == Null)
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Journal #</th>
                                                        <th>Description</th>
                                                        <th class="text-right">Debit</th>
                                                        <th class="text-right">Credit</th>
                                                        <th class="text-right">Balance</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td colspan="5"><strong>Opening Balance</strong></td>
                                                        <td class="text-right"><strong>{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($opening_balance)}}</strong></td>
                                                    </tr>
                                                    @forelse ($transactions as $t)
                                                    <tr>
                                                        <td>{{ \Carbon\Carbon::parse($t['date'])->format('d M Y') }}</td>
                                                        <td>{{ $t['journal_number'] }}</td>
                                                        <td>{{ $t['description'] }}</td>
                                                        <td class="text-right">{{ $t['debit'] != 0 ? $default_currency->name.' '.$default_currency->symbol.$this->fmt($t['debit']) : '' }}</td>
                                                        <td class="text-right">{{ $t['credit'] != 0 ? $default_currency->name.' '.$default_currency->symbol.$this->fmt($t['credit']) : '' }}</td>
                                                        <td class="text-right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($t['running_balance'])}}</td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted">No transactions in this period.</td>
                                                    </tr>
                                                    @endforelse
                                                    <tr style="background-color: #D3D3D3">
                                                        <td colspan="5"><strong>Closing Balance</strong></td>
                                                        <td class="text-right"><strong>{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($closing_balance)}}</strong></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            @else
                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n"><span style="margin-left:5px">Opening Balance</span></div>
                                                <div class="col-xs-6 p-n"><span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($opening_balance)}}</span></div>
                                            </div>
                                            <hr style="width:100%;", size="3", color=black>
                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n"><span style="margin-left:5px">Total Debit</span></div>
                                                <div class="col-xs-6 p-n"><span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($total_debit)}}</span></div>
                                            </div>
                                            <hr style="width:100%;", size="3", color=black>
                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n"><span style="margin-left:5px">Total Credit</span></div>
                                                <div class="col-xs-6 p-n"><span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($total_credit)}}</span></div>
                                            </div>
                                            <hr style="width:100%;", size="3", color=black>
                                            <div class="col-xs-12 p-n" style="background-color: #D3D3D3">
                                                <div class="col-xs-5 p-n"><strong><span style="margin-left:5px">Closing Balance</span></strong></div>
                                                <div class="col-xs-6 p-n"><strong><span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($closing_balance)}}</span></strong></div>
                                            </div>
                                            @endif

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
