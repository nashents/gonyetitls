<div>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
        <x-loading/>
        <table id="sale_itemsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead>
              <tr>
                <th class="th-sm">Sale#
                </th>
                <th class="th-sm">Item
                </th>
                <th class="th-sm">Qty
                </th>
                <th class="th-sm">Ccy
                </th>
                <th class="th-sm">Rate
                </th>
                <th class="th-sm">Subtotal(Excl)
                </th>
                <th class="th-sm">Tax Amt
                </th>
                <th class="th-sm">Subtotal(Incl)
                </th>
                <th class="th-sm">Action
                </th>
              </tr>
            </thead>
            @if ($sale_items->count()>0)
            <tbody>
                @foreach ($sale_items as $sale_item)
              <tr>
                <td>{{$sale_item->sale->sale_number}}</td>
                <td>
                  <strong>{{ucfirst($sale_item->product ? $sale_item->product->name : "")}}</strong>  
                  <br>
                  {{ucfirst($sale_item->product ? $sale_item->product->description : "")}}
                </td>
                <td>{{$sale_item->qty}}</td>
                <td>{{$sale_item->sale->currency->name}}</td>
                <td>
                    @if ($sale_item->amount)
                    {{$sale_item->sale->currency->symbol}}{{number_format($sale_item->amount,2)}}        
                    @endif
                </td>
                <td>
                    @if ($sale_item->subtotal)
                    {{$sale_item->sale->currency->symbol}}{{number_format($sale_item->subtotal,2)}}
                    @endif
                </td>
                <td>
                    @if ($sale_item->tax_amount)
                    {{$sale_item->sale->currency->symbol}}{{number_format($sale_item->tax_amount,2)}}
                    @endif
                </td>
                <td>
                    @if ($sale_item->subtotal_incl)
                    {{$sale_item->sale->currency->symbol}}{{number_format($sale_item->subtotal_incl,2)}}
                    @endif
                </td>
                
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
              @endforeach
            </tbody>
            @else
                <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
             @endif
          </table>  
   
</div>
