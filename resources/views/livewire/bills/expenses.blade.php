<div>
    <table  class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
        <thead >
            <tr>
                <th class="th-sm">Category
                </th>
                <th class="th-sm">Item
                </th>
                <th class="th-sm">Description
                </th>
                <th class="th-sm">Qty
                </th>
                <th class="th-sm">Price
                </th>
                <th class="th-sm">Subtotal(Excl)
                </th>
                <th class="th-sm">Tax Amt
                </th>
                <th class="th-sm">Subtotal(Incl)
                </th>
            </tr>
        </thead>
        <tbody>
            @if (isset($bill_expenses))
                @forelse ($bill_expenses as $bill_expense)
                    <tr>
                        <td>
                            @if ($bill_expense->account)
                                {{$bill_expense->account ? $bill_expense->account->name : ""}}
                            @endif
                        </td>
                        <td>
                            @if ($bill_expense->expense)
                                {{ $bill_expense->expense ? $bill_expense->expense->name : ""}}
                            @elseif($bill_expense->product)
                                {{ $bill_expense->product->brand ? $bill_expense->product->brand->name : ""}} {{ $bill_expense->product ? $bill_expense->product->name : ""}}
                            @endif
                        </td>
                        <td>{{ $bill_expense->description}}</td>
                        <td>{{ $bill_expense->qty}}</td>
                        <td>
                            @if ($bill_expense->amount)
                                {{ $bill_expense->currency ? $bill_expense->currency->symbol : ""}}{{ number_format($bill_expense->amount,2)}}
                            @endif
                        </td>
                        <td>
                            @if ($bill_expense->subtotal)
                                {{ $bill_expense->currency ? $bill_expense->currency->symbol : ""}}{{ number_format($bill_expense->subtotal,2)}}
                            @endif
                        </td>
                        <td>
                            @if ($bill_expense->tax_amount)
                                {{ $bill_expense->currency ? $bill_expense->currency->symbol : ""}}{{ number_format($bill_expense->tax_amount,2)}}
                            @endif
                        </td>
                        <td>
                            @if ($bill_expense->subtotal_incl)
                                {{ $bill_expense->currency ? $bill_expense->currency->symbol : ""}}{{ number_format($bill_expense->subtotal_incl,2)}}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                No Bill Items Recorded....
                            </div> 
                        </td>
                    </tr>  
                @endforelse
            @else
                <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
            @endif
        </tbody>
    </table>
    <nav class="text-center" style="float: right">
        <ul class="pagination rounded-corners">
            @if (isset($bill_expenses))
                {{ $bill_expenses->links() }} 
            @endif 
        </ul>
    </nav>    
</div>
