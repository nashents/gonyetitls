<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Freight Forwarding Demo Data</h5>
                                <small style="color: green">Load sample freight jobs into {{ $company?->name ?? 'your company' }} so new users can explore the module, then remove them again when they are done.
                                    Only system administrators see this page.</small>
                            </div>
                            <div>
                                @include('includes.messages')
                            </div>
                        </div>
                        <div class="panel-body p-20">
                            @unless ($companyId)
                                <div class="alert alert-warning">Your login has no employee record / company, so demo data can't be loaded from it.</div>
                            @endunless

                            <div class="row">
                                <div class="col-md-7">
                                    <h6 class="underline mb-10"><strong>What gets loaded</strong></h6>
                                    <table class="table table-bordered table-striped">
                                        <tbody>
                                            @foreach ($scenarios as [$title, $detail])
                                                <tr>
                                                    <td style="width:32%"><strong>{{ $title }}</strong></td>
                                                    <td>{{ $detail }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <div style="font-size:12px; line-height:1.5; color:#31708f;">
                                        <i class="fa fa-info-circle"></i> Everything is labelled <strong>DEMO</strong>: its own demo customers, vendors, consignees, clearing agent and broker (real parties are never touched),
                                        job references DEMO-PO-…, container numbers and documents. Jobs are created already approved.<br>
                                        <i class="fa fa-shield"></i> Loading sends no emails, posts nothing to the ledger, creates no invoices, bills or trips, and sends nothing to a linked transport instance or Sage.
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <h6 class="underline mb-10"><strong>Status</strong></h6>
                                    @if ($active)
                                        <div class="alert alert-info" style="margin-bottom:10px">
                                            <i class="fa fa-flask"></i> Demo data is <strong>loaded</strong> since {{ $active->loaded_at?->format('d M Y H:i') }}
                                            by {{ $active->user ? trim($active->user->name . ' ' . $active->user->surname) : '—' }}.
                                            <div style="margin-top:6px;font-size:12px">
                                                {{ $active->summary['freight_jobs'] ?? 0 }} freight jobs ·
                                                {{ $active->summary['shipping_containers'] ?? 0 }} containers ·
                                                {{ $active->summary['customs_declarations'] ?? 0 }} customs declarations ·
                                                {{ ($active->summary['freight_costs'] ?? 0) + ($active->summary['freight_charges'] ?? 0) }} cost/charge lines
                                            </div>
                                        </div>
                                        <a href="{{ route('freight.jobs.index') }}" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-ship"></i> View Freight Jobs</a>
                                        @unless ($plan)
                                            <a href="#" wire:click.prevent="preview" class="btn btn-danger btn-rounded btn-wide"><i class="fa fa-undo"></i> Roll Back Demo Data</a>
                                        @endunless
                                    @else
                                        <p class="text-muted">No demo data is loaded for this company.</p>
                                        @if ($companyId)
                                            <a href="#" wire:click.prevent="load" wire:confirm="Load the freight demo jobs into {{ $company?->name }}? You can roll them back later." wire:loading.attr="disabled" class="btn btn-primary btn-rounded btn-wide">
                                                <i class="fa fa-download"></i> Load Demo Data
                                            </a>
                                        @endif
                                    @endif

                                    @if ($plan)
                                        <div style="border:1px solid #e3e7ec;border-radius:6px;padding:10px 12px;margin-top:12px;background:#fafbfc">
                                            @if ($plan['blockers'])
                                                <strong style="color:#c0392b"><i class="fa fa-ban"></i> Rollback can't run yet</strong>
                                                <ul style="margin:6px 0 8px 18px;font-size:13px">
                                                    @foreach ($plan['blockers'] as $reason)
                                                        <li>{{ $reason }}</li>
                                                    @endforeach
                                                </ul>
                                                <a href="#" wire:click.prevent="preview" class="btn btn-xs btn-default"><i class="fa fa-refresh"></i> Check again</a>
                                                <a href="#" wire:click.prevent="cancelPreview" class="btn btn-xs btn-default">Close</a>
                                            @else
                                                <strong><i class="fa fa-trash"></i> This will permanently delete</strong>
                                                <div style="font-size:12px;margin:6px 0">
                                                    @foreach ($plan['delete'] as $table => $count)
                                                        <span class="label label-default" style="display:inline-block;margin:2px">{{ $count }} {{ \App\Http\Livewire\Freight\DemoData::label($table) }}</span>
                                                    @endforeach
                                                </div>
                                                @if (array_sum($plan['keep']))
                                                    <div style="font-size:12px;color:#c77a00;margin-bottom:6px">
                                                        <i class="fa fa-exclamation-triangle"></i> Kept because real records use them:
                                                        @foreach ($plan['keep'] as $table => $count)
                                                            {{ $count }} {{ \App\Http\Livewire\Freight\DemoData::label($table) }}@if (!$loop->last), @endif
                                                        @endforeach
                                                    </div>
                                                @endif
                                                <div style="font-size:12px;color:#555;margin-bottom:8px">Includes anything users added to the demo jobs (extra costs, documents, reminders...). Job numbers already used are not re-issued.</div>
                                                <a href="#" wire:click.prevent="rollback" wire:confirm="Permanently delete all freight demo data? This cannot be undone." wire:loading.attr="disabled" class="btn btn-danger btn-sm"><i class="fa fa-undo"></i> Confirm Rollback</a>
                                                <a href="#" wire:click.prevent="cancelPreview" class="btn btn-default btn-sm">Cancel</a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if ($history->isNotEmpty())
                                <h6 class="underline mb-10 mt-20"><strong>History</strong></h6>
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr><th>Loaded</th><th>By</th><th>Freight Jobs</th><th>Status</th><th>Rolled Back</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($history as $batch)
                                            <tr wire:key="demo-batch-{{ $batch->id }}">
                                                <td>{{ $batch->loaded_at?->format('d M Y H:i') }}</td>
                                                <td>{{ $batch->user ? trim($batch->user->name . ' ' . $batch->user->surname) : '—' }}</td>
                                                <td>{{ $batch->summary['freight_jobs'] ?? 0 }}</td>
                                                <td>
                                                    @if ($batch->isLoaded())
                                                        <span class="label label-info">Loaded</span>
                                                    @else
                                                        <span class="label label-default">Rolled back</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($batch->rolled_back_at)
                                                        {{ $batch->rolled_back_at->format('d M Y H:i') }}
                                                        by {{ $batch->rolledBackBy ? trim($batch->rolledBackBy->name . ' ' . $batch->rolledBackBy->surname) : '—' }}
                                                        @if (array_sum($batch->rollback_summary['kept'] ?? []))
                                                            <br><small class="text-muted">{{ array_sum($batch->rollback_summary['kept']) }} demo record(s) kept (in use)</small>
                                                        @endif
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
