<div class="container-fluid">
    @php
        $sections = [
            ['title' => 'Awaiting my receipt', 'icon' => 'fa-inbox', 'rows' => $incoming, 'empty' => 'Nothing waiting for you.'],
            ['title' => 'Sent — not yet received', 'icon' => 'fa-paper-plane', 'rows' => $outgoing, 'empty' => 'No open hand-overs from you.'],
            ['title' => 'Recent history', 'icon' => 'fa-history', 'rows' => $history, 'empty' => 'No history yet.'],
        ];
    @endphp

    @foreach($sections as $section)
        <div class="card" style="background:#fff;border:1px solid #e3e7ec;border-radius:6px;padding:16px;margin-bottom:16px">
            <h5 style="margin-top:0"><i class="fa {{ $section['icon'] }}"></i> {{ $section['title'] }} ({{ $section['rows']->count() }})</h5>
            <div class="table-responsive">
                <table class="table table-striped" style="margin:0">
                    <thead>
                        <tr><th>Document</th><th>From</th><th>To</th><th>Handed over</th><th>Location</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse($section['rows'] as $m)
                            <tr wire:key="{{ $loop->parent->index }}-{{ $m->id }}">
                                <td>{{ $m->document->title }}<br><small class="text-muted">{{ ucwords(str_replace('_', ' ', $m->document->category)) }}</small></td>
                                <td>{{ $m->fromLabel() }}</td>
                                <td>{{ $m->toLabel() }}</td>
                                <td>{{ optional($m->handed_over_at)->format('d M Y H:i') }}</td>
                                <td>{{ $m->location }}</td>
                                <td>
                                    @if($m->status === 'received')<span class="badge bg-success">Received</span>
                                    @elseif($m->status === 'rejected')<span class="badge bg-danger">Rejected</span>
                                    @else<span class="badge bg-warning">In transit</span>@endif
                                </td>
                                <td><a class="btn btn-default btn-xs" href="{{ route('documents.custody', $m->document_id) }}">{{ $m->status === 'pending' && $m->to_user_id == auth()->id() ? 'Receive' : 'Open' }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-muted text-center">{{ $section['empty'] }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
</div>
