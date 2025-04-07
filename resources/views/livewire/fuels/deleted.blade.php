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


            
                            <table id="fuelsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th class="th-sm">Order#
                                        </th>
                                        <th class="th-sm">Date
                                        </th>
                                        <th class="th-sm">Category
                                        </th>
                                        <th class="th-sm">Station
                                        </th>
                                        <th class="th-sm">FillUp
                                        </th>
                                        <th class="th-sm">Quantity
                                        </th>
                                        <th class="th-sm">Authorization
                                        </th>
                                        <th class="th-sm">Action
                                        </th>
                                      </tr>
                                </thead>
                                @if ($fuels->count()>0)
                                <tbody>
                                    @foreach ($fuels as $fuel)
                                    @if ($fuel->fillup == 1)
                                    <tr style="background-color: #4CAF50">
                                      <td>{{$fuel->order_number}}</td>
                                      <td>{{$fuel->date}}</td>
                                      <td>
                                        @if ($fuel->horse)
                                        Horse | {{$fuel->horse ? $fuel->horse->registration_number : ""}} {{$fuel->horse->horse_make ? $fuel->horse->horse_make->name : ""}} {{$fuel->horse->horse_model ? $fuel->horse->horse_model->name : ""}} 
                                        @elseif($fuel->asset)
                                        @if ($fuel->asset)
                                            Asset | {{$fuel->asset->product->brand ? $fuel->asset->product->brand->name : ""}} {{$fuel->asset->product ? $fuel->asset->product->name : ""}}
                                        @endif
                                        @elseif($fuel->vehicle) 
                                            Vehicle | {{  $fuel->vehicle ? $fuel->vehicle->registration_number : "" }} {{$fuel->vehicle->vehicle_make ? $fuel->vehicle->vehicle_make->name : ""}} {{$fuel->vehicle->vehicle_model ? $fuel->vehicle->vehicle_model->name : ""}} 
                                        @endif
                                      </td>
                                      <td>{{ucfirst($fuel->container ? $fuel->container->name : "")}}</td>
                                      <td>{{$fuel->fillup == "1" ? "Initial" : "Top Up"}}</td>
                                      <td>{{$fuel->quantity}}Litres</td>
                                      <td><span class="badge bg-{{($fuel->authorization == 'approved') ? 'success' : (($fuel->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($fuel->authorization == 'approved') ? 'approved' : (($fuel->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                      <td class="w-10 line-height-35 table-dropdown">
                                          <div class="dropdown">
                                              <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                  <i class="fa fa-bars"></i>
                                                  <span class="caret"></span>
                                              </button>
                                              <ul class="dropdown-menu">
                                                  <li><a href="#" wire:click.prevent="showRestore({{$fuel->id}})" ><i class="fa fa-refresh color-default"></i>Restore</a></li>
                                              </ul>
                                          </div>
                                          @include('fuels.delete')
                                  </td>
                                    </tr>
                                    @else
                                    <tr style="background-color: #FFC107">
                                      <td>{{$fuel->order_number}}</td>
                                      <td>{{$fuel->date}}</td>
                                      <td>
                                        @if ($fuel->horse)
                                        Horse | {{$fuel->horse ? $fuel->horse->registration_number : ""}} {{$fuel->horse->horse_make ? $fuel->horse->horse_make->name : ""}} {{$fuel->horse->horse_model ? $fuel->horse->horse_model->name : ""}} 
                                        @elseif($fuel->asset)
                                        @if ($fuel->asset)
                                            Asset | {{$fuel->asset->product->brand ? $fuel->asset->product->brand->name : ""}} {{$fuel->asset->product ? $fuel->asset->product->name : ""}}
                                        @endif
                                        @elseif($fuel->vehicle) 
                                            Vehicle | {{  $fuel->vehicle ? $fuel->vehicle->registration_number : "" }} {{$fuel->vehicle->vehicle_make ? $fuel->vehicle->vehicle_make->name : ""}} {{$fuel->vehicle->vehicle_model ? $fuel->vehicle->vehicle_model->name : ""}} 
                                        @endif
                                    </td>
                                      <td>{{ucfirst($fuel->container ? $fuel->container->name : "")}}</td>
                                      <td>{{$fuel->fillup == "1" ? "Initial" : "Top Up"}}</td>
                                      <td>{{$fuel->quantity}}Litres</td>
                                      <td><span class="badge bg-{{($fuel->authorization == 'approved') ? 'success' : (($fuel->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($fuel->authorization == 'approved') ? 'approved' : (($fuel->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                      <td class="w-10 line-height-35 table-dropdown">
                                          <div class="dropdown">
                                              <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                  <i class="fa fa-bars"></i>
                                                  <span class="caret"></span>
                                              </button>
                                              <ul class="dropdown-menu">
                                                <li><a href="#" wire:click.prevent="showRestore({{$fuel->id}})" ><i class="fa fa-refresh color-default"></i>Restore</a></li>
                                              </ul>
                                          </div>
                                          @include('fuels.delete')
                                  </td>
                                    </tr>
                                    @endif
                                  @endforeach
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                 @endif
                              </table>
                              <nav class="text-center" style="float: right">
                                <ul class="pagination rounded-corners">
                                    @if (isset($fuels))
                                        {{ $fuels->links() }} 
                                    @endif 
                                </ul>
                            </nav>  
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>

    <div data-backdrop="static" data-keyboard="false" class="modal fade" id="fuelRestoreModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                   <center> <strong>Are you sure you want to restore this Fuel Order?</strong> </center>
                </div>
                <form wire:submit.prevent="update()">
                <div class="modal-footer no-border">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fas fa-undo"></i> Restore</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>

</div>
