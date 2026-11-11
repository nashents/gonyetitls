{{-- Auth Status filter for index tables; see App\Http\Livewire\Concerns\FiltersAuthStatus --}}
<div class="{{ $class ?? 'col-lg-2' }}" @isset($style) style="{{ $style }}" @endisset>
    <div class="input-group">
        <span class="input-group-addon">
            Auth Status
        </span>
        <select wire:model="auth_status" class="form-control" aria-label="Auth Status">
            <option value="">All</option>
            @foreach ($this->authStatusOptions() as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>
