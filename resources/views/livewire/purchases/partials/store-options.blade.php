{{-- Store <option>s for a PO line: the product's own stores first, then every other store. Expects $productId. --}}
@php
    $productStoreIds = $this->productStoreIds($productId);
    [$productStores, $otherStores] = $stores->partition(fn ($store) => in_array((int) $store->id, $productStoreIds, true));
@endphp
@if ($productStores->isNotEmpty())
    <optgroup label="This product's stores">
        @foreach ($productStores as $store)
            <option value="{{ $store->id }}">{{ $store->name }}</option>
        @endforeach
    </optgroup>
    <optgroup label="Other stores">
        @foreach ($otherStores as $store)
            <option value="{{ $store->id }}">{{ $store->name }}</option>
        @endforeach
    </optgroup>
@else
    @foreach ($stores as $store)
        <option value="{{ $store->id }}">{{ $store->name }}</option>
    @endforeach
@endif
