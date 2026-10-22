{{-- Role / rank / department / branch filters (App\Http\Livewire\Concerns\FiltersStaff). Also applied to the Excel/CSV/PDF exports.
     Pass ['staffType' => true] to put the component's $staff_type (all / drivers / admin) select first. --}}
@php($staffFilterOptions = $this->staffFilterOptions)
@php($staffType = $staffType ?? false)
@php($col = $staffType ? 'col-md-2' : 'col-md-3')
<div class="row" style="clear: both;">
    @if ($staffType)
        <div class="col-md-2 col-sm-6">
            <div class="form-group">
                <select wire:model="staff_type" class="form-control" title="Also applies to Excel/CSV/PDF exports">
                    <option value="all">All Employees</option>
                    <option value="drivers">Drivers Only</option>
                    <option value="admin">Admin Employees Only</option>
                </select>
            </div>
        </div>
    @endif
    <div class="{{ $col }} col-sm-6">
        <div class="form-group">
            <select wire:model="filter_role_id" class="form-control" title="Also applies to Excel/CSV/PDF exports">
                <option value="">All Roles</option>
                @foreach ($staffFilterOptions['roles'] as $option)
                    <option value="{{ $option->id }}">{{ $option->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="{{ $col }} col-sm-6">
        <div class="form-group">
            <select wire:model="filter_rank_id" class="form-control" title="Also applies to Excel/CSV/PDF exports">
                <option value="">All Ranks</option>
                @foreach ($staffFilterOptions['ranks'] as $option)
                    <option value="{{ $option->id }}">{{ $option->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-2 col-sm-6">
        <div class="form-group">
            <select wire:model="filter_department_id" class="form-control" title="Also applies to Excel/CSV/PDF exports">
                <option value="">All Departments</option>
                @foreach ($staffFilterOptions['departments'] as $option)
                    <option value="{{ $option->id }}">{{ $option->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-2 col-sm-6">
        <div class="form-group">
            <select wire:model="filter_branch_id" class="form-control" title="Also applies to Excel/CSV/PDF exports">
                <option value="">All Branches</option>
                @foreach ($staffFilterOptions['branches'] as $option)
                    <option value="{{ $option->id }}">{{ $option->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-2 col-sm-12">
        @if ($this->hasStaffFilters() || ($staffType && $this->staff_type !== 'all'))
            <button type="button" wire:click="clearStaffFilters()" class="btn btn-default border-primary btn-rounded">
                <i class="fa fa-times"></i> Clear Filters
            </button>
        @endif
    </div>
</div>
