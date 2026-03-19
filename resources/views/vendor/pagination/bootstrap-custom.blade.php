@if ($paginator->hasPages())
<nav aria-label="Page navigation" style="float:right">
    <div class="d-flex align-items-center" style="gap: 6px;">
            <small class="text-muted text-nowrap">Rows per page:</small>
            <select
                class="form-select form-select-sm"
                style="width: 70px;"
                onchange="
                    window.Livewire.find(
                        this.closest('[wire\\:id]').getAttribute('wire:id')
                    ).set('perPage', parseInt(this.value));
                "
            >
                @foreach([10, 25, 50, 100] as $option)
                    <option value="{{ $option }}" {{ $paginator->perPage() == $option ? 'selected' : '' }}>
                        {{ $option }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted text-nowrap">
                &mdash; {{ number_format($paginator->total()) }} total
            </small>
        </div>
    <ul class="pagination rounded-corners justify-content-end flex-wrap" style="gap: 2px;">

        {{-- First Page --}}
        <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
            <a class="page-link" wire:click.prevent="gotoPage(1)" href="#">&laquo;&laquo;</a>
        </li>

        {{-- Previous --}}
        <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
            <a class="page-link" wire:click.prevent="previousPage" href="#">&laquo;</a>
        </li>

        @php
            $current  = $paginator->currentPage();
            $last     = $paginator->lastPage();
            $window   = 3; // pages either side of current
            $start    = max(1, $current - $window);
            $end      = min($last, $current + $window);
        @endphp

        {{-- Leading ellipsis --}}
        @if ($start > 1)
            <li class="page-item">
                <a class="page-link" wire:click.prevent="gotoPage(1)" href="#">1</a>
            </li>
            @if ($start > 2)
                <li class="page-item disabled"><span class="page-link">…</span></li>
            @endif
        @endif

        {{-- Page window --}}
        @for ($page = $start; $page <= $end; $page++)
            <li class="page-item {{ $page === $current ? 'active' : '' }}">
                <a class="page-link" wire:click.prevent="gotoPage({{ $page }})" href="#">{{ $page }}</a>
            </li>
        @endfor

        {{-- Trailing ellipsis --}}
        @if ($end < $last)
            @if ($end < $last - 1)
                <li class="page-item disabled"><span class="page-link">…</span></li>
            @endif
            <li class="page-item">
                <a class="page-link" wire:click.prevent="gotoPage({{ $last }})" href="#">{{ $last }}</a>
            </li>
        @endif

        {{-- Next --}}
        <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
            <a class="page-link" wire:click.prevent="nextPage" href="#">&raquo;</a>
        </li>

        {{-- Last Page --}}
        <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
            <a class="page-link" wire:click.prevent="gotoPage({{ $last }})" href="#">&raquo;&raquo;</a>
        </li>

        {{-- Jump-to input --}}
      <li class="page-item ms-2 d-flex align-items-center" style="gap: 4px;">
        <input
            type="number"
            min="1"
            max="{{ $last }}"
            placeholder="{{ $current }}"
            class="form-control form-control-sm"
            style="width: 70px;"
            onkeydown="
                if (event.key === 'Enter') {
                    event.preventDefault();
                    var p = parseInt(this.value);
                    if (p >= 1 && p <= {{ $last }}) {
                        var component = window.Livewire.find(
                            this.closest('[wire\\:id]').getAttribute('wire:id')
                        );
                        component.gotoPage(p);
                        this.value = '';
                    }
                }
            "
        >
        <small class="text-muted text-nowrap">of {{ $last }}</small>
    </li>

    </ul>
</nav>
@endif