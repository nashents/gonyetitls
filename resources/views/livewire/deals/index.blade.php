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
                                <a href="#" data-toggle="modal" data-target="#dealModal" class="btn btn-default">
                                    <i class="fa fa-plus-square-o"></i> Deal
                                </a>
                            </div>
                        </div>

                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-5" style="float: right; padding-right:2px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search deals...">
                                </div>
                            </div>

                            @php
                                $dealStatusMap = [
                                    'Completed' => ['row' => '#E8F5E9', 'border' => '#2E7D32'],
                                    'Active'    => ['row' => '#E3F2FD', 'border' => '#1565C0'],
                                    'Cancelled' => ['row' => '#FFEBEE', 'border' => '#C62828'],
                                ];
                            @endphp

                            <table class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>Deal#</th>
                                        <th>Reference</th>
                                        <th>Customer</th>
                                        <th>Cargo</th>
                                        <th>Allocation</th>
                                        <th>Progress</th>
                                        <th>Timeline</th>
                                        <th>Ccy</th>
                                        <th>Freight</th>
                                        <th>Status</th>
                                        <th>Closure</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                @if (isset($deals))
                                    <tbody>
                                        @forelse ($deals as $deal)
                                            @php
                                                $dealState = $deal->cancelled ? 'Cancelled' : ($deal->completed ? 'Completed' : 'Active');
                                                $ds = $dealStatusMap[$dealState];
                                            @endphp
                                            <tr style="background-color: {{ $ds['row'] }}; border-left: 6px solid {{ $ds['border'] }};">
                                                <td>{{ $deal->deal_number }}</td>
                                                <td>{{ $deal->reference ?? 'N/A' }}</td>
                                                <td>{{ $deal->customer ? $deal->customer->name : 'N/A' }}</td>
                                                <td>{{ $deal->cargo ? $deal->cargo->name : 'N/A' }}</td>
                                                <td>
                                                    @if ($deal->weight)
                                                        <strong>Weight:</strong> {{ number_format($deal->weight, 2) }}t
                                                        <br>
                                                    @endif

                                                    @if ($deal->litreage)
                                                        <strong>Litreage:</strong> {{ number_format($deal->litreage, 2) }}l<br>
                                                    @endif

                                                    @if ($deal->quantity)
                                                        <strong>Quantity:</strong> {{ number_format($deal->quantity, 2) }} {{ $deal->units_of_measure ? $deal->units_of_measure->name : '' }}
                                                    @endif

                                                    @if (!$deal->weight && !$deal->litreage && !$deal->quantity)
                                                        N/A
                                                    @endif

                                                    {{-- Actual amount pushed/delivered at completion (kept alongside the target). --}}
                                                    @if ($deal->completed && ($deal->completed_weight || $deal->completed_litreage || $deal->completed_quantity))
                                                        <br>
                                                        <span class="badge bg-success" style="margin-top:3px">Delivered</span>
                                                        <small class="text-success">
                                                            @if ($deal->completed_weight) {{ number_format($deal->completed_weight, 2) }}t @endif
                                                            @if ($deal->completed_litreage) {{ number_format($deal->completed_litreage, 2) }}l @endif
                                                            @if ($deal->completed_quantity) {{ number_format($deal->completed_quantity, 2) }} {{ $deal->units_of_measure ? $deal->units_of_measure->name : '' }} @endif
                                                        </small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($deal->weight)
                                                        @php $wPct = min(100, round((($deal->trips_sum_weight ?? 0) / $deal->weight) * 100)); @endphp
                                                        <strong>Weight:</strong> {{ number_format($deal->trips_sum_weight ?? 0, 2) }} / {{ number_format($deal->weight, 2) }}t
                                                        <div class="progress" style="height:4px; margin-top:2px; margin-bottom:2px;">
                                                            <div class="progress-bar {{ $wPct >= 100 ? 'bg-success' : 'bg-info' }}" style="width:{{ $wPct }}%"></div>
                                                        </div>
                                                        <small class="text-muted">{{ $wPct }}% moved</small><br>
                                                    @endif

                                                    @if ($deal->litreage)
                                                        @php $lPct = min(100, round((($deal->trips_sum_litreage ?? 0) / $deal->litreage) * 100)); @endphp
                                                        <strong>Litreage:</strong> {{ number_format($deal->trips_sum_litreage ?? 0, 2) }} / {{ number_format($deal->litreage, 2) }}l
                                                        <div class="progress" style="height:4px; margin-top:2px; margin-bottom:2px;">
                                                            <div class="progress-bar {{ $lPct >= 100 ? 'bg-success' : 'bg-info' }}" style="width:{{ $lPct }}%"></div>
                                                        </div>
                                                        <small class="text-muted">{{ $lPct }}% moved</small><br>
                                                    @endif

                                                    @if ($deal->quantity)
                                                        @php $qPct = min(100, round((($deal->trips_sum_quantity ?? 0) / $deal->quantity) * 100)); @endphp
                                                        <strong>Quantity:</strong> {{ number_format($deal->trips_sum_quantity ?? 0, 2) }} / {{ number_format($deal->quantity, 2) }} {{ $deal->units_of_measure ? $deal->units_of_measure->name : '' }}
                                                        <div class="progress" style="height:4px; margin-top:2px; margin-bottom:2px;">
                                                            <div class="progress-bar {{ $qPct >= 100 ? 'bg-success' : 'bg-info' }}" style="width:{{ $qPct }}%"></div>
                                                        </div>
                                                        <small class="text-muted">{{ $qPct }}% moved</small>
                                                    @endif

                                                    @if (!$deal->weight && !$deal->litreage && !$deal->quantity)
                                                        N/A
                                                    @endif
                                                </td>
                                              <td>
                                                    <strong>Start:</strong>
                                                    {{ $deal->start_date ? \Carbon\Carbon::parse($deal->start_date)->format('d M Y H:i') : 'N/A' }}
                                                    <br>

                                                    <strong>End:</strong>
                                                    {{ $deal->end_date ? \Carbon\Carbon::parse($deal->end_date)->format('d M Y H:i') : 'N/A' }}

                                                    @if($deal->end_date)
                                                        <br>

                                                        @if(!$deal->is_closed && \Carbon\Carbon::parse($deal->end_date)->lt(now()))
                                                            <span class="badge bg-danger">Expired</span>
                                                        @elseif($deal->is_closed)
                                                            <span class="badge bg-secondary">Closed</span>
                                                        @else
                                                            <span class="badge bg-success">Valid</span>
                                                        @endif
                                                    @endif
                                                </td>
                                                <td>{{$deal->currency?->name}}</td>
                                                 <td>
                                                    <strong>Rate:</strong>
                                                        {{$deal->currency?->symbol}}{{ $deal->rate }}
                                                    <br>
                                                    <strong>Freight:</strong>
                                                        {{$deal->currency?->symbol}}{{ $deal->freight }}
                                                </td>
                                                <td>
                                                    @if ($deal->status == 1)
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-danger">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($dealState === 'Completed')
                                                        <span class="badge bg-success">Completed</span>
                                                        <br>
                                                        <small>
                                                            <strong>By:</strong> {{ $deal->completedBy?->name }} {{ $deal->completedBy?->surname }} <br>
                                                            <strong>On:</strong> {{ $deal->completed_at?->format('d M Y H:i') }}
                                                            @if ($deal->status_comment)
                                                                <br><strong>Comment:</strong> {{ $deal->status_comment }}
                                                            @endif
                                                        </small>
                                                    @elseif ($dealState === 'Cancelled')
                                                        <span class="badge bg-danger">Cancelled</span>
                                                        <br>
                                                        <small>
                                                            <strong>By:</strong> {{ $deal->cancelledBy?->name }} {{ $deal->cancelledBy?->surname }} <br>
                                                            <strong>On:</strong> {{ $deal->cancelled_at?->format('d M Y H:i') }} <br>
                                                            <strong>Comment:</strong> {{ $deal->status_comment }}
                                                        </small>
                                                    @else
                                                        <span class="badge bg-success">Open</span>
                                                    @endif
                                                </td>
                                                <td class="w-10 line-height-35 table-dropdown">
                                                    <div class="dropdown">
                                                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">
                                                            <i class="fa fa-bars"></i>
                                                            <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <a href="{{ route('deals.show', $deal->id) }}" target="_blank">
                                                                    <i class="fas fa-eye color-default"></i> View
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="#" wire:click.prevent="edit({{ $deal->id }})">
                                                                    <i class="fa fa-edit color-success"></i> Edit
                                                                </a>
                                                            </li>
                                                            @if ($dealState === 'Active')
                                                            <li>
                                                                <a href="#" wire:click.prevent="openStatusUpdate({{ $deal->id }}, 'Completed')">
                                                                    <i class="fa fa-check-circle color-success"></i> Mark as Completed
                                                                </a>
                                                            </li>
                                                            @endif
                                                            @if ($dealState === 'Active')
                                                            <li>
                                                                <a href="#" wire:click.prevent="openStatusUpdate({{ $deal->id }}, 'Cancelled')">
                                                                    <i class="fa fa-ban color-danger"></i> Cancel
                                                                </a>
                                                            </li>
                                                            @endif
                                                            {{-- No delete once the deal is completed or cancelled. --}}
                                                            @unless ($deal->completed || $deal->cancelled)
                                                            <li>
                                                                <a href="#" wire:click.prevent="delete({{ $deal->id }})">
                                                                    <i class="fa fa-trash color-danger"></i> Delete
                                                                </a>
                                                            </li>
                                                            @endunless
                                                        </ul>
                                                    </div>

                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="12">
                                                    <div style="text-align:center; color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                        No Deals Found ....
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                @else
                                    <tbody>
                                        <tr>
                                            <td colspan="8" class="text-center">No data available.</td>
                                        </tr>
                                    </tbody>
                                @endif
                            </table>

                            <nav class="text-center" style="float: right">
                                <ul class="pagination rounded-corners">
                                    @if (isset($deals) && $deals->count() > 0)
                                        {{ $deals->links() }}
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                    <center><strong>Are you sure you want to delete this Deal?</strong></center> 
                </div>
                <form wire:submit.prevent="destroy()">
                    <div class="modal-footer no-border">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                            <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fa fa-trash"></i>Delete</button>
                        </div>
                        <!-- /.btn-group -->
                    </div>
                </form>
            </div>
        </div>
    </div>
   
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="dealStatusModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content {{ $statusUpdateTarget === 'Cancelled' ? 'bg-danger' : '' }}">
                <div class="modal-header">
                    <h4 class="modal-title">
                        Mark Deal {{$deal?->deal_number}}{{$deal?->reference ? "/".$deal?->reference : ""}} as {{ $statusUpdateTarget }}
                    </h4>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form wire:submit.prevent="saveStatusUpdate">
                    <div class="modal-body">
                        @if ($statusUpdateTarget === 'Completed')
                            <p>Optionally correct the final quantities pushed for this deal before marking it Completed.</p>
                            <div class="row">
                                @if ($statusUpdateCargoType === 'Solid')
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Weight(t)</label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="statusUpdateWeight">
                                        </div>
                                    </div>
                                @elseif ($statusUpdateCargoType === 'Liquid')
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Litreage(l)</label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="statusUpdateLitreage">
                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Quantity</label>
                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="statusUpdateQuantity">
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="form-group">
                            <label>
                                Comments
                                @if ($statusUpdateTarget === 'Cancelled')
                                    <span class="required" style="color:red">*</span>
                                @else
                                    <small class="text-muted">(optional)</small>
                                @endif
                            </label>
                            <textarea class="form-control" rows="3" wire:model.debounce.300ms="statusUpdateComment" placeholder="{{ $statusUpdateTarget === 'Cancelled' ? 'Please provide a reason for cancelling (required)' : 'Add a comment (optional)' }}"></textarea>
                            @error('statusUpdateComment') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer no-border">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                            <button type="submit" class="btn {{ $statusUpdateTarget === 'Cancelled' ? 'bg-danger' : 'bg-success' }} btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                        </div>
                        <!-- /.btn-group -->
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- CREATE MODAL --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="dealModal" tabindex="-1" role="dialog" data-backdrop-color="blue">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        <i class="fas fa-plus"></i> Add Deal
                        <button type="button" class="close" data-dismiss="modal">
                            <span>×</span>
                        </button>
                    </h4>
                </div>

                <form wire:submit.prevent="store">
                    <div class="modal-body">
                        @include('livewire.deals.form')
                    </div>

                    <div class="modal-footer">
                        <div class="btn-group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal">
                                <i class="fa fa-times"></i> Close
                            </button>
                            <button type="submit" class="btn bg-success btn-wide btn-rounded">
                                <i class="fa fa-save"></i> Save
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- EDIT MODAL --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="dealEditModal" tabindex="-1" role="dialog" data-backdrop-color="blue">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        <i class="fas fa-edit"></i> Edit Deal
                        <button type="button" class="close" data-dismiss="modal">
                            <span>×</span>
                        </button>
                    </h4>
                </div>

                <form wire:submit.prevent="update">
                    <input type="hidden" wire:model="deal_id">

                    <div class="modal-body">
                        @include('livewire.deals.form')
                    </div>

                    <div class="modal-footer">
                        <div class="btn-group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal">
                                <i class="fa fa-times"></i> Close
                            </button>
                            <button type="submit" class="btn bg-success btn-wide btn-rounded">
                                <i class="fa fa-refresh"></i> Update
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

   

</div>