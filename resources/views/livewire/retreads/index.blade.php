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
                                <a href="{{route('retreads.create')}}"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Retread</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

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
                                                @if ($retread->status == 1)
                                                    <li><a href="#"  wire:click="showRreated({{$retread->id}})"><i class="fa fa-times color-warning"></i> Close</a></li>
                                                @endif
                                                <li><a href="{{route('retreads.edit', $retread->id)}}"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#retreadsDeleteModal{{ $retread->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('retreads.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="9">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Retreads Found ....
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="bulkyCloseTicketModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-window-close"></i> Close Retread<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="closeRetread()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Decision</label>
                    <select class="form-control" wire:model.debounce.300ms="status" required>
                        <option value="">Select Decision</option>
                        <option value="0">Close</option>
                        <option value="1">Open</option>
                    </select>
                        @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

