<div>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
        <x-loading/>
        <table id="quotation_itemsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead>
              <tr>
                <th class="th-sm">quotation#
                </th>
                <th class="th-sm">Item
                </th>
                <th class="th-sm">Qty
                </th>
                <th class="th-sm">Ccy
                </th>
                <th class="th-sm">Rate
                </th>
                <th class="th-sm">Subtotal
                </th>
                <th class="th-sm">Tax Amt
                </th>
                <th class="th-sm">Subtotal(Incl)
                </th>
             
              </tr>
            </thead>
            @if ($quotation_items->count()>0)
            <tbody>
                @foreach ($quotation_items as $quotation_item)
              <tr>
                <td>{{$quotation_item->quotation->quotation_number}}</td>
                <td>
                    @if ($quotation_item->product)
                        <strong>{{$quotation_item->product ? $quotation_item->product->name : ""}} {{$quotation_item->product ? $quotation_item->product->identification_number : ""}} {{$quotation_item->inventory ? $quotation_item->inventory->serial_number : ""}}</strong>  
                        <br>
                    @elseif($quotation_item->trip)  
                        <strong>{{$quotation_item->trip ? $quotation_item->trip->trip_number : ""}}</strong>  
                        <br>
                    @endif
                  {{$quotation_item->description}}
                </td>
                <td>{{$quotation_item->qty}}</td>
                <td>{{$quotation_item->quotation->currency->name}}</td>
                <td>
                    @if ($quotation_item->amount)
                    {{$quotation_item->quotation->currency->symbol}}{{number_format($quotation_item->amount,2)}}        
                    @endif
                </td>
                <td>
                    @if ($quotation_item->subtotal)
                    {{$quotation_item->quotation->currency->symbol}}{{number_format($quotation_item->subtotal,2)}}
                    @endif
                </td>
                <td>
                    {{$quotation_item->quotation->currency->symbol}}{{number_format($quotation_item->tax_amount ? $quotation_item->tax_amount : 0,2)}}
                </td>
                <td>
                    @if ($quotation_item->subtotal_incl)
                    {{$quotation_item->quotation->currency->symbol}}{{number_format($quotation_item->subtotal_incl,2)}}
                    @endif
                </td>
                
               
              </tr>
              @endforeach
            </tbody>
            @else
                <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
             @endif
          </table>
     
</div>
