<div>
    <div class="row mt-30">
        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1">
            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">TopUp Details</a></li>
            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                <table class="table table-striped">
                    <tbody class="text-center line-height-35 ">
                       
                        @if ($top_up->container)
                        <tr>
                            <th class="w-10 text-center line-height-35">Fueling Station</th>
                            <td class="w-20 line-height-35">{{$top_up->container ? $top_up->container->name : ""}}</td>
                        </tr>
                        @endif
                        <tr>
                            <th class="w-10 text-center line-height-35">Top Up Date</th>
                            <td class="w-20 line-height-35">{{$top_up->date}}</td>
                        </tr>
                       
                        <tr>
                            <th class="w-10 text-center line-height-35">Currency</th>
                            <td class="w-20 line-height-35">{{$top_up->currency ? $top_up->currency->name : ""}}</td>
                        </tr>
                        @if ($top_up->exchange_rate)
                        <tr>
                            <th class="w-10 text-center line-height-35">Currency conversion:</th>
                            <td class="w-20 line-height-35"> {{ Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->name : "" }} {{ Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->symbol : "" }}{{ number_format($top_up->exchange_amount,2)}} at {{ $top_up->exchange_rate}} </td>
                        </tr> 
                        @endif
                     
                        <tr>
                            <th class="w-10 text-center line-height-35">Total</th>
                            <td class="w-20 line-height-35">
                                @if ($top_up->amount)
                                {{$top_up->currency ? $top_up->currency->symbol : ""}}{{number_format($top_up->amount,2)}}  
                                @endif
                              </td>
                        </tr>
                        @if ($top_up->exchange_amount)
                        <tr>
                            <th class="w-10 text-center line-height-35">Total in {{ Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->name : "" }}</th>
                            <td class="w-20 line-height-35">{{ Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->symbol : "" }}{{$top_up->exchange_amount}}</td>
                        </tr> 
                        @endif
                     
                        <tr>
                            <th class="w-10 text-center line-height-35">Authorization</th>
                            <td class="w-20 line-height-35"><span class="badge bg-{{($top_up->authorization == 'approved') ? 'success' : (($top_up->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($top_up->authorization == 'approved') ? 'approved' : (($top_up->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                        </tr>
                        @php
                        $user = App\Models\User::find($top_up->authorized_by_id);
                        @endphp
                        @if (isset($user))
                            <tr>
                                <th class="w-10 text-center line-height-35">Authorized By</th>
                                <td class="w-20 line-height-35">
                                    {{ $user->name }} {{ $user->surname }}
                                </td>
                            </tr>
                        @endif
                        
                        @if ($top_up->reason)
                        <tr>
                            <th class="w-10 text-center line-height-35">Authorization Comments</th>
                            <td class="w-20 line-height-35">{{ $top_up->comments }}</td>
                        </tr>
                        @endif
    
                    </tbody>
                </table>
                </div>
                <div class="row">
                    <div class="col-md-12" >
                        <div class="btn-group" >
                           <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded" style="float:right;"><i class="fa fa-arrow-left"></i>Back</a>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        <!-- /.col-md-9 -->
    </div>


</div>
