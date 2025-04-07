<div>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
        <x-loading/>
        {{-- <a href="" data-toggle="modal" data-target="#otherModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>other</a> --}}
        <br>
        <br>
        <table id="othersTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead >
                <th class="th-sm">Type
                </th>
                <th class="th-sm">Currency
                </th>
                <th class="th-sm">Cost
                </th>
                <th class="th-sm">Nature of loss
                </th>
                <th class="th-sm">Object Inflicting Harm
                </th>
                <th class="th-sm">Actions
                </th>
              </tr>
            </thead>
            <tbody>
                @if (isset($others))
                @if ($others->count() > 0)
                @foreach ($others as $other)
              <tr>
                <td>{{$other->type}}</td>
                <td>{{$other->currency ? $other->currency->name : ""}}</td>
                <td>
                    @if ($other->cost)
                        {{$other->currency ? $other->currency->symbol : ""}}{{number_format($other->cost,2)}}
                    @endif
                </td>
                <td>{{$other->nature_of_loss}}</td>
                <td>{{$other->object_other}}</td>
                <td class="w-10 line-height-35 table-dropdown">
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-bars"></i>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            {{-- <li><a href="#" wire:click="edit({{$other->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                            <li><a href="#" data-toggle="modal" data-target="#otherDeleteModal{{$other->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li> --}}
                        </ul>
                    </div>
                    {{-- @include('others.delete') --}}

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
        $('#othersTable').DataTable();
    } );
    </script>
 
@endsection

</div>
