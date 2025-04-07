<div>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
        <x-loading/>
        {{-- <a href="" data-toggle="modal" data-target="#injuryModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>injury</a> --}}
        <br>
        <br>
        <table id="injuriesTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead >
                <th class="th-sm">Name
                </th>
                <th class="th-sm">Taken To
                </th>
                <th class="th-sm">Body Part
                </th>
                <th class="th-sm">Days Lost
                </th>
                <th class="th-sm">Nature of Injury
                </th>
                <th class="th-sm">Object Inflicting Harm
                </th>
                <th class="th-sm">Actions
                </th>
              </tr>
            </thead>
            <tbody>
                @if (isset($injuries))
                @if ($injuries->count() > 0)
                @foreach ($injuries as $injury)
              <tr>
                <td>{{$injury->name}}</td>
                <td>{{$injury->taken_to}}</td>
                <td>{{$injury->body_part}}</td>
                <td>{{$injury->days_lost}}</td>
                <td>{{$injury->nature_of_injury}}</td>
                <td>{{$injury->object}}</td>
                <td class="w-10 line-height-35 table-dropdown">
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-bars"></i>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            {{-- <li><a href="#" wire:click="edit({{$injury->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                            <li><a href="#" data-toggle="modal" data-target="#injuryDeleteModal{{$injury->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li> --}}
                        </ul>
                    </div>
                    {{-- @include('injuries.delete') --}}

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
        $('#injuriesTable').DataTable();
    } );
    </script>
 
@endsection

</div>
