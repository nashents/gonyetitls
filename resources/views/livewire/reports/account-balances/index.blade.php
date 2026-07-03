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
                                                <h5>Account Balances</h5>
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
                                                            Details
                                                        </button>
                                                    </div>
                                                    <div class="btn-group" style="margin-left: 25px">
                                                        <a href="{{ route('reports.account_balances.print', ['from' => $from, 'to' => $to, 'view' => $this->viewMode()]) }}" target="_blank" class="btn btn-default btn-wide btn-rounded">
                                                            <i class="fa fa-print"></i> Print
                                                        </a>
                                                        <a href="{{ route('reports.account_balances.pdf', ['from' => $from, 'to' => $to, 'view' => $this->viewMode()]) }}" target="_blank" class="btn btn-default btn-wide btn-rounded">
                                                            <i class="fa fa-file-pdf-o"></i> Export PDF
                                                        </a>
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
                                                   <strong>ACCOUNTS</strong>
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

                                            @foreach ($groups as $groupName => $group)
                                            <h5 style="background-color: #D3D3D3; padding: 8px;">{{ $groupName }}</h5>
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Account</th>
                                                        <th class="text-right">Opening Balance</th>
                                                        <th class="text-right">Activity</th>
                                                        <th class="text-right">Closing Balance</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if (isset($details) && $summary == Null)
                                                        @foreach ($group['rows'] as $row)
                                                        @if ($row['total'] != 0 || ($row['buckets']['Opening Balance'] ?? 0) != 0)
                                                        <tr>
                                                            <td>{{$row['label']}}</td>
                                                            <td class="text-right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($row['buckets']['Opening Balance'] ?? 0)}}</td>
                                                            <td class="text-right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($row['buckets']['Activity'] ?? 0)}}</td>
                                                            <td class="text-right"><strong>{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($row['total'])}}</strong></td>
                                                        </tr>
                                                        @endif
                                                        @endforeach
                                                    @endif
                                                    <tr style="background-color: #f1f1f1">
                                                        <td><strong>{{ $groupName }} Total</strong></td>
                                                        <td class="text-right"><strong>{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($group['grandTotals']['buckets']['Opening Balance'] ?? 0)}}</strong></td>
                                                        <td class="text-right"><strong>{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($group['grandTotals']['buckets']['Activity'] ?? 0)}}</strong></td>
                                                        <td class="text-right"><strong>{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($group['grandTotals']['total'] ?? 0)}}</strong></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            @endforeach

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
