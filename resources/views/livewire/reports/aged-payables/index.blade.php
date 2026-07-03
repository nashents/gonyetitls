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
                                                <h5>Aged Payables</h5>
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
                                                        <a href="{{ route('reports.aged_payables.print', ['as_of_date' => $as_of_date, 'view' => $this->viewMode()]) }}" target="_blank" class="btn btn-default btn-wide btn-rounded">
                                                            <i class="fa fa-print"></i> Print
                                                        </a>
                                                        <a href="{{ route('reports.aged_payables.pdf', ['as_of_date' => $as_of_date, 'view' => $this->viewMode()]) }}" target="_blank" class="btn btn-default btn-wide btn-rounded">
                                                            <i class="fa fa-file-pdf-o"></i> Export PDF
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <br>
                                            <div class="row">
                                                <div class="col-md-10 col-md-offset-1">
                                                    <div class="row text-center">
                                                        @foreach (\App\Services\AgedPayablesCalculator::BUCKETS as $bucket)
                                                        <div class="col-md-2">
                                                            <h5>{{$bucket}}</h5>
                                                            <br>
                                                            <h4 style="margin-top:-15px">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($grand_totals['buckets'][$bucket] ?? 0)}}</h4>
                                                        </div>
                                                        @endforeach
                                                        <div class="col-md-2">
                                                            <h5>Total Payable</h5>
                                                            <br>
                                                            <h4 style="margin-top:-15px">{{$default_currency->name}} {{$default_currency->symbol}}{{ $this->fmt($grand_totals['total'])}}</h4>
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
                                                   <strong>VENDORS</strong>
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
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Vendor</th>
                                                        @foreach (\App\Services\AgedPayablesCalculator::BUCKETS as $bucket)
                                                        <th class="text-right">{{$bucket}}</th>
                                                        @endforeach
                                                        <th class="text-right">Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if (isset($details) && $summary == Null)
                                                        @foreach ($vendor_rows as $row)
                                                        @if ($row['total'] != 0)
                                                        <tr>
                                                            <td>{{$row['label']}}</td>
                                                            @foreach (\App\Services\AgedPayablesCalculator::BUCKETS as $bucket)
                                                            <td class="text-right">{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($row['buckets'][$bucket] ?? 0)}}</td>
                                                            @endforeach
                                                            <td class="text-right"><strong>{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($row['total'])}}</strong></td>
                                                        </tr>
                                                        @endif
                                                        @endforeach
                                                    @endif
                                                    <tr style="background-color: #D3D3D3">
                                                        <td><strong>Total</strong></td>
                                                        @foreach (\App\Services\AgedPayablesCalculator::BUCKETS as $bucket)
                                                        <td class="text-right"><strong>{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($grand_totals['buckets'][$bucket] ?? 0)}}</strong></td>
                                                        @endforeach
                                                        <td class="text-right"><strong>{{$default_currency->name}} {{$default_currency->symbol}}{{$this->fmt($grand_totals['total'])}}</strong></td>
                                                    </tr>
                                                </tbody>
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
