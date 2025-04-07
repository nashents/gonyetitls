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
                                {{-- <a href="" data-toggle="modal" data-target="#decisionModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Fuel Request</a> --}}

                            </div>

                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="fuel_requestsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>

                                    <th class="th-sm">Request#
                                    </th>
                                    <th class="th-sm">Allocation#
                                    </th>
                                    <th class="th-sm">Employee
                                    </th>
                                    <th class="th-sm">Fuel Type
                                    </th>
                                    <th class="th-sm">Quantity
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($fuel_requests->count()>0)
                                <tbody>
                                    @foreach ($fuel_requests as $fuel_request)
                                  <tr>
                                    <td>{{ucfirst($fuel_request->request_number)}}</td>
                                    <td>{{ucfirst($fuel_request->allocation ? $fuel_request->allocation->allocation_number : "")}}</td>
                                    <td>{{ucfirst($fuel_request->employee->name)}} {{ucfirst($fuel_request->employee->surname)}}</td>
                                    <td>{{$fuel_request->fuel_type}}</td>
                                    <td>{{$fuel_request->quantity ? $fuel_request->quantity."Litres" : ""}}</td>
                                    @if ($fuel_request->status == "pending")
                                    <td><span class="label label-warning label-rounded">{{$fuel_request->status}}</span></td>
                                    @elseif($fuel_request->status == "approved")
                                    <td><span class="label label-success label-rounded">{{$fuel_request->status}}</span></td>
                                    @elseif($fuel_request->status == "rejected")
                                    <td><span class="label label-danger label-rounded">{{$fuel_request->status}}</span></td>
                                    @endif

                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#"  wire:click="decision({{$fuel_request->id}})" ><i class="fa fa-gavel color-success"></i>Authorization</a></li>
                                            </ul>
                                        </div>
                                        @include('fuel_requests.delete')
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="fuelRequestAuthorizationModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-gas-pump"></i> Authorize Fuel Request <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
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
                        <label for="comment">Comment</label>
                  <textarea class="form-control" wire:model.debounce.300ms="comments" cols="30" rows="3"></textarea>
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

