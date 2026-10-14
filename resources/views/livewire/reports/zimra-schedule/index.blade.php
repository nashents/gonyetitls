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
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>ZIMRA Sales & Purchases Schedule</h5>
                                <small>Approved invoices, credit notes and bills, split into with-VAT and without-VAT schedules per currency.</small>
                            </div>
                        </div>
                        <div class="panel-body">

                            <div class="row hidden-print">
                                <form wire:submit.prevent="generateStatement()">
                                    <div class="col-lg-3">
                                        <div class="input-group">
                                            <span class="input-group-addon">From</span>
                                            <input type="date" wire:model.debounce.300ms="from" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="input-group">
                                            <span class="input-group-addon">To</span>
                                            <input type="date" wire:model.debounce.300ms="to" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="input-group">
                                            <span class="input-group-addon">Currency</span>
                                            <select wire:model.debounce.300ms="selectedCurrency" class="form-control">
                                                <option value="">All Currencies</option>
                                                @foreach ($currencies as $currency)
                                                    <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </form>
                                <div class="col-lg-3 text-right">
                                    <a href="{{ route('reports.zimra_schedule.print', $this->exportParams()) }}" target="_blank" class="btn btn-default btn-rounded">
                                        <i class="fa fa-print"></i> Print
                                    </a>
                                    <a href="{{ route('reports.zimra_schedule.excel', $this->exportParams()) }}" class="btn btn-success btn-rounded">
                                        <i class="fa fa-file-excel-o"></i> Export Excel
                                    </a>
                                </div>
                            </div>

                            <div class="row hidden-print" style="margin-top: 12px">
                                <div class="col-md-12">
                                    <small class="text-muted">Quick periods:</small>
                                    <div class="btn-group btn-group-xs">
                                        <button type="button" wire:click="setPeriod('this_month')" class="btn btn-default">This Month</button>
                                        <button type="button" wire:click="setPeriod('last_month')" class="btn btn-default">Last Month</button>
                                        <button type="button" wire:click="setPeriod('last_two_months')" class="btn btn-default">Last 2 Months</button>
                                        <button type="button" wire:click="setPeriod('this_year')" class="btn btn-default">This Year</button>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="text-center" style="margin-bottom: 20px">
                                <h4 style="margin-bottom: 4px"><strong>{{ strtoupper($company->name ?? '') }}</strong></h4>
                                <div>ZIMRA SALES AND PURCHASES SCHEDULE &mdash; {{ $periodLabel }}</div>
                            </div>

                            <div style="overflow-x:auto; width:100%">
                                @include('reports.zimra_schedule.partials.schedule', ['links' => true])
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
