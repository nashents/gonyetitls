<div>
    <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
        <thead>
          <tr>
            <th class="th-sm">Tyre#
            </th>
            <th class="th-sm">Date
            </th>
            <th class="th-sm">Product
            </th>
            <th class="th-sm">Type
            </th>
            <th class="th-sm">Serial#
            </th>
            <th class="th-sm">Specifications
            </th>
            <th class="th-sm">Currency
            </th>
            <th class="th-sm">Price
            </th>
            <th class="th-sm">Status
            </th>
            <th class="th-sm">Action
            </th>
          </tr>
        </thead>
        @if (isset($tyres))
        <tbody>
            @forelse ($tyres as $tyre)
          <tr>
            <td>{{$tyre->tyre_number}}</td>
            <td>{{$tyre->purchase_date}}</td>
            <td>{{$tyre->product->brand ? $tyre->product->brand->name : ""}} {{$tyre->product ? $tyre->product->name : ""}}</td>
            <td>{{$tyre->type}}</td>
            <td>{{$tyre->serial_number}}</td>
            <td>{{$tyre->width}} / {{$tyre->aspect_ratio}} R {{$tyre->diameter}}</td>
            <td>{{$tyre->currency ? $tyre->currency->name : ""}}</td>
            <td>{{$tyre->currency ? $tyre->currency->symbol : ""}}{{number_format($tyre->rate,2)}}</td>
            <td>
                @if ($tyre->status == 0 && isset($tyre->tyre_assignment))
                <a href="{{route('tyre_assignments.show',$tyre->tyre_assignment->id)}}">
                    <span class="badge bg-{{$tyre->status == 1 ? "warning" : "success"}}">{{$tyre->status == 1 ? "Unassigned" : "Assigned"}}</span>        
                </a>
               @else
               <span class="badge bg-{{$tyre->status == 1 ? "warning" : "success"}}">{{$tyre->status == 1 ? "Unassigned" : "Assigned"}}</span>        
                @endif
            </td>
            <td class="w-10 line-height-35 table-dropdown">
                <div class="dropdown">
                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-bars"></i>
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="{{ route('tyres.show',$tyre->id) }}" ><i class="fa fa-eye color-default"></i>View</a></li>
                        <li><a href="#"  ><i class="fa fa-exchange color-success"></i> Transfer</a></li>
                      </ul>
                </div>
        </td>
          </tr>
          @empty
          <tr>
            <td colspan="10">
                <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                    No tyres from store found ....
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
            @if (isset($tyres))
                {{ $tyres->links() }} 
            @endif 
        </ul>
    </nav>    
</div>
