<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Charge Types</h5>
                                <small style="color: green">The categories used on Freight Cost/Charge lines (e.g. Ocean Freight, Documentation Fee, Port Handling). Tick "Tracks container free time" to make one selectable in Charge Config for free days and rate tiers.</small>
                            </div>
                        </div>
                        <div class="panel-body">
                            <form wire:submit.prevent="save" class="mb-20">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Name <span class="required text-danger">*</span></label>
                                            <input type="text" class="form-control" wire:model="name">
                                            @error('name') <span class="text-danger error">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Description</label>
                                            <input type="text" class="form-control" wire:model="description">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Status <span class="required text-danger">*</span></label>
                                            @if ($editing_built_in)
                                                <input type="text" class="form-control" value="Active (built-in)" disabled>
                                            @else
                                                <select class="form-control" wire:model.defer="is_active">
                                                    <option value="1">Active</option>
                                                    <option value="0">Inactive</option>
                                                </select>
                                            @endif
                                            @error('is_active') <span class="text-danger error">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label>&nbsp;</label>
                                        <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-save"></i></button>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label>Revenue Account <small style="color:green">(used when this charge type is invoiced to a customer)</small></label>
                                            <select class="form-control" wire:model="revenue_account_id">
                                                <option value="">Select Revenue Account</option>
                                                @foreach ($revenueAccounts as $account)
                                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('revenue_account_id') <span class="text-danger error">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label>Expense Account <small style="color:green">(used when this charge type is billed from a supplier)</small></label>
                                            <select class="form-control" wire:model="expense_account_id">
                                                <option value="">Select Expense Account</option>
                                                @foreach ($expenseAccounts as $account)
                                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('expense_account_id') <span class="text-danger error">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    @if ($charge_type_id)
                                        <div class="col-md-2">
                                            <label>&nbsp;</label>
                                            <a href="#" wire:click.prevent="resetForm" class="btn btn-default btn-block">Cancel</a>
                                        </div>
                                    @endif
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        @if ($editing_built_in)
                                            <p style="color: #c77a00; margin-bottom: 10px;"><i class="fa fa-cube"></i> Tracks container free time automatically (built-in). Set its free days and rate tiers in Charge Config.</p>
                                            @if ($builtInClock)
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Clock Starts At <i class="fa fa-lock" title="Fixed by the system"></i></label>
                                                            <input type="text" class="form-control" value="{{ $stageLabels[$builtInClock['start']] ?? $builtInClock['start'] ?? '—' }}" disabled>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Clock Stops At <i class="fa fa-lock" title="Fixed by the system"></i></label>
                                                            <input type="text" class="form-control" value="{{ $stageLabels[$builtInClock['stop']] ?? $builtInClock['stop'] ?? '—' }}" disabled>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @else
                                            <div class="checkbox" style="margin-top: 0;">
                                                <label><input type="checkbox" wire:model="tracks_free_time"> Tracks container free time <small style="color:green">(free days + daily rate in Charge Config, e.g. reefer plug-in)</small></label>
                                            </div>
                                            @error('tracks_free_time') <span class="text-danger error">{{ $message }}</span> @enderror
                                        @endif
                                    </div>
                                </div>
                                @if ($tracks_free_time && !$editing_built_in)
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Clock Starts At</label>
                                                <select class="form-control" wire:model.defer="start_milestone">
                                                    <option value="">Select Milestone</option>
                                                    @foreach ($milestones as $code => $label)
                                                        <option value="{{ $code }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                @error('start_milestone') <span class="text-danger error">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Clock Stops At</label>
                                                <select class="form-control" wire:model.defer="stop_milestone">
                                                    <option value="">Select Milestone</option>
                                                    @foreach ($milestones as $code => $label)
                                                        <option value="{{ $code }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                @error('stop_milestone') <span class="text-danger error">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-12" style="margin-top:-8px; margin-bottom:8px;">
                                            <small><a href="{{ route('freight.settings.container-stages') }}" target="_blank"><i class="fa fa-info-circle"></i> What each container stage means / add a custom stage</a></small>
                                        </div>
                                        <div class="col-md-4">
                                            <label>&nbsp;</label>
                                            <div class="checkbox" style="margin-top: 8px;">
                                                <label title="Untick for port/terminal charges that don't vary by shipping line"><input type="checkbox" wire:model.defer="is_line_specific"> Varies by shipping line</label>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </form>

                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr><th>Name</th><th>Description</th><th>Revenue Account</th><th>Expense Account</th><th>Container Free Time</th><th title="Cost + charge lines using this type">Lines</th><th>Status</th><th></th></tr>
                                </thead>
                                <tbody>
                                    @forelse ($chargeTypes as $type)
                                        <tr wire:key="charge-type-{{ $type->id }}" class="{{ $type->is_active ? '' : 'text-muted' }}">
                                            <td>{{ $type->name }}</td>
                                            <td>{{ $type->description }}</td>
                                            <td>{{ $type->revenue_account->name ?? '—' }}</td>
                                            <td>{{ $type->expense_account->name ?? '—' }}</td>
                                            <td>
                                                @if ($type->isBuiltInFreeTime())
                                                    @php $clock = \App\Services\Freight\PortExposureService::builtInClock($type->exposure_code); @endphp
                                                    {{ $stageLabels[$clock['start']] ?? $clock['start'] }} → {{ $stageLabels[$clock['stop']] ?? $clock['stop'] }}
                                                    <span class="label label-default" title="Start/stop stages are fixed by the system">Built-in</span>
                                                @elseif ($type->hasFreeTime())
                                                    {{ $stageLabels[$type->start_milestone] ?? $type->start_milestone }} → {{ $stageLabels[$type->stop_milestone] ?? $type->stop_milestone }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>{{ $type->freight_costs_count + $type->freight_charges_count }}</td>
                                            <td><span class="label label-{{ $type->is_active ? 'success' : 'default' }}">{{ $type->is_active ? 'Active' : 'Inactive' }}</span></td>
                                            <td style="white-space:nowrap">
                                                <a href="#" wire:click.prevent="edit({{ $type->id }})" class="btn btn-xs btn-default" title="Edit"><i class="fa fa-edit"></i></a>
                                                @unless ($type->isBuiltInFreeTime())
                                                    <a href="#" wire:click.prevent="toggleActive({{ $type->id }})" class="btn btn-xs btn-warning" title="{{ $type->is_active ? 'Deactivate (stop offering on new lines / rate cards)' : 'Activate' }}"><i class="fa fa-{{ $type->is_active ? 'eye-slash' : 'eye' }}"></i></a>
                                                    <a href="#" wire:click.prevent="delete({{ $type->id }})" wire:confirm="Remove charge type {{ $type->name }}?" class="btn btn-xs btn-danger" title="Delete"><i class="fa fa-trash"></i></a>
                                                @endunless
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="8" class="text-center">No charge types configured yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                            @include('livewire.freight.partials.pagination', ['paginator' => $chargeTypes])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
