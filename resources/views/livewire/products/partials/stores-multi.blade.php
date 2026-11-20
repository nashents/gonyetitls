{{-- Every store this product may be kept in (product_store). The Default Store is always included. --}}
<div class="col-md-4">
    <div class="form-group">
        <label for="store_ids">Other Stores <small class="text-muted">(where it can be kept)</small></label>
        <select wire:model="store_ids" class="form-control" multiple size="4">
            @foreach ($stores as $store)
                <option value="{{ $store->id }}">{{ $store->name }}</option>
            @endforeach
        </select>
        <small class="text-muted">Hold Ctrl (Cmd on Mac) to pick several. The default store is always included.</small>
        @error('store_ids') <span class="error" style="color:red">{{ $message }}</span> @enderror
    </div>
</div>
