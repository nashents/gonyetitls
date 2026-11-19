{{--
    Per-line Expense Category, shown ONLY when the line's product/service has no expense account.
    Expects: $productId (selected product on the line), $model (wire:model path, e.g. "item_account_id.0").
--}}
@if ($this->requisition_for == 'Other' && $this->lineNeedsAccount($productId))
    <div class="col-md-12">
        <div class="form-group" style="margin-bottom: 5px;">
            <label>Expense Category<span class="required" style="color: red">*</span>
                <small class="text-muted">— this item has no expense category on its product/service</small>
            </label>
            <select wire:model.debounce.300ms="{{ $model }}" class="form-control" required>
                <option value="">Select Category</option>
                @foreach ($expense_accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                @endforeach
            </select>
            @error($model) <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
@endif
