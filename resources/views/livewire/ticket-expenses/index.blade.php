<div>
  
    <table  class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
        <thead >
         <tr>
            <th class="th-sm">Vendor
            </th>
            <th class="th-sm">Item
            </th>
            <th class="th-sm">MOP
            </th>
            <th class="th-sm">Ccy
            </th>
            <th class="th-sm">Qty
            </th>
            <th class="th-sm">Amt
            </th>
            <th class="th-sm">Subtotal
            </th>
            <th class="th-sm">Tax Amt
            </th>
            <th class="th-sm">Total
            </th>
          
          </tr>
        </thead>
        @if (isset($ticket_expenses))
        <tbody>
           @forelse ($ticket_expenses as  $ticket_expense)
            <tr>
                <td>
                    {{$ticket_expense->vendor ? $ticket_expense->vendor->name : ""}}
                </td>  
                <td>
                    {{$ticket_expense->product ? $ticket_expense->product->product_number : ""}} {{$ticket_expense->product ? $ticket_expense->product->name : ""}}
                </td>  
                <td>{{$ticket_expense->payment_method ? $ticket_expense->payment_method->name : ""}}</td>
                <td>{{$ticket_expense->currency ? $ticket_expense->currency->name : ""}}</td>
                <td>{{$ticket_expense->qty}}</td>
                <td>{{$ticket_expense->currency ? $ticket_expense->currency->symbol : ""}}{{number_format($ticket_expense->amount,2)}}</td>
                <td>{{$ticket_expense->currency ? $ticket_expense->currency->symbol : ""}}{{number_format($ticket_expense->subtotal,2)}}</td> 
                <td>{{$ticket_expense->currency ? $ticket_expense->currency->symbol : ""}}{{number_format($ticket_expense->tax_amount ? $ticket_expense->tax_amount : 0,2)}}</td> 
                <td>{{$ticket_expense->currency ? $ticket_expense->currency->symbol : ""}}{{number_format($ticket_expense->subtotal_incl,2)}}</td> 
               
            </tr>
            @empty
                <tr>
                <td colspan="10">
                    <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                        No Additional Expenses Found ....
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
            @if (isset($ticket_expenses))
                @if ($ticket_expenses->count()>0)
                    {{ $ticket_expenses->links() }} 
                @endif
            @endif 
        </ul>
    </nav>
    
</div>
