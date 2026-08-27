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
                                Pending Edit Authorizations
                            </div>
                        </div>
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">

                            @if ($selectedRows)
                            <div class="row">
                                <div class="col-lg-2" >
                                    <div class="dropdown">
                                        <button class="btn btn-default border-primary btn-rounded btn-wide dropdown-toggle" type="button" id="menuBulkDecide" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                            <i class="fa fa-bars"></i> Bulk Actions
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu bg-gray" aria-labelledby="menuBulkDecide">
                                            <li><a href="#" wire:click="showBulkDecide()"><i class="fas fa-gavel"></i> Decide Selected</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-lg-3" style="margin-top: 5px; margin-left: -30px;">
                                <span>selected {{ count($selectedRows) }} request(s) to decide on.</span>
                                </div>
                            </div>
                            <br>
                            @endif

                            <table class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">
                                        <input type="checkbox" wire:model.debounce.300ms="selectPageRows">
                                    </th>
                                    <th class="th-sm">Module</th>
                                    <th class="th-sm">Record</th>
                                    <th class="th-sm">Batch</th>
                                    <th class="th-sm">Requested By</th>
                                    <th class="th-sm">Reason</th>
                                    <th class="th-sm">Requested At</th>
                                    <th class="th-sm">Action</th>
                                  </tr>
                                </thead>
                                @if ($requests->count() > 0)
                                <tbody>
                                    @foreach ($requests as $request)
                                  <tr>
                                    <td><input type="checkbox" wire:model.debounce.300ms="selectedRows" id="request-{{ $request->id }}" value="{{ $request->id }}"></td>
                                    <td>{{ ucfirst($request->module) }}</td>
                                    <td>{{ $request->editable?->editAuthLabel() }}</td>
                                    <td>
                                        @if ($request->batch_uuid)
                                            <span class="badge bg-info">Batch of {{ \App\Models\EditAuthorizationRequest::where('batch_uuid', $request->batch_uuid)->count() }}</span>
                                        @endif
                                    </td>
                                    <td>{{$request->requester?->name}} {{$request->requester?->surname}}</td>
                                    <td>{{$request->reason}}</td>
                                    <td>{{$request->created_at?->format('Y-m-d H:i')}}</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <a href="#" class="btn btn-sm btn-default" wire:click="decide({{$request->id}})"><i class="fas fa-gavel color-success"></i> Decide</a>
                                    </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                 @endif
                              </table>

                            <ul class="pagination rounded-corners">
                                {{ $requests->links() }}
                            </ul>

                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="editAuthorizationDecisionModal" tabindex="-1" role="dialog" aria-labelledby="editAuthorizationDecisionModalLabel" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="editAuthorizationDecisionModalLabel"><i class="fas fa-gavel"></i> Decide Edit Authorization Request <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></h4>
                </div>
                <form wire:submit.prevent="update()">
                    <div class="modal-body">
                        @if ($editAuthorizationRequest)
                            <p><strong>{{ ucfirst($editAuthorizationRequest->module) }}:</strong> {{$editAuthorizationRequest->editable?->editAuthLabel()}}</p>
                            <p><strong>Requested By:</strong> {{$editAuthorizationRequest->requester?->name}} {{$editAuthorizationRequest->requester?->surname}}</p>
                            <p><strong>Reason:</strong> {{$editAuthorizationRequest->reason}}</p>
                        @endif
                        <div class="form-group">
                            <label for="decision">Decision<span class="required" style="color: red">*</span></label>
                            <select class="form-control" wire:model.debounce.300ms="decision" required>
                                <option value="">Select Decision</option>
                                <option value="approved">Approve</option>
                                <option value="rejected">Reject</option>
                            </select>
                            @error('decision') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="decision_comments">Comments</label>
                            <textarea wire:model.debounce.300ms="decision_comments" class="form-control" cols="30" rows="5"></textarea>
                            @error('decision_comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                            <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Submit Decision</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="bulkEditAuthorizationDecisionModal" tabindex="-1" role="dialog" aria-labelledby="bulkEditAuthorizationDecisionModalLabel" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="bulkEditAuthorizationDecisionModalLabel"><i class="fas fa-gavel"></i> Decide Selected Edit Authorization Requests <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></h4>
                </div>
                <form wire:submit.prevent="bulkUpdate()">
                    <div class="modal-body">
                        <p>Deciding on <strong>{{ count($selectedRows) }}</strong> request(s).</p>
                        <div class="form-group">
                            <label for="decision">Decision<span class="required" style="color: red">*</span></label>
                            <select class="form-control" wire:model.debounce.300ms="decision" required>
                                <option value="">Select Decision</option>
                                <option value="approved">Approve</option>
                                <option value="rejected">Reject</option>
                            </select>
                            @error('decision') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="decision_comments">Comments</label>
                            <textarea wire:model.debounce.300ms="decision_comments" class="form-control" cols="30" rows="5"></textarea>
                            @error('decision_comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                            <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Submit Decision</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
