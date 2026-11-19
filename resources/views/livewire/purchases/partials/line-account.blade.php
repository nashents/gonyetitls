{{--
    Per-line Expense Category, shown ONLY when the line's product has no expense account
    (the PO header category was removed: lines take the account from their product).
    Inventory POs never show it - their GRV posts to Spares Inventory, not an expense.
    Expects: $productId (selected product on the line), $model (wire:model path, e.g. "item_account_id.0").
--}}
@if ($this->lineNeedsAccount($productId))
    <div class="col-md-12">
        <div class="form-group" style="margin-bottom: 5px;">
            <label>Expense Category<span class="required" style="color: red">*</span>
                <small class="text-muted">— this product has no expense category set</small>
            </label>
            <select wire:model.debounce.300ms="{{ $model }}" class="form-control" required>
                <option value="">Select Category</option>
                @foreach ($expense_accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                @endforeach
            </select>
            @error($model) <span class="text-danger error">{{ $message }}</span> @enderror
        </div>
    </div>
@endif
