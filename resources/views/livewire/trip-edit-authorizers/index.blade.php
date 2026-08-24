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
                                <a href="#" data-toggle="modal" data-target="#tripEditAuthorizerModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Trip Edit Authorizer</a>
                            </div>
                        </div>
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">

                            <table id="tripEditAuthorizersTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">User</th>
                                    <th class="th-sm">Assigned By</th>
                                    <th class="th-sm">Status</th>
                                    <th class="th-sm">Action</th>
                                  </tr>
                                </thead>
                                @if ($trip_edit_authorizers->count() > 0)
                                <tbody>
                                    @foreach ($trip_edit_authorizers as $authorizer)
                                  <tr>
                                    <td>{{$authorizer->user ? $authorizer->user->name : ""}} {{$authorizer->user ? $authorizer->user->surname : ""}}</td>
                                    <td>{{$authorizer->creator ? $authorizer->creator->name : ""}} {{$authorizer->creator ? $authorizer->creator->surname : ""}}</td>
                                    <td><span class="badge bg-{{$authorizer->status == 1 ? "success" : "danger"}}">{{$authorizer->status == 1 ? "Active" : "Inactive"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#" wire:click="edit({{$authorizer->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#tripEditAuthorizerDeleteModal{{$authorizer->id}}"><i class="fa fa-trash color-danger"></i> Remove</a></li>
                                            </ul>
                                        </div>
                                        <div data-backdrop="static" data-keyboard="false" class="modal fade" id="tripEditAuthorizerDeleteModal{{$authorizer->id}}" tabindex="-1" role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content bg-danger">
                                                    <div class="modal-body">
                                                        <center><strong>Remove this Trip Edit Authorizer?</strong></center>
                                                    </div>
                                                    <div class="modal-footer no-border">
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                                                            <button type="button" class="btn bg-black btn-wide btn-rounded" data-dismiss="modal" wire:click="delete({{$authorizer->id}})"><i class="fa fa-trash"></i>Delete</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                 @endif
                              </table>

                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="tripEditAuthorizerModal" tabindex="-1" role="dialog" aria-labelledby="tripEditAuthorizerModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="tripEditAuthorizerModalLabel"><i class="fas fa-plus"></i> Add Trip Edit Authorizer <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></h4>
                </div>
                <form wire:submit.prevent="store()">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="user_id">User<span class="required" style="color: red">*</span></label>
                            <select wire:model.debounce.300ms="user_id" class="form-control" required>
                                <option value="">Select User</option>
                                @foreach ($users as $user)
                                    <option value="{{$user->id}}">{{$user->name}} {{$user->surname}}</option>
                                @endforeach
                            </select>
                            @error('user_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select wire:model.debounce.300ms="status" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="tripEditAuthorizerEditModal" tabindex="-1" role="dialog" aria-labelledby="tripEditAuthorizerEditModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="tripEditAuthorizerEditModalLabel"><i class="fas fa-edit"></i> Edit Trip Edit Authorizer <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></h4>
                </div>
                <form wire:submit.prevent="update()">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="user_id">User<span class="required" style="color: red">*</span></label>
                            <select wire:model.debounce.300ms="user_id" class="form-control" required>
                                <option value="">Select User</option>
                                @foreach ($users as $user)
                                    <option value="{{$user->id}}">{{$user->name}} {{$user->surname}}</option>
                                @endforeach
                            </select>
                            @error('user_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select wire:model.debounce.300ms="status" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
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
