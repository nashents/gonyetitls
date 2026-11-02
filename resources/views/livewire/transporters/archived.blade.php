<div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="panel-title">
                <a href="{{ route('transporters.index') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Active Transporters</a>
            </div>
        </div>
        <div class="panel-body p-20" style="overflow-x:auto;">
            <div class="col-md-5" style="float: right; padding-right:2px">
                <div class="form-group">
                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search archived transporters...">
                </div>
            </div>
            <table class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th class="th-sm">Number</th>
                        <th class="th-sm">Name</th>
                        <th class="th-sm">Email</th>
                        <th class="th-sm">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transporters as $transporter)
                        <tr wire:key="arch-transporter-{{ $transporter->id }}">
                            <td>{{ $transporter->transporter_number }}</td>
                            <td>{{ $transporter->name }}</td>
                            <td>{{ $transporter->email }}</td>
                            <td>
                                <a href="#" wire:click="restore({{ $transporter->id }})" class="btn btn-sm btn-default border-primary">
                                    <i class="fa fa-undo color-primary"></i> Restore
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">No archived transporters.</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $transporters->links() }}
        </div>
    </div>
</div>
