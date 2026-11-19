@php
    $expiresAt = $document->expires_at ? \Carbon\Carbon::parse($document->expires_at) : null;
    $isExpired = $expiresAt && $expiresAt->isPast();
    $daysLeft = $expiresAt ? now()->startOfDay()->diffInDays($expiresAt->copy()->startOfDay(), false) : null;
    $isWarning = $expiresAt && !$isExpired && $daysLeft <= 30;
    $extension = strtolower(pathinfo($document->filename, PATHINFO_EXTENSION));
    if ($extension === 'pdf') {
        $icon = 'fa-file-pdf-o';
    } elseif (in_array($extension, ['xls', 'xlsx', 'csv'])) {
        $icon = 'fa-file-excel-o';
    } elseif (in_array($extension, ['doc', 'docx'])) {
        $icon = 'fa-file-word-o';
    } elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        $icon = 'fa-file-image-o';
    } else {
        $icon = 'fa-file-o';
    }
@endphp

<div class="doc-card {{ $isExpired ? 'doc-expired' : ($isWarning ? 'doc-warning' : '') }}">
    <div class="doc-main">
        <div class="doc-icon">
            <i class="fa {{ $icon }}"></i>
        </div>

        <div class="doc-name">
            <a href="{{ asset('myfiles/documents/'.$document->filename) }}" target="_blank">
                {{ $document->title }}
            </a>
            <small class="doc-file-name">{{ $document->filename }}</small>

            @if($category === 'all')
                <span class="badge bg-info">
                    {{ ucwords(str_replace('_', ' ', $document->category)) }}
                </span>
            @endif
        </div>
    </div>

    <div class="doc-meta">
        @if($expiresAt)
            <span class="badge bg-{{ $isExpired ? 'danger' : ($isWarning ? 'warning' : 'success') }}">
                @if($isExpired)
                    Expired {{ abs($daysLeft) }} day{{ abs($daysLeft) == 1 ? '' : 's' }} ago
                @elseif($daysLeft == 0)
                    Expires today
                @else
                    Expires in {{ $daysLeft }} day{{ $daysLeft == 1 ? '' : 's' }}
                @endif
            </span>
            <small class="text-muted d-block">Expiry: {{ $expiresAt->format('d M Y') }}</small>
        @else
            <span class="badge bg-secondary">No expiry</span>
        @endif

        <small class="text-muted d-block">
            Uploaded by: {{ $document->user ? $document->user->name.' '.$document->user->surname : 'N/A' }}
        </small>

        @if($lastMove = $document->latestMovement)
            <small class="d-block">
                @if($lastMove->status === 'pending')
                    <span class="badge bg-warning">In transit to {{ $lastMove->toLabel() }}</span>
                @elseif($lastMove->status === 'received')
                    <i class="fa fa-user"></i> With {{ $lastMove->toLabel() }}@if($lastMove->location) · {{ $lastMove->location }}@endif
                @else
                    <i class="fa fa-user"></i> With {{ $lastMove->fromLabel() }}
                @endif
            </small>
        @endif
    </div>

    <div class="doc-actions">
        <a href="{{ route('documents.custody', $document->id) }}" title="Chain of custody / hand over"><i class="fa fa-exchange color-primary"></i></a>
    @if($canManageDocuments)
            <a href="#" wire:click.prevent="edit({{ $document->id }})"><i class="fa fa-edit color-success"></i></a>
            <a href="#" wire:click.prevent="showDocumentDelete({{ $document->id }})"><i class="fa fa-trash color-danger"></i></a>
    @endif
    </div>
</div>
