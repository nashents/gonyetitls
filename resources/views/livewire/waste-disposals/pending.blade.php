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
                            
                                <div class="col-md-5" style="float: right; padding-right:2px">
                                    <div class="form-group">
                                        <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search waste  disposals register...">
                                    </div>
                                </div>
                                <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <thead >
                                        <tr>
                                        <th class="th-sm">Disposal#
                                        </th>
                                        <th class="th-sm">Disposal
                                        </th>
                                        <th class="th-sm">Type
                                        </th>
                                        <th class="th-sm">Ccy
                                        </th>
                                        <th class="th-sm">Items
                                        </th>
                                        <th class="th-sm">Auth
                                        </th>
                                        <th class="th-sm">Actions
                                        </th>
                                      </tr>
                                    </thead>
                                    @if (isset($waste_disposals))
                                    <tbody>
                                        @forelse  ($waste_disposals as $waste_disposal)
                                       
                                      <tr>
                                        <td>
                                            {{$waste_disposal->waste_disposal_number}}
                                            <br>
                                            <small>
                                                <strong>CreatedBy:</strong> {{ucfirst($waste_disposal->user ? $waste_disposal->user->name : "")}} {{ucfirst($waste_disposal->user ? $waste_disposal->user->surname : "")}} <br>
                                                <strong>CreatedOn:</strong> {{$waste_disposal->created_at}}
                                            </small>
                                        </td>  
                                        <td>
                                            <small><strong>DisposedBy: </strong>{{ucfirst($waste_disposal->employee ? $waste_disposal->employee->name : "")}} {{ucfirst($waste_disposal->employee ? $waste_disposal->employee->surname : "")}}</small>
                                            <br>
                                            <small><strong>DisposedOn: </strong>{{$waste_disposal->date}}</small>
                                        </td>
                                        <td>
                                            {{$waste_disposal->movement}}
                                            @if ($waste_disposal->customer)
                                                <br>
                                                <small><strong>TransferTo: </strong>{{$waste_disposal->customer ? $waste_disposal->customer->name : ""}}</small>
                                            @endif
                                        </td>
                                        <td>{{$waste_disposal->currency ? $waste_disposal->currency->name : ""}}</td>
                                        <td>
                                            @if ($waste_disposal->waste_disposal_items)
                                                @foreach ($waste_disposal->waste_disposal_items as $item)
                                                    {{$item->waste_type ? $item->waste_type->name : ""}} X ({{$item->qty}} {{$item->unit_of_measure}}) {{$item->currency ? $item->currency->symbol : ""}}{{number_format($item->amount ? $item->amount : 0,2)}} @if (!$loop->last), @endif
                                                @endforeach
                                            @endif
                                        </td>
                                        <td><span class="badge bg-{{($waste_disposal->authorization == 'approved') ? 'success' : (($waste_disposal->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($waste_disposal->authorization == 'approved') ? 'approved' : (($waste_disposal->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                        <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('waste_disposals.show', $waste_disposal->id)}}"><i class="fa fa-eye color-default"></i>View</a></li>
                                                     <li><a href="#" wire:click="authorize({{$waste_disposal->id}})"><i class="fas fa-gavel color-success"></i> Authorization</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                      </tr>
                                      @empty
                                      <tr>
                                        <td colspan="11">
                                            <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                No Pending Waste Disposals Found ....
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
                                        @if (isset($waste_disposals))
                                            @if ($waste_disposals->count()>0)
                                                {{ $waste_disposals->links() }} 
                                            @endif
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
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-gavel"></i> Authorize Waste Collection <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Authorize</label>
                        <select class="form-control" wire:model.debounce.300ms="authorize">
                            <option value="">Select Decision</option>
                            <option value="approved">Approve</option>
                            <option value="rejected">Reject</option>
                        </select>
                        @error('authorize') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="comment">Comment</label>
                        <textarea class="form-control" wire:model.debounce.300ms="comments" cols="30" rows="3"></textarea>
                        @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <input type="checkbox" wire:model.debounce.300ms="stay_on_page" class="line-style" id="stay_on_page" />
                        <label for="stay_on_page" class="radio-label">Stay on this page after authorizing (don't redirect to Approved/Rejected)</label>
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
