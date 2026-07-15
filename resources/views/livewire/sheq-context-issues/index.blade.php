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
                                <a href="" data-toggle="modal" data-target="#sheq_context_issueModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Context Issue</a>
                            </div>
                        </div>
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                            <div class="row" style="margin-bottom:10px">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" placeholder="Search..." wire:model.debounce.500ms="search">
                                </div>
                                <div class="col-md-4">
                                    <select class="form-control" wire:model="type_filter">
                                        <option value="">Internal & External</option>
                                        <option value="internal">Internal</option>
                                        <option value="external">External</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-control" wire:model="department_filter">
                                        <option value="">All Departments</option>
                                        @foreach ($departments as $department)
                                            <option value="{{$department->id}}">{{$department->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <table class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Type</th>
                                    <th class="th-sm">Framework</th>
                                    <th class="th-sm">Category</th>
                                    <th class="th-sm">Issue</th>
                                    <th class="th-sm">Impact on the Management System</th>
                                    <th class="th-sm">Department</th>
                                    <th class="th-sm">Review Date</th>
                                    <th class="th-sm">Status</th>
                                    <th class="th-sm">Action</th>
                                  </tr>
                                </thead>
                                @if (isset($sheq_context_issues))
                                <tbody>
                                    @forelse ($sheq_context_issues as $issue)
                                  <tr>
                                    <td>{{ucwords($issue->type)}}</td>
                                    <td>{{strtoupper($issue->framework)}}</td>
                                    <td>{{ucwords($issue->category)}}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($issue->description, 80) }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($issue->impact, 80) }}</td>
                                    <td>{{$issue->department->name ?? '-'}}</td>
                                    <td>{{$issue->review_date ? \Carbon\Carbon::parse($issue->review_date)->format('d M Y') : '-'}}</td>
                                    <td>
                                        @if ($issue->status == 'open')
                                            <span class="label label-default">Open</span>
                                        @elseif ($issue->status == 'monitored')
                                            <span class="label label-info">Monitored</span>
                                        @else
                                            <span class="label label-success">Closed</span>
                                        @endif
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#" wire:click="edit({{$issue->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" wire:click.prevent="delete({{$issue->id}})"><i class="fa fa-trash color-danger"></i> Delete</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="9">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Context Issues Found ....
                                        </div>
                                    </td>
                                  </tr>
                                @endforelse
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                @endif
                              </table>
                                 <nav class="text-center" style="float: right">
                                <ul class="pagination rounded-corners">
                                    @if (isset($sheq_context_issues))
                                        {{ $sheq_context_issues->links() }}
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div data-backdrop="static" data-keyboard="false" class="modal fade" id="sheq_context_issueDeleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                <center> <strong>Are you sure you want to delete this Context Issue?</strong> </center>
                </div>
                <form wire:submit.prevent="destroy()" >
                <div class="modal-footer no-border">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fa fa-trash"></i>Delete</button>
                    </div>
                </div>
            </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="sheq_context_issueModal" tabindex="-1" role="dialog" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-plus"></i> Add Context Issue <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    @include('livewire.sheq-context-issues.form')
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Save</button>
                    </div>
                </div>
            </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="sheq_context_issueEditModal" tabindex="-1" role="dialog" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-edit"></i> Edit Context Issue <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <input type="hidden" wire:model="sheq_context_issue_id">
                <div class="modal-body">
                    @include('livewire.sheq-context-issues.form')
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                    </div>
                </div>
            </form>
            </div>
        </div>
    </div>

</div>
