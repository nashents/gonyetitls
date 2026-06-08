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

                            <table class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>Deal#</th>
                                        <th>Reference</th>
                                        <th>Customer</th>
                                        <th>Cargo</th>
                                        <th>Allocation</th>
                                        <th>Timeline</th>
                                        <th>Ccy</th>
                                        <th>Freight</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                @if (isset($deals))
                                    <tbody>
                                        @forelse ($deals as $deal)
                                            <tr>
                                                <td>{{ $deal->deal_number }}</td>
                                                <td>{{ $deal->reference ?? 'N/A' }}</td>
                                                <td>{{ $deal->customer ? $deal->customer->name : 'N/A' }}</td>
                                                <td>{{ $deal->cargo ? $deal->cargo->name : 'N/A' }}</td>
                                                <td>
                                                    @if ($deal->weight)
                                                        <strong>Weight:</strong> {{ number_format($deal->weight, 2) }}
                                                        {{ $deal->units_of_measure ? $deal->units_of_measure->name : '' }}<br>
                                                    @endif

                                                    @if ($deal->litreage)
                                                        <strong>Litreage:</strong> {{ number_format($deal->litreage, 2) }} L<br>
                                                    @endif

                                                    @if ($deal->quantity)
                                                        <strong>Quantity:</strong> {{ number_format($deal->quantity, 2) }}
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
                                                <td class="w-10 line-height-35 table-dropdown">
                                                    <div class="dropdown">
                                                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">
                                                            <i class="fa fa-bars"></i>
                                                            <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <a href="#" wire:click.prevent="edit({{ $deal->id }})">
                                                                    <i class="fa fa-edit color-success"></i> Edit
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="#" wire:click.prevent="close({{ $deal->id }})">
                                                                    <i class="fa fa-cancel color-success"></i> Close
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="#" wire:click.prevent="delete({{ $deal->id }})">
                                                                    <i class="fa fa-trash color-danger"></i> Delete
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>

                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10">
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
   
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="closeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                    <center><strong>Mark Deal {{$deal?->deal_number}}{{$deal?->reference ? "/".$deal?->reference : ""}} as closed.</strong></center> 
                </div>
                <form wire:submit.prevent="markClosed()">
                    <div class="modal-footer no-border">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                            <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fa fa-refresh"></i>Update</button>
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