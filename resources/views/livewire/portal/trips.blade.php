<div>
    <div class="panel">
        <div class="panel-heading">
            <div class="panel-title"><h5>My Trips</h5></div>
        </div>
        <div class="panel-body">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Trip #</th><th>From</th><th>To</th><th>Status</th><th>Start Date</th><th>End Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($trips as $trip)
                        <tr>
                            <td>{{ ucfirst($trip->trip_number) }}</td>
                            <td>{{ $trip->fromDestination?->name }}</td>
                            <td>{{ $trip->toDestination?->name }}</td>
                            <td><span class="label label-info label-wide">{{ $trip->trip_status }}</span></td>
                            <td>{{ $trip->start_date ? \Carbon\Carbon::parse($trip->start_date)->format('d M Y') : '—' }}</td>
                            <td>{{ $trip->end_date ? \Carbon\Carbon::parse($trip->end_date)->format('d M Y') : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">No trips found.</td></tr>
                    @endforelse
                </tbody>
            </table>

            {{ $trips->links() }}
        </div>
    </div>
</div>
