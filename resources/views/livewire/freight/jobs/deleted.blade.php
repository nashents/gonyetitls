<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Deleted Freight Jobs</h5>
                            </div>
                            <div>
                                @include('includes.messages')
                            </div>
                        </div>
                        <div class="panel-body p-20">
                            <div class="row mb-20">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" wire:model.debounce.300ms="search" placeholder="Search job #, customer ref, customer">
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Job #</th>
                                            <th>Customer</th>
                                            <th>Service</th>
                                            <th>Origin &rarr; Destination</th>
                                            <th>Status</th>
                                            <th>Authorization</th>
                                            <th>Created</th>
                                            <th>Deleted On</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($jobs as $job)
                                            <tr wire:key="deleted-freight-job-{{ $job->id }}">
                                                <td>{{ $job->job_number }}</td>
                                                <td>{{ $job->customer_label }}</td>
                                                <td>{{ $job->freight_service_type?->name ?? '—' }}</td>
                                                <td>
                                                    @if ($job->fromDestination || $job->toDestination)
                                                        {{ $job->fromDestination ? trim(($job->fromDestination->country->name ?? '') . ' ' . $job->fromDestination->city) : '—' }}
                                                        &rarr;
                                                        {{ $job->toDestination ? trim(($job->toDestination->country->name ?? '') . ' ' . $job->toDestination->city) : '—' }}
                                                    @else
                                                        {{ $job->origin ?: '—' }} &rarr; {{ $job->destination ?: '—' }}
                                                    @endif
                                                </td>
                                                <td>{{ ucwords(str_replace('_', ' ', (string) $job->status)) }}</td>
                                                <td><span class="badge bg-{{ ['approved' => 'success', 'rejected' => 'danger'][$job->authorization] ?? 'warning' }}">{{ $job->authorization ?: 'pending' }}</span></td>
                                                <td>{{ $job->created_at?->format('d M Y') }}</td>
                                                <td>{{ $job->deleted_at ? \Carbon\Carbon::parse($job->deleted_at)->format('d M Y H:i') : '—' }}</td>
                                                <td class="w-10 line-height-35 table-dropdown">
                                                    <div class="dropdown">
                                                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="fa fa-bars"></i>
                                                            <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-right">
                                                            <li><a href="#" wire:click.prevent="restore({{ $job->id }})"><i class="fas fa-refresh color-success"></i> Restore</a></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9">
                                                    <div style="text-align:center; color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                        No Deleted Freight Jobs Found ....
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <nav class="text-center" style="float: right">
                                @include('livewire.freight.partials.pagination', ['paginator' => $jobs])
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="freightJobRestoreModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                    <center><strong>Are you sure you want to restore Freight Job {{ $restoring_job_number }}?</strong></center>
                    <center><small>Its shipments, cargo lines and parties deleted with it come back too.</small></center>
                </div>
                <form wire:submit.prevent="update()">
                    <div class="modal-footer no-border">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                            <button type="submit" class="btn bg-black btn-wide btn-rounded"><i class="fas fa-undo"></i> Restore</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
