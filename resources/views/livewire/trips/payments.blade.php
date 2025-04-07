<div>
   
    <table id="paymentsTable" class="table  table-spaymented table-bordered table-sm table-responsive" cellspacing="0" width="100%" style=" width:100%; height:100%;">
        <thead >
            <th class="th-sm">Payment#
            </th>
            <th class="th-sm">Trip#
            </th>
            <th class="th-sm">Customer
            </th>
            <th class="th-sm">PaidBy
            </th>
            <th class="th-sm">MOP
            </th>
            <th class="th-sm">Currency
            </th>
            <th class="th-sm">Amount
            </th>
            <th class="th-sm">Balance
            </th>
            <th class="th-sm">Actions
            </th>

          </tr>
        </thead>
   
        @if ($payments->count()>0)
        <tbody>
            @foreach ($payments as $payment)
          <tr>
            
            <td>{{ucfirst($payment->payment_number)}}</td>
            <td><a href="{{ route('trips.show',$payment->trip_id) }}" style="color:blue">{{$payment->trip ? $payment->trip->trip_number : ""}}</a></td>
            <td>{{ucfirst($payment->customer ? $payment->customer->name : "")}} {{ucfirst($payment->customer ? $payment->customer->surname : "")}}</td>
            <td>{{ucfirst($payment->name)}} {{ucfirst($payment->surname)}}</td>
            <td>{{$payment->mode_of_payment}}</td>
            <td>{{$payment->currency ? $payment->currency->name : ""}}</td>
            <td>
              {{$payment->currency ? $payment->currency->symbol : ""}}@if ($payment->amount)
                {{number_format($payment->amount,2)}}
              @endif
            </td>
            <td>{{$payment->balance}}</td>
             <td class="w-10 line-height-35 table-dropdown">
                <div class="dropdown">
                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-bars"></i>
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="{{route('payments.show', $payment->id)}}"><i class="fas fa-eye color-default"></i>View</a></li>
                    </ul>
                </div>
         

        </td>
          </tr>
          @endforeach
        </tbody>
        @else
        <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
        @endif
      

      </table>


    </div>
