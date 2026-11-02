<div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="panel-title">
                <a href="{{ route('expenses.index') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Active Expenses</a>
            </div>
        </div>
        <div class="panel-body p-20" style="overflow-x:auto;">
            <div class="col-md-5" style="float: right; padding-right:2px">
                <div class="form-group">
                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search archived expenses...">
                </div>
            </div>
            <table class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th class="th-sm">Name</th>
                        <th class="th-sm">Type</th>
                        <th class="th-sm">Account</th>
                        <th class="th-sm">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expenses as $expense)
                        <tr wire:key="arch-expense-{{ $expense->id }}">
                            <td>{{ $expense->name }}</td>
                            <td>{{ $expense->type }}</td>
                            <td>{{ optional($expense->account)->name }}</td>
                            <td>
                                <a href="#" wire:click="restore({{ $expense->id }})" class="btn btn-sm btn-default border-primary">
                                    <i class="fa fa-undo color-primary"></i> Restore
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">No archived expenses.</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $expenses->links() }}
        </div>
    </div>
</div>
