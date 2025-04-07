<div>
    <style>
        .modal-lg {
        max-width: 80%;
    }
    </style>
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
                            <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                                <div class="panel-title">
                                    <div class="row">
                                    
                                    <div class="col-lg-3">
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                      Filter By
                                      </span>
                                      <select wire:model.debounce.300ms="retread_filter " class="form-control" aria-label="..." >
                                        <option value="created_at">Retread Created At</option>
                                  </select>
                                        </div>
                                        <!-- /input-group -->
                                    </div>
                                    @if ($retread_filter  == "created_at")
                                    <div class="col-lg-2" style="margin-right: 7px">
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                      From
                                      </span>
                                      <input type="date" wire:model.debounce.300ms="from"  class="form-control" aria-label="...">
                                        </div>
                                        <!-- /input-group -->
                                    </div>
                                    <div class="col-lg-2" style="margin-left: 30px">
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                      To
                                      </span>
                                      <input type="date" wire:model.debounce.300ms="to"  class="form-control" aria-label="...">
                                        </div>
                                        <!-- /input-group -->
                                    </div>
                              
                                    @endif
                                   
                                    <!-- /input-group -->
                                </div>
                              
                               
                                </div>
                                <br>
                                <div class="col-md-3" style="float: right; padding-right:0px">
                                    <div class="form-group">
                                        <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search retreads...">
                                    </div>
                                </div>
                
                                <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <thead>
                                      <tr>
                                        <th class="th-sm">Retread#
                                        </th>
                                        <th class="th-sm">Vendor
                                        </th>
                                        <th class="th-sm">Account
                                        </th>
                                        <th class="th-sm">Date
                                        </th>
                                        <th class="th-sm">Tyre(s)
                                        </th>
                                        <th class="th-sm">Currency
                                        </th>
                                        <th class="th-sm">Total
                                        </th>
                                        <th class="th-sm">Status
                                        </th>
                                        <th class="th-sm">Auth
                                        </th>
                                        <th class="th-sm">Action
                                        </th>
                                      </tr>
                                    </thead>
                                    @if (isset($retreads))
                                    <tbody>
                                        @forelse ($retreads as $retread)
                                      <tr>
                                        <td>{{$retread->retread_number}}</td>
                                        <td>{{$retread->vendor ? $retread->vendor->name : ""}}</td>
                                        <td>{{$retread->account ? $retread->account->name : ""}}</td>
                                        <td>{{$retread->date}}</td>
                                        <td>
                                            @foreach ($retread->retread_tyres as $retread_tyre)
                                                {{$retread_tyre->tyre->product ? $retread_tyre->tyre->product->name : ""}} {{$retread_tyre->tyre ? $retread_tyre->tyre->width : ""}} / {{$retread_tyre->tyre ? $retread_tyre->tyre->aspect_ratio : ""}} R {{$retread_tyre->tyre ? $retread_tyre->tyre->diameter : ""}}
                                                <br>
                                            @endforeach
                                        </td>
                                        <td>{{$retread->currency ? $retread->currency->name : ""}}</td>
                                        <td>{{$retread->currency ? $retread->currency->symbol : ""}}{{$retread->total ? number_format($retread->total,2) : ""}}</td>
                                        <td><span class="badge bg-{{$retread->status == 1 ? "warning" : "success"}}">{{$retread->status == 1 ? "Open" : "Closed"}}</span></td>
                                        <td><span class="badge bg-{{($retread->authorization == 'approved') ? 'success' : (($retread->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($retread->authorization == 'approved') ? 'approved' : (($retread->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                        <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('retreads.show', $retread->id)}}"><i class="fa fa-eye color-default"></i>View</a></li>
                                                    <li><a href="#" wire:click="authorize({{$retread->id}})"><i class="fas fa-gavel color-success"></i> Authorization</a></li>
                                                </ul>
                                            </div>
                                            @include('retreads.delete')
                                    </td>
                                      </tr>
                                      @empty
                                      <tr>
                                        <td colspan="9">
                                            <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                No Pending Retreads Found ....
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
                                        @if (isset($retreads))
                                            {{ $retreads->links() }} 
                                        @endif 
                                    </ul>
                                </nav> 

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
                        <h4 class="modal-title" id="modal4Label"><i class="fas fa-gavel"></i> Authorize Retreads<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
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
