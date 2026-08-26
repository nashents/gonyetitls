<div class="dropdown">
    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fa fa-bars"></i>
        <span class="caret"></span>
    </button>

    <ul class="dropdown-menu">
        <li>
            <a href="{{ route('trips.show', ['trip' => $trip->id, 'return' => urlencode(request()->fullUrl())]) }}">
                <i class="fas fa-eye color-default"></i> View
            </a>
        </li>
        @if ($notDriver)
            @if ($user->is_admin())
                <li>
                    <a href="{{ route('audits.index', ['id' => $trip->id, 'category' => 'trip']) }}">
                        <i class="fas fa-list color-default"></i> Audits
                    </a>
                </li>
            @endif
            @if ($employee)
                @if ($trip->authorization == "approved")
                    @foreach ($trip->transport_orders as $transport_order)
                    <li>
                        <a href="{{ route('transport_orders.preview', $transport_order->id) }}">
                            <i class="fas fa-file color-warning"></i> Transport Order
                            @if ($trip->transport_orders->count() > 1)
                                ({{ $transport_order->transport_order_number }})
                            @endif
                        </a>
                    </li>
                    @endforeach
                    <li>
                        <a href="{{ route('trips.trip_sheet', $trip->id) }}">
                            <i class="fas fa-file color-warning"></i> Trip Sheet
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('trips.manifest', $trip->id) }}">
                            <i class="fas fa-file color-warning"></i> Manifest
                        </a>
                    </li>
                @endif
                @if ($trip->status == False)
                    @if ($trip->trip_status == "Offloaded")
                        <li>
                            <a href="" wire:click.prevent="showCompleted({{$trip->id}})">
                                <i class="fas fa-check color-success"></i> Mark as completed
                            </a>
                        </li>
                    @endif

                    <li>
                        <a href="{{ route('trips.edit', $trip->id) }}">
                            <i class="fas fa-edit color-success"></i> Edit
                        </a>
                    </li>
                    <li>
                        <a href="#" data-toggle="modal" data-target="#tripDeleteModal{{ $trip->id }}">
                            <i class="fa fa-trash color-danger"></i> Delete
                        </a>
                    </li>
                @elseif ($user->is_admin())
                    {{-- Completed trips can still be deleted by an admin - the
                    destroy route now reverses any invoice/bill raised against
                    it (see TripDeletionService) instead of just detaching it. --}}
                    <li>
                        <a href="#" data-toggle="modal" data-target="#tripDeleteModal{{ $trip->id }}">
                            <i class="fa fa-trash color-danger"></i> Delete
                        </a>
                    </li>
                @endif

            @endif
        @endif
    </ul>
</div>

@include('trips.delete')