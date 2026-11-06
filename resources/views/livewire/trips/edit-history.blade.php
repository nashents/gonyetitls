<div>
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-sm" style="width:100%">
            <thead>
                <tr>
                    <th style="width:14%">Date &amp; Time</th>
                    <th style="width:14%">Done By</th>
                    <th style="width:12%">Section</th>
                    <th style="width:8%">Action</th>
                    <th>Changes</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr wire:key="trip-edit-history-{{ $row['id'] }}">
                        <td>{{ optional($row['when'])->format('d M Y H:i:s') }}</td>
                        <td>{{ $row['user'] }}</td>
                        <td>{{ $row['section'] }}</td>
                        <td>
                            @php
                                $badge = [
                                    'created'  => 'success',
                                    'updated'  => 'info',
                                    'deleted'  => 'danger',
                                    'restored' => 'warning',
                                ][$row['event']] ?? 'default';
                            @endphp
                            <span class="label label-{{ $badge }}">{{ ucfirst($row['event']) }}</span>
                        </td>
                        <td class="p-0">
                            @if (count($row['changes']))
                                <table class="table table-condensed mb-0" style="background:transparent">
                                    @if ($row['event'] === 'updated')
                                        <tr>
                                            <th style="width:30%">Field</th>
                                            <th style="width:35%">From</th>
                                            <th style="width:35%">To</th>
                                        </tr>
                                    @endif
                                    @foreach ($row['changes'] as $i => $change)
                                        <tr wire:key="trip-edit-history-{{ $row['id'] }}-{{ $i }}">
                                            <td><strong>{{ $change['field'] }}</strong></td>
                                            @if ($row['event'] === 'updated')
                                                <td class="text-danger">{{ $change['old'] }}</td>
                                                <td class="text-success">{{ $change['new'] }}</td>
                                            @elseif ($row['event'] === 'deleted')
                                                <td colspan="2">{{ $change['old'] }}</td>
                                            @else
                                                <td colspan="2">{{ $change['new'] }}</td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </table>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No edits recorded for this trip yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pull-right">
        {{ $audits->links() }}
    </div>
    <div class="clearfix"></div>
</div>
