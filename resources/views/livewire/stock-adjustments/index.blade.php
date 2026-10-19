<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div>
                                @include('includes.messages')
                            </div>
                            <div class="panel-title">
                                <a href="#" wire:click.prevent="showCreate()" class="btn btn-default pull-right"><i class="fa fa-plus-square-o"></i> New Stock Count</a>
                                <h5>Stock Adjustments</h5>
                                <small class="text-muted">
                                    Count what is physically in the store and correct the system to match.
                                    A new count lists every inventory item in stock with its system quantity. Enter what you find, then submit it for approval.
                                    On approval, <strong>missing</strong> stock comes off the balance (DR Stock Write-off, CR Spares Inventory) and <strong>extra</strong> stock goes on (DR Spares Inventory, CR Stock Write-off).
                                    Items you leave blank are not changed.
                                    If you know where missing items went, capture the dispatch instead of writing them off.
                                </small>
                            </div>
                        </div>
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                            <div class="row" style="margin-bottom: 10px">
                                <div class="col-md-4">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search count#, store, description...">
                                </div>
                                <div class="col-md-3">
                                    <select wire:model="status_filter" class="form-control">
                                        <option value="">Any status</option>
                                        <option value="draft">Draft (counting)</option>
                                        <option value="pending">Awaiting approval</option>
                                        <option value="approved">Approved</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>
                            </div>

                            <table class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>Count#</th>
                                        <th>Date</th>
                                        <th>Store</th>
                                        <th>Description</th>
                                        <th>Progress</th>
                                        <th>Missing / Extra</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($adjustments as $adjustment)
                                        @php
                                            $badge = ['draft' => 'secondary', 'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'][$adjustment->status] ?? 'secondary';
                                            $label = ['draft' => 'Counting', 'pending' => 'Awaiting approval', 'approved' => 'Approved', 'rejected' => 'Rejected'][$adjustment->status] ?? ucfirst($adjustment->status);
                                        @endphp
                                        <tr wire:key="sa-{{ $adjustment->id }}">
                                            <td>
                                                <a href="{{ route('stock_adjustments.show', $adjustment->id) }}" style="color: blue">{{ $adjustment->adjustment_number }}</a>
                                                <br><small class="text-muted">By {{ optional($adjustment->user)->name }} {{ optional($adjustment->user)->surname }}</small>
                                            </td>
                                            <td>{{ $adjustment->date ? $adjustment->date->format('Y-m-d') : '' }}</td>
                                            <td>{{ $adjustment->store ? $adjustment->store->name : 'All stores' }}</td>
                                            <td>{{ $adjustment->description }}</td>
                                            <td>
                                                {{ $adjustment->counted_count }} / {{ $adjustment->items_count }} counted
                                                <br><small class="text-muted">{{ $adjustment->variance_count }} with a difference</small>
                                            </td>
                                            <td>
                                                @if ($adjustment->status === 'approved')
                                                    {{ number_format((float) $adjustment->shortage_value, 2) }} / {{ number_format((float) $adjustment->surplus_value, 2) }}
                                                    @if ($adjustment->journal_entry)
                                                        <br><small class="text-muted">Journal {{ $adjustment->journal_entry->journal_number }}</small>
                                                    @endif
                                                @else
                                                    <small class="text-muted">On approval</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $badge }}">{{ $label }}</span>
                                                @if ($adjustment->authorized_by)
                                                    <br><small class="text-muted">By {{ $adjustment->authorized_by->name }} {{ $adjustment->authorized_by->surname }}</small>
                                                @endif
                                                @if ($adjustment->authorization_comments)
                                                    <br><small class="text-muted">{{ $adjustment->authorization_comments }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('stock_adjustments.show', $adjustment->id) }}" class="btn btn-default btn-sm">
                                                    @if ($adjustment->status === 'draft')
                                                        <i class="fa fa-edit"></i> Count
                                                    @else
                                                        <i class="fa fa-eye"></i> View
                                                    @endif
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="8" class="text-center">No stock counts yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                            {{ $adjustments->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="saCreateModal" tabindex="-1" role="dialog" aria-labelledby="saCreateLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="saCreateLabel"><i class="fa fa-clipboard"></i> New Stock Count
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    </h4>
                </div>
                <form wire:submit.prevent="store()">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Count Date<span class="required" style="color: red">*</span></label>
                            <input type="date" wire:model.defer="date" class="form-control" required>
                            <small class="text-muted">The ledger entry is dated this day.</small>
                            @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Store</label>
                            <select wire:model.defer="store_id" class="form-control">
                                <option value="">All stores</option>
                                @foreach ($stores as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                            @error('store_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <input type="text" wire:model.defer="description" class="form-control" placeholder="e.g. October 2026 full stock take">
                            @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <p class="text-muted"><small>
                            The system quantities are captured when you create the count. Hold back receiving and issuing in this store until the count is finished.
                        </small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i> Start Count</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
