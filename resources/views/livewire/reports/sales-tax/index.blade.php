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
                                                <h5>Sales Tax Report</h5>
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
                                                        <a href="{{ route('reports.sales_tax.print', ['from' => $from, 'to' => $to, 'type' => $selectedType, 'view' => $this->viewMode()]) }}" target="_blank" class="btn btn-default btn-wide btn-rounded">
                                                            <i class="fa fa-print"></i> Print
                                                        </a>
                                                        <a href="{{ route('reports.sales_tax.pdf', ['from' => $from, 'to' => $to, 'type' => $selectedType, 'view' => $this->viewMode()]) }}" target="_blank" class="btn btn-default btn-wide btn-rounded">
                                                            <i class="fa fa-file-pdf-o"></i> Export PDF
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-10 col-md-offset-1">
                                                    <div class="row text-center">
                                                        <div class="col-md-3">
                                                            <h5>Taxable Sales</h5>
                                                            <br>
                                                            <h4 style="margin-top:-15px" class="{{ $total_taxable_sales < 0 ? 'text-danger' : '' }}">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($total_taxable_sales)}}</h4>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <h5>Tax Collected</h5>
                                                            <br>
                                                            <h4 style="margin-top:-15px" class="{{ $total_tax_collected < 0 ? 'text-danger' : '' }}">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($total_tax_collected)}}</h4>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <h5>Tax Paid</h5>
                                                            <br>
                                                            <h4 style="margin-top:-15px" class="{{ $total_tax_paid < 0 ? 'text-danger' : '' }}">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($total_tax_paid)}}</h4>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <h5>Net Tax Payable</h5>
                                                            <br>
                                                            <h4 style="margin-top:-15px" class="{{ $net_tax_payable < 0 ? 'text-danger' : '' }}">{{$default_currency->name}} {{$default_currency->symbol}}{{ $this->fmt($net_tax_payable)}}</h4>
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

                                            <div class="col-xs-12 p-n" style="background-color: #D3D3D3">
                                                <div class="col-xs-12 p-n">
                                                  <strong><span style="margin-left:5px">Sales (Output Tax)</span></strong>
                                                </div>
                                            </div>
                                            <hr style="width:100%;", size="3", color=black>

                                            @if (isset($details) && $summary == Null)
                                                <div class="col-xs-12 p-n" style="font-weight:bold;">
                                                    <div class="col-xs-4 p-n"><span style="margin-left:5px">Tax</span></div>
                                                    <div class="col-xs-4 p-n"><span style="float: right">Taxable Amount</span></div>
                                                    <div class="col-xs-4 p-n"><span style="float: right">Tax Amount</span></div>
                                                </div>
                                                <hr style="width:100%;", size="3", color=black>
                                                @foreach ($output_tax_rows as $row)
                                                    @if ($row['taxable'] != 0 || $row['tax'] != 0)
                                                    <div class="col-xs-12 p-n">
                                                        <div class="col-xs-4 p-n"><span style="margin-left:5px">{{$row['label']}}</span></div>
                                                        <div class="col-xs-4 p-n"><span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($row['taxable'])}}</span></div>
                                                        <div class="col-xs-4 p-n"><span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($row['tax'])}}</span></div>
                                                    </div>
                                                    <hr style="width:100%;", size="3", color=black>
                                                    @endif
                                                @endforeach
                                            @endif

                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n">
                                                    <strong><span style="margin-left:5px">Total Taxable Sales</span></strong>
                                                </div>
                                                <div class="col-xs-6 p-n">
                                                <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($total_taxable_sales)}}</span>
                                                </div>
                                            </div>
                                            <hr style="width:100%", size="3", color=black>
                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n">
                                                    <strong><span style="margin-left:5px">Total Tax Collected</span></strong>
                                                </div>
                                                <div class="col-xs-6 p-n">
                                                <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($total_tax_collected)}}</span>
                                                </div>
                                            </div>
                                            <hr style="width:100%", size="3", color=black>

                                            <div class="col-xs-12 p-n" style="background-color: #D3D3D3">
                                                <div class="col-xs-12 p-n">
                                                  <strong><span style="margin-left:5px">Purchases (Input Tax)</span></strong>
                                                </div>
                                            </div>
                                            <hr style="width:100%;", size="3", color=black>

                                            @if (isset($details) && $summary == Null)
                                                <div class="col-xs-12 p-n" style="font-weight:bold;">
                                                    <div class="col-xs-4 p-n"><span style="margin-left:5px">Tax</span></div>
                                                    <div class="col-xs-4 p-n"><span style="float: right">Taxable Amount</span></div>
                                                    <div class="col-xs-4 p-n"><span style="float: right">Tax Amount</span></div>
                                                </div>
                                                <hr style="width:100%;", size="3", color=black>
                                                @foreach ($input_tax_rows as $row)
                                                    @if ($row['taxable'] != 0 || $row['tax'] != 0)
                                                    <div class="col-xs-12 p-n">
                                                        <div class="col-xs-4 p-n"><span style="margin-left:5px">{{$row['label']}}</span></div>
                                                        <div class="col-xs-4 p-n"><span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($row['taxable'])}}</span></div>
                                                        <div class="col-xs-4 p-n"><span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($row['tax'])}}</span></div>
                                                    </div>
                                                    <hr style="width:100%;", size="3", color=black>
                                                    @endif
                                                @endforeach
                                            @endif

                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n">
                                                    <strong><span style="margin-left:5px">Total Taxable Purchases</span></strong>
                                                </div>
                                                <div class="col-xs-6 p-n">
                                                <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($total_taxable_purchases)}}</span>
                                                </div>
                                            </div>
                                            <hr style="width:100%", size="3", color=black>
                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n">
                                                    <strong><span style="margin-left:5px">Total Tax Paid</span></strong>
                                                </div>
                                                <div class="col-xs-6 p-n">
                                                <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($total_tax_paid)}}</span>
                                                </div>
                                            </div>
                                            <hr style="width:100%", size="3", color=black>

                                            <div class="col-xs-12 p-n" style="background-color: #D3D3D3; ">
                                                <div class="col-xs-5 p-n" style="margin-top: 10px; margin-bottom: -10px;">
                                                    <strong><span style="margin-left:5px">NET TAX PAYABLE</span></strong>
                                                    <p style="margin-left:5px">Tax Collected less Tax Paid</p>
                                                </div>
                                                <div class="col-xs-6 p-n" style="margin-top: 10px; margin-bottom: -10px;" >
                                                    <strong><span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($net_tax_payable)}}</span></strong>
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
