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

                 
                            <table id="recoveriesTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>

                                    <th class="th-sm">Recovery#
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">Driver
                                    </th>
                                    <th class="th-sm">Trip
                                    </th>
                                    <th class="th-sm">Location
                                    </th>
                                    <th class="th-sm">Deduction
                                    </th>
                                    <th class="th-sm">Currency
                                    </th>
                                    <th class="th-sm">Amount
                                    </th>
                                    <th class="th-sm">Balance
                                    </th>
                                    <th class="th-sm">Payment
                                    </th>
                                    <th class="th-sm">Progress
                                    </th>
                                    <th class="th-sm">Authorization
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($recoveries->count()>0)
                                <tbody>
                                    @foreach ($recoveries as $recovery)
                                  <tr>
                                    <td>{{$recovery->recovery_number}}</td>
                                    <td>{{$recovery->date}}</td>
                                    <td>{{$recovery->driver ? $recovery->driver->driver_number : ""}} {{$recovery->driver ? $recovery->driver->employee->name : ""}} {{$recovery->driver ? $recovery->driver->employee->surname : ""}}</td>
                                    <td>{{$recovery->trip ? $recovery->trip->trip_number : ""}} <strong>From:</strong> {{$recovery->trip->loading_point ? $recovery->trip->loading_point->name : "undefined"}} <strong>To:</strong> {{$recovery->trip->offloading_point ? $recovery->trip->offloading_point->name : ""}}</td>
                                    <td>
                                        @if ($recovery->destination)
                                            {{$recovery->destination->country ? $recovery->destination->country->name : ""}} {{$recovery->destination ? $recovery->destination->city : ""}}        
                                        @endif
                                    </td>  
                                    <td>{{$recovery->deduction ? $recovery->deduction->name : ""}}</td>
                                    <td>{{$recovery->currency ? $recovery->currency->name : ""}}</td>  
                                    <td>
                                        @if ($recovery->amount)
                                            {{$recovery->currency ? $recovery->currency->symbol : ""}}{{number_format($recovery->amount,2)}}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($recovery->balance)
                                            {{$recovery->currency ? $recovery->currency->symbol : ""}}{{number_format($recovery->balance,2)}}
                                        @endif
                                    </td>
                                    <td><span class="label label-{{($recovery->status == 'Paid') ? 'success' : (($recovery->status == 'Partial') ? 'warning' : 'danger') }}">{{ $recovery->status }}</span></td>
                                    <td><span class="label label-{{$recovery->progress == "Open"  ? 'warning' : 'success' }}">{{$recovery->progress}}</span></td>
                                    <td><span class="badge bg-{{($recovery->authorization == 'approved') ? 'success' : (($recovery->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($recovery->authorization == 'approved') ? 'approved' : (($recovery->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('recoveries.show', $recovery->id) }}"  ><i class="fa fa-eye color-default"></i> View</a></li>
                                                {{-- <li><a href="#" wire:click="authorize({{$recovery->id}})"><i class="fas fa-gavel color-success"></i> Authorization</a></li> --}}
                                            </ul>
                                        </div>
                                        @include('recoveries.delete')
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
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-gavel"></i> Authorize Recovery<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
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

