<div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="panel-title">
                <a href="{{ route('vendors.index') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Active Vendors</a>
            </div>
        </div>
        <div class="panel-body p-20" style="overflow-x:auto;">
            <div class="col-md-5" style="float: right; padding-right:2px">
                <div class="form-group">
                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search archived vendors...">
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
                    @forelse ($vendors as $vendor)
                        <tr wire:key="arch-vendor-{{ $vendor->id }}">
                            <td>{{ $vendor->vendor_number }}</td>
                            <td>{{ $vendor->name }}</td>
                            <td>{{ $vendor->email }}</td>
                            <td>
                                <a href="#" wire:click="restore({{ $vendor->id }})" class="btn btn-sm btn-default border-primary">
                                    <i class="fa fa-undo color-primary"></i> Restore
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">No archived vendors.</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $vendors->links() }}
        </div>
    </div>
</div>
