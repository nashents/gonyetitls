<div>
    <table id="paymentsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
        <thead>
          <tr>
            <th class="th-sm">Date
            </th>
            <th class="th-sm">Description
            </th>
            <th class="th-sm">Type
            </th>
            <th class="th-sm">Category
            </th>
            <th class="th-sm">Currency
            </th>
            <th class="th-sm">Amount
            </th>
            <th class="th-sm">Action
            </th>
          </tr>
        </thead>
        @if ($payments->count()>0)
        <tbody>
            @foreach ($payments as $payment)
          <tr>
            <td>{{$payment->date}}</td>
            <td>
                                       
              @if ($payment->invoice)
                  {{$payment->customer ? $payment->customer->name : ""}} Payment for invoice# <a href="{{route('invoices.show',$payment->invoice->id)}}" style="color: blue">{{$payment->invoice ? $payment->invoice->invoice_number : ""}}</a> <br>
              @elseif($payment->bill)
              Bill# <a href="{{route('bills.show',$payment->bill->id)}}" style="color: blue">{{$payment->bill ? $payment->bill->bill_number : ""}}</a> Payment to {{$payment->vendor ? $payment->vendor->name : ""}} <br>
              @endif
              @if ($payment->description)
              {{$payment->description}}
          @endif
          </td>
            <td>{{$payment->transaction_type ? $payment->transaction_type->name : ""}}</td>
            <td>
              @if ($payment->transaction_category)
              {{$payment->transaction_category}}
              @elseif($payment->invoice)
              Invoice# <a href="{{route('invoices.show',$payment->invoice->id)}}" style="color: blue">{{$payment->invoice ? $payment->invoice->invoice_number : ""}}</a> | Payment from {{$payment->customer ? $payment->customer->name : ""}}<br>
              @elseif($payment->bill)
              Bill# <a href="{{route('bills.show',$payment->bill->id)}}" style="color: blue">{{$payment->bill ? $payment->bill->bill_number : ""}}</a> Payment to {{$payment->vendor ? $payment->vendor->name : ""}} <br>
              @endif
             </td>
            <td>{{$payment->currency ? $payment->currency->name : ""}}</td>
            <td>
                @if (isset($payment->amount))
                {{$payment->currency ? $payment->currency->symbol : ""}}{{number_format($payment->amount,2)}}
                @endif
            </td>
            <td class="w-10 line-height-35 table-dropdown">
                <div class="dropdown">
                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-bars"></i>
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="{{route('payments.show',$payment->id)}}"  ><i class="fa fa-eye color-default"></i> View</a></li>
                       {{-- <li><a href="#" wire:click="update({{$payment->id}})" ><i class="fas fa-paperclip color-success"></i> Invoice/Receipt</a></li>
                        <li><a href="#" data-toggle="modal" data-target="#paymentDeleteModal{{ $payment->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li> --}}
                    </ul>
                </div>
                @include('payments.delete')
        </td>
          </tr>
          @endforeach
        </tbody>
        @else
            <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
         @endif
      </table>
</div>
