<div>

    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Customs Turnaround Time</h5>
                                <small style="color: green">Days elapsed from entry submission to customs clearance.</small>
                            </div>
                        </div>
                        <div class="panel-body">

                            <div class="row hidden-print">
                                <form wire:submit.prevent="generateStatement()">
                                    <div class="col-lg-2">
                                        <div class="input-group">
                                            <span class="input-group-addon">From</span>
                                            <input type="date" wire:model.debounce.300ms="from" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <div class="input-group">
                                            <span class="input-group-addon">To</span>
                                            <input type="date" wire:model.debounce.300ms="to" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <select class="form-control" wire:model="clearing_officer_id">
                                            <option value="">All Clearing Officers</option>
                                            @foreach ($this->clearingOfficers as $officer)
                                                <option value="{{ $officer->id }}">{{ $officer->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </form>
                            </div>
                            <br><br>

                            <div class="row hidden-print">
                                <div class="col-md-12 text-center">
                                    <div class="btn-group">
                                        <button wire:click.prevent="set_report('summary')" class="btn btn-default {{$summary == "summary" ? 'border-primary' : ""}} btn-wide btn-rounded" type="button">Summary</button>
                                        <button wire:click.prevent="set_report('details')" class="btn btn-default {{$details == "details" ? 'border-primary' : ""}} btn-wide btn-rounded" type="button">Details</button>
                                    </div>
                                    <div class="btn-group" style="margin-left: 25px">
                                        <a href="{{ route('freight.reports.customs_turnaround.print', ['from' => $from, 'to' => $to, 'clearing_officer_id' => $clearing_officer_id, 'view' => $this->viewMode()]) }}" target="_blank" class="btn btn-default btn-wide btn-rounded">
                                            <i class="fa fa-print"></i> Print
                                        </a>
                                        <a href="{{ route('freight.reports.customs_turnaround.pdf', ['from' => $from, 'to' => $to, 'clearing_officer_id' => $clearing_officer_id, 'view' => $this->viewMode()]) }}" target="_blank" class="btn btn-default btn-wide btn-rounded">
                                            <i class="fa fa-file-pdf-o"></i> Export PDF
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <br>

                            <div class="row">
                                <div class="col-md-10 col-md-offset-1">
                                    <div class="row text-center">
                                        <div class="col-md-4">
                                            <h5>Declarations Cleared</h5><br>
                                            <h4 style="margin-top:-15px">{{ $overall['count'] ?? 0 }}</h4>
                                        </div>
                                        <div class="col-md-4">
                                            <h5>Average Turnaround</h5><br>
                                            <h4 style="margin-top:-15px">{{ $overall['avgDays'] ?? 0 }} days</h4>
                                        </div>
                                        <div class="col-md-4">
                                            <h5>Median Turnaround</h5><br>
                                            <h4 style="margin-top:-15px">{{ $overall['medianDays'] ?? 0 }} days</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <div class="col-xs-5 p-n"><strong>{{ $details ? 'DECLARATIONS' : 'CLEARING OFFICERS' }}</strong></div>
                                <div class="col-xs-6 p-n"><p style="font-size: 15px;"><strong><span style="float: right">{{ $from }} to {{ $to }}</span></strong></p></div>
                            </div>
                        </div>
                        <hr style="width:100%;" size="3" color="black">
                        <div class="panel-body">
                            <table class="table table-bordered table-striped">
                                @if ($details)
                                <thead>
                                    <tr><th>Declaration #</th><th>Clearing Officer</th><th>Submitted</th><th>Cleared</th><th class="text-right">Days</th></tr>
                                </thead>
                                <tbody>
                                    @forelse ($rows as $row)
                                    <tr>
                                        <td>{{ $row['declaration_number'] }}</td>
                                        <td>{{ $row['clearing_officer'] }}</td>
                                        <td>{{ $row['submission_date'] }}</td>
                                        <td>{{ $row['clearance_date'] }}</td>
                                        <td class="text-right">{{ $row['days'] }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-center">No cleared declarations found for this period/filter combination.</td></tr>
                                    @endforelse
                                </tbody>
                                @else
                                <thead>
                                    <tr><th>Clearing Officer</th><th class="text-right">Declarations</th><th class="text-right">Avg Days</th><th class="text-right">Median Days</th></tr>
                                </thead>
                                <tbody>
                                    @forelse ($rows as $row)
                                    <tr>
                                        <td>{{ $row['label'] }}</td>
                                        <td class="text-right">{{ $row['count'] }}</td>
                                        <td class="text-right">{{ $row['avgDays'] }}</td>
                                        <td class="text-right">{{ $row['medianDays'] }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center">No cleared declarations found for this period/filter combination.</td></tr>
                                    @endforelse
                                    <tr style="background-color: #D3D3D3">
                                        <td><strong>Overall</strong></td>
                                        <td class="text-right"><strong>{{ $overall['count'] ?? 0 }}</strong></td>
                                        <td class="text-right"><strong>{{ $overall['avgDays'] ?? 0 }}</strong></td>
                                        <td class="text-right"><strong>{{ $overall['medianDays'] ?? 0 }}</strong></td>
                                    </tr>
                                </tbody>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
