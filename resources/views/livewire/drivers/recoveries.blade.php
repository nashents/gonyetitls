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
                        {{-- <div class="col-md-3" style="float: right; padding-right:0px">
                            <div class="form-group">
                                <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search drivers...">
                            </div>
                        </div> --}}
             <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>

                                    <th class="th-sm">Recovery#
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">Driver
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
                                @if (isset($recoveries))
                                <tbody>
                                   
                                    @forelse ($recoveries as $recovery)
                                  <tr>
                                    <td>{{$recovery->recovery_number}}</td>
                                    <td>{{$recovery->date}}</td>
                                    <td>{{$recovery->driver ? $recovery->driver->driver_number : ""}} {{$recovery->driver ? $recovery->driver->employee->name : ""}} {{$recovery->driver ? $recovery->driver->employee->surname : ""}}</td>
                                    
                                    <td>{{$recovery->deduction ? $recovery->deduction->name : ""}} | {{$recovery->type}}</td>
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
                                                <li><a href="{{route('recoveries.show',$recovery->id)}}"  ><i class="fa fa-eye color-default"></i>View</a></li>
                                                @if ($recovery->authorization == "approved")
                                                <li><a href="#" wire:click="showPayment({{$recovery->id}})"  ><i class="fas fa-credit-card color-primary"></i> Record Payment</a></li>
                                                @endif
                                                @if ($recovery->payments->count()>0)
                                                @else 
                                                <li><a href="{{route('recoveries.edit',$recovery->id)}}" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                @endif
                                                <li><a href="#" data-toggle="modal" data-target="#recoveryDeleteModal{{ $recovery->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('recoveries.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="11">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Recoveries Found ....
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
                                    @if (isset($recoveries))
                                        {{ $recoveries->links() }} 
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
</div>
