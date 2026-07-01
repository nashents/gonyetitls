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
                                                <h5>Cash Flow Statement</h5>
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
                                                        <a href="{{ route('reports.cashflow.print', ['from' => $from, 'to' => $to, 'view' => $this->viewMode()]) }}" target="_blank" class="btn btn-default btn-wide btn-rounded">
                                                            <i class="fa fa-print"></i> Print
                                                        </a>
                                                        <a href="{{ route('reports.cashflow.pdf', ['from' => $from, 'to' => $to, 'view' => $this->viewMode()]) }}" target="_blank" class="btn btn-default btn-wide btn-rounded">
                                                            <i class="fa fa-file-pdf-o"></i> Export PDF
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-10 col-md-offset-1">
                                                    <div class="row text-center">
                                                        <div class="col-md-2">
                                                            <h5>Cash Received</h5>
                                                            <br>
                                                            <h4 style="margin-top:-15px" class="{{ $cash_received_from_customers < 0 ? 'text-danger' : '' }}">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($cash_received_from_customers)}}</h4>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <h5>Cash Paid</h5>
                                                            <br>
                                                            <h4 style="margin-top:-15px" class="{{ $cash_paid_to_vendors < 0 ? 'text-danger' : '' }}">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($cash_paid_to_vendors)}}</h4>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <h5>Net Operating Cash Flow</h5>
                                                            <br>
                                                            <h4 style="margin-top:-15px" class="{{ $net_operating_cash_flow < 0 ? 'text-danger' : '' }}">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($net_operating_cash_flow)}}</h4>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <h5>Net Increase/(Decrease)</h5>
                                                            <br>
                                                            <h4 style="margin-top:-15px" class="{{ $net_increase_in_cash < 0 ? 'text-danger' : '' }}">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($net_increase_in_cash)}}</h4>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <h5>Ending Cash Balance</h5>
                                                            <br>
                                                            <h4 style="margin-top:-15px" class="{{ $ending_cash_balance < 0 ? 'text-danger' : '' }}">{{$default_currency->name}} {{$default_currency->symbol}}{{ $this->fmt($ending_cash_balance)}}</h4>
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
                                                   <strong>CASH FLOW</strong>
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

                                            <div class="col-xs-12 p-n" style="background-color: #D3D3D3">
                                                <div class="col-xs-5 p-n">
                                                  <strong><span style="margin-left:5px">Cash Flows from Operating Activities</span></strong>
                                                </div>
                                                <!-- /.col-md-6 -->
                                            </div>
                                            <hr style="width:100%;", size="3", color=black>

                                            @if (isset($details) && $summary == Null)
                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n">
                                                  <span style="margin-left:5px">Cash received from customers</span>
                                                </div>
                                                <div class="col-xs-6 p-n">
                                                <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($cash_received_from_customers)}}</span>
                                                </div>
                                            </div>
                                            <hr style="width:100%;", size="3", color=black>
                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n">
                                                  <span style="margin-left:5px">Cash paid to vendors and suppliers</span>
                                                </div>
                                                <div class="col-xs-6 p-n">
                                                <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($cash_paid_to_vendors)}}</span>
                                                </div>
                                            </div>
                                            <hr style="width:100%;", size="3", color=black>
                                            @if ($other_movements != 0)
                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n">
                                                  <span style="margin-left:5px">Other/Unclassified Cash Movements</span>
                                                </div>
                                                <div class="col-xs-6 p-n">
                                                <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($other_movements)}}</span>
                                                </div>
                                            </div>
                                            <hr style="width:100%;", size="3", color=black>
                                            @endif
                                            @endif

                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n">
                                                    <strong><span style="margin-left:5px">Net Cash from Operating Activities</span></strong>
                                                </div>
                                                <div class="col-xs-6 p-n">
                                                <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($net_operating_cash_flow)}}</span>
                                                </div>
                                            </div>
                                            <hr style="width:100%", size="3", color=black>

                                            @if (isset($details) && $summary == Null && $other_movements != 0)
                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n">
                                                    <strong><span style="margin-left:5px">Other/Unclassified Cash Movements</span></strong>
                                                </div>
                                                <div class="col-xs-6 p-n">
                                                <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($other_movements)}}</span>
                                                </div>
                                            </div>
                                            <hr style="width:100%", size="3", color=black>
                                            @endif

                                            <div class="col-xs-12 p-n" style="background-color: #D3D3D3">
                                                <div class="col-xs-5 p-n" style="margin-top: 10px; margin-bottom: -10px;" >
                                                    <strong><span style="margin-left:5px">NET INCREASE/(DECREASE) IN CASH</span></strong>
                                                </div>
                                                <div class="col-xs-6 p-n" style="margin-top: 10px; margin-bottom: -10px;">
                                                    <strong><span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($net_increase_in_cash)}}</span></strong>
                                                </div>
                                            </div>
                                            <br>
                                            <hr style="width:100%;", size="3", color=black>

                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n">
                                                  <span style="margin-left:5px">Cash at Beginning of Period</span>
                                                </div>
                                                <div class="col-xs-6 p-n">
                                                <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($beginning_cash_balance)}}</span>
                                                </div>
                                            </div>
                                            <hr style="width:100%;", size="3", color=black>

                                            <div class="col-xs-12 p-n" style="background-color: #D3D3D3; ">
                                                <div class="col-xs-5 p-n" style="margin-top: 10px; margin-bottom: -10px;">
                                                    <strong><span style="margin-left:5px">CASH AT END OF PERIOD</span></strong>
                                                </div>
                                                <div class="col-xs-6 p-n" style="margin-top: 10px; margin-bottom: -10px;" >
                                                    <strong><span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($ending_cash_balance)}}</span></strong>
                                                </div>
                                            </div>

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
