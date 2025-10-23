<div>
    <section class="section">
        <x-loading/>
        <div class="transfer-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div>
                                @include('includes.messages')
                            </div>
                         
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search fueling stations...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">InitiatedBy
                                    </th>
                                    <th class="th-sm">From
                                    </th>
                                    <th class="th-sm">To
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">Quantity(l)
                                    </th>
                                    <th class="th-sm">Reason
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($transfers))
                                <tbody>
                                    @forelse ($transfers as $transfer)
                                  <tr>
                                    @php
                                        $from = App\Models\Container::find($transfer->from);
                                        $to = App\Models\Container::find($transfer->to);
                                    @endphp
                                    <td>{{$transfer->user ? $transfer->user->name : ""}} {{$transfer->user ? $transfer->user->surname : ""}}</td>
                                    <td>{{$from ? $from->name : ""}}</td>
                                    <td>{{$to ? $to->name : ""}}</td>
                                    <td>{{$transfer->date}}</td>
                                    <td>{{$transfer->quantity}}</td>
                                    <td>{{$transfer->comments}}</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                            </ul>
                                        </div>
                                       
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="7">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                           No Fuel Transfers Found ....
                                        </div>
                                       
                                    </td>
                                  </tr>
                                  @endforelse
                                </tbody>
                               
                                 @endif
                              </table>
                              <nav class="text-center" style="float: right">
                                <ul class="pagination rounded-corners">
                                    @if (isset($transfers))
                                        {{ $transfers->links() }} 
                                    @endif 
                                </ul>
                            </nav>   
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.transfer-fluid -->
    </section>




</div>

