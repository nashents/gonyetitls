<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">

            {{-- KPI Summary (HR/Finance only) --}}
            @if($isHR || $isFinance)
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="info-box bg-info">
                        <span class="info-box-icon"><i class="fa fa-money"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Advances Granted</span>
                            <span class="info-box-number">{{ number_format($totalGranted, 2) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-warning">
                        <span class="info-box-icon"><i class="fa fa-hourglass-half"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Outstanding Balance</span>
                            <span class="info-box-number">{{ number_format($totalOutstanding, 2) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-success">
                        <span class="info-box-icon"><i class="fa fa-check-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Recovered</span>
                            <span class="info-box-number">{{ number_format(max(0, $totalGranted - $totalOutstanding), 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div>@include('includes.messages')</div>
                            <div class="panel-title">
                                <button wire:click="openCreateModal" class="btn btn-default">
                                    <i class="fa fa-plus-square-o"></i> Request Advance
                                </button>
                            </div>
                        </div>

                        <div class="panel-body p-20">
                            {{-- Filters --}}
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <input wire:model.debounce.300ms="search" type="text" class="form-control" placeholder="Search employee...">
                                </div>
                                <div class="col-md-3">
                                    <select wire:model="filterAuth" class="form-control">
                                        <option value="">All Authorizations</option>
                                        <option value="pending">Pending</option>
                                        <option value="approved">Approved</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select wire:model="filterStatus" class="form-control">
                                        <option value="">All Statuses</option>
                                        <option value="Unpaid">Unpaid</option>
                                        <option value="Partially Paid">Partially Paid</option>
                                        <option value="Paid">Paid</option>
                                    </select>
                                </div>
                            </div>

                            <div style="overflow-x:auto;">
                                <table class="table table-striped table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th>Advance #</th>
                                            <th>Employee</th>
                                            <th>Date</th>
                                            <th>Currency</th>
                                            <th>Amount</th>
                                            <th>Balance</th>
                                            <th>Monthly Recovery</th>
                                            <th>Reason</th>
                                            <th>Auth</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($advances as $adv)
                                        @php
                                            $authColor = ['pending'=>'warning','approved'=>'success','rejected'=>'danger'][$adv->authorization] ?? 'secondary';
                                            $statusColor = ['Unpaid'=>'danger','Partially Paid'=>'warning','Paid'=>'success'][$adv->status] ?? 'secondary';
                                            $pct = $adv->amount > 0 ? round((($adv->amount - $adv->balance) / $adv->amount) * 100) : 0;
                                        @endphp
                                        <tr>
                                            <td><code>{{ $adv->advance_number }}</code></td>
                                            <td>{{ $adv->employee?->name }} {{ $adv->employee?->surname }}</td>
                                            <td>{{ \Carbon\Carbon::parse($adv->advance_date)->format('d M Y') }}</td>
                                            <td>{{ $adv->currency?->code }}</td>
                                            <td class="text-right">{{ number_format($adv->amount, 2) }}</td>
                                            <td class="text-right">
                                                <div>{{ number_format($adv->balance, 2) }}</div>
                                                <div class="progress" style="height:4px; margin-top:4px;">
                                                    <div class="progress-bar bg-success" style="width:{{ $pct }}%"></div>
                                                </div>
                                                <small class="text-muted">{{ $pct }}% recovered</small>
                                            </td>
                                            <td class="text-right">{{ number_format($adv->monthly_recovery_amount, 2) }}</td>
                                            <td>{{ Str::limit($adv->reason, 30) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $authColor }} text-white">{{ ucfirst($adv->authorization) }}</span>
                                                @if($adv->authorizedBy)
                                                <br><small class="text-muted">{{ $adv->authorizedBy?->name }}</small>
                                                @endif
                                            </td>
                                            <td><span class="badge bg-{{ $statusColor }} text-white">{{ $adv->status }}</span></td>
                                            <td>
                                                @if(($isHR || $isFinance) && $adv->authorization === 'pending')
                                                <button wire:click="openAuthModal({{ $adv->id }}, 'approve')" class="btn btn-xs btn-success" title="Approve">
                                                    <i class="fa fa-check"></i>
                                                </button>
                                                <button wire:click="openAuthModal({{ $adv->id }}, 'reject')" class="btn btn-xs btn-danger" title="Reject">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="11" class="text-center py-4">
                                                <img src="{{ asset('images/nodata.png') }}" style="max-width:180px;" alt="">
                                                <p class="text-muted mt-2">No salary advances found.</p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            {{ $advances->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Create Modal --}}
    @if($showCreateModal)
    <div class="modal fade show" style="display:block; background:rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fa fa-money"></i> Request Salary Advance</h4>
                    <button class="close" wire:click="$set('showCreateModal', false)">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Employee <span class="text-danger">*</span></label>
                                <select wire:model.defer="selectedEmployee" class="form-control @error('selectedEmployee') is-invalid @enderror">
                                    <option value="">Select employee</option>
                                    @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }} {{ $emp->surname }}</option>
                                    @endforeach
                                </select>
                                @error('selectedEmployee') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Currency <span class="text-danger">*</span></label>
                                <select wire:model.defer="selectedCurrency" class="form-control">
                                    @foreach($currencies as $cur)
                                    <option value="{{ $cur->id }}">{{ $cur->code }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Advance Date <span class="text-danger">*</span></label>
                                <input wire:model.defer="advance_date" type="date" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Amount <span class="text-danger">*</span></label>
                                <input wire:model.defer="amount" type="number" step="0.01" min="0" class="form-control @error('amount') is-invalid @enderror" placeholder="0.00">
                                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Monthly Recovery <small class="text-muted">(0 = full balance each run)</small></label>
                                <input wire:model.defer="monthly_recovery_amount" type="number" step="0.01" min="0" class="form-control" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Repayment Start Date</label>
                                <input wire:model.defer="repayment_start_date" type="date" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Reason <span class="text-danger">*</span></label>
                        <input wire:model.defer="reason" type="text" class="form-control @error('reason') is-invalid @enderror" placeholder="Brief reason for advance">
                        @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label>Additional Notes</label>
                        <textarea wire:model.defer="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" wire:click="$set('showCreateModal', false)">Cancel</button>
                    <button class="btn btn-primary" wire:click="store" wire:loading.attr="disabled">
                        <span wire:loading wire:target="store"><i class="fa fa-spinner fa-spin"></i></span>
                        Submit Request
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Approve/Reject Modal --}}
    @if($showAuthModal)
    <div class="modal fade show" style="display:block; background:rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-{{ $authAction === 'approve' ? 'success' : 'danger' }}">
                    <h4 class="modal-title text-white">
                        <i class="fa fa-{{ $authAction === 'approve' ? 'check' : 'times' }}"></i>
                        {{ ucfirst($authAction) }} Advance
                    </h4>
                    <button class="close text-white" wire:click="$set('showAuthModal', false)">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Comments <small class="text-muted">(optional)</small></label>
                        <textarea wire:model.defer="authComments" class="form-control" rows="3" placeholder="Add any notes for the employee..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" wire:click="$set('showAuthModal', false)">Cancel</button>
                    <button class="btn btn-{{ $authAction === 'approve' ? 'success' : 'danger' }}" wire:click="authorise" wire:loading.attr="disabled">
                        <span wire:loading wire:target="authorise"><i class="fa fa-spinner fa-spin"></i></span>
                        Confirm {{ ucfirst($authAction) }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
