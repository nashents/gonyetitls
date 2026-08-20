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
                                                <h5>Profit & Loss (Income Statement)</h5>
                                            </div>
                                        </div>
                                        <div class="panel-body">

                                            <div class="row hidden-print">
                                                <form wire:submit.prevent="generateStatement()">
                                                <div class="col-lg-4">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                    Report Type
                                                    </span>
                                                    <select wire:model.debounce.300ms="selectedType" class="form-control" aria-label="..." >
                                                    <option value="Accrual (Paid & Unpaid)">Accrual (Paid & Unpaid)</option>
                                                    <option value="Cash Basis">Cash Basis (Paid)</option>
                                                    </select>
                                                    </div>
                                                    <!-- /input-group -->
                                                </div>


                                                <!-- /.col-lg-6 -->

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
                                                        <a href="{{ route('reports.income_statement.print', ['from' => $from, 'to' => $to, 'type' => $selectedType, 'view' => $this->viewMode()]) }}" target="_blank" class="btn btn-default btn-wide btn-rounded">
                                                            <i class="fa fa-print"></i> Print
                                                        </a>
                                                        <a href="{{ route('reports.income_statement.pdf', ['from' => $from, 'to' => $to, 'type' => $selectedType, 'view' => $this->viewMode()]) }}" target="_blank" class="btn btn-default btn-wide btn-rounded">
                                                            <i class="fa fa-file-pdf-o"></i> Export PDF
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-10 col-md-offset-1">
                                                    <div class="row text-center">
                                                        <div class="col-md-2">
                                                            <h5>Revenue</h5>
                                                            <br>
                                                            <h4 style="margin-top:-15px" class="{{ $total_income < 0 ? 'text-danger' : '' }}">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($total_income)}} - </h4>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <h5>Cost Of Goods Sold</h5>
                                                            <br>
                                                            <h4 style="margin-top:-15px" class="{{ $total_cost_of_goods_sold < 0 ? 'text-danger' : '' }}">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($total_cost_of_goods_sold)}} =</h4>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <h5>Gross Profit</h5>
                                                            <br>
                                                            <h4 style="margin-top:-15px" class="{{ $gross_profit < 0 ? 'text-danger' : '' }}">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($gross_profit)}} -</h4>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <h5>Operating Expenses</h5>
                                                            <br>
                                                            <h4 style="margin-top:-15px" class="{{ $total_operating_expenses < 0 ? 'text-danger' : '' }}">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($total_operating_expenses) }} =</h4>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <h5>Net Profit</h5>
                                                            <br>
                                                            <h4 style="margin-top:-15px" class="{{ $net_profit < 0 ? 'text-danger' : '' }}">{{$default_currency->name}} {{$default_currency->symbol}}{{ $this->fmt($net_profit)}}</h4>
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

                                            @if (isset($summary) && $details == Null)

                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n">
                                                  <span style="margin-left:5px">Revenue</span>
                                                </div>
                                                <!-- /.col-md-6 -->
                                                <div class="col-xs-6 p-n">
                                                <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($total_income)}}</span>
                                                </div>
                                                <!-- /.col-md-6 -->
                                            </div>
                                            <hr style="width:100%;", size="3", color=black>

                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n">
                                                  <span style="margin-left:5px">Cost of Goods Sold</span>
                                                </div>
                                                <!-- /.col-md-6 -->
                                                <div class="col-xs-6 p-n">
                                                <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($total_cost_of_goods_sold) }}</span>
                                                </div>
                                                <!-- /.col-md-6 -->
                                            </div>
                                            <hr style="width:100%;", size="3", color=black>

                                            <div class="col-xs-12 p-n" style="background-color: #D3D3D3">
                                                <div class="col-xs-5 p-n" style="margin-top: 10px; margin-bottom: -10px;" >
                                                    <strong><span style="margin-left:5px">GROSS PROFIT</span></strong>
                                                    <p style="margin-left:5px">As a percentage of Total Revenue</p>
                                                </div>
                                                <!-- /.col-md-6 -->

                                                <div class="col-xs-6 p-n" style="margin-top: 10px; margin-bottom: -10px;">
                                                    <strong><span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($gross_profit)}}</span></strong>
                                                    <br>
                                                    <p><span style="float: right">{{$this->pct($gross_profit_percentage)}}</span></p>

                                                </div>
                                                <!-- /.col-md-6 -->
                                            </div>
                                            <br>
                                            <hr style="width:100%;", size="3", color=black>
                                            <div class="col-xs-12 p-n" >
                                                <div class="col-xs-5 p-n">
                                                  <span style="margin-left:5px">Operating Expenses</span>
                                                </div>
                                                <!-- /.col-md-6 -->
                                                <div class="col-xs-6 p-n">
                                                <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($total_operating_expenses)}}</span>
                                                </div>
                                                <!-- /.col-md-6 -->
                                            </div>
                                            <hr style="width:100%;", size="3", color=black>
                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n">
                                                  <span style="margin-left:5px">Other Income</span>
                                                </div>
                                                <!-- /.col-md-6 -->
                                                <div class="col-xs-6 p-n">
                                                <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($total_other_income)}}</span>
                                                </div>
                                                <!-- /.col-md-6 -->
                                            </div>
                                            <hr style="width:100%;", size="3", color=black>
                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n">
                                                  <span style="margin-left:5px">Other Expenses</span>
                                                </div>
                                                <!-- /.col-md-6 -->
                                                <div class="col-xs-6 p-n">
                                                <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($total_other_expenses)}}</span>
                                                </div>
                                                <!-- /.col-md-6 -->
                                            </div>
                                            <hr style="width:100%;", size="3", color=black>
                                            <div class="col-xs-12 p-n" style="background-color: #D3D3D3; ">
                                                <div class="col-xs-5 p-n" style="margin-top: 10px; margin-bottom: -10px;">
                                                    <strong><span style="margin-left:5px">NET PROFIT</span></strong>
                                                    <p style="margin-left:5px">As a percentage of Total Revenue</p>
                                                </div>

                                                <div class="col-xs-6 p-n" style="margin-top: 10px; margin-bottom: -10px;" >
                                                    <strong><span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($net_profit)}}</span></strong>
                                                    <br>
                                                    <p><span style="float: right">{{$this->pct($net_profit_percentage)}}</span></p>
                                                </div>
                                                <!-- /.col-md-6 -->
                                            </div>

                                        @elseif (isset($details) && $summary == Null)


                                            <div class="col-xs-12 p-n" style="background-color: #D3D3D3">
                                                <div class="col-xs-5 p-n">
                                                  <strong><span style="margin-left:5px">Revenue</span> </strong>
                                                </div>
                                                <!-- /.col-md-6 -->
                                            </div>
                                            <hr style="width:100%;", size="3", color=black>

                                            @foreach ($income_accounts as $account)
                                                @php $amount = $income_by_account[$account->id] ?? 0; @endphp
                                                @if ($amount != 0)
                                                <div class="col-xs-12 p-n">
                                                    <div class="col-xs-5 p-n">
                                                    <span style="margin-left:5px">{{$account->name}}</span>
                                                    </div>
                                                    <!-- /.col-md-6 -->

                                                    <div class="col-xs-6 p-n">
                                                    <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($amount)}}</span>
                                                    </div>
                                                    <!-- /.col-md-6 -->
                                                </div>
                                                <hr style="width:100%;", size="3", color=black>
                                                @endif
                                            @endforeach

                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n">
                                                    <strong><span style="margin-left:5px">Total Revenue</span></strong>
                                                </div>
                                                <!-- /.col-md-6 -->
                                                <div class="col-xs-6 p-n">
                                                <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($total_income)}}</span>
                                                </div>
                                                <!-- /.col-md-6 -->
                                            </div>
                                            <hr style="width:100%", size="3", color=black>
                                            <div class="col-xs-12 p-n" style="background-color: #D3D3D3">
                                                <div class="col-xs-5 p-n">
                                                 <strong><span style="margin-left:5px">Cost of Goods Sold</span></strong>
                                                </div>
                                                <!-- /.col-md-6 -->
                                            </div>
                                            <hr style="width:100%", size="3", color=black>

                                            @foreach ($cost_of_goods_sold_accounts as $account)
                                                @php $amount = $cost_of_goods_sold_by_account[$account->id] ?? 0; @endphp
                                                @if ($amount != 0)
                                                    <div class="col-xs-12 p-n">
                                                        <div class="col-xs-5 p-n">
                                                        <span style="margin-left:5px">{{$account->name}}</span>
                                                        </div>
                                                        <!-- /.col-md-6 -->

                                                        <div class="col-xs-6 p-n">
                                                        <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($amount)}}</span>
                                                        </div>
                                                        <!-- /.col-md-6 -->
                                                    </div>
                                                    <hr style="width:100%;", size="3", color=black>
                                                @endif
                                            @endforeach
                                            <div class="col-xs-12 p-n" style="background-color: #D3D3D3">
                                                <div class="col-xs-5 p-n" style="margin-top: 10px; margin-bottom: -10px;" >
                                                    <strong><span style="margin-left:5px">GROSS PROFIT</span></strong>
                                                    <p style="margin-left:5px">As a percentage of Total Revenue</p>
                                                </div>
                                                <!-- /.col-md-6 -->

                                            <div class="col-xs-6 p-n" style="margin-top: 10px; margin-bottom: -10px;">
                                                <strong><span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($gross_profit)}}</span></strong>
                                                <br>
                                                <p><span style="float: right">{{$this->pct($gross_profit_percentage)}}</span></p>

                                            </div>
                                                <!-- /.col-md-6 -->
                                            </div>
                                            <br>
                                            <hr style="width:100%", size="3", color=black>
                                            <div class="col-xs-12 p-n" style="background-color: #D3D3D3">
                                                <div class="col-xs-5 p-n">
                                                  <strong><span style="margin-left:5px">Operating Expenses</span> </strong>
                                                </div>
                                                <!-- /.col-md-6 -->
                                            </div>
                                            <hr style="width:100%", size="3", color=black>

                                            @foreach ($operating_expenses_accounts as $account)
                                                @php $amount = $operating_expenses_by_account[$account->id] ?? 0; @endphp
                                                @if ($amount != 0)
                                                        <div class="col-xs-12 p-n">
                                                            <div class="col-xs-5 p-n">
                                                            <span style="margin-left:5px">{{$account->name}}</span>
                                                            </div>
                                                            <!-- /.col-md-6 -->

                                                            <div class="col-xs-6 p-n">
                                                            <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($amount)}}</span>
                                                            </div>
                                                            <!-- /.col-md-6 -->
                                                        </div>
                                                        <hr style="width:100%;", size="3", color=black>
                                                @endif
                                            @endforeach

                                            <div class="col-xs-12 p-n" style="background-color: #D3D3D3">
                                                <div class="col-xs-5 p-n">
                                                  <strong><span style="margin-left:5px">Other Income</span> </strong>
                                                </div>
                                                <!-- /.col-md-6 -->
                                            </div>
                                            <hr style="width:100%", size="3", color=black>

                                            @foreach ($other_income_accounts as $account)
                                                @php $amount = $other_income_by_account[$account->id] ?? 0; @endphp
                                                @if ($amount != 0)
                                                        <div class="col-xs-12 p-n">
                                                            <div class="col-xs-5 p-n">
                                                            <span style="margin-left:5px">{{$account->name}}</span>
                                                            </div>
                                                            <!-- /.col-md-6 -->

                                                            <div class="col-xs-6 p-n">
                                                            <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($amount)}}</span>
                                                            </div>
                                                            <!-- /.col-md-6 -->
                                                        </div>
                                                        <hr style="width:100%;", size="3", color=black>
                                                @endif
                                            @endforeach

                                            <div class="col-xs-12 p-n" style="background-color: #D3D3D3">
                                                <div class="col-xs-5 p-n">
                                                  <strong><span style="margin-left:5px">Other Expenses</span> </strong>
                                                </div>
                                                <!-- /.col-md-6 -->
                                            </div>
                                            <hr style="width:100%", size="3", color=black>

                                            @foreach ($other_expenses_accounts as $account)
                                                @php $amount = $other_expenses_by_account[$account->id] ?? 0; @endphp
                                                @if ($amount != 0)
                                                        <div class="col-xs-12 p-n">
                                                            <div class="col-xs-5 p-n">
                                                            <span style="margin-left:5px">{{$account->name}}</span>
                                                            </div>
                                                            <!-- /.col-md-6 -->

                                                            <div class="col-xs-6 p-n">
                                                            <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($amount)}}</span>
                                                            </div>
                                                            <!-- /.col-md-6 -->
                                                        </div>
                                                        <hr style="width:100%;", size="3", color=black>
                                                @endif
                                            @endforeach

                                            <div class="col-xs-12 p-n" style="background-color: #D3D3D3; ">
                                                <div class="col-xs-5 p-n" style="margin-top: 10px; margin-bottom: -10px;">
                                                    <strong><span style="margin-left:5px">NET PROFIT</span></strong>
                                                    <p style="margin-left:5px">As a percentage of Total Revenue</p>
                                                </div>
                                                <!-- /.col-md-6 -->

                                                <div class="col-xs-6 p-n" style="margin-top: 10px; margin-bottom: -10px;" >
                                                    <strong><span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($net_profit)}}</span></strong>
                                                    <br>
                                                    <p><span style="float: right">{{$this->pct($net_profit_percentage)}}</span></p>
                                                </div>
                                                <!-- /.col-md-6 -->
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
