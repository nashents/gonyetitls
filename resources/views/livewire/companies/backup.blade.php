<div @if ($hasPending) wire:poll.10s @endif>
    <div class="alert alert-info">
        <strong><i class="fa fa-download"></i> Full Data Backup</strong>
        <p class="mb-0 mt-5">
            Generates every Excel report in the system — trips, invoices, payments, bills, fuel, shifts, workshop,
            inventory, fleet, HR, contacts and master data — covering <strong>all dates</strong>, and bundles them into
            a single zip file.
        </p>
        <p class="mb-0 mt-5">
            Use this to keep a copy of your records, for example before you stop using the system. Building the
            backup runs in the background and can take a while for large datasets; this page updates when it is ready.
        </p>
    </div>

    <button type="button" class="btn btn-primary btn-rounded" wire:click="generate" wire:loading.attr="disabled"
            @if ($hasPending) disabled @endif>
        @if ($hasPending)
            <i class="fa fa-spinner fa-spin"></i> Backup in progress…
        @else
            <i class="fa fa-download"></i> Generate Backup
        @endif
    </button>

    <div class="table-responsive mt-15">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Requested</th>
                    <th>By</th>
                    <th>Status</th>
                    <th>Files</th>
                    <th>Size</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($backups as $backup)
                    <tr wire:key="backup-{{ $backup->id }}">
                        <td>{{ $backup->created_at->format('d M Y H:i') }}</td>
                        <td>{{ optional($backup->user)->name }} {{ optional($backup->user)->surname }}</td>
                        <td>
                            @if ($backup->status === 'completed')
                                <span class="label label-success">Ready</span>
                            @elseif ($backup->isPending())
                                <span class="label label-warning">{{ $backup->status === 'queued' ? 'Queued' : 'Building' }}</span>
                            @elseif ($backup->status === 'running')
                                <span class="label label-danger">Stalled</span>
                            @else
                                <span class="label label-danger" title="{{ $backup->error }}">Failed</span>
                            @endif
                        </td>
                        <td>
                            @if ($backup->status === 'completed')
                                {{ $backup->exported_count }}
                                @if ($backup->failed_exports)
                                    <span class="text-danger" title="{{ implode(', ', array_keys($backup->failed_exports)) }}">
                                        ({{ count($backup->failed_exports) }} skipped — see README in zip)
                                    </span>
                                @endif
                            @endif
                        </td>
                        <td>{{ $backup->size ? number_format($backup->size / 1048576, 2).' MB' : '' }}</td>
                        <td>
                            @if ($backup->isDownloadable())
                                <a href="{{ route('data-backups.download', $backup->id) }}" class="btn btn-success btn-xs">
                                    <i class="fa fa-download"></i> Download
                                </a>
                            @endif
                            @unless ($backup->isPending())
                                <button type="button" class="btn btn-danger btn-xs" wire:click="delete({{ $backup->id }})"
                                        onclick="confirm('Delete this backup file?') || event.stopImmediatePropagation()">
                                    <i class="fa fa-trash"></i>
                                </button>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">No backups generated yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
