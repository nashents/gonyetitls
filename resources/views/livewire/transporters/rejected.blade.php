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
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="transportersTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>

                                    <th class="th-sm">Transporter#
                                    </th>
                                    <th class="th-sm">Name
                                    </th>
                                    <th class="th-sm">Email
                                    </th>
                                    <th class="th-sm">Phonenumber
                                    </th>
                                    <th class="th-sm">Address
                                    </th>
                                    <th class="th-sm">Authorization
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($transporters->count()>0)
                                <tbody>
                                    @foreach ($transporters as $transporter)
                                  <tr>
                                    <td>{{$transporter->transporter_number}}</td>
                                    <td>{{ucfirst($transporter->name)}}</td>
                                    <td>{{$transporter->email}}</td>
                                    <td>{{$transporter->phonenumber}}</td>
                                    <td>
                                      
                                        @if ($transporter->street_address)
                                        {{$transporter->street_address}}
                                        @endif
                                        @if ($transporter->suburb)
                                        {{$transporter->suburb}}, 
                                        @endif
                                        @if ($transporter->city)
                                        {{$transporter->city}},
                                        @endif
                                        @if ($transporter->country)
                                        {{$transporter->country}},
                                        @endif
                                    </td>
                                    <td><span class="badge bg-{{($transporter->authorization == 'approved') ? 'success' : (($transporter->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($transporter->authorization == 'approved') ? 'approved' : (($transporter->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                    <td><span class="badge bg-{{$transporter->status == 1 ? "success" : "danger"}}">{{$transporter->status == 1 ? "Active" : "Inactive"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('transporters.show', $transporter->id) }}"  ><i class="fa fa-eye color-default"></i> View</a></li>
                                                <li><a href="#" wire:click="authorize({{$transporter->id}})"><i class="fas fa-gavel color-success"></i> Authorization</a></li>
                                            </ul>
                                        </div>
                                        @include('transporters.delete')
                                </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                 @endif
                              </table>

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="authorizationModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-gavel"></i> Authorize Transporter<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Authorize</label>
                    <select class="form-control" wire:model.debounce.300ms="authorize" required>
                        <option value="">Select Decision</option>
                        <option value="approved">Approve</option>
                        <option value="rejected">Reject</option>
                    </select>
                        @error('authorize') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="reason">Comments</label>
                       <textarea wire:model.debounce.300="comments" class="form-control" cols="30" rows="5"></textarea>
                        @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>


</div>

