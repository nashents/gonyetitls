<div>
    <section class="section">
        <x-loading/>
        <div class="top_up-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div>
                                @include('includes.messages')
                            </div>
                       
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="ordersTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Fuel Station#
                                    </th>
                                    <th class="th-sm">Station
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">Fuel Type
                                    </th>
                                    <th class="th-sm">Qty
                                    </th>
                                    <th class="th-sm">Currency
                                    </th>
                                    <th class="th-sm">Rate
                                    </th>
                                    <th class="th-sm">Amount
                                    </th>
                                    <th class="th-sm">Auth
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($top_ups->count()>0)
                                <tbody>
                                    @foreach ($top_ups as $top_up)
                                  <tr>
                                    <td>
                                        @if ($top_up->container)
                                             <a href="{{ route('containers.show',$top_up->container->id) }}" target="_blank" rel="noopener noreferrer" style="color: blue">{{ $top_up->container ? $top_up->container->container_number : "" }}</a>
                                        @endif
                                    </td>
                                    <td>{{$top_up->container ? $top_up->container->name : ""}}</td>
                                    <td>{{$top_up->date}}</td>
                                    <td>{{$top_up->fuel_type}}</td>
                                    <td>{{$top_up->quantity ? $top_up->quantity." Litres" : ""}}</td>
                                    <td>{{$top_up->currency ? $top_up->currency->name : ""}}</td>
                                    <td>
                                        @if ($top_up->rate)
                                             {{$top_up->currency ? $top_up->currency->symbol : ""}}{{number_format($top_up->rate,2)}}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($top_up->amount)
                                            {{$top_up->currency ? $top_up->currency->symbol : ""}}{{number_format($top_up->amount,2)}}        
                                        @endif
                                    </td>
                                    <td><span class="badge bg-{{($top_up->authorization == 'approved') ? 'success' : (($top_up->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($top_up->authorization == 'approved') ? 'approved' : (($top_up->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('top_ups.show',$top_up->id)}}"  ><i class="fa fa-eye color-default"></i> View</a></li>
                                             
                                            </ul>
                                        </div>
                                      
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
        <!-- /.top_up-fluid -->
    </section>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="top_upEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-gas-pump"></i> Edit Fuel Supplier <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >

                <div class="modal-body">
                    <div class="form-group">
                        <label for="container_id">Fuel Supplier</label>
                       <select wire:model.debounce.300ms="container_id" class="form-control" required disabled>
                           <option value="">Select Supplier</option>
                           @foreach ($containers as $container)
                            <option value="{{$container->id}}">{{$container->name}}</option>
                           @endforeach
                       </select>
                        @error('container_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="name"> Date</label>
                        <input type="date"  class="form-control" wire:model.debounce.300ms="date" placeholder="Enter TopUp date" required >
                        @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="fuel_type">Fuel Type</label>
                       <select wire:model.debounce.300ms="fuel_type" class="form-control" required>
                           <option value="">Select Fuel Type</option>
                            <option value="Petrol">Petrol</option>
                            <option value="Diesel">Diesel</option>
                       </select>
                        @error('fuel_type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="quantity" placeholder="Enter Quantity" required>
                        @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="rate">Rate</label>
                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="rate" placeholder="Enter Rate" required>
                        @error('rate') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="currency_id">Currency</label>
                       <select wire:model.debounce.300ms="currency_id" class="form-control" required>
                           <option value="">Select Currency</option>
                           @foreach ($currencies as $currency)
                            <option value="{{$currency->id}}">{{$currency->name}}</option>
                           @endforeach
                       </select>
                        @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="amount">Amount</label>
                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="amount" disabled required>
                        @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="balance">Balance</label>
                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="balance" disabled required>
                        @error('balance') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>

                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>



</div>

