<div>
    <div class="panel">
        <div class="panel-heading">
            <div class="panel-title"><h5>My Freight Jobs</h5></div>
        </div>
        <div class="panel-body">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Job #</th><th>Your Reference</th><th>Mode</th><th>Origin</th><th>Destination</th><th>Status</th><th>Opened</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($jobs as $job)
                        <tr>
                            <td>{{ $job->job_number }}</td>
                            <td>{{ $job->customer_reference }}</td>
                            <td>{{ ucfirst($job->primary_transport_mode ?? '') }}</td>
                            <td>{{ $job->origin }}</td>
                            <td>{{ $job->destination }}</td>
                            <td><span class="label label-info label-wide">{{ ucwords(str_replace('_', ' ', $job->status)) }}</span></td>
                            <td>{{ optional($job->opened_at)->format('d M Y') }}</td>
                            <td><a href="{{ route('customer.jobs.show', $job->id) }}" class="btn btn-xs btn-default">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center">No freight jobs found.</td></tr>
                    @endforelse
                </tbody>
            </table>

            {{ $jobs->links() }}
        </div>
    </div>
</div>
