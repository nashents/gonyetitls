<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">

            {{-- Header card --}}
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h4>
                                    <i class="fa fa-file-text-o"></i> {{ $run->name }}
                                    @php
                                        $statusColors = [
                                            'draft'=>'secondary','calculating'=>'info','validated'=>'primary',
                                            'approved'=>'success','locked'=>'dark','exported'=>'dark',
                                            'posted'=>'success','archived'=>'secondary','reversed'=>'danger',
                                        ];
                                        $color = $statusColors[$run->status] ?? 'secondary';
                                        $user      = Auth::user();
                                        $roleNames = $user->roles->pluck('name');
                                        $deptNames = $user->employee?->departments->pluck('name') ?? collect();
                                        $isHR      = $deptNames->contains('Human Resources') || $roleNames->contains('Super Admin') || $roleNames->contains('Admin');
                                        $isFinance = $deptNames->contains('Finance') || $deptNames->contains('Management') || $roleNames->contains('Super Admin') || $roleNames->contains('Admin');
                                        $canApprove = $isFinance && $run->created_by !== Auth::id() && in_array($run->status, ['draft','validated']);
                                    @endphp
                                    <span class="badge bg-{{ $color }} text-white ml-2">{{ strtoupper($run->status) }}</span>
                                </h4>
                            </div>
                            <div class="d-flex gap-2 flex-wrap mt-2">
                                @if($canApprove)
                                    <button wire:click="confirmLifecycle('approve')" class="btn btn-success btn-sm">
                                        <i class="fa fa-check"></i> Approve
                                    </button>
                                @endif
                                @if($run->status === 'approved' && $isHR)
                                    <button wire:click="confirmLifecycle('lock')" class="btn btn-warning btn-sm">
                                        <i class="fa fa-lock"></i> Lock
                                    </button>
                                @endif
                                @if(in_array($run->status, ['locked','exported']) && $isHR)
                                    <button wire:click="confirmLifecycle('post')" class="btn btn-info btn-sm">
                                        <i class="fa fa-book"></i> Post to GL
                                    </button>
                                @endif
                                @if($run->canBeReversed() && ($deptNames->contains('Finance') || $roleNames->contains('Super Admin') || $roleNames->contains('Admin')))
                                    <button wire:click="confirmLifecycle('reverse')" class="btn btn-danger btn-sm">
                                        <i class="fa fa-undo"></i> Reverse
                                    </button>
                                @endif
                                <a href="{{ route('payroll-runs.index') }}" class="btn btn-default btn-sm">
                                    <i class="fa fa-arrow-left"></i> Back
                                </a>
                            </div>
                        </div>

                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="info-box bg-default">
                                        <div class="info-box-content">
                                            <span class="info-box-text">Period</span>
                                            <span class="info-box-number">
                                                {{ \Carbon\Carbon::parse($run->period_start)->format('d M') }}
                                                – {{ \Carbon\Carbon::parse($run->period_end)->format('d M Y') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box bg-info">
                                        <div class="info-box-content">
                                            <span class="info-box-text">Employees</span>
                                            <span class="info-box-number">{{ number_format($run->employee_count ?? 0) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box bg-success">
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Gross ({{ $run->currency?->code }})</span>
                                            <span class="info-box-number">{{ number_format($run->total_gross ?? 0, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box bg-primary">
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Net ({{ $run->currency?->code }})</span>
                                            <span class="info-box-number">{{ number_format($run->total_net ?? 0, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Meta detail --}}
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <table class="table table-sm table-borderless">
                                        <tr><th width="40%">Frequency</th><td>{{ $run->frequency?->name }}</td></tr>
                                        <tr><th>Payroll Date</th><td>{{ $run->payroll_date?->format('d M Y') }}</td></tr>
                                        <tr><th>Payment Date</th><td>{{ $run->payment_date?->format('d M Y') ?? '—' }}</td></tr>
                                        <tr><th>Proration</th><td>{{ ucwords(str_replace('_',' ',$run->proration_method ?? '')) }}</td></tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm table-borderless">
                                        <tr><th width="40%">Created By</th><td>{{ $run->createdBy?->name }} {{ $run->createdBy?->surname }}</td></tr>
                                        @if($run->approvedBy)
                                        <tr><th>Approved By</th><td>{{ $run->approvedBy?->name }} {{ $run->approvedBy?->surname }} <small class="text-muted">{{ $run->approved_at?->format('d M Y H:i') }}</small></td></tr>
                                        @endif
                                        @if($run->lockedBy)
                                        <tr><th>Locked By</th><td>{{ $run->lockedBy?->name }} {{ $run->lockedBy?->surname }} <small class="text-muted">{{ $run->locked_at?->format('d M Y H:i') }}</small></td></tr>
                                        @endif
                                        @if($run->postedBy)
                                        <tr><th>Posted By</th><td>{{ $run->postedBy?->name }} {{ $run->postedBy?->surname }} <small class="text-muted">{{ $run->posted_at?->format('d M Y H:i') }}</small></td></tr>
                                        @endif
                                        @if($run->notes)
                                        <tr><th>Notes</th><td>{{ $run->notes }}</td></tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Employee breakdown --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title"><i class="fa fa-users"></i> Employee Payslips</div>
                        </div>
                        <div class="panel-body p-20">
                            <div class="col-md-4 mb-3" style="float:right;">
                                <input wire:model.debounce.300ms="employeeSearch" type="text" class="form-control" placeholder="Search employee...">
                            </div>
                            <div style="overflow-x:auto;">
                                <table class="table table-striped table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Employee</th>
                                            <th>Basic</th>
                                            <th>Allowances</th>
                                            <th>Gross</th>
                                            <th>Deductions</th>
                                            <th>Net Pay</th>
                                            <th>Payslip</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($salaryLines as $i => $line)
                                        <tr>
                                            <td>{{ $salaryLines->firstItem() + $i }}</td>
                                            <td>{{ $line->employee?->name }} {{ $line->employee?->surname }}</td>
                                            <td class="text-right">{{ number_format($line->basic, 2) }}</td>
                                            <td class="text-right">{{ number_format($line->total_allowances, 2) }}</td>
                                            <td class="text-right"><strong>{{ number_format($line->gross, 2) }}</strong></td>
                                            <td class="text-right text-danger">{{ number_format($line->total_deductions, 2) }}</td>
                                            <td class="text-right text-success"><strong>{{ number_format($line->net, 2) }}</strong></td>
                                            <td>
                                                <a href="{{ route('payslips.pdf', $line->id) }}" class="btn btn-xs btn-default" target="_blank" title="Download PDF">
                                                    <i class="fa fa-file-pdf-o"></i>
                                                </a>
                                                <a href="{{ route('payslips.preview', $line->id) }}" class="btn btn-xs btn-info" target="_blank" title="Preview">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-3">No payslips linked to this run yet.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                    @if($salaryLines->count() > 0)
                                    <tfoot>
                                        <tr class="table-active font-weight-bold">
                                            <td colspan="2">Totals</td>
                                            <td class="text-right">{{ number_format($salaryLines->sum('basic'), 2) }}</td>
                                            <td class="text-right">{{ number_format($salaryLines->sum('total_allowances'), 2) }}</td>
                                            <td class="text-right">{{ number_format($salaryLines->sum('gross'), 2) }}</td>
                                            <td class="text-right text-danger">{{ number_format($salaryLines->sum('total_deductions'), 2) }}</td>
                                            <td class="text-right text-success">{{ number_format($salaryLines->sum('net'), 2) }}</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                    @endif
                                </table>
                            </div>
                            {{ $salaryLines->links() }}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- Lifecycle Confirm Modal --}}
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Confirm: {{ ucfirst($confirmAction) }}</h4>
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
                    @if($confirmAction === 'lock')
                    <div class="alert alert-warning mt-2">
                        <i class="fa fa-warning"></i> Locking prevents any further edits to this payroll run.
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-{{ $confirmAction === 'reverse' ? 'danger' : 'primary' }}"
                            wire:click="executeLifecycle" wire:loading.attr="disabled">
                        <span wire:loading wire:target="executeLifecycle"><i class="fa fa-spinner fa-spin"></i></span>
                        Confirm {{ ucfirst($confirmAction) }}
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
