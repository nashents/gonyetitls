<div>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
        <x-loading/>
      
        <table id="itemsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead>
              <tr>
                <th class="th-sm">Equipment
                </th>
                <th class="th-sm">Product
                </th>
                <th class="th-sm">Mileage
                </th>
                <th class="th-sm">Qty
                </th>
                <th class="th-sm">Position
                </th>
              </tr>
            </thead>
            @if ($movements->count()>0)
            <tbody>
                @foreach ($movements as $movement)
              <tr>
                <td>
                    @if ($movement->ticket?->horse_id)
                        {{$movement->ticket?->horse->registration_number}} {{$movement->ticket?->horse?->fleet_number ? "(".$movement->ticket?->horse?->fleet_number.")" : ""}} {{$movement->ticket?->horse?->horse_make?->name}} {{$movement->ticket?->horse?->horse_model?->name}}
                    @elseif ($movement->ticket?->vehicle_id)
                        {{$movement->ticket?->vehicle->registration_number}} {{$movement->ticket?->vehicle?->fleet_number ? "(".$movement->ticket?->vehicle?->fleet_number.")" : ""}} {{$movement->ticket?->vehicle?->vehicle_make?->name}} {{$movement->ticket?->vehicle?->vehicle_model?->name}}
                    @elseif ($movement->ticket?->trailer_id)
                        {{$movement->ticket?->trailer->registration_number}} {{$movement->ticket?->trailer?->fleet_number ? "(".$movement->ticket?->trailer?->fleet_number.")" : ""}} {{$movement->ticket?->trailer?->trailer_make?->name}} {{$movement->ticket?->trailer?->trailer_model?->name}}
                    @endif
                </td>
                <td>
                    {{$movement->product ? $movement->product->product_number : ""}} {{$movement->product ? $movement->product->name : ""}} {{$movement->product->brand ? $movement->product->brand->name : ""}}
                </td>
                <td>{{$movement->mileage_moved}}</td>
                <td>{{$movement->qty}}</td>
                <td>{{$movement->position}}</td>
              </tr>
              @endforeach
            </tbody>
            @else
                <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
             @endif
          </table>

</div>
