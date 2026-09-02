<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div>@include('includes.messages')</div>
                        </div>
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%;">

                            @if (! $this->sageEnabled)
                                <div class="alert alert-warning">The Sage Intacct integration is not active for your company.</div>
                            @else

                            {{-- Summary tiles --}}
                            @php
                                $synced   = $summary['synced'] ?? 0;
                                $pending  = $summary['pending'] ?? 0;
                                $attn     = $summary['requires_attention'] ?? 0;
                                $failed   = $summary['failed'] ?? 0;
                                $total    = array_sum($summary);
                            @endphp
                            <div class="row" style="margin-bottom:15px">
                                <div class="col-md-3"><div class="panel" style="border-left:4px solid #5cb85c"><div class="panel-body"><h4 style="margin:0">{{ $synced }}</h4><small class="text-muted">Synced</small></div></div></div>
                                <div class="col-md-3"><div class="panel" style="border-left:4px solid #f0ad4e"><div class="panel-body"><h4 style="margin:0">{{ $attn }}</h4><small class="text-muted">Requires attention</small></div></div></div>
                                <div class="col-md-3"><div class="panel" style="border-left:4px solid #d9534f"><div class="panel-body"><h4 style="margin:0">{{ $failed }}</h4><small class="text-muted">Failed</small></div></div></div>
                                <div class="col-md-3"><div class="panel" style="border-left:4px solid #777"><div class="panel-body"><h4 style="margin:0">{{ $pending }}</h4><small class="text-muted">Outstanding / pending</small></div></div></div>
                            </div>

                            {{-- Filters --}}
                            <div class="row" style="margin-bottom:10px">
                                <div class="col-md-3">
                                    <select wire:model="entityFilter" class="form-control">
                                        <option value="">All document types</option>
                                        @foreach ($entities as $e)
                                            <option value="{{ $e }}">{{ $labels[$e] ?? ucwords(str_replace('_',' ', $e)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select wire:model="statusFilter" class="form-control">
                                        <option value="">All statuses</option>
                                        <option value="failed">Failed</option>
                                        <option value="requires_attention">Requires attention</option>
                                        <option value="pending">Outstanding / pending</option>
                                        <option value="synced">Synced</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" wire:model.debounce.400ms="search" class="form-control" placeholder="Search reference, Sage #, or error...">
                                </div>
                            </div>

                            <table class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th class="th-sm">Document type</th>
                                        <th class="th-sm">Gonyeti Ref</th>
                                        <th class="th-sm">Sage Ref</th>
                                        <th class="th-sm">Status</th>
                                        <th class="th-sm">Exception / last error</th>
                                        <th class="th-sm">Last attempt</th>
                                        <th class="th-sm">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($rows as $m)
                                        @php
                                            $ss = $m->sync_status;
                                            $cls = $ss === 'synced' ? 'success' : ($ss === 'failed' ? 'danger' : ($ss === 'requires_attention' ? 'warning' : 'secondary'));
                                        @endphp
                                        <tr>
                                            <td>{{ $labels[$m->entity_type] ?? ucwords(str_replace('_',' ', $m->entity_type)) }}</td>
                                            <td>{{ $m->local_reference ?: ('#'.$m->local_id) }}</td>
                                            <td>{{ $m->external_id ?: '—' }}</td>
                                            <td><span class="badge bg-{{ $cls }}">{{ ucwords(str_replace('_',' ', $ss)) }}</span></td>
                                            <td style="max-width:340px"><small class="text-danger">{{ $m->last_error }}</small></td>
                                            <td><small>{{ optional($m->last_attempted_at)->format('d M Y H:i') }}</small></td>
                                            <td>
                                                @if ($ss !== 'synced')
                                                    <a href="#" wire:click.prevent="retry({{ $m->id }})" wire:loading.attr="disabled" title="Re-attempt sync"><i class="fa fa-refresh"></i> Retry</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7" style="text-align:center; padding:14px; color:grey; font-size:16px">No integration records match.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>

                            <nav class="text-center" style="float:right">
                                <ul class="pagination rounded-corners">{{ $rows->links() }}</ul>
                            </nav>

                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
