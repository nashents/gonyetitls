<div>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
      
        <table class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead >
                <th class="th-sm">Product
                </th>
                <th class="th-sm">SN#
                </th>
              </tr>
            </thead>
            <tbody>
            @if (isset($items))
              @forelse ($items as $item)
                <tr>
                  <td>{{$item->product->brand ? $item->product->brand->name : ""}} {{$item->product ? $item->product->name : ""}}</td>
                  <td>{{$item->serial_number ? "SN#: ".$item->serial_number : ""}} {{$item->product->identification_number ? "ID#: ".$item->product->identification_number : ""}}</td> 
                </tr>
              @empty
                <tr>
                  <td colspan="2">
                      <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                          No Items Found ....
                      </div>
                      
                  </td>
                </tr>  
              @endforelse
            </tbody>
            @else
                <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
             @endif
          </table>

         
        
         
    
   
</div>
