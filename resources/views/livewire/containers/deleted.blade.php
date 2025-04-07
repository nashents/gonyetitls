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
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">

                            <table id="containersTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th class="th-sm">Station#
                                        </th>
                                        <th class="th-sm">Name
                                        </th>
                                        <th class="th-sm">Fuel Type
                                        </th>
                                        <th class="th-sm">Quantity(l)
                                        </th>
                                        <th class="th-sm">Currency
                                        </th>
                                        <th class="th-sm">Rate
                                        </th>
                                        <th class="th-sm">Total($)
                                        </th>
                                        <th class="th-sm">Balance(l)
                                        </th>
                                        <th class="th-sm">Action
                                        </th>
                                      </tr>
                                </thead>
                                @if ($containers->count()>0)
                                <tbody>
                                    @foreach ($containers as $container)
                                  <tr>

                                    <td>{{$container->container_number}}</td>
                                    <td>{{$container->name}}</td>
                                    <td>{{$container->fuel_type}}</td>
                                    <td>{{$container->quantity}} Litres</td>
                                    <td>{{$container->currency ? $container->currency->name : "undefined"}}</td>
                                    <td>${{$container->rate}}</td>
                                    <td>${{$container->amount}}</td>
                                    <td>{{$container->balance}} Litres</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#"><i class="fa fa-refresh color-default"></i>Restore</a></li>
                                            </ul>
                                        </div>
                                        @include('containers.delete')
                                </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                 @endif
                              </table>

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

