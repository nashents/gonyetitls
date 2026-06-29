<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div>@include('includes.messages')</div>
                            <div class="panel-title d-flex align-items-center gap-2">
                                @php
                                    $user      = Auth::user();
                                    $roleNames = $user->roles->pluck('name');
                                    $deptNames = $user->employee?->departments->pluck('name') ?? collect();
                                    $isHR      = $deptNames->contains('Human Resources') || $roleNames->contains('Super Admin') || $roleNames->contains('Admin');
                                @endphp
                                @if($isHR)
                                <button wire:click="openCreateModal" class="btn btn-default">
                                    <i class="fa fa-plus-square-o"></i> New Payroll Run
                                </button>
                                @endif
                            </div>
                        </div>

                        <div class="panel-body p-20">
                            {{-- Filters --}}
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <input wire:model.debounce.300ms="search" type="text" class="form-control" placeholder="Search runs...">
                                </div>
                                <div class="col-md-3">
                                    <select wire:model="filterStatus" class="form-control">
                                        <option value="">All Statuses</option>
                                        @foreach(['draft','calculating','validated','approved','locked','exported','posted','archived','reversed'] as $s)
                                        <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div style="overflow-x:auto;">
                                <table class="table table-striped table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th>Run Name</th>
                                            <th>Period</th>
                                            <th>Frequency</th>
                                            <th>Currency</th>
                                            <th>Employees</th>
                                            <th>Total Gross</th>
                                            <th>Total Net</th>
                                            <th>Status</th>
                                            <th>Created By</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($runs as $run)
                                        @php
                                            $statusColors = [
                                                'draft'       => 'secondary',
                                                'calculating' => 'info',
                                                'validated'   => 'primary',
                                                'approved'    => 'success',
                                                'locked'      => 'dark',
                                                'exported'    => 'dark',
                                                'posted'      => 'success',
                                                'archived'    => 'secondary',
                                                'reversed'    => 'danger',
                                            ];
                                            $color = $statusColors[$run->status] ?? 'secondary';
                                        @endphp
                                        <tr>
                                            <td><strong>{{ $run->name }}</strong></td>
                                            <td>{{ \Carbon\Carbon::parse($run->period_start)->format('d M') }} – {{ \Carbon\Carbon::parse($run->period_end)->format('d M Y') }}</td>
                                            <td>{{ $run->frequency?->name ?? '—' }}</td>
                                            <td>{{ $run->currency?->code ?? '—' }}</td>
                                            <td class="text-center">{{ number_format($run->employee_count ?? 0) }}</td>
                                            <td class="text-right">{{ number_format($run->total_gross ?? 0, 2) }}</td>
                                            <td class="text-right">{{ number_format($run->total_net ?? 0, 2) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $color }} text-white" style="font-size:0.78rem; padding:4px 8px;">
                                                    {{ strtoupper($run->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $run->createdBy?->name }} {{ $run->createdBy?->surname }}</td>
                                            <td class="w-10 line-height-35 table-dropdown">
                                                <div class="dropdown">
                                                    <button class="btn btn-default dropdown-toggle btn-sm" type="button" data-toggle="dropdown">
                                                        <i class="fa fa-bars"></i> <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-right">
                                                        <li>
                                                            <a href="{{ route('payroll-runs.show', $run->id) }}">
                                                                <i class="fa fa-eye color-default"></i> View Detail
                                                            </a>
                                                        </li>

                                                        @if(in_array($run->status, ['draft','validated']) && ($deptNames->contains('Finance') || $deptNames->contains('Management') || $roleNames->contains('Super Admin') || $roleNames->contains('Admin')) && $run->created_by !== Auth::id())
                                                        <li>
                                                            <a href="#" wire:click.prevent="confirmLifecycle({{ $run->id }}, 'approve')">
                                                                <i class="fa fa-check color-success"></i> Approve
                                                            </a>
                                                        </li>
                                                        @endif

                                                        @if($run->status === 'approved' && $isHR)
                                                        <li>
                                                            <a href="#" wire:click.prevent="confirmLifecycle({{ $run->id }}, 'lock')">
                                                                <i class="fa fa-lock color-warning"></i> Lock
                                                            </a>
                                                        </li>
                                                        @endif

                                                        @if(in_array($run->status, ['locked','exported']) && $isHR)
                                                        <li>
                                                            <a href="#" wire:click.prevent="confirmLifecycle({{ $run->id }}, 'post')">
                                                                <i class="fa fa-book color-info"></i> Post to GL
                                                            </a>
                                                        </li>
                                                        @endif

                                                        @if($run->canBeReversed() && ($deptNames->contains('Finance') || $roleNames->contains('Super Admin') || $roleNames->contains('Admin')))
                                                        <li class="divider"></li>
                                                        <li>
                                                            <a href="#" wire:click.prevent="confirmLifecycle({{ $run->id }}, 'reverse')" class="text-danger">
                                                                <i class="fa fa-undo color-danger"></i> Reverse
                                                            </a>
                                                        </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="10" class="text-center py-4">
                                                <img src="{{ asset('images/nodata.png') }}" style="max-width:200px;" alt="No data">
                                                <p class="text-muted mt-2">No payroll runs found.</p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            {{ $runs->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Create Modal --}}
    @if($showCreateModal)
    <div class="modal fade show" id="createRunModal" style="display:block; background:rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fa fa-plus-square-o"></i> New Payroll Run</h4>
                    <button type="button" class="close" wire:click="$set('showCreateModal', false)">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Run Name <span class="text-danger">*</span></label>
                                <input wire:model.defer="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. June 2026 Monthly Payroll">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Frequency <span class="text-danger">*</span></label>
                                <select wire:model.defer="selectedFrequency" class="form-control @error('selectedFrequency') is-invalid @enderror">
                                    <option value="">Select frequency</option>
                                    @foreach($frequencies as $freq)
                                    <option value="{{ $freq->id }}">{{ $freq->name }}</option>
                                    @endforeach
                                </select>
                                @error('selectedFrequency') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Period Start <span class="text-danger">*</span></label>
                                <input wire:model.defer="period_start" type="date" class="form-control @error('period_start') is-invalid @enderror">
                                @error('period_start') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Period End <span class="text-danger">*</span></label>
                                <input wire:model.defer="period_end" type="date" class="form-control @error('period_end') is-invalid @enderror">
                                @error('period_end') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Payroll Date <span class="text-danger">*</span></label>
                                <input wire:model.defer="payroll_date" type="date" class="form-control @error('payroll_date') is-invalid @enderror">
                                @error('payroll_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Payment Date</label>
                                <input wire:model.defer="payment_date" type="date" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Currency <span class="text-danger">*</span></label>
                                <select wire:model.defer="selectedCurrency" class="form-control">
                                    @foreach($currencies as $cur)
                                    <option value="{{ $cur->id }}">{{ $cur->code }} – {{ $cur->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Proration Method</label>
                                <select wire:model.defer="proration_method" class="form-control">
                                    <option value="calendar_days">Calendar Days</option>
                                    <option value="working_days">Working Days</option>
                                    <option value="fixed_divisor">Fixed Divisor</option>
                                    <option value="none">None (full month)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Notes</label>
                                <textarea wire:model.defer="notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" wire:click="$set('showCreateModal', false)">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="store" wire:loading.attr="disabled">
                        <span wire:loading wire:target="store"><i class="fa fa-spinner fa-spin"></i></span>
                        Create Run
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Lifecycle Confirm Modal --}}
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Confirm Action</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to <strong>{{ $confirmAction }}</strong> this payroll run?</p>
                    @if($confirmAction === 'reverse')
                    <div class="form-group mt-3">
                        <label>Reason for reversal <span class="text-danger">*</span></label>
                        <textarea wire:model.defer="confirmReason" class="form-control" rows="3" placeholder="Required for audit trail..."></textarea>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-{{ $confirmAction === 'reverse' ? 'danger' : 'primary' }}"
                            wire:click="executeLifecycle" wire:loading.attr="disabled">
                        <span wire:loading wire:target="executeLifecycle"><i class="fa fa-spinner fa-spin"></i></span>
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.addEventListener('show-confirm-modal', () => $('#confirmModal').modal('show'));
    window.addEventListener('hide-confirm-modal', () => $('#confirmModal').modal('hide'));
</script>
@endpush
