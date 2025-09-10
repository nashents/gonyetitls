<div>

<section class="section">
    <x-loading/>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel">
                    <div class="panel-heading">
                        <div>
                            @include('includes.messages')
                        </div>
                    </div>
                    <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                        {{-- <div class="col-md-3" style="float: right; padding-right:0px">
                            <div class="form-group">
                                <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search drivers...">
                            </div>
                        </div> --}}
            <table  class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <caption>Breakdown Reports</caption>
            <thead >
                <th class="th-sm">Breakdown#
                </th>
                <th class="th-sm">Transporter
                </th>
                <th class="th-sm">Equipment
                </th>
                <th class="th-sm">Date
                </th>
                <th class="th-sm">Location
                </th>
                <th class="th-sm">Details
                </th>
                <th class="th-sm">Status
                </th>
                <th class="th-sm">Actions
                </th>
               
              </tr>
            </thead>

            <tbody>
                @forelse ($breakdowns as $breakdown)
            
              <tr>
                <td>{{$breakdown->breakdown_number}}</td>
                <td>{{$breakdown->transporter ? $breakdown->transporter->name : ""}}</td>
                <td>
                    @if ($breakdown->horse)
                        Horse: {{$breakdown->horse ? $breakdown->horse->registration_number : ""}} {{$breakdown->horse->horse_make ? $breakdown->horse->horse_make->name : ""}} {{$breakdown->horse->horse_model ? $breakdown->horse->horse_model->name : ""}}        
                    @endif
                </td>
                <td>{{$breakdown->date}}</td>
                <td>{{$breakdown->location}}</td>
                <td>{{$breakdown->description}}</td>      
                <td><span class="badge bg-{{$breakdown->status == 1 ? "warning" : "success"}}">{{$breakdown->status == 1 ? "Open" : "Closed"}}</span></td>      
               
                <td class="w-10 line-height-35 table-dropdown">
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-bars"></i>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="#" wire:click="showAssignment({{$breakdown->id}})"><i class="fa fa-plus color-success"></i> Assign</a></li>
                            <li><a href="#" wire:click="edit({{$breakdown->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                            <li><a href="#" data-toggle="modal" data-target="#breakdownDeleteModal{{$breakdown->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                        </ul>
                    </div>
                    @include('breakdowns.delete')

                </td>
         
            </tr>
            @empty
            <tr>
                <td colspan="8">
                    <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                        No Breakdown Reports Recorded ....
                    </div>
                   
                </td>
            </tr> 
            @endforelse
            </tbody>
          </table>
    
          <nav class="text-center" style="float: right">
            <ul class="pagination rounded-corners">
                @if (isset($breakdowns))
                    {{ $breakdowns->links() }} 
                @endif 
            </ul>
        </nav> 

        <!-- /.col-md-12 -->
    </div>
</div>
</div>
</div>
        <!-- /.row -->

    </div>
    <!-- /.container-fluid -->
</section>
</div>
