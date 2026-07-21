<div>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
      
        <table class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead >
              <tr>
                <th class="th-sm">Product
                </th>
                <th class="th-sm">SN#
                </th>
                <th class="th-sm text-right">Qty
                </th>
                <th class="th-sm">Currency
                </th>
                <th class="th-sm text-right">Unit Cost
                </th>
                <th class="th-sm text-right">Subtotal
                </th>
              </tr>
            </thead>
            <tbody>
            @if (isset($items))
              @forelse ($items as $item)
                <tr>
                  <td>{{$item->product->brand ? $item->product->brand->name : ""}} {{$item->product ? $item->product->name : ""}}</td>
                  <td>{{$item->serial_number ? "SN#: ".$item->serial_number : ""}} {{$item->product->identification_number ? "ID#: ".$item->product->identification_number : ""}}</td>
                  <td class="text-right">{{number_format($item->qty ?? 1, 2)}}</td>
                  <td>{{$item->currency ? $item->currency->name : ""}}</td>
                  <td class="text-right">{{$item->currency ? $item->currency->symbol : ""}}{{number_format($item->amount ?? 0, 2)}}</td>
                  <td class="text-right">{{$item->currency ? $item->currency->symbol : ""}}{{number_format($item->subtotal ?? 0, 2)}}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="6">
                      <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                          No Items Found ....
                      </div>

                  </td>
                </tr>
              @endforelse
            </tbody>
            @if (isset($items) && $items->isNotEmpty())
            <tfoot>
              <tr>
                <th colspan="5" class="text-right">Total</th>
                <th class="text-right">{{number_format($items->sum('subtotal'), 2)}}</th>
              </tr>
            </tfoot>
            @endif
            @else
                <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
             @endif
          </table>

         
        
         
    
   
</div>
