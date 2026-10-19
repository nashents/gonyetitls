<div>
    <section class="section">
        <x-loading/>
        @php
            $editable = $adjustment->isEditable();
            $statusBadge = ['draft' => 'secondary', 'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'][$adjustment->status] ?? 'secondary';
            $statusLabel = ['draft' => 'Counting', 'pending' => 'Awaiting approval', 'approved' => 'Approved', 'rejected' => 'Rejected'][$adjustment->status] ?? ucfirst($adjustment->status);
            $settled = in_array($adjustment->status, ['approved', 'rejected']);
        @endphp
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div>
                                @include('includes.messages')
                            </div>
                            <div class="panel-title">
                                <div class="pull-right">
                                    @if ($editable)
                                        <button type="button" wire:click="submit" class="btn bg-success btn-sm"><i class="fa fa-paper-plane"></i> Submit for Approval</button>
                                    @endif
                                    @if ($adjustment->status === 'pending')
                                        @if ($canApprove)
                                            <button type="button" data-toggle="modal" data-target="#saApproveModal" class="btn bg-success btn-sm"><i class="fa fa-gavel"></i> Approve / Reject</button>
                                        @endif
                                        <button type="button" wire:click="reopen" class="btn btn-default btn-sm"><i class="fa fa-undo"></i> Reopen for Editing</button>
                                    @endif
                                    @if (!$settled)
                                        <button type="button" wire:click="delete" onclick="confirm('Delete this stock count? Nothing has been adjusted yet.') || event.stopImmediatePropagation()" class="btn btn-default btn-sm text-danger"><i class="fa fa-trash"></i> Delete</button>
                                    @endif
                                    <a href="{{ route('stock_adjustments.index') }}" class="btn btn-default btn-sm"><i class="fa fa-list"></i> All Counts</a>
                                </div>
                                <h5>
                                    Stock Count {{ $adjustment->adjustment_number }}
                                    <span class="badge bg-{{ $statusBadge }}">{{ $statusLabel }}</span>
                                </h5>
                                <small class="text-muted">
                                    {{ $adjustment->date ? $adjustment->date->format('d M Y') : '' }}
                                    · {{ $adjustment->store ? $adjustment->store->name : 'All stores' }}
                                    · Started by {{ optional($adjustment->user)->name }} {{ optional($adjustment->user)->surname }}
                                    @if ($adjustment->description) · {{ $adjustment->description }} @endif
                                </small>
                                @if ($adjustment->authorized_by)
                                    <br><small class="text-muted">
                                        {{ $statusLabel }} by {{ $adjustment->authorized_by->name }} {{ $adjustment->authorized_by->surname }}
                                        on {{ optional($adjustment->authorization_date)->format('d M Y H:i') }}
                                        @if ($adjustment->authorization_comments) · {{ $adjustment->authorization_comments }} @endif
                                        @if ($adjustment->journal_entry) · Journal {{ $adjustment->journal_entry->journal_number }} @endif
                                    </small>
                                @endif
                            </div>
                        </div>
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                            @if ($errors->has('adjustment'))
                                <div class="alert alert-danger">
                                    @foreach ($errors->get('adjustment') as $message)
                                        <div>{{ $message }}</div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="row" style="margin-bottom: 10px">
                                <div class="col-md-3"><strong>Counted:</strong> {{ (int) $totals->counted }} of {{ (int) $totals->items }} items</div>
                                <div class="col-md-3"><strong>With a difference:</strong> {{ (int) $totals->variances }}</div>
                                <div class="col-md-3"><strong>Missing (write-off):</strong> <span class="text-danger">{{ $currency }}{{ number_format((float) $totals->shortage, 2) }}</span></div>
                                <div class="col-md-3"><strong>Extra (found):</strong> <span class="text-success">{{ $currency }}{{ number_format((float) $totals->surplus, 2) }}</span></div>
                            </div>

                            @if ($editable)
                                <div class="alert alert-info" style="margin-bottom: 10px">
                                    <small>
                                        Enter the quantity you find for each item. Each count saves as soon as you leave the box.
                                        <strong>Leave an item blank if you didn't count it</strong>, and it won't be changed.
                                        If items on a <strong>dispatch awaiting approval</strong> (Reserved) have already been handed out, count them as if they were still on the shelf. They come off the balance when that dispatch is approved.
                                        Items in the store that the system doesn't have at all: use Inventories &gt; Import instead.
                                    </small>
                                </div>
                            @endif

                            <div class="row" style="margin-bottom: 10px">
                                <div class="col-md-4">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search item# or product...">
                                </div>
                                <div class="col-md-3">
                                    <select wire:model="filter" class="form-control">
                                        <option value="all">All items</option>
                                        <option value="not_counted">Not counted yet</option>
                                        <option value="counted">Counted</option>
                                        <option value="variances">With a difference</option>
                                    </select>
                                </div>
                            </div>

                            <table class="table table-bordered table-sm" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>Item#</th>
                                        <th>Product</th>
                                        <th>Store</th>
                                        <th class="text-right">System Qty</th>
                                        <th class="text-right">Reserved</th>
                                        <th style="width: 140px">Counted</th>
                                        <th class="text-right">Difference</th>
                                        <th class="text-right">Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($items as $item)
                                        @php
                                            $variance = $item->variance_qty;
                                            $rowStyle = !$item->isCounted() ? '' : ($variance < 0 ? 'background-color:#FDECEA;' : ($variance > 0 ? 'background-color:#E8F5E9;' : ''));
                                            $uom = optional($item->inventory)->measurement ?: optional($item->product)->unit_of_measure;
                                            $reserved = $item->inventory ? $item->inventory->reserved_qty : 0;
                                        @endphp
                                        <tr wire:key="sa-item-{{ $item->id }}" style="{{ $rowStyle }}">
                                            <td>{{ optional($item->inventory)->inventory_number }}</td>
                                            <td>
                                                {{ optional($item->product)->name }}
                                                @if (optional($item->product)->brand) <small class="text-muted">{{ $item->product->brand->name }}</small> @endif
                                            </td>
                                            <td>{{ optional($item->store)->name }}</td>
                                            <td class="text-right">{{ (float) $item->system_qty }} <small class="text-muted">{{ $uom }}</small></td>
                                            <td class="text-right">
                                                @if ($reserved > 0 && !$settled)
                                                    <span class="badge bg-warning" title="On dispatches awaiting approval">{{ (float) $reserved }}</span>
                                                @else
                                                    <small class="text-muted">-</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($editable)
                                                    <input type="number" step="any" min="0" class="form-control input-sm" wire:model.lazy="counts.{{ $item->id }}" placeholder="Not counted">
                                                    @error('counts.'.$item->id) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                @else
                                                    {{ $item->isCounted() ? (float) $item->counted_qty : 'Not counted' }}
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                @if ($item->isCounted() && $variance != 0)
                                                    <strong class="{{ $variance < 0 ? 'text-danger' : 'text-success' }}">{{ $variance > 0 ? '+' : '' }}{{ $variance }}</strong>
                                                @elseif ($item->isCounted())
                                                    <small class="text-muted">Matches</small>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                @if ($item->isCounted() && $variance != 0)
                                                    {{ $currency }}{{ number_format($item->variance_value, 2) }}
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="8" class="text-center">No items match.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                            {{ $items->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="saApproveModal" tabindex="-1" role="dialog" aria-labelledby="saApproveLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="saApproveLabel"><i class="fa fa-gavel"></i> Approve Stock Count {{ $adjustment->adjustment_number }}
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    </h4>
                </div>
                <div class="modal-body">
                    <p>
                        Approving adjusts <strong>{{ (int) $totals->variances }}</strong> item(s):
                        <span class="text-danger">{{ $currency }}{{ number_format((float) $totals->shortage, 2) }} missing</span> is written off (DR Stock Write-off, CR Spares Inventory) and
                        <span class="text-success">{{ $currency }}{{ number_format((float) $totals->surplus, 2) }} extra</span> is brought back in (DR Spares Inventory, CR Stock Write-off).
                        This can't be undone.
                    </p>
                    @if ($errors->has('adjustment'))
                        <div class="alert alert-danger">
                            @foreach ($errors->get('adjustment') as $message)
                                <div>{{ $message }}</div>
                            @endforeach
                        </div>
                    @endif
                    <div class="form-group">
                        <label>Comments</label>
                        <textarea wire:model.defer="comments" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-gray btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
                    <button type="button" wire:click="reject" class="btn btn-danger btn-rounded"><i class="fa fa-ban"></i> Reject</button>
                    <button type="button" wire:click="approve" class="btn bg-success btn-rounded"><i class="fa fa-check"></i> Approve</button>
                </div>
            </div>
        </div>
    </div>
</div>
