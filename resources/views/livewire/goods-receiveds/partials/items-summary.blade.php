@php
    $items = collect()
        ->concat($goods_received->inventories)
        ->concat($goods_received->tyres)
        ->concat($goods_received->assets);
@endphp
@if ($items->isEmpty())
    <span class="text-muted">&mdash;</span>
@else
    @foreach ($items as $item)
        <div class="text-nowrap">
            <small>
                <strong>{{ $item->inventory_number ?? $item->tyre_number ?? $item->asset_number }}</strong>
                {{ optional($item->product)->name }}
                x {{ $item->qty ?? 1 }}
                @if (optional($item->product)->unit_of_measure)
                    ({{ $item->product->unit_of_measure }})
                @endif
            </small>
        </div>
    @endforeach
@endif
