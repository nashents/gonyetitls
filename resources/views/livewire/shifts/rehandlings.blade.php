<div>
    <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
        <thead>
          <tr>
            <th class="th-sm">Start Time
            </th>
            <th class="th-sm">Open Hours
            </th>
            <th class="th-sm">Open Mileage
            </th>
            <th class="th-sm">Work Description & Location
            </th>
            <th class="th-sm">Stop Time
            </th>
            <th class="th-sm">Close Hours
            </th>
            <th class="th-sm">Close Mileage
            </th>
            <th class="th-sm">Action
            </th>
          </tr>
        </thead>
        @if (isset($rehandlings))
        <tbody>
            @forelse ($rehandlings as $rehanding)
          <tr>
            <td>{{$rehanding->start_time}}</td>
            <td>{{$rehanding->open_hours ? $rehanding->open_hours : " Hours"}}</td>
            <td>{{$rehanding->open_mileage ? $rehanding->open_mileage." Kms" : ""}}</td>
            <td>
                {{$rehanding->work ? $rehanding->work->description : ""}}
                @if ($rehandling->location)
                    @ {{$rehandling->location ? $rehandling->location->name : ""}}
                @endif
            </td>
              <td>{{$rehanding->stop_time}}</td>
              <td>{{$rehanding->close_hours ? $rehanding->close_hours." Hours" : ""}}</td>
              <td>{{$rehanding->close_mileage ? $rehanding->close_mileage." Kms" : ""}}</td>
            <td class="w-10 line-height-35 table-dropdown">
                <div class="dropdown">
                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-bars"></i>
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="#" wire:click="edit({{$rehanding->id}})"  ><i class="fa fa-exchange color-success"></i> Edit</a></li>
                    </ul>
                </div>
                @include('rehandlings.delete')
        </td>
          </tr>
          @empty
          <tr>
            <td colspan="9">
                <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                    No rehandling work found ....
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
            @if (isset($rehandlings))
                {{ $rehandlings->links() }} 
            @endif 
        </ul>
    </nav>     
</div>
