{{-- Deduction calculation fields - shared by the Add and Edit deduction modals (same layout as Earnings). --}}
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="calculate_by">Calculate By<span class="required" style="color: red">*</span></label>
            <select wire:model="calculate_by" class="form-control" required>
                <option value="currency">Fixed Amount</option>
                <option value="percentage">Percentage</option>
            </select>
            @error('calculate_by') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            @if ($calculate_by === 'percentage')
                <label for="percentage">Default Percentage</label>
                <input type="number" step="any" min="0" max="100" class="form-control" wire:model.debounce.300ms="percentage" placeholder="Eg 5" />
                @error('percentage') <span class="error" style="color:red">{{ $message }}</span> @enderror
            @else
                <label for="amount">Default Amount</label>
                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="amount" placeholder="Eg 50" />
                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
            @endif
            <small class="text-muted">Pre-fills the salary form; can be changed per salary.</small>
        </div>
    </div>
    @if ($calculate_by === 'percentage')
        <div class="col-md-4">
            <div class="form-group">
                <label for="calculate_on">Percentage Of<span class="required" style="color: red">*</span></label>
                <select wire:model.defer="calculate_on" class="form-control" required>
                    <option value="basic">Basic salary</option>
                    <option value="gross">Gross (basic + earnings)</option>
                </select>
                @error('calculate_on') <span class="error" style="color:red">{{ $message }}</span> @enderror
            </div>
        </div>
    @endif
</div>
