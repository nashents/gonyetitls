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
                            <table class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Loan#
                                    </th>
                                    <th class="th-sm">Movement
                                    </th>
                                    <th class="th-sm">Employee
                                    </th>
                                    <th class="th-sm">Vendor
                                    </th>
                                    <th class="th-sm">Account
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">PBP
                                    </th>
                                    <th class="th-sm">Total
                                    </th>
                                    <th class="th-sm">Instal
                                    </th>
                                    <th class="th-sm">Bal
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Auth
                                    </th>
                                    <th class="th-sm">Actions
                                    </th>

                                  </tr>
                                </thead>
                                @if (isset($loans))
                                <tbody>
                                    @forelse ($loans as $loan)
                                  <tr>
                                    <td>{{$loan->loan_number}}</td>
                                    <td>
                                        <center>
                                            @if ($loan->movement == "In")
                                                <i class="fa fa-arrow-down" style="color: green"></i>
                                            @elseif($loan->movement == "Out") 
                                                <i class="fa fa-arrow-up" style="color: blue"></i>
                                            @endif
                                        </center>
                                    </td>
                                    <td>{{$loan->employee ? $loan->employee->name : ""}} {{$loan->employee ? $loan->employee->surname : ""}} </td>
                                    <td>{{$loan->vendor ? $loan->vendor->name : ""}} </td>
                                    <td>{{$loan->account ? $loan->account->name : ""}}</td>
                                    <td>{{$loan->start_date}}</td>
                                    <td>
                                        {{$loan->currency ? $loan->currency->name : ""}}  {{$loan->currency ? $loan->currency->symbol : ""}}{{number_format($loan->amount,2)}} @ {{number_format($loan->interest ? $loan->interest : 0,2)}}% interest {{$loan->period ? "Payback Period: ".$loan->period." Months" : ""}}     
                                    </td>
                                    <td>
                                        @if ($loan->total)
                                            {{$loan->currency ? $loan->currency->symbol : ""}}{{number_format($loan->total,2)}}        
                                        @endif
                                    </td>
                                    <td> 
                                        @if ($loan->payment_per_month)
                                            {{$loan->currency ? $loan->currency->symbol : ""}}{{number_format($loan->payment_per_month,2)}}        
                                        @endif
                                    </td>
                                    <td> 
                                        @if ($loan->balance)
                                            {{$loan->currency ? $loan->currency->symbol : ""}}{{number_format($loan->balance,2)}}        
                                        @endif
                                    </td>
                                    <td><span class="label label-{{($loan->status == 'Paid') ? 'success' : (($loan->status == 'Partial') ? 'warning' : 'danger') }}">{{ $loan->status }}</span></td>
                                    <td><span class="badge bg-{{($loan->authorization == 'approved') ? 'success' : (($loan->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($loan->authorization == 'approved') ? 'approved' : (($loan->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('loans.show',$loan->id)}}"  ><i class="fas fa-eye color-default"></i> View</a></li>
                                                {{-- <li><a href="#" wire:click="authorize({{$loan->id}})"><i class="fas fa-gavel color-success"></i> Authorization</a></li> --}}
                                            </ul>
                                        </div>
                                        @include('loans.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="13">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Loans Found ....
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
                                    @if (isset($loans))
                                        {{ $loans->links() }} 
                                    @endif 
                                </ul>
                            </nav>    
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
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-money"></i> Authorize Loan <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
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

