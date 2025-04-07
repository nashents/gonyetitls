<div>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
        <x-loading/>
        {{-- <a href="" data-toggle="modal" data-target="#addTransporterModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Transporter</a> --}}
        <br>
        <br>
        <table id="bill_expensesTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
          <thead>
            <tr>

              <th class="th-sm">Bill#
              </th>
              <th class="th-sm">Category
              </th>
              <th class="th-sm">Date
              </th>
              <th class="th-sm">Currency
              </th>
              <th class="th-sm">Total
              </th>
              <th class="th-sm">Amount Due
              </th>
              <th class="th-sm">Payment Status
              </th>
             
            </tr>
          </thead>
          @if ($bill_expenses->count()>0)
          <tbody>
              @foreach ($bill_expenses as $bill_expense)
              @if ($bill_expense->bill)
            <tr>
              <td>{{$bill_expense->bill ? $bill_expense->bill->bill_number : ""}}</td>
              <td>
                @if ($bill_expense->bill->transporter)
                    Transporter | <a href="{{ route('transporters.show',$bill_expense->bill->transporter->id) }}" style="color: blue" target="_blank">{{ $bill_expense->bill->transporter ? $bill_expense->bill->transporter->name  : ""}}</a> 
                @elseif($bill_expense->bill->vendor && ($bill_expense->bill->container == NULL || $bill_expense->bill->top_up == NULL))
                    Vendor | <a href="{{ route('vendors.show',$bill_expense->bill->vendor->id) }}" style="color: blue" target="_blank">{{ $bill_expense->bill->vendor ? $bill_expense->bill->vendor->name : "" }}</a> 
                @elseif ( $bill_expense->bill->container && $bill_expense->bill->top_up)
                    Fuel Topup | <a href="{{ route('containers.show', $bill_expense->bill->container->id) }}" style="color: blue" target="_blank">{{ $bill_expense->bill->container ? $bill_expense->bill->container->name : "" }}</a> 
                @elseif ( $bill_expense->bill->fuel)
                    @if ($bill_expense->bill->trip)
                    Trip Expense - Fuel Order | <a href="{{ route('fuels.show', $bill_expense->bill->fuel->id) }}" style="color: blue" target="_blank">{{ $bill_expense->bill->fuel ? $bill_expense->bill->fuel->order_number : "" }}</a> | <a href="{{ route('trips.show', $bill_expense->bill->trip->id) }}" style="color: blue" target="_blank">{{ $bill_expense->bill->trip->trip_number }}</a> 
                    @else
                    Fuel Order | <a href="{{ route('fuels.show', $bill_expense->bill->fuel->id) }}" style="color: blue" target="_blank">{{ $bill_expense->bill->fuel ? $bill_expense->bill->fuel->order_number : "" }}</a> 
                    @endif
                   
                @elseif ( $bill_expense->bill->invoice)
                    Invoice VAT | <a href="{{ route('invoices.show', $bill_expense->bill->invoice->id) }}" style="color: blue" target="_blank">{{ $bill_expense->bill->invoice ? $bill_expense->bill->invoice->invoice_number : "" }}</a> 
                @elseif ( $bill_expense->bill->ticket)
                    Ticket | <a href="{{ route('tickets.show', $bill_expense->bill->ticket->id) }}" style="color: blue" target="_blank">{{  $bill_expense->bill->ticket ? $bill_expense->bill->ticket->ticket_number : "" }}</a> 
                @elseif ($bill_expense->bill->trip && ($bill_expense->bill->horse || $bill_expense->bill->driver || $bill_expense->bill->driver))
                    Trip Expense | <a href="{{ route('trips.show', $bill_expense->bill->trip->id) }}" style="color: blue" target="_blank">{{ $bill_expense->bill->trip->trip_number }}</a> 
                @elseif ($bill_expense->bill->purchase)
                    {{ $bill_expense->bill->category }} | <a href="{{ route('purchases.show', $bill_expense->bill->purchase->id) }}" style="color: blue" target="_blank">{{ $bill_expense->bill->purchase->purchase_number }}</a> 
                @elseif ($bill_expense->bill->workshop_service)
                    Service | <a href="{{ route('workshop_services.show', $bill_expense->bill->workshop_service->id) }}" style="color: blue" target="_blank">{{ $bill_expense->bill->workshop_service->workshop_service_number }}</a> 
                @endif
            </td>
              <td>{{$bill_expense->bill_expense_date}}</td>
              <td>{{$bill_expense->currency ? $bill_expense->currency->name : ""}}</td> 
              <td>
                  @if ($bill_expense->bill->total)
                       {{$bill_expense->bill->currency ? $bill_expense->bill->currency->symbol : ""}}{{number_format($bill_expense->bill->total,2)}}
                  @endif
              </td>
              <td>
                  {{-- @if ($bill_expense->balance) --}}
                       {{$bill_expense->bill->currency ? $bill_expense->bill->currency->symbol : ""}}{{number_format($bill_expense->bill->balance,2)}}
                  {{-- @endif --}}
              </td>
              <td><span class="label label-{{($bill_expense->bill->status == 'Paid') ? 'success' : (($bill_expense->bill->status == 'Partial') ? 'warning' : 'danger') }}">{{ $bill_expense->bill->status }}</span></td>
             
            </tr>
            @endif
            @endforeach
          </tbody>
          @else
              <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
           @endif
        </table>

         
        

        
</div>
