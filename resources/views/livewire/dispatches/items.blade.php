<div>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
        <x-loading/>
      
        <table id="itemsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead>
              <tr>
                <th class="th-sm">Item
                </th>
                <th class="th-sm">Qty
                </th>
              </tr>
            </thead>
            @if ($items->count()>0)
            <tbody>
                @foreach ($items as $item)
              <tr>
                <td>
                   
                    {{$item->product ? $item->product->product_number : ""}} {{$item->product ? $item->product->name : ""}} {{$item->product->brand ? $item->product->brand->name : ""}}
                    @if ($item->inventory)
                        {{$item->inventory ? $item->inventory->serial_number : ""}}
                    @elseif($item->tyre)
                        {{$item->tyre ? $item->tyre->serial_number : ""}}
                    @elseif($item->asset)
                        {{$item->asset ? $item->asset->serial_number : ""}}
                    @endif
                </td>
                <td>{{$item->qty}}</td>
              </tr>
              @endforeach
            </tbody>
            @else
                <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
             @endif
          </table>

</div>
