<div>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
      
        <table id="itemsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead >
                <th class="th-sm">Product
                </th>
                <th class="th-sm">SN#
                </th>
              </tr>
            </thead>
            <tbody>
                @if ($items->count()>0)
                @foreach ($items as $item)
              <tr>
                <td>{{$item->product->brand ? $item->product->brand->name : ""}} {{$item->product ? $item->product->name : ""}}</td>
                <td>{{$item->serial_number ? "SN#: ".$item->serial_number : ""}} {{$item->product->identification_number ? "ID#: ".$item->product->identification_number : ""}}</td> 
            </tr>
              @endforeach
            </tbody>
            @else
                <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
             @endif
          </table>

         
        
         
    
   
</div>
