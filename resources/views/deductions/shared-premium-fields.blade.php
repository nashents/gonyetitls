{{-- Shared premium (Medical Aid, Funeral Cover ...) fields - used by the Add and Edit deduction modals. --}}
@unless ($is_system ?? false)
<h5 class="underline mt-10">Who pays?</h5>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="contribution_type">Contribution<span class="required" style="color: red">*</span></label>
            <select wire:model="contribution_type" class="form-control" required>
                <option value="employee">Employee only</option>
                <option value="shared">Shared premium (company pays part)</option>
            </select>
            @error('contribution_type') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="payable_account_id">Payable Account @if ($contribution_type === 'shared')<span class="required" style="color: red">*</span>@endif</label>
            <select wire:model.defer="payable_account_id" class="form-control">
                <option value="">{{ $contribution_type === 'shared' ? 'Select Account' : 'Payroll Suspense (default)' }}</option>
                @foreach ($payableAccounts as $account)
                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                @endforeach
            </select>
            <small class="text-muted">Owed to the provider (e.g. Medical Aid Payable).</small>
            @error('payable_account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
</div>

@if ($contribution_type === 'shared')
    @php
        $premiumValue = is_numeric($premium) ? (float) $premium : 0;
        $companyPct = is_numeric($employer_percentage) ? max(0, min(100, (float) $employer_percentage)) : 0;
    @endphp
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="premium">Premium per pay period<span class="required" style="color: red">*</span></label>
                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="premium" placeholder="Eg 100" />
                @error('premium') <span class="error" style="color:red">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="employer_percentage">Company pays %<span class="required" style="color: red">*</span></label>
                <input type="number" step="any" min="0" max="100" class="form-control" wire:model.debounce.300ms="employer_percentage" placeholder="Eg 60" />
                @error('employer_percentage') <span class="error" style="color:red">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Split</label>
                <p class="form-control-static" style="margin:0;">
                    Company: {{ number_format($premiumValue * $companyPct / 100, 2) }} ({{ rtrim(rtrim(number_format($companyPct, 2), '0'), '.') }}%)<br>
                    Employee: {{ number_format($premiumValue * (100 - $companyPct) / 100, 2) }} ({{ rtrim(rtrim(number_format(100 - $companyPct, 2), '0'), '.') }}%)
                </p>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="expense_account_id">Expense Account - Admin (company share)<span class="required" style="color: red">*</span></label>
                <select wire:model.defer="expense_account_id" class="form-control">
                    <option value="">Select Account</option>
                    @foreach ($expenseAccounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Used for everyone when payroll expenses are not split by employee type.</small>
                @error('expense_account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="expense_account_drivers_id">Expense Account - Drivers (company share)</label>
                <select wire:model.defer="expense_account_drivers_id" class="form-control">
                    <option value="">Same as Admin</option>
                    @foreach ($expenseAccounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Drivers' company share when Payroll Config splits expenses Admin / Drivers (e.g. a Cost Of Sales account).</small>
                @error('expense_account_drivers_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="employer_share_taxable">Company share is a taxable benefit?<span class="required" style="color: red">*</span></label>
                <select wire:model.defer="employer_share_taxable" class="form-control" required>
                    <option value="1">Yes - added to the PAYE base</option>
                    <option value="0">No</option>
                </select>
                @error('employer_share_taxable') <span class="error" style="color:red">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>
    <small class="text-muted">A salary is pre-filled with the employee share. The company share is worked out automatically, is not deducted from net pay, and posts DR the Expense account / CR the Payable account.</small>
@endif

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="paye_credit_percentage">PAYE credit % of employee share</label>
            <input type="number" step="any" min="0" max="100" class="form-control" wire:model.defer="paye_credit_percentage" placeholder="Eg 50 for medical aid, blank = none" />
            <small class="text-muted">This % of what the employee pays is taken off their PAYE.</small>
            @error('paye_credit_percentage') <span class="error" style="color:red">{{ $message }}</span> @enderror
        </div>
    </div>
</div>
@endunless
