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
                                                <h5>Balance Sheet</h5>
                                            </div>
                                        </div>
                                        <div class="panel-body">

                                            <div class="row hidden-print">
                                                <form wire:submit.prevent="generateStatement()">
                                                <div class="col-lg-2">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                  As Of
                                                  </span>
                                                  <input type="date" wire:model.debounce.300ms="as_of_date" class="form-control" aria-label="...">
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
                                                        <a href="{{ route('reports.balance_sheet.print', ['as_of_date' => $as_of_date, 'view' => $this->viewMode()]) }}" target="_blank" class="btn btn-default btn-wide btn-rounded">
                                                            <i class="fa fa-print"></i> Print
                                                        </a>
                                                        <a href="{{ route('reports.balance_sheet.pdf', ['as_of_date' => $as_of_date, 'view' => $this->viewMode()]) }}" target="_blank" class="btn btn-default btn-wide btn-rounded">
                                                            <i class="fa fa-file-pdf-o"></i> Export PDF
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12 text-center hidden-print">
                                                    @if ($is_balanced)
                                                        <span class="badge badge-success px-3 py-2">
                                                            <i class="fas fa-check-circle"></i> Balance Sheet is Balanced
                                                        </span>
                                                    @else
                                                        <span class="badge badge-danger px-3 py-2">
                                                            <i class="fas fa-exclamation-triangle"></i> Out of Balance
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <br>
                                            <div class="row">
                                                <div class="col-md-10 col-md-offset-1">
                                                    <div class="row text-center">
                                                        <div class="col-md-3">
                                                            <h5>Total Assets</h5>
                                                            <br>
                                                            <h4 style="margin-top:-15px" class="{{ $total_assets < 0 ? 'text-danger' : '' }}">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($total_assets)}}</h4>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <h5>Total Liabilities</h5>
                                                            <br>
                                                            <h4 style="margin-top:-15px" class="{{ $total_liabilities < 0 ? 'text-danger' : '' }}">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($total_liabilities)}}</h4>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <h5>Total Equity</h5>
                                                            <br>
                                                            <h4 style="margin-top:-15px" class="{{ $total_equity < 0 ? 'text-danger' : '' }}">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($total_equity)}}</h4>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <h5>Net Income</h5>
                                                            <br>
                                                            <h4 style="margin-top:-15px" class="{{ $net_income < 0 ? 'text-danger' : '' }}">{{$default_currency->name}} {{$default_currency->symbol}}{{ $this->fmt($net_income)}}</h4>
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
                                                    <p style="font-size: 15px;"><strong><span style="float: right" >As of: {{$as_of_date}}</span></strong></p>
                                                </div>

                                            </div>

                                        </div>
                                        <br>
                                        <br>
                                        <hr style="width:100%;", size="3", color=black>


                                        <div class="panel-body ">

                                            <div class="col-xs-12 p-n" style="background-color: #D3D3D3">
                                                <div class="col-xs-5 p-n">
                                                  <strong><span style="margin-left:5px">Assets</span></strong>
                                                </div>
                                            </div>
                                            <hr style="width:100%;", size="3", color=black>

                                            @if (isset($details) && $summary == Null)
                                                @foreach ($assets_items as $item)
                                                <div class="col-xs-12 p-n">
                                                    <div class="col-xs-5 p-n">
                                                    <span style="margin-left:5px">{{$item['label']}}</span>
                                                    </div>
                                                    <div class="col-xs-6 p-n">
                                                    <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($item['amount'])}}</span>
                                                    </div>
                                                </div>
                                                <hr style="width:100%;", size="3", color=black>
                                                @endforeach
                                            @endif

                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n">
                                                    <strong><span style="margin-left:5px">Total Assets</span></strong>
                                                </div>
                                                <div class="col-xs-6 p-n">
                                                <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($total_assets)}}</span>
                                                </div>
                                            </div>
                                            <hr style="width:100%", size="3", color=black>

                                            <div class="col-xs-12 p-n" style="background-color: #D3D3D3">
                                                <div class="col-xs-5 p-n">
                                                 <strong><span style="margin-left:5px">Liabilities</span></strong>
                                                </div>
                                            </div>
                                            <hr style="width:100%", size="3", color=black>

                                            @if (isset($details) && $summary == Null)
                                                @foreach ($liabilities_items as $item)
                                                <div class="col-xs-12 p-n">
                                                    <div class="col-xs-5 p-n">
                                                    <span style="margin-left:5px">{{$item['label']}}</span>
                                                    </div>
                                                    <div class="col-xs-6 p-n">
                                                    <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($item['amount'])}}</span>
                                                    </div>
                                                </div>
                                                <hr style="width:100%;", size="3", color=black>
                                                @endforeach
                                            @endif

                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n">
                                                    <strong><span style="margin-left:5px">Total Liabilities</span></strong>
                                                </div>
                                                <div class="col-xs-6 p-n">
                                                <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($total_liabilities)}}</span>
                                                </div>
                                            </div>
                                            <hr style="width:100%", size="3", color=black>

                                            <div class="col-xs-12 p-n" style="background-color: #D3D3D3">
                                                <div class="col-xs-5 p-n">
                                                  <strong><span style="margin-left:5px">Equity</span></strong>
                                                </div>
                                            </div>
                                            <hr style="width:100%", size="3", color=black>

                                            @if (isset($details) && $summary == Null)
                                                @foreach ($equity_items as $item)
                                                <div class="col-xs-12 p-n">
                                                    <div class="col-xs-5 p-n">
                                                    <span style="margin-left:5px">{{$item['label']}}</span>
                                                    </div>
                                                    <div class="col-xs-6 p-n">
                                                    <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($item['amount'])}}</span>
                                                    </div>
                                                </div>
                                                <hr style="width:100%;", size="3", color=black>
                                                @endforeach
                                            @endif

                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n">
                                                    <strong><span style="margin-left:5px">Total Equity</span></strong>
                                                </div>
                                                <div class="col-xs-6 p-n">
                                                <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($total_equity)}}</span>
                                                </div>
                                            </div>
                                            <hr style="width:100%", size="3", color=black>

                                            <div class="col-xs-12 p-n" style="background-color: #D3D3D3; ">
                                                <div class="col-xs-5 p-n" style="margin-top: 10px; margin-bottom: -10px;">
                                                    <strong><span style="margin-left:5px">TOTAL LIABILITIES AND EQUITY</span></strong>
                                                </div>
                                                <div class="col-xs-6 p-n" style="margin-top: 10px; margin-bottom: -10px;" >
                                                    <strong><span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($total_liabilities_and_equity)}}</span></strong>
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
