<div>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
        <x-loading/>
        {{-- <a href="" data-toggle="modal" data-target="#damageModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>damage</a> --}}
        <br>
        <br>
        <table id="damagesTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead >
                <th class="th-sm">Damage
                </th>
                <th class="th-sm">Nature of damage
                </th>
                <th class="th-sm">Currency
                </th>
                <th class="th-sm">Estimated
                </th>
                <th class="th-sm">Actual
                </th>
                <th class="th-sm">Object Inflicting Harm
                </th>
                <th class="th-sm">Actions
                </th>
              </tr>
            </thead>
            <tbody>
                @if (isset($damages))
                @if ($damages->count() > 0)
                @foreach ($damages as $damage)
              <tr>
                <td>{{$damage->damage}}</td>
                <td>{{$damage->nature_of_damage}}</td>
                <td>{{$damage->currency ? $damage->currency->name : ""}}</td>
                <td>
                    @if ($damage->estimated_cost)
                        {{$damage->currency ? $damage->currency->symbol : ""}}{{number_format($damage->estimated_cost,2)}}
                    @endif
                </td>
                <td>
                    @if ($damage->actual_cost)
                        {{$damage->currency ? $damage->currency->symbol : ""}}{{number_format($damage->actual_cost,2)}}
                    @endif
                </td>
                <td>{{$damage->object}}</td>
                <td class="w-10 line-height-35 table-dropdown">
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-bars"></i>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            {{-- <li><a href="#" wire:click="edit({{$damage->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                            <li><a href="#" data-toggle="modal" data-target="#damageDeleteModal{{$damage->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li> --}}
                        </ul>
                    </div>
                    {{-- @include('damages.delete') --}}

            </td>
            </tr>
              @endforeach
              @else
              <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
              @endif
              @else
              {{-- <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt=""> --}}
              @endif
            </tbody>
          </table>
    {{-- </blockquote> --}}
   
   
    @section('extra-js')
    <script>
    $(document).ready( function () {
        $('#damagesTable').DataTable();
    } );
    </script>
 
@endsection

</div>
