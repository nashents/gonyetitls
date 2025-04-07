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
                            <div class="panel-title">
                                <form wire:submit.prevent="search()" class="p-20" enctype="multipart/form-data">

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="from">From</label>
                                                <input type="date" class="form-control" wire:model.debounce.300ms="from" placeholder="From Date">
                                                @error('from') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="to">To</label>
                                                <input type="date" class="form-control" wire:model.debounce.300ms="to" placeholder="To Date">
                                                @error('to') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        <!-- /.col-md-6 -->
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="btn-group pull-right mt-10" >
                                               <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                                                <button type="submit" class="btn bg-success btn-wide btn-rounded" > <i class="fa fa-save"></i>Generate Report</button>
                                            </div>
                                        </div>
                                        </div>
                                </form>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="containersTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>

                                    <th class="th-sm">#
                                    </th>
                                    <th class="th-sm">Container #
                                    </th>
                                    <th class="th-sm">Vendor
                                    </th>
                                    <th class="th-sm">Name
                                    </th>
                                    <th class="th-sm">Email
                                    </th>
                                    <th class="th-sm">Phone #
                                    </th>
                                    <th class="th-sm">Fuel Type
                                    </th>
                                    <th class="th-sm">Capacity(Litres)
                                    </th>
                                    <th class="th-sm">Quantity(Litres)
                                    </th>
                                    <th class="th-sm">Rate
                                    </th>
                                    <th class="th-sm">Amount($)
                                    </th>
                                    <th class="th-sm">Currency
                                    </th>
                                    <th class="th-sm">Balance(Litres)
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($search))
                                @if ($containers->count()>0)
                                <tbody>
                                    @foreach ($containers as $container)
                                  <tr>
                                  <td>{{$container->id}}</td>
                                    <td>{{$container->container_number}}</td>
                                    <td>{{$container->vendor->name}}</td>
                                    <td>{{$container->name}}</td>
                                    <td>{{$container->email}}</td>
                                    <td>{{$container->phonenumber}}</td>
                                    <td>{{$container->fuel_type}}</td>
                                    <td>{{$container->capacity}}l</td>
                                    <td>{{$container->quantity}}l</td>
                                    <td>${{$container->rate}}</td>
                                    <td>${{$container->amount}}</td>
                                    <td>{{$container->currency->name}}</td>
                                    <td>{{$container->balance}}l</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#"  wire:click="edit({{$container->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#containerDeleteModal{{ $container->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
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

