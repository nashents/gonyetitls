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
                    <li>
                        <a href="{{ route('transport_orders.preview', $trip->id) }}">
                            <i class="fas fa-file color-warning"></i> Transport Order
                        </a>
                    </li>
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
                    <li>
                        <a href="" wire:click.prevent="showCompleted({{$trip->id}})">
                            <i class="fas fa-check color-success"></i> Mark as completed
                        </a>
                    </li>
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
                @endif
                
            @endif
        @endif
    </ul>
</div>

@include('trips.delete')